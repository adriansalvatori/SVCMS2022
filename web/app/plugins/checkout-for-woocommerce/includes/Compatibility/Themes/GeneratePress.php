<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class GeneratePress extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'GENERATE_VERSION' );
	}

	public function run() {
		$this->remove_gp_scripts();
	}

	function remove_gp_scripts() {
		remove_action( 'wp_enqueue_scripts', 'generatepress_wc_scripts', 100 );
	}
}
