<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WooCommerceProductBundles extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'WC_PB_Cart' );
	}

	public function pre_init() {
		add_action( 'cfw_order_bump_add_to_cart_product_type_bundle', array( $this, 'bundle_add_to_cart' ), 10, 6 );
	}

	function bundle_add_to_cart( $product_id, $quantity, $variation_id, $variation_data, $metadata, $product ) {
		$configuration = $this->get_default_attributes( $product );
		\WC_PB_Cart::instance()->add_bundle_to_cart( $product_id, $quantity, $configuration, $metadata );
	}

	function get_default_attributes( $product ): array {
		$configuration = array();

		/** @var \WC_PB_Cart $parsed */
		$parsed = \WC_PB_Cart::instance()->parse_bundle_configuration( $product );

		if ( empty( $parsed ) ) {
			return $configuration;
		}

		foreach ( $parsed as $item_id => $item_configuration ) {
			$product = wc_get_product( $item_configuration['product_id'] );

			if ( ! $product->is_type( 'variable' ) ) {
				continue;
			}

			$default_attributes                        = $product->get_default_attributes();
			$configuration[ $item_id ]                 = $item_configuration;
			$configuration[ $item_id ]['attributes']   = array();
			$configuration[ $item_id ]['variation_id'] = cfw_get_variation_id_from_attributes( $product, $default_attributes );

			foreach ( $default_attributes as $attribute_name => $attribute_value ) {
				$configuration[ $item_id ]['attributes'][ wc_variation_attribute_name( $attribute_name ) ] = $attribute_value;
			}
		}

		return $configuration;
	}
}
