<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use _WP_Dependency;
use Objectiv\Plugins\Checkout\Admin\Pages\PageAbstract;
use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

class ThemeHighCheckoutFieldEditorPro extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'THWCFE_VERSION' );
	}

	public function pre_init() {
		add_action( 'cfw_admin_integrations_settings', array( $this, 'admin_integration_settings' ) );
	}

	public function run() {
		add_filter( 'thwcfe_public_script_deps', array( $this, 'cleanup_select_woo' ), 1000 );

		// Stop modifying address fields
		$hp_cf    = apply_filters( 'thwcfd_woocommerce_checkout_fields_hook_priority', 1000 );
		$instance = cfw_get_hook_instance_object( 'woocommerce_billing_fields', 'woo_billing_fields', $hp_cf );

		if ( $instance && SettingsManager::instance()->get_setting( 'allow_thcfe_address_modification' ) !== 'yes' ) {
			remove_filter( 'woocommerce_billing_fields', array( $instance, 'woo_billing_fields' ), $hp_cf );
			remove_filter( 'woocommerce_shipping_fields', array( $instance, 'woo_shipping_fields' ), $hp_cf );
		}
	}

	public function cleanup_select_woo( $deps ) {
		$key = array_search( 'selectWoo', $deps, true );

		if ( $key ) {
			unset( $deps[ $key ] );
		}

		return $deps;
	}

	/**
	 * @param PageAbstract $integrations
	 */
	public function admin_integration_settings( PageAbstract $integrations ) {
		if ( ! $this->is_available() ) {
			return;
		}

		$integrations->output_checkbox_row(
			'allow_thcfe_address_modification',
			cfw__( 'Enable ThemeHigh Checkout Field Editor address field overrides.', 'checkout-wc' ),
			cfw__( 'Allow ThemeHigh Checkout Field Editor to modify billing and shipping address fields. (Not Recommended)', 'checkout-wc' )
		);
	}
}
