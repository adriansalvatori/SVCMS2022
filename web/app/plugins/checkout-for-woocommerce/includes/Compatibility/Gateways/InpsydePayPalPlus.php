<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class InpsydePayPalPlus extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\WCPayPalPlus\\PayPalPlus' );
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'InpsydePayPalPlus',
			'params' => array(),
		);

		return $compatibility;
	}
}
