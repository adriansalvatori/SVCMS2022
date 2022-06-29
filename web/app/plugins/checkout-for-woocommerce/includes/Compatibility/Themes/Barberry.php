<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Barberry extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'BARBERRY_ADDONS_DIR' );
	}

	public function remove_scripts( array $scripts ): array {
		$scripts['barberry-shortcodes'] = 'barberry-shortcodes';

		return $scripts;
	}
}