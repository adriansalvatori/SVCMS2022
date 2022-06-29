<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class CO2OK extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\co2ok_plugin_woocommerce\\Co2ok_Plugin' );
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'CO2OK',
			'params' => array(),
		);

		return $compatibility;
	}
}
