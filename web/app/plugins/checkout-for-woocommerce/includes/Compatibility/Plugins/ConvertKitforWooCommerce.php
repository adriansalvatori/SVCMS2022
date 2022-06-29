<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class ConvertKitforWooCommerce extends CompatibilityAbstract {
	/** @var \CKWC_Integration */
	protected $plugin_instance;

	public function is_available(): bool {
		return defined( 'CKWC_VERSION' );
	}

	public function run() {
		$this->plugin_instance = cfw_get_hook_instance_object( 'woocommerce_checkout_fields', 'add_opt_in_checkbox' );

		if ( $this->plugin_instance ) {
			remove_filter( 'woocommerce_checkout_fields', array( $instance, 'add_opt_in_checkbox' ) );
			add_action( 'woocommerce_after_checkout_billing_form', array( $this, 'output_convertkit_checkbox' ) );
		}
	}

	public function output_convertkit_checkbox() {
		$field = $this->plugin_instance->add_opt_in_checkbox( array() );

		woocommerce_form_field( 'ckwc_opt_in', $field['billing']['ckwc_opt_in'] );
	}
}
