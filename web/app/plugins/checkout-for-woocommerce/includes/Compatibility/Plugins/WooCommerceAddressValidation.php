<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WooCommerceAddressValidation extends CompatibilityAbstract {
	public function is_available(): bool {
		if ( function_exists( 'wc_address_validation' ) ) {
			return wc_address_validation()->get_handler_instance()->get_active_provider()->id === 'smartystreets';
		}

		return false;
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'WooCommerceAddressValidation',
			'params' => array(),
		);

		return $compatibility;
	}
}
