<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

class Square extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\WooCommerce_Square_Loader' );
	}

	public function pre_init() {
		/**
		 * Filters whether to override Stripe payment request button heights
		 *
		 * @since 4.3.3
		 *
		 * @param bool $allow Whether to ignore shipping phone requirement during payment requests
		 */
		if ( apply_filters( 'cfw_square_payment_requests_ignore_shipping_phone', true ) ) {
			add_action( 'wc_ajax_square_digital_wallet_process_checkout', array( $this, 'process_payment_request_ajax_checkout' ), 1 );
		}
	}

	public function run() {
		add_action( 'cfw_checkout_before_order_review_container', array( $this, 'render_error_receiver_stub' ) );
		add_action( 'wp', array( $this, 'payment_request_buttons' ), 100 );
	}

	public function payment_request_buttons() {
		$instance = cfw_get_hook_instance_object( 'woocommerce_before_checkout_form', 'render_button', 15 );

		if ( ! $instance ) {
			return;
		}

		remove_action( 'woocommerce_before_checkout_form', array( $instance, 'render_button' ), 15 );
		add_action( 'cfw_payment_request_buttons', array( $instance, 'render_button' ), 1 );
		add_action( 'cfw_after_payment_request_buttons', 'cfw_add_separator', 11 );
	}

	public function render_error_receiver_stub() {
		?>
		<div class="shop_table cart" style="display: none"></div>
		<?php
	}

	public function process_payment_request_ajax_checkout() {
		if ( ! $this->is_available() ) {
			return;
		}

		$payment_request_type = isset( $_POST['wc-square-digital-wallet-type'] ) ? wc_clean( $_POST['wc-square-digital-wallet-type'] ) : '';

		// Disable shipping phone validation when using payment request
		if ( ! empty( $payment_request_type ) ) {
			add_filter(
				'woocommerce_checkout_fields',
				function( $fields ) {
					if ( isset( $fields['shipping']['shipping_phone'] ) ) {
						$fields['shipping']['shipping_phone']['required'] = false;
						$fields['shipping']['shipping_phone']['validate'] = array();
					}

					if ( 'yes' === SettingsManager::instance()->get_setting( 'use_fullname_field' ) ) {
						unset( $fields['shipping']['shipping_full_name'] );
						unset( $fields['billing']['billing_full_name'] );
					}

					if ( 'yes' === SettingsManager::instance()->get_setting( 'enable_discreet_address_1_fields' ) ) {
						unset( $fields['shipping']['shipping_house_number'] );
						unset( $fields['billing']['billing_house_number'] );
						unset( $fields['shipping']['shipping_street_name'] );
						unset( $fields['billing']['billing_street_name'] );
					}

					return $fields;
				},
				1
			);
		}
	}

	/**
	 * @param array $compatibility
	 *
	 * @return array
	 */
	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'Square',
			'params' => array(),
		);

		return $compatibility;
	}
}
