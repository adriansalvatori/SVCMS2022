<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class ShipMondo extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'shipmondo_load_shipping_methods_init' );
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'ShipMondo',
			'params' => array(
				'notice' => cfw__( 'Please select a pickup point before placing your order.', 'pakkelabels-for-woocommerce' ),
			),
		);

		return $compatibility;
	}
}
