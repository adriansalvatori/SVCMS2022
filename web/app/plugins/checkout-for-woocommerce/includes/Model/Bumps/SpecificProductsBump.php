<?php

namespace Objectiv\Plugins\Checkout\Model\Bumps;

use Objectiv\Plugins\Checkout\Model\MatchedVariationResult;
use WC_Product_Variation;

class SpecificProductsBump extends BumpAbstract {
	public function is_displayable(): bool {
		$offer_product = $this->get_offer_product();

		if ( ! $this->can_offer_product_be_added_to_the_cart() ) {
			return false;
		}

		if ( $this->quantity_of_product_in_cart( $offer_product->get_id() ) ) {
			return false;
		}

		// If we are variation matching, make sure we have matched variations
		if ( $offer_product->is_type( 'variable' ) && ! $this->get_matched_variation_attributes_from_cart_search_product( $offer_product )->get_id() ) {
			return false;
		}

		// Is it a valid upsell (setup correctly) and are there enough units of the offer product to match the cart product?
		// TODO: Technically this disallows upsell offer products that are backordered. Bug or feature? YOU DECIDE.
		if ( $this->is_valid_upsell() && $offer_product->get_manage_stock() && $this->quantity_of_normal_product_in_cart( array_values( $this->products )[0] ) > $offer_product->get_stock_quantity() ) {
			return false;
		}

		$matching_products_in_cart = 0;

		// Count matching products in the cart
		foreach ( $this->products as $product ) {
			if ( $this->quantity_of_normal_product_in_cart( (int) $product ) ) {
				$matching_products_in_cart++;
			}
		}

		// If all products must match and we have fewer products in the cart than in our matching list, return false
		if ( ! $this->any_product && count( $this->products ) > $matching_products_in_cart ) {
			return false;
		}

		// If we get here, matching rule is set to any product, so we can
		// use the number of matching products to determine if we have a match
		return boolval( $matching_products_in_cart );
	}

	public function is_cart_bump_valid(): bool {
		$offer_product = $this->get_offer_product();

		if ( $this->is_valid_upsell() && $this->is_in_cart() ) {
			return true;
		}

		// Is it a valid upsell (setup correctly) and are there enough units of the offer product to match the cart product?
		if ( $this->is_valid_upsell() && $this->quantity_of_product_in_cart( array_values( $this->products )[0] ) > $offer_product->get_stock_quantity() ) {
			return false;
		}

		$matching_products_in_cart = 0;

		// Count matching products in the cart
		foreach ( $this->products as $product ) {
			if ( $this->quantity_of_normal_product_in_cart( (int) $product ) ) {
				$matching_products_in_cart++;
			}
		}

		// If all products must match and we have fewer products in the cart than in our matching list, return false
		if ( ! $this->any_product && count( $this->products ) > $matching_products_in_cart ) {
			return false;
		}

		// If we get here, matching rule is set to any product, so we can
		// use the number of matching products to determine if we have a match
		return boolval( $matching_products_in_cart );
	}

	/**
	 * @throws \Exception
	 */
	public function add_to_cart( \WC_Cart $cart ) {
		$product          = $this->get_offer_product();
		$discounted_price = $this->get_offer_product_sale_price();
		$variation_id     = $product->is_type( 'variable' ) ? $product->get_id() : null;
		$product_id       = $product->get_id();
		$variation_data   = null;
		$metadata         = array(
			'cfw_order_bump_price' => strval( $discounted_price ),
			'_cfw_order_bump_id'   => $this->id,
		);

		if ( $product->is_type( 'variation' ) ) {
			$variation_data = array();
			foreach ( $product->get_variation_attributes() as $taxonomy => $term_names ) {
				$taxonomy                                = str_replace( 'attribute_', '', $taxonomy );
				$attribute_label_name                    = str_replace( 'attribute_', '', wc_attribute_label( $taxonomy ) );
				$variation_data[ $attribute_label_name ] = $term_names;
			}
		}

		// Attempt to match variation attributes to search product
		if ( $product->is_type( 'variable' ) ) {
			$matched_variation_data = $this->get_matched_variation_attributes_from_cart_search_product( $product );

			if ( ! empty( $matched_variation_data ) ) {
				$variation_id   = $matched_variation_data->get_id();
				$variation_data = $matched_variation_data->get_attributes();
			}
		}

		$quantity = $this->get_offer_quantity();

		if ( $this->is_valid_upsell() ) {
			$search_product = array_values( $this->get_products() )[0];
			$quantity       = $this->quantity_of_product_in_cart( $search_product );

			$this->remove_product_from_cart( $search_product );
		}

		do_action( 'cfw_before_order_bump_add_to_cart', $this );

		if ( has_action( 'cfw_order_bump_add_to_cart_product_type_' . $product->get_type() ) ) {
			do_action( 'cfw_order_bump_add_to_cart_product_type_' . $product->get_type(), $product_id, $quantity, $variation_id, $variation_data, $metadata, $product );

			return true;
		}

		return $cart->add_to_cart( $product_id, $quantity, $variation_id, $variation_data, $metadata );
	}

	protected function get_cart_item_for_product( $search_product_id ) {
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( $this->cart_item_is_product( $cart_item, $search_product_id ) ) {
				return $cart_item;
			}
		}

		return array();
	}

	protected function get_matched_variation_attributes_from_cart_search_product( \WC_Product $offer_product ): MatchedVariationResult {
		$variation_data = null;

		if ( ! $offer_product->is_type( 'variable' ) || count( $this->products ) !== 1 ) {
			return new MatchedVariationResult();
		}

		// Attempt to match variation attributes to search product
		/** @var WC_Product_Variation $search_product */
		$search_product_id = $this->products[0];
		$cart_item         = $this->get_cart_item_for_product( $search_product_id );
		$variation_id      = cfw_get_variation_id_from_attributes( $offer_product, $cart_item['variation'] ?? array() );

		if ( empty( $cart_item ) || empty( $variation_id ) ) {
			return new MatchedVariationResult();
		}

		foreach ( $cart_item['variation'] as $taxonomy => $term_names ) {
			$taxonomy                                = str_replace( 'attribute_', '', $taxonomy );
			$attribute_label_name                    = str_replace( 'attribute_', '', wc_attribute_label( $taxonomy ) );
			$variation_data[ $attribute_label_name ] = $term_names;
		}

		return new MatchedVariationResult( $variation_id, $variation_data );
	}
}
