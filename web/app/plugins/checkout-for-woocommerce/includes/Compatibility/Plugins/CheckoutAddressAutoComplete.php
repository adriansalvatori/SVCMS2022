<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class CheckoutAddressAutoComplete extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'ecr_addrac_scripts' );
	}

	public function run() {
		add_filter( 'cfw_enable_zip_autocomplete', '__return_false' );
	}
}
