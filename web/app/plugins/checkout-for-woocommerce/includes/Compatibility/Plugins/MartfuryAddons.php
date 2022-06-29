<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class MartfuryAddons extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'martfury_vc_addons_init' );
	}

	public function remove_scripts( array $scripts ): array {
		$scripts['martfury-shortcodes'] = 'martfury-shortcodes';

		return $scripts;
	}
}
