<?php

namespace Objectiv\Plugins\Checkout;

class PhpErrorOutputSuppressor {
	public function __construct() {}

	public function init() {
		add_action( 'plugins_loaded', array( $this, 'suppress_error_output' ) );
	}

	public function suppress_error_output() {
		/**
		 * Legacy Error Suppression
		 *
		 * In CheckoutWC 4.x and prior, we accidentally turned off display errors for all sites
		 *
		 * In order to avoid issues with warnings now showing up after update, we are keeping this behavior but putting it behind a filter
		 */
		if ( apply_filters( 'cfw_legacy_suppress_php_errors_output', ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) ) {
			ini_set( 'display_errors', 'Off' );
		}
	}
}