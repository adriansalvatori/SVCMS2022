<?php
/**
 * Premium Blocks Loader
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since 	3.0.0
 * @package Stackable
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'stackable_get_premium_metadata_by_folders' ) ) {
	/**
	 * Function for getting the block.json metadata
	 * based on folder names array.
	 *
	 * @array array folders
	 * @array string handle
	 * @return array metadata
	 */
	function stackable_get_premium_metadata_by_folders( $block_folders, $handle = 'metadata' ) {
		// Check if the metadata have been parsed already.
		global $stk_premium_block_meta_data_cache;
		if ( ! empty( $stk_premium_block_meta_data_cache ) && isset( $stk_premium_block_meta_data_cache[ $handle ] ) ) {
			return $stk_premium_block_meta_data_cache[ $handle ];
		}

		$blocks = array();
		$blocks_dir = dirname( __FILE__ ) . '/block';
		if ( ! file_exists( $blocks_dir ) ) {
			return $blocks;
		}

		foreach ( $block_folders as $folder_name ) {
			$block_json_file = $blocks_dir . '/' . $folder_name . '/block.json';
			if ( ! file_exists( $block_json_file ) ) {
				continue;
			}

			$metadata = json_decode( file_get_contents( $block_json_file ), true );
			array_push( $blocks, array_merge( $metadata, array( 'block_json_file' => $block_json_file ) ) );
		}

		if ( empty( $stk_premium_block_meta_data_cache ) ) {
			$stk_premium_block_meta_data_cache = array();
		}
		$stk_premium_block_meta_data_cache[ $handle ] = $blocks; // Cache.
		return $blocks;
	}
}

if ( ! function_exists( 'stackable_get_premium_stk_block_folders_metadata' ) ) {
	function stackable_get_premium_stk_block_folders_metadata() {
	/**
	 * folders containing stackable blocks without inner blocks.
	 */
	$stk_premium_block_folders = array(
		'pagination'
	);

	return stackable_get_premium_metadata_by_folders( $stk_premium_block_folders, 'stk-premium-block-folders' );

	}
}

if ( ! function_exists( 'stackable_get_premium_stk_wrapper_block_folders_metadata' ) ) {
	function stackable_get_premium_stk_wrapper_block_folders_metadata() {
	/**
	 * folders containing stackable blocks with inner blocks.
	 */
	$stk_premium_wrapper_block_folders = array(
		'load-more'
	);

	return stackable_get_premium_metadata_by_folders( $stk_premium_wrapper_block_folders, 'stk-premium-wrapper-block-folders' );
	}
}


if ( ! function_exists( 'stackable_register_blocks_premium' ) ) {
	function stackable_register_blocks_premium() {
		// Blocks directory may not exist if working from a fresh clone.
		$blocks_dir = dirname( __FILE__ ) . '/block';
		if ( ! file_exists( $blocks_dir ) ) {
			return;
		}

		$blocks_metadata = array_merge(
			stackable_get_premium_stk_block_folders_metadata(),
			stackable_get_premium_stk_wrapper_block_folders_metadata()
		);

		foreach ( $blocks_metadata as $metadata ) {
			$registry = WP_Block_Type_Registry::get_instance();
			if ( $registry->is_registered( $metadata['name'] ) ) {
				$registry->unregister( $metadata['name'] );
			}

			$register_options = apply_filters( 'stackable.register-blocks.options',
				// This automatically enqueues all our styles and scripts.
				array(
					'style' => 'ugb-style-css-premium', // Frontend styles.
					'script' => 'ugb-block-frontend-js-premium', // Frontend scripts.
					'editor_script' => 'ugb-block-js-premium', // Editor scripts.
					'editor_style' => 'ugb-block-editor-css-premium', // Editor styles.
				),
				$metadata['name'],
				$metadata
			);

			register_block_type_from_metadata( $metadata['block_json_file'], $register_options );
		}
	}
	add_action( 'init', 'stackable_register_blocks_premium' );
}
