<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class PayPalPlusCw extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\PayPalPlusCw_Util' );
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'PayPalPlusCw',
			'params' => array(),
		);

		return $compatibility;
	}
}
