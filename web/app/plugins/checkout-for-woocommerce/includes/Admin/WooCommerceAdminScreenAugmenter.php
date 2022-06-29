<?php

namespace Objectiv\Plugins\Checkout\Admin;

use Objectiv\Plugins\Checkout\Managers\SettingsManager;

class WooCommerceAdminScreenAugmenter {
	public function __construct() {}

	public function init() {
		add_action( 'woocommerce_sections_account', array( $this, 'output_woocommerce_account_settings_notice' ) );
		add_action( 'woocommerce_sections_products', array( $this, 'output_woocommerce_account_settings_notice' ) );
		add_filter( 'woocommerce_get_settings_account', array( $this, 'mark_possibly_overridden_account_settings' ), 10, 1 );
		add_filter( 'woocommerce_get_settings_products', array( $this, 'mark_possibly_overridden_product_settings' ), 10, 1 );
	}

	public function output_woocommerce_account_settings_notice() {
		?>
		<div id="message" class="updated woocommerce-message inline">
			<p>
				<strong><?php cfw_e( 'CheckoutWC:' ); ?></strong>
				<?php cfw_e( 'Settings marked with asterisks (**) may be overridden on the checkout page based on your Login and Registration settings. (CheckoutWC > Checkout)' ); ?>
			</p>
		</div>
		<?php
	}

	public function output_woocommerce_product_settings_notice() {
		if ( SettingsManager::instance()->get_setting( 'enable_side_cart' ) !== 'yes' ) {
			return;
		}
		?>
		<div id="message" class="updated woocommerce-message inline">
			<p>
				<strong><?php cfw_e( 'CheckoutWC:' ); ?></strong>
				<?php cfw_e( 'Settings marked with asterisks (**) may be overridden based on your Side Cart settings. (CheckoutWC > Side Cart)' ); ?>
			</p>
		</div>
		<?php
	}

	public function mark_possibly_overridden_account_settings( array $settings ): array {
		foreach ( $settings as $key => $setting ) {
			if ( 'woocommerce_registration_generate_username' === $setting['id'] || 'woocommerce_registration_generate_password' === $setting['id'] ) {
				$settings[ $key ]['desc'] = "{$setting['desc']} **";
			}
		}

		return $settings;
	}

	public function mark_possibly_overridden_product_settings( array $settings ): array {
		foreach ( $settings as $key => $setting ) {
			if ( 'woocommerce_enable_ajax_add_to_cart' === $setting['id'] || 'woocommerce_cart_redirect_after_add' === $setting['id'] ) {
				$settings[ $key ]['desc'] = "{$setting['desc']} **";
			}
		}

		return $settings;
	}
}
