<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class BeaverBuilder extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\FLThemeCompat' );
	}

	/**
	 * Add WP theme styles to list of blocked style handles.
	 *
	 * @param array $styles
	 *
	 * @return array
	 */
	public function remove_styles( array $styles ): array {
		global $wp_styles;

		foreach ( $wp_styles->registered as $wp_style ) {
			if ( ! empty( $wp_style->src ) && stripos( $wp_style->src, '/bb-theme/' ) !== false && stripos( $wp_style->src, '/checkout-wc/' ) === false ) {
				$styles[] = $wp_style->handle;
			}
		}

		return $styles;
	}
}
