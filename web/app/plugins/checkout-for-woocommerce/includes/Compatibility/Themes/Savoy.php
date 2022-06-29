<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Savoy extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'NM_THEME_DIR' );
	}

	function run() {
		remove_filter( 'woocommerce_checkout_required_field_notice', 'nm_checkout_required_field_notice' );
	}
}
