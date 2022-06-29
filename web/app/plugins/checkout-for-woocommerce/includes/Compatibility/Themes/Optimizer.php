<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Optimizer extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'optimizer_setup' );
	}

	public function run() {
		remove_action( 'wp_footer', 'optimizer_load_js' );
	}
}
