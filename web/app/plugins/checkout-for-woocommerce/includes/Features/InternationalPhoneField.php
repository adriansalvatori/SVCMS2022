<?php

namespace Objectiv\Plugins\Checkout\Features;

use Objectiv\Plugins\Checkout\Admin\Pages\PageAbstract;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 */
class InternationalPhoneField extends FeaturesAbstract {
	protected function run_if_cfw_is_enabled() {
		if ( 'required' === get_option( 'woocommerce_checkout_phone_field', 'required' ) ) {
			add_filter( 'cfw_get_billing_checkout_fields', array( $this, 'add_billing_phone_custom_validator' ) );
			add_filter( 'cfw_get_shipping_checkout_fields', array( $this, 'add_shipping_phone_custom_validator' ) );
		}

		add_filter( 'woocommerce_default_address_fields', array( $this, 'shim_hidden_phone_formatted_phone_field' ) );
		add_filter( 'cfw_event_data', array( $this, 'add_localized_settings' ) );
		add_action( 'cfw_before_process_checkout', array( $this, 'override_phone_numbers' ) );
	}

	public function shim_hidden_phone_formatted_phone_field( $fields ): array {
		$fields['phone_formatted'] = array(
			'type'     => 'hidden',
			'priority' => 1000,
			'required' => false,
		);

		return $fields;
	}

	/**
	 * @param array $event_data
	 * @return array
	 */
	public function add_localized_settings( array $event_data ): array {
		$format = $this->settings_getter->get_setting( 'international_phone_field_standard' );

		$event_data['settings']['enable_international_phone_field']                 = true;
		$event_data['settings']['international_phone_field_standard']               = $format ? $format : 'raw';
		$event_data['settings']['allow_international_phone_field_country_dropdown'] = apply_filters( 'cfw_allow_international_phone_field_country_dropdown', true );

		return $event_data;
	}

	public function init() {
		parent::init();

		add_action( 'cfw_do_plugin_activation', array( $this, 'run_on_plugin_activation' ) );
		add_action( 'cfw_after_admin_page_field_options_section', array( $this, 'output_admin_setting' ) );
	}

	public function output_admin_setting( PageAbstract $checkout_admin_page ) {
		if ( ! $this->available ) {
			$notice = $checkout_admin_page->get_upgrade_required_notice( $this->required_plans_list );
		}

		$checkout_admin_page->output_checkbox_row(
			'enable_international_phone_field',
			cfw__( 'Enable International Phone Field', 'checkout-wc' ),
			cfw__( 'Validate phone number entry based on selected country. Replaces phone field placeholder with example phone number. Stores phone number according to International Phone Format.', 'checkout-wc' ),
			array(
				'enabled' => $this->available,
				'notice'  => $notice ?? '',
			)
		);

		$checkout_admin_page->output_radio_group_row(
			'international_phone_field_standard',
			'International Phone Format',
			cfw__( 'Choose how to store phone numbers.' ),
			'raw',
			array(
				'raw'           => 'Raw Value (No Formatting)',
				'E164'          => 'E164',
				'INTERNATIONAL' => 'International',
				'NATIONAL'      => 'National',
				'RFC3966'       => 'RFC3966',
			),
			array(
				'raw'           => cfw__( 'The number is stored exactly how the user entered it.' ),
				'E164'          => cfw__( 'Format phone number with E164 standard.' ),
				'INTERNATIONAL' => cfw__( 'Format phone number with RFC3966 standard without the tel: prefix' ),
				'NATIONAL'      => cfw__( 'Format phone number based on selected country. US Example (555) 555 - 5555, UK Example: 07911 123457' ),
				'RFC3966'       => cfw__( 'Format phone number with RFC3966 standard.' ),
			),
			array(
				'enabled' => $this->available,
				'notice'  => $notice ?? null,
				'nested'  => true,
			)
		);
	}

	public function run_on_plugin_activation() {
		SettingsManager::instance()->add_setting( 'enable_international_phone_field', 'no' );
	}

	public function override_phone_numbers() {
		if ( ! empty( $_POST['shipping_phone_formatted'] ) ) {
			$_POST['shipping_phone'] = $_POST['shipping_phone_formatted'];
		}

		if ( ! empty( $_POST['billing_phone_formatted'] ) ) {
			$_POST['billing_phone'] = $_POST['billing_phone_formatted'];
		}
	}

	public function add_billing_phone_custom_validator( $fields ): array {
		if ( isset( $fields['billing_phone'] ) ) {
			$fields['billing_phone']['custom_attributes']['data-parsley-valid-international-phone'] = 'billing';
		}

		return $fields;
	}

	public function add_shipping_phone_custom_validator( $fields ): array {
		if ( isset( $fields['shipping_phone'] ) ) {
			$fields['shipping_phone']['custom_attributes']['data-parsley-valid-international-phone'] = 'shipping';
		}

		return $fields;
	}
}
