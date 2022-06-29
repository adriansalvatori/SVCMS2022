<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class ExtraCheckoutFieldsBrazil extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\Extra_Checkout_Fields_For_Brazil' );
	}

	public function run() {
		add_filter( 'wcbcf_billing_fields', array( $this, 'checkout_billing_fields' ) );
		add_filter( 'wcbcf_shipping_fields', array( $this, 'checkout_shipping_fields' ) );
		add_filter( 'cfw_form_field_append_optional_to_placeholder', array( $this, 'suppress_optional_in_placeholder' ), 10, 2 );

		// Otherwise fields won't be filled in that need to be filled in
		add_filter( 'cfw_force_display_billing_address', '__return_true' );
	}

	public function checkout_billing_fields( $fields ) {
		$unmodified_fields = WC()->countries->get_default_address_fields();

		$fields['billing_first_name'] = $unmodified_fields['first_name'];
		$fields['billing_last_name']  = $unmodified_fields['last_name'];
		$fields['billing_address_1']  = $unmodified_fields['address_1'];
		$fields['billing_address_2']  = $unmodified_fields['address_2'];

		if ( isset( $unmodified_fields['company'] ) ) {
			$fields['billing_company'] = $unmodified_fields['company'];
		} else {
			unset( $fields['billing_company'] );
		}

		$fields['billing_country']  = $unmodified_fields['country'];
		$fields['billing_postcode'] = $unmodified_fields['postcode'];
		$fields['billing_state']    = $unmodified_fields['state'];
		$fields['billing_city']     = $unmodified_fields['city'];

		$fields['billing_number']['columns']       = 12;
		$fields['billing_number']['class']         = array();
		$fields['billing_neighborhood']['columns'] = 12;
		$fields['billing_neighborhood']['class']   = array();
		$fields['billing_cellphone']['columns']    = 12;
		$fields['billing_cellphone']['class']      = array();

		if ( isset( $fields['billing_birthdate'] ) ) {
			$fields['billing_birthdate']['columns'] = 12;
			$fields['billing_birthdate']['class']   = array();
		}

		if ( isset( $fields['billing_sex'] ) ) {
			$fields['billing_sex']['columns'] = 12;
			$fields['billing_sex']['class']   = array();
		}

		return $fields;
	}

	public function checkout_shipping_fields( $fields ) {
		$unmodified_fields = WC()->countries->get_default_address_fields();

		$fields['shipping_first_name'] = $unmodified_fields['first_name'];
		$fields['shipping_last_name']  = $unmodified_fields['last_name'];
		$fields['shipping_address_1']  = $unmodified_fields['address_1'];
		$fields['shipping_address_2']  = $unmodified_fields['address_2'];

		if ( isset( $unmodified_fields['company'] ) ) {
			$fields['shipping_company'] = $unmodified_fields['company'];
		} else {
			unset( $fields['shipping_company'] );
		}

		$fields['shipping_country']  = $unmodified_fields['country'];
		$fields['shipping_postcode'] = $unmodified_fields['postcode'];
		$fields['shipping_state']    = $unmodified_fields['state'];
		$fields['shipping_city']     = $unmodified_fields['city'];

		$fields['shipping_number']['columns']       = 12;
		$fields['shipping_number']['class']         = array();
		$fields['shipping_neighborhood']['columns'] = 12;
		$fields['shipping_neighborhood']['class']   = array();

		if ( cfw_is_phone_fields_enabled() && ! empty( $unmodified_fields['phone'] ) ) {
			$fields['shipping_phone'] = $unmodified_fields['phone'];
		}

		return $fields;
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'ExtraCheckoutFieldsBrazil',
			'params' => array(),
		);

		return $compatibility;
	}

	function suppress_optional_in_placeholder( $append, $field_key ) {
		$blocked_fields = array(
			'billing_persontype',
			'billing_cnpj',
			'billing_ie',
			'billing_cpf',
			'billing_rg',
			'billing_company',
		);

		if ( in_array( $field_key, $blocked_fields, true ) ) {
			return false;
		}

		return $append;
	}
}
