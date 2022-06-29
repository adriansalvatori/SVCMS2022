<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class OxygenBuilder extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'CT_VERSION' );
	}

	public function run() {
		remove_action( 'wp_head', 'oxy_print_cached_css', 999999 );
	}
}
