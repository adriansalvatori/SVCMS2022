<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class PixelCaffeine extends CompatibilityAbstract {
	public function is_available(): bool {
		if ( class_exists( '\\PixelCaffeine' ) && class_exists( '\\AEPC_Pixel_Scripts' ) && class_exists( '\\AEPC_Woocommerce_Addon_Support' ) ) {
			return true;
		}

		return false;
	}

	public function run() {
		add_filter( 'cfw_body_classes', array( $this, 'add_pixel_caffeine_body_class' ) );
	}

	public function add_pixel_caffeine_body_class( $classes ) {
		$classes[] = 'woocommerce-page';

		return $classes;
	}
}
