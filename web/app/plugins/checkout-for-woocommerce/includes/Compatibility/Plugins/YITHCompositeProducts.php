<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

class YITHCompositeProducts {
	public function init() {
		add_filter( 'cfw_cart_item_row_class', array( $this, 'maybe_add_class_to_composite_items' ), 10, 2 );
	}

	public function maybe_add_class_to_composite_items( $classes, $cart_item ): string {
		if ( ! defined( 'YITH_WCP_VERSION' ) ) {
			return $classes;
		}

		if ( ! isset( $cart_item['yith_wcp_child_component_data'] ) ) {
			return '';
		}

		return $classes . ' yith-composite-product-component';
	}
}
