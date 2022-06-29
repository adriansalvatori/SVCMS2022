<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Blaszok extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'mpcth_woo_fix' );
	}

	function pre_init() {
		add_action(
			'init',
			function() {
				remove_action( 'init', 'mpcth_woo_fix' );
			},
			1
		);
	}
}
