<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;
use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Genesis extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'genesis_header_scripts' );
	}

	public function run() {
		remove_action( 'wp_head', 'genesis_header_scripts' );
		remove_action( 'wp_footer', 'genesis_footer_scripts' );
	}
}
