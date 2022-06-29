<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class UpSolutionCore extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'US_CORE_DIR' );
	}

	public function remove_styles( array $styles ): array {
		$styles['us-theme'] = 'us-theme';

		return $styles;
	}
}
