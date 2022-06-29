<?php
/**
 * Loads the Font Awesome Premium Integration
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stackable_Premium_Icons' ) ) {

	/**
	 * Our Premium Icons
	 */
    class Stackable_Premium_Icons {

		/**
		 * Initialize icons
		 */
        function __construct() {
			if ( sugb_fs()->can_use_premium_code() ) {

				// Prevent the free FA from loading.
				add_filter( 'stackable_load_font_awesome_kit', array( $this, 'should_stop_loading_free_fa_kit' ) );

				// Enable searching of FA Pro icons in blocks.
				add_filter( 'stackable_search_fontawesome_pro_icons', array( $this, 'stackable_search_fontawesome_pro_icons' ) );

				// Load our own Premium Font Awesome icons.
				add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_fa_pro_icons' ) );

				// Async load the icons.
				add_filter( 'script_loader_tag', array( $this, 'script_loader_tag' ), 10, 2 );
			}
		}

		/**
		 * Is there a Pro Kit saved in the settings.
		 *
		 * @return boolean
		 */
		public function is_pro_icons_enabled() {
			return !! get_option( 'stackable_icons_fa_kit' ) || self::is_fontawesome_plugin_pro_enabled();
		}

		/**
		 * Gets the Pro Kit to use.
		 *
		 * @return string Or null if not available.
		 */
		public function get_pro_kit() {
			if ( ! $this->is_pro_icons_enabled() ) {
				return null;
			}

			// Use the saved kit.
			if ( get_option( 'stackable_icons_fa_kit' ) ) {
				return get_option( 'stackable_icons_fa_kit' );
			}

			// Use the FA Pro Kit saved in the FA plugin.
			$settings = self::get_fontawesome_plugin_settings();
			if ( ! empty( $settings['kitToken'] ) ) {
				return $settings['kitToken'];
			}

			return null;
		}

		/**
		 * Stop the loading of the Free Font Awesome Kit.
		 *
		 * @param boolean $should_load
		 * @return boolean
		 */
		public function should_stop_loading_free_fa_kit( $should_load ) {
			return $this->is_pro_icons_enabled() ? false : $should_load;
		}

		/**
		 * Enable searching of Font Awesome icons.
		 *
		 * @param boolean $search_pro_icons
		 * @return boolean If true, pro icons will be included in icon searches.
		 */
		public function stackable_search_fontawesome_pro_icons( $search_pro_icons ) {
			return $this->is_pro_icons_enabled() ? true : $search_pro_icons;
		}

		/**
		 * Enqueue the Font Awesome Pro icons.
		 *
		 * @return void
		 */
		public function enqueue_fa_pro_icons() {
			if ( $this->is_pro_icons_enabled() ) {
				// Load our Premium Font Awesome Icons.
				$script = $this->get_pro_kit() ?
					'https://kit.fontawesome.com/' . $this->get_pro_kit() . '.js' :
					'https://pro.fontawesome.com/releases/v5.15.4/js/all.js';

				wp_enqueue_script(
					'ugb-font-awesome-kit',
					$script,
					null,
					null,
					true
				);

				// Preload all the Font Awesome Icons.
				wp_enqueue_script(
					'ugb-font-awesome-icons-duotone',
					'https://pro.fontawesome.com/releases/v5.15.4/js/duotone.js',
					array( 'ugb-block-js-vendor' ),
					null
				);
				wp_enqueue_script(
					'ugb-font-awesome-icons-light',
					'https://pro.fontawesome.com/releases/v5.15.4/js/light.js',
					array( 'ugb-block-js-vendor' ),
					null
				);
				wp_enqueue_script(
					'ugb-font-awesome-icons-regular',
					'https://pro.fontawesome.com/releases/v5.15.4/js/regular.js',
					array( 'ugb-block-js-vendor' ),
					null
				);
				wp_enqueue_script(
					'ugb-font-awesome-icons-solid',
					'https://pro.fontawesome.com/releases/v5.15.4/js/solid.js',
					array( 'ugb-block-js-vendor' ),
					null
				);
				wp_enqueue_script(
					'ugb-font-awesome-icons-brands',
					'https://pro.fontawesome.com/releases/v5.15.4/js/brands.js',
					array( 'ugb-block-js-vendor' ),
					null
				);
			}
		}

		/**
		 * Load the FA Pro icons asynchronously.
		 *
		 * @param string $html
		 * @param string $handle
		 * @return string
		 */
		public function script_loader_tag( $html, $handle ) {
			if ( stripos( $handle, 'ugb-font-awesome-icons' ) !== false ) {
				$html = preg_replace( '#></script>#', ' async></script>', $html );
			}
			return $html;
		}

		/**
		 * Checks whether the Font Awesome plugin is activated
		 *
		 * @return boolean True if the plugin is activated.
		 */
		public static function is_fontawesome_plugin_active() {
			return is_plugin_active( 'font-awesome/index.php' );
		}

		/**
		 * Gets the Kit settings saved in the FA plugin
		 *
		 * @return mixed An array of settings, or false if not available.
		 */
		public static function get_fontawesome_plugin_settings() {
			$fa_settings = get_option( 'font-awesome' );
			if ( empty( $fa_settings ) ) {
				return false;
			}

			// Only get what we need.
			return array(
				'usePro' => $fa_settings['usePro'],
				'technology' => $fa_settings['technology'],
				'kitToken' => $fa_settings['kitToken'],
				'version' => $fa_settings['version'],
			);
		}

		/**
		 * Check whether the FA settings are set to Pro, returns also the
		 * necessary message to display near the settings.
		 *
		 * @return array
		 */
		public static function has_fontawesome_plugin_pro() {
			$settings = self::get_fontawesome_plugin_settings();
			$error = false;
			$message = '';
			if ( ! empty( $settings ) ) {
				if ( ! $settings['usePro'] && ! empty( $settings['kitToken'] ) ) {
					// Using a free Kit.
					$error = true;
					$message = sprintf( __( 'Hold on! We noticed that you\'re using the Font Awesome plugin and that you\'re using a free Kit. If you have a FontAwesome Pro subscription, you can just set your Kit to use Pro Icons, and you should be able to use your Pro Icons inside your Stackable blocks. %sLearn more about this here.%s', STACKABLE_I18N ), '<a href="https://docs.wpstackable.com/article/358-how-to-use-your-font-awesome-pro-icons?utm_source=wp-settings-icons&utm_campaign=learnmore&utm_medium=wp-dashboard">', '</a>' );
				} else if ( ! $settings['usePro'] ) {
					// Using a free CDN.
					$error = true;
					$message = sprintf( __( 'Hold on! We noticed that you\'re using the Font Awesome plugin and that you\'re using the free CDN. If you have a FontAwesome Pro subscription, you can just set your CDN to use Pro Icons, and you should be able to use your Pro Icons inside your Stackable blocks. %sLearn more about this here.%s', STACKABLE_I18N ), '<a href="https://docs.wpstackable.com/article/358-how-to-use-your-font-awesome-pro-icons?utm_source=wp-settings-icons&utm_campaign=learnmore&utm_medium=wp-dashboard">', '</a>' );
				} else if ( $settings['usePro'] && empty( $settings['kitToken'] ) ) {
					// Pro but using the CDN. Warn about whitelist.
					$message = __( 'Good news! We noticed that you\'re using the Font Awesome plugin. Your Font Awesome Pro icons are already available inside your Stackable blocks.', STACKABLE_I18N ) . ' ' .
						sprintf( __( 'Make sure you need to add your WordPress site to the %sallowed domains for your CDN%s.', STACKABLE_I18N ), '<a href="https://fontawesome.com/account/cdn" target="_fontawesome">', '</a>' );
				} else if ( $settings['usePro'] ) {
					// Pro and using Kit.
					$message = __( 'Good news! We noticed that you\'re using the Font Awesome plugin. Your Font Awesome Pro icons are already available inside your Stackable blocks.', STACKABLE_I18N );
				}
			}
			return array(
				'error' => $error,
				'message' => $message,
			);
		}

		/**
		 * Check if FA Pro settings are enabled in the FA plugin.
		 *
		 * @return boolean
		 */
		public static function is_fontawesome_plugin_pro_enabled() {
			$settings = self::get_fontawesome_plugin_settings();
			if ( empty( $settings ) ) {
				return false;
			}

			$has_pro = self::has_fontawesome_plugin_pro();
			return ! $has_pro['error'];
		}
	}

	new Stackable_Premium_Icons();
}
