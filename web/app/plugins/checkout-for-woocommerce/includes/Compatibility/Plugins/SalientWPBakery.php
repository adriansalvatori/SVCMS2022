<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class SalientWPBakery extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'SALIENT_VC_ACTIVE' );
	}

	public function remove_styles( array $styles ): array {
		$styles['js_composer_front'] = 'js_composer_front';

		return $styles;
	}
}
