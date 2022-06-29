<?php
/**
 * Effects and Animations.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stackable_Effects_Animations' ) ) {

	/**
	 * Stackable Effects and Animations
	 */
    class Stackable_Effects_Animations {

		private $is_script_loaded = false;

		/**
		 * Initialize
		 */
        function __construct() {
			// Load the scripts only when Stackable effects are detected.
			add_filter( 'render_block', array( $this, 'load_frontend_scripts_conditionally' ), 10, 2 );
		}

		/**
		 * Load the scripts only when Stackable effects are detected.
		 *
		 * @param String $block_content
		 * @param Array $block
		 *
		 * @return void
		 *
		 * @since 3.0.0
		 */
		public function load_frontend_scripts_conditionally( $block_content, $block ) {
			if ( ! $this->is_script_loaded && ! is_admin() ) {
				if ( ! empty( $block ) && is_array( $block['attrs'] ) ) {
					if ( array_key_exists( 'effectType', $block['attrs'] ) && ! empty( $block['attrs']['effectType'] ) ) {
						$this->is_script_loaded = true;

						wp_enqueue_script(
							'ugb-block-frontend-js-effect-premium',
							plugins_url( 'dist/frontend_effects__premium_only.js', STACKABLE_FILE ),
							array(),
							STACKABLE_VERSION
						);
					}
				}
			}

			return $block_content;
		}
	}

	new Stackable_Effects_Animations();
}
