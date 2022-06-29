<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class SkyVergeCheckoutAddons extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'init_woocommerce_checkout_add_ons' ) || class_exists( '\\WC_Checkout_Add_Ons_Loader' );
	}

	public function run() {
		add_filter( 'wc_checkout_add_ons_position', array( $this, 'set_checkout_add_ons_position' ) );
		add_filter( 'cfw_non_floating_label_field_types', array( $this, 'add_non_floating_label_field_types' ) );
		add_filter( 'cfw_checkbox_like_field_types', array( $this, 'add_checkbox_like_field_types' ) );
	}

	/**
	 * @param array $types
	 * @return array
	 */
	public function add_non_floating_label_field_types( array $types ): array {
		$types[] = 'wc_checkout_add_ons_multicheckbox';
		$types[] = 'wc_checkout_add_ons_radio';
		$types[] = 'wc_checkout_add_ons_file';
		$types[] = 'wc_checkout_add_ons_checkbox';

		return $types;
	}

	public function add_checkbox_like_field_types( array $types ): array {
		$types[] = 'wc_checkout_add_ons_radio';
		$types[] = 'wc_checkout_add_ons_checkbox';

		return $types;
	}

	public function set_checkout_add_ons_position(): string {
		return 'cfw_checkout_before_payment_method_terms_checkbox';
	}
}
