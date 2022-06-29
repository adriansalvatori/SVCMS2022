<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'stackable_premium_block_assets_v2' ) ) {

	/**
	* Enqueue block assets for both frontend + backend.
	*
	* @since 0.1
	*/
	function stackable_premium_block_assets_v2() {
		if ( ! has_stackable_v2_frontend_compatibility() && ! has_stackable_v2_editor_compatibility() ) {
			return;
		}

		wp_register_style(
			'ugb-style-css-premium-v2',
			plugins_url( 'dist/deprecated/frontend_blocks_deprecated_v2__premium_only.css', STACKABLE_FILE ),
			array( 'ugb-style-css-v2' ),
			STACKABLE_VERSION
		);

		if ( ! is_admin() ) {
			wp_register_script(
				'ugb-block-frontend-js-premium-v2',
				plugins_url( 'dist/deprecated/frontend_blocks_deprecated_v2__premium_only.js', STACKABLE_FILE ),
				array( 'ugb-block-frontend-js-v2'),
				STACKABLE_VERSION
			);
		}
	}
	add_action( 'init', 'stackable_premium_block_assets_v2' );
}

if ( ! function_exists( 'stackable_premium_block_editor_assets_v2' ) ) {

	/**
	 * Enqueue block assets for backend editor.
	 *
	 * @since 0.1
	 */
	function stackable_premium_block_editor_assets_v2() {
		if ( ! is_admin() ) {
			return;
		}

		if ( ! has_stackable_v2_frontend_compatibility() && ! has_stackable_v2_editor_compatibility() ) {
			return;
		}

		// This should enqueue BEFORE the main Stackable block script.
		wp_register_script(
			'ugb-block-js-premium-v2',
			plugins_url( 'dist/deprecated/editor_blocks_deprecated_v2__premium_only.js', STACKABLE_FILE ),
			array(),
			STACKABLE_VERSION
		);

		// Add translations.
		wp_set_script_translations( 'ugb-block-js-premium-v2', STACKABLE_I18N );

		wp_register_style(
			'ugb-block-editor-css-premium-v2',
			plugins_url( 'dist/deprecated/editor_blocks_deprecated_v2__premium_only.css', STACKABLE_FILE ),
			array( 'ugb-block-editor-css-v2' ),
			STACKABLE_VERSION
		);
	}
	add_action( 'init', 'stackable_premium_block_editor_assets_v2' );
}

require_once( plugin_dir_path( __FILE__ ) . 'block/blog-posts/index.php' );

/**
 * Load the premium block assets, they will load the free version as dependencies.
 *
 * @since 3.0.0
 */
if ( ! function_exists( 'stackable_premium_enqueue_scripts_v2' ) ) {
	function stackable_premium_enqueue_scripts_v2( $options, $block_name, $meta_data ) {
		$options['style'] = 'ugb-style-css-premium-v2'; // Frontend styles.
		$options['script'] = 'ugb-block-frontend-js-premium-v2'; // Frontend scripts.
		$options['editor_style'] = 'ugb-block-editor-css-premium-v2'; // Editor styles.
		return $options;
	}
	add_filter( 'stackable.v2.register-blocks.options', 'stackable_premium_enqueue_scripts_v2', 10, 3 );
}


/**
 * Load the premium editor script before the free one since the premium one adds hooks.
 *
 * @since 3.0.0
 */
if ( ! function_exists( 'stackable_premium_editor_enqueue_script_v2' ) ) {
	function stackable_premium_editor_enqueue_script_v2( $dependencies ) {
		$dependencies[] = 'ugb-block-js-premium-v2';
		return $dependencies;
	}
	add_filter( 'stackable_editor_js_dependencies_v2', 'stackable_premium_editor_enqueue_script_v2' );
}
