<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Divi extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'et_maybe_add_scroll_to_anchor_fix' );
	}

	public function run() {
		// If this isn't disabled, Divi hides any div with an ID matching the location hash :-/
		remove_action( 'wp_head', 'et_maybe_add_scroll_to_anchor_fix', 9 );
	}
}
