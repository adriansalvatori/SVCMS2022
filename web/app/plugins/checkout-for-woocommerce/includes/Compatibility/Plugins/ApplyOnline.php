<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class ApplyOnline extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'run_applyonline' );
	}

	public function remove_styles( array $styles ): array {
		$styles['apply-online-BS'] = 'apply-online-BS';

		return $styles;
	}
}
