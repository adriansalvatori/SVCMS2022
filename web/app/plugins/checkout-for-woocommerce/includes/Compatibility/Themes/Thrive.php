<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use TCB\Integrations\WooCommerce\Hooks;
use Thrive\Theme\Integrations\WooCommerce\Filters;

class Thrive extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'thrive_theme_builder_requirements' );
	}

	public function run() {
		remove_filter( 'woocommerce_checkout_fields', array( Filters::class, 'alter_billing_fields' ) );
		remove_action( 'wp_enqueue_scripts', array( Hooks::class, 'enqueue_scripts' ), PHP_INT_MAX );
	}
}
