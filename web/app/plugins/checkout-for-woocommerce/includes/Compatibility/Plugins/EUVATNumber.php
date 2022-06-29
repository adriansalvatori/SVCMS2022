<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class EUVATNumber extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\WC_EU_VAT_Number' );
	}

	public function run() {
		add_filter( 'woocommerce_form_field_args', array( $this, 'maybe_change_placeholder' ), 10, 2 );
	}

	public function maybe_change_placeholder( $field, $key ) {
		if ( 'vat_number' === $key || 'billing_vat_number' === $key ) {
			$field['placeholder'] = $field['label'];
		}

		return $field;
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'EUVatNumber',
			'params' => array(),
		);

		return $compatibility;
	}
}
