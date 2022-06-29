<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class The7 extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'presscore_enqueue_dynamic_stylesheets' );
	}

	function run() {
		remove_action( 'wp_enqueue_scripts', 'presscore_enqueue_dynamic_stylesheets', 20 );
	}
}
