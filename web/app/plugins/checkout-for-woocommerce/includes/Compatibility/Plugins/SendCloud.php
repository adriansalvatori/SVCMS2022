<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class SendCloud extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'sendcloudshipping_add_service_point_to_checkout' );
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'SendCloud',
			'params' => array(
				'notice' => cfw__( 'Please choose a service point.', 'sendcloud-shipping' ),
			),
		);

		return $compatibility;
	}
}
