<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class MyShipper extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\IGN_Use_My_Shipper_Base' );
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'MyShipper',
			'params' => array(
				'notice' => 'Shipping Account Number is required.',
			),
		);

		return $compatibility;
	}
}
