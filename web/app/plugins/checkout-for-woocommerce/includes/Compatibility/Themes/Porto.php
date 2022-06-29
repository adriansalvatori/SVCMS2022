<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Porto extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'porto_setup' );
	}

	/**
	 * Add WP theme styles to list of blocked style handles.
	 *
	 * @param array $scripts
	 *
	 * @return array
	 */
	function remove_scripts( array $scripts ): array {
		global $wp_scripts;

		foreach ( $wp_scripts->registered as $wp_script ) {
			if ( ! empty( $wp_script->src ) && ( stripos( $wp_script->src, '/themes/' ) !== false || stripos( $wp_script->src, '/porto_styles/' ) !== false ) && stripos( $wp_script->src, '/checkout-wc/' ) === false ) {
				$scripts[] = $wp_script->handle;
			}
		}

		return $scripts;
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
			if ( ! empty( $wp_style->src ) && stripos( $wp_style->src, '/porto_styles/' ) !== false && stripos( $wp_style->src, '/checkout-wc/' ) === false ) {
				$styles[] = $wp_style->handle;
			}
		}

		return $styles;
	}
}
