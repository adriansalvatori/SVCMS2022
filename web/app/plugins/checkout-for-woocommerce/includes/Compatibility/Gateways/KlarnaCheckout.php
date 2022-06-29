<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

class KlarnaCheckout extends CompatibilityAbstract {

	protected $klarna = null;

	protected $klarna_gateway = null;

	protected $klarna_id = 'kco';

	public function is_available(): bool {
		return defined( 'KCO_WC_VERSION' );
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'KlarnaCheckout',
			'event'  => 'before-setup',
			'params' => array(
				'showEasyTabs' => ! $this->is_klarna_payment_selected(),
			),
		);

		return $compatibility;
	}

	public function run_immediately() {
		if ( $this->is_klarna_payment_selected() ) {
			add_filter( 'woocommerce_checkout_fields', array( $this, 'unrequire_shipping_phone' ), 100000, 1 );
		}
	}

	public function run() {
		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
		$klarna_gateway     = $available_gateways[ $this->klarna_id ] ? $available_gateways[ $this->klarna_id ] : null;

		// If the gateway is not null
		if ( $klarna_gateway ) {
			$this->klarna_gateway = $klarna_gateway;
		}

		add_filter( 'cfw_load_checkout_template', array( $this, 'detect_confirmation_page' ), 10, 1 );
		add_action( 'cfw_checkout_loaded_pre_head', array( $this, 'klarna_template_hooks' ), 10 );
	}

	public function is_klarna_payment_selected(): bool {
		return function_exists( 'kco_wc_get_selected_payment_method' ) && kco_wc_get_selected_payment_method() === $this->klarna_id;
	}

	public function klarna_template_hooks() {
		if ( $this->is_klarna_payment_selected() ) {
			$klarna_checkout_for_woocommerce_templates = \KCO_Templates::get_instance();

			remove_action( 'wp_footer', array( $klarna_checkout_for_woocommerce_templates, 'check_that_kco_template_has_loaded' ) );

			add_filter( 'cfw_replace_form', '__return_true' );
			add_action( 'cfw_checkout_form', array( $this, 'klarna_checkout_form' ) );
		} elseif ( apply_filters( 'cfw_show_klarna_checkout_express_button', true ) ) {
			add_action( 'cfw_after_payment_request_buttons', 'cfw_add_separator', 11 );
			add_action( 'cfw_payment_request_buttons', array( $this, 'add_klarna_pay_button' ) );
		}
	}

	public function klarna_checkout_form() {
		wc_get_template( 'checkout/form-checkout.php', array( 'checkout' => WC()->checkout() ) );
	}

	public function add_klarna_pay_button() {
		?>
		<button id="klarna-pay-button" class="klarna-pay-button">
			<span>Pay With</span>
			<svg id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 452.9 101.1" style="enable-background:new 0 0 452.9 101.1;" xml:space="preserve">
<path d="M79.7,0H57.4c0,18.3-8.4,35-23,46l-8.8,6.6l34.2,46.6h28.1L56.4,56.3C71.3,41.5,79.7,21.5,79.7,0z"/>
				<rect width="22.8" height="99.2"/>
				<rect x="94.5" width="21.5" height="99.2"/>
				<path d="M304.6,28.7c-8.2,0-16,2.5-21.2,9.6v-7.7H263v68.6h20.7v-36c0-10.4,7-15.5,15.4-15.5c9,0,14.2,5.4,14.2,15.4v36.2h20.5V55.6
	C333.8,39.6,321.1,28.7,304.6,28.7z"/>
				<path d="M181,30.6V35c-5.8-4-12.8-6.3-20.4-6.3c-20,0-36.2,16.2-36.2,36.2s16.2,36.2,36.2,36.2c7.6,0,14.6-2.3,20.4-6.3v4.4h20.5
	V30.6H181z M162.3,82.5c-10.3,0-18.6-7.9-18.6-17.6s8.3-17.6,18.6-17.6c10.3,0,18.6,7.9,18.6,17.6S172.6,82.5,162.3,82.5z"/>
				<path d="M233.3,39.5v-8.9h-21v68.6h21.1v-32c0-10.8,11.7-16.6,19.8-16.6c0.1,0,0.2,0,0.2,0v-20C245.1,30.6,237.4,34.2,233.3,39.5z"
				/>
				<path d="M397.6,30.6V35c-5.8-4-12.8-6.3-20.4-6.3c-20,0-36.2,16.2-36.2,36.2s16.2,36.2,36.2,36.2c7.6,0,14.6-2.3,20.4-6.3v4.4h20.5
	V30.6H397.6z M378.9,82.5c-10.3,0-18.6-7.9-18.6-17.6s8.3-17.6,18.6-17.6c10.3,0,18.6,7.9,18.6,17.6
	C397.6,74.6,389.2,82.5,378.9,82.5z"/>
				<g>
					<path d="M434,32.6c0-1-0.7-1.6-1.8-1.6h-1.9v5.2h0.9v-1.9h1l0.8,1.9h1l-0.9-2.1C433.7,33.8,434,33.3,434,32.6z M432.2,33.4h-1v-1.6
		h1c0.6,0,0.9,0.3,0.9,0.8S432.9,33.4,432.2,33.4z"/>
					<path d="M431.9,28.8c-2.7,0-4.9,2.2-4.9,4.9c0.1,2.7,2.2,4.9,4.9,4.9s4.9-2.2,4.9-4.9C436.8,31,434.6,28.8,431.9,28.8z M431.9,37.7
		c-2.2,0-3.9-1.8-3.9-4c0-2.2,1.8-4,3.9-4c2.2,0,3.9,1.8,3.9,4C435.8,35.9,434,37.7,431.9,37.7z"/>
				</g>
				<path d="M440,74.9c-7.1,0-12.9,5.8-12.9,12.9c0,7.1,5.8,12.9,12.9,12.9c7.1,0,12.9-5.8,12.9-12.9C452.9,80.6,447.1,74.9,440,74.9z"
				/>
</svg>
		</button>
		<?php
	}

	public function detect_confirmation_page( $load ): bool {
		if ( ! empty( $_GET['confirm'] ) && ! empty( $_GET['kco_wc_order_id'] ) ) {
			return false;
		}

		return $load;
	}

	/**
	 * @param array $fields
	 *
	 * @return array
	 */
	public function unrequire_shipping_phone( $fields ) {
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
	}
}
