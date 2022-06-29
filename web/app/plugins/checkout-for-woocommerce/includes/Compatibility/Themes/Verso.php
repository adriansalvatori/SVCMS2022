<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Verso extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'verso_scripts' );
	}

	public function run() {
		add_filter( 'verso_filter_theme_style_url', '__return_empty_string', 100 );

		// Hide search form
		remove_action( 'wp_footer', 'verso_render_footer', 10 );
	}
}
