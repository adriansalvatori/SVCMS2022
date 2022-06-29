<?php

namespace Objectiv\Plugins\Checkout\Model\Bumps;

class CategoriesBump extends BumpAbstract {
	public function is_displayable(): bool {
		if ( ! $this->can_offer_product_be_added_to_the_cart() ) {
			return false;
		}

		if ( $this->quantity_of_product_in_cart( $this->offer_product ) ) {
			return false;
		}

		return $this->cart_contains_normal_product_of_categories();
	}

	public function is_cart_bump_valid(): bool {
		/**
		 * Filters promo code button label
		 *
		 * @param string $is_cart_bump_valid Whether the categories bump in the cart is still valid
		 * @since 6.3.0
		 */
		return apply_filters( 'cfw_categories_bump_is_cart_bump_valid', $this->cart_contains_normal_product_of_categories(), $this );
	}

	protected function cart_contains_normal_product_of_categories(): bool {
		foreach ( $this->categories as $category ) {
			if ( $this->quantity_of_normal_cart_items_in_category( $category ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $needle_category_slug
	 * @return int
	 */
	public function quantity_of_normal_cart_items_in_category( string $needle_category_slug ): int {
		$needle_category = get_term_by( 'slug', $needle_category_slug, 'product_cat' );

		if ( ! $needle_category ) {
			return 0;
		}

		$found = 0;

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( isset( $cart_item['_cfw_order_bump_id'] ) ) {
				continue;
			}

			$cart_item_terms = wp_get_post_terms( $cart_item['product_id'], 'product_cat' );

			/** @var \WP_Term $cart_item_term */
			foreach ( $cart_item_terms as $cart_item_term ) {
				if ( $cart_item_term->slug === $needle_category_slug ) {
					$found++;
				}
			}
		}

		return $found;
	}
}
