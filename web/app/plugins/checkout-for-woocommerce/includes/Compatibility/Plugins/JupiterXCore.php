<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class JupiterXCore extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\JupiterX_Core' );
	}

	/**
	 * Add JupiterX compiled styles to list of blocked style handles.
	 *
	 * @param array $styles
	 *
	 * @return mixed
	 */
	public function remove_styles( array $styles ): array {
		global $wp_styles;

		foreach ( $wp_styles->registered as $wp_style ) {
			if ( ! empty( $wp_style->src ) && stripos( $wp_style->src, 'compiler/jupiterx' ) !== false ) {
				$styles[] = $wp_style->handle;
			}
		}

		return $styles;
	}
}
