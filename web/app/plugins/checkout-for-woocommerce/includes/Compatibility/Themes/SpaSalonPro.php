<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class SpaSalonPro extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'spasalon_scripts' );
	}

	function run() {
		remove_action( 'wp_enqueue_scripts', 'spasalon_scripts' );
		remove_action( 'wp_head', 'spasalon_custom_css_function' );
	}

	function run_on_thankyou() {
		remove_action( 'wp_enqueue_scripts', 'spasalon_scripts' );
		remove_action( 'wp_head', 'spasalon_custom_css_function' );
	}
}
