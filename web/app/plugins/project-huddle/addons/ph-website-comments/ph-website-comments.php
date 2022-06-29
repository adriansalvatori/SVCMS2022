<?php

/**
 * Plugin Name: ProjectHuddle - Website Comments
 * Plugin URL: http://projecthuddle.com
 * Description: Adds website commenting to projecthuddle
 * Version: 1.0.0
 * Author: Andre Gagnon
 * Author URI: http://projecthuddle.com
 * Text Domain: project-huddle
 * Domain Path: languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Setup Constants before init because we're running plugin on plugins_loaded
 *
 * @since 1.1.1
 */

// Plugin Folder Path
if (!defined('PH_WEBSITE_PLUGIN_DIR')) {
	define('PH_WEBSITE_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Plugin Folder URL
if (!defined('PH_WEBSITE_PLUGIN_URL')) {
	define('PH_WEBSITE_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Plugin Root File
if (!defined('PH_WEBSITE_PLUGIN_FILE')) {
	define('PH_WEBSITE_PLUGIN_FILE', __FILE__);
}

if (!class_exists('PH_Website')) :
	/**
	 * Main PH_Website Class
	 * Uses singleton design pattern
	 *
	 * @since 1.0.0
	 */
	final class PH_Website
	{

		/**
		 * Holds only one PH_Website instance
		 * @var $instance
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * Main PH_Website Instance
		 *
		 * Insures that only one instance of PH_Website exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since  1.0.0
		 * @static var array $instance
		 * @uses   PH_Website::includes() Include the required files
		 * @uses   PH_Website::load_textdomain() load the language files
		 * @see    PHW()
		 * @return PH_Website $instance The one true PH_Website
		 */
		public static function instance()
		{
			if (!isset(self::$instance) && !(self::$instance instanceof PH_Website)) {

				// do nothing if ProjectHuddle is not activated
				if (!class_exists('Project_Huddle', false)) {
					return false;
				}

				self::$instance = new PH_Website();

				// make sure we have the correct minimum version
				if (version_compare(PH_VERSION, '1.1.0') < 0) {
					add_action('admin_notices', array(self::$instance, 'update_required'));
					return false;
				}

				// load includes
				self::$instance->includes();

				// api
				self::$instance->scripts    = new PH_Website_Scripts();
				self::$instance->meta_boxes = new PH_Website_Meta_Boxes();

				// Loaded action
				do_action('projecthuddle_website_loaded');
			}

			return self::$instance;
		}

		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since 1.0.0
		 *
		 * @uses _doing_it_wrong() Mark something as being incorrectly called.
		 *
		 * @access public
		 * @return void
		 */
		public function __clone()
		{
			// Cloning instances of the class is forbidden
			_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'project-huddle'), '1.0.0');
		}

		/**
		 * Disable un-serializing of the class
		 *
		 * @since 1.0.0
		 *
		 * @uses _doing_it_wrong() Mark something as being incorrectly called.
		 *
		 * @access public
		 * @return void
		 */
		public function __wakeup()
		{
			// Un-serializing instances of the class is forbidden
			_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'project-huddle'), '1.0.0');
		}

		/**
		 * Show notice if ProjectHuddle needs updating
		 *
		 * @since 1.0.0
		 *
		 * @access protected
		 * @return string
		 */
		public function update_required()
		{
?>
			<div class="update-nag">
				<p><?php _e('The ProjectHuddle versions plugin requires the ProjectHuddle plugin to be at least version 1.0.2. Please update your ProjectHuddle plugin.', 'project-huddle'); ?></p>
			</div>
<?php
		}

		/**
		 * Include required files
		 *
		 * @access private
		 * @since 1.0.0
		 * @return void
		 */
		private function includes()
		{
			// post types
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/ph-website-post-types.php';

			// templates
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/ph-website-functions.php';

			// general
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/class-ph-website-options.php';
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/class-ph-website-scripts.php';

			// api
			//          require_once PH_WEBSITE_PLUGIN_DIR . 'includes/api/class-ph-website-api.php';
			//          require_once PH_WEBSITE_PLUGIN_DIR . 'includes/api/class-ph-website-actions.php';

			// functions
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/ph-website-model-functions.php';
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/ph-website-template-functions.php';
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/ph-website-misc-functions.php';
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/ph-website-functions.php';
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/ph-website-translations.php';
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/ph-website-style-options.php';
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/shortcodes.php';

			// admin
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/ph-website-admin-scripts.php';
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/admin/class-ph-website-meta-boxes.php';
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/admin/class-ph-website-meta-box-setup.php';
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/admin/class-ph-website-meta-box-pages.php';
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/admin/class-ph-website-meta-box-options.php';
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/admin/class-ph-website-meta-box-page-feedback.php';
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/admin/post-type-ui.php';
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/connector/class-ph-child-connection-controller.php';

			// settings
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/ph-website-settings.php';

			// backwards compat
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/backwards-compat/backwards-comment-actions.php';
			require_once PH_WEBSITE_PLUGIN_DIR . 'includes/backwards-compat/backwards-thread-actions.php';
		}
	}

endif; // end if class_exists

/**
 * The main function responsible for returning the one true PH_Website
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $websites = PH_Website_Instance(); ?>
 *
 * @since 1.0.0
 * @return object The one true PH_Website_Instance
 */
function PHW()
{
	return PH_Website::instance();
}
add_action('plugins_loaded', 'PHW');
