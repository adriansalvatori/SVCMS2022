<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class CSSHero extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'csshero_activation_notice' );
	}

	public function remove_styles( array $styles ): array {
		$styles['csshero-main-stylesheet'] = 'csshero-main-stylesheet';
		$styles['csshero-aos-stylesheet']  = 'csshero-aos-stylesheet';

		return $styles;
	}

	public function remove_scripts( array $scripts ): array {
		$scripts['csshero_aos']         = 'csshero_aos';
		$scripts['csshero_aos-trigger'] = 'csshero_aos-trigger';

		return $scripts;
	}
}
