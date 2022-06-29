<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class IndeedAffiliatePro extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\UAP_Main' );
	}

	public function remove_scripts( array $scripts ): array {
		$scripts['uap-select2'] = 'uap-select2';

		return $scripts;
	}

	public function remove_styles( array $styles ): array {
		$styles['uap_select2_style'] = 'uap_select2_style';

		return $styles;
	}
}
