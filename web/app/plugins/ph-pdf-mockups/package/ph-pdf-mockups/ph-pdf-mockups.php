<?php

/**
 * Plugin Name: ProjectHuddle — PDF Mockups Addon
 * Plugin URL: http://projecthuddle.io
 * Description: Adds pdf capabilities to ProjectHuddle mockups.
 * Version: 1.0.4-beta2
 * Author: Andre Gagnon
 * Author URI: http://projecthuddle.io
 * Text Domain: ph-pdf-mockups
 * Domain Path: languages
 *
 * @package ProjectHuddle
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Setup Constants before init because we're running plugin on plugins_loaded
 *
 * @since 1.1.1
 */

// Plugin Folder Path.
if (!defined('PH_PDF_PLUGIN_DIR')) {
	define('PH_PDF_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Plugin Folder URL.
if (!defined('PH_PDF_PLUGIN_URL')) {
	define('PH_PDF_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Plugin Root File.
if (!defined('PH_PDF_PLUGIN_FILE')) {
	define('PH_PDF_PLUGIN_FILE', __FILE__);
}

// Plugin Folder Path.
if (!defined('PH_PDF_PLUGIN_VERSION')) {
	define('PH_PDF_PLUGIN_VERSION', '1.0.4-beta2');
}

// this is the URL our updater / license checker pings. Do not change.
if (!defined('PH_PDF_SL_STORE_URL')) {
	define('PH_PDF_SL_STORE_URL', 'http://projecthuddle.io');
}

// item name (for updates) do no change.
if (!defined('PH_PDF_SL_ITEM_NAME')) {
	define('PH_PDF_SL_ITEM_NAME', 'ProjectHuddle');
}

// item id.
if (!defined('PH_PDF_SL_ITEM_ID')) {
	define('PH_PDF_SL_ITEM_ID', 18650);
}


if (!class_exists('PH_PDF_Mockups')) :
	/**
	 * Main PH_PDF_Mockups Class
	 * Uses singleton design pattern
	 *
	 * @since 1.0.0
	 */
	final class PH_PDF_Mockups
	{

		/**
		 * Holds only one PH_PDF_Mockups instance
		 *
		 * @var $instance
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * Main PH_PDF_Mockups Instance
		 *
		 * Insures that only one instance of PH_PDF_Mockups exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since  1.0.0
		 * @static var array $instance
		 * @uses   PH_PDF_Mockups::includes() Include the required files
		 * @uses   PH_PDF_Mockups::load_textdomain() load the language files
		 * @see    PHW()
		 * @return PH_PDF_Mockups|bool $instance The one true PH_PDF_Mockups
		 */
		public static function instance()
		{
			if (!isset(self::$instance) && !(self::$instance instanceof PH_PDF_Mockups)) {
				// start instance.
				self::$instance = new PH_PDF_Mockups();

				// do nothing if ProjectHuddle is not activated.
				if (!class_exists('Project_Huddle', false)) {
					add_action('admin_notices', array(self::$instance, 'projecthuddle_required'));
					return false;
				}

				// make sure we have the correct minimum version.
				if (version_compare(PH_VERSION, '3.2.0-beta2') < 0) {
					add_action('admin_notices', array(self::$instance, 'update_required'));
					return false;
				}

				// load includes.
				self::$instance->includes();

				// classes.
				self::$instance->scripts = new PH_PDF_Scripts();

				// make sure we have activated the license
				if (!get_option('ph_pdf_license_data', false)) {
					add_action('admin_notices', array(self::$instance, 'activate_license'));
					return false;
				}

				// Loaded action.
				do_action('ph_file_uploads_loaded');
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
			// Cloning instances of the class is forbidden.
			_doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'ph-pdf-mockups'), '1.0.0');
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
			// Un-serializing instances of the class is forbidden.
			_doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'ph-pdf-mockups'), '1.0.0');
		}

		/**
		 * Show notice if ProjectHuddle needs updating
		 *
		 * @since 1.0.0
		 *
		 * @access protected
		 * @return void
		 */
		public function projecthuddle_required()
		{
?>
			<div class="update-nag">
				<p><?php esc_html_e('You need to run the ProjectHuddle plugin in order to use the PDF Mockups extension.', 'ph-pdf-mockups'); ?></p>
			</div>
		<?php
		}

		/**
		 * Show notice if ProjectHuddle needs updating
		 *
		 * @since 1.0.0
		 *
		 * @access protected
		 * @return void
		 */
		public function update_required()
		{
		?>
			<div class="update-nag">
				<p><?php esc_html_e('The ProjectHuddle PDF Mockups addon requires the ProjectHuddle plugin to be at least version 3.2.0-beta2. Please update your ProjectHuddle plugin to this version.', 'ph-pdf-mockups'); ?></p>
			</div>
<?php
		}

		/**
		 * Show notice if ProjectHuddle needs updating
		 *
		 * @since 1.0.0
		 *
		 * @access protected
		 * @return void
		 */
		public function activate_license()
		{
			if (!get_option('dismissed-ph-pdf-license-data', false)) {
				if (isset($_GET['page']) && $_GET['page'] == 'project_huddle_settings') {
					return;
				}
				echo '<div class="notice update-nag is-dismissible ph-notice" data-notice="ph-pdf-license-data">
					<p>' . esc_html__('Please activate your PDF Mockups Addon license from the updates page to enable automatic updates!', 'project-huddle') . '</p>
					<p><a href="' . admin_url('admin.php?page=project_huddle_settings&tab=updates') . '">' . esc_html__('Activate License', 'ph-pdf-mockups') . '</a> </p>
				</div>';
				ph_dismiss_js();
			}
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
			require_once PH_PDF_PLUGIN_DIR . 'includes/class-ph-pdf-scripts.php';

			// settings.
			require_once PH_PDF_PLUGIN_DIR . 'includes/ph-pdf-functions.php';

			// settings.
			require_once PH_PDF_PLUGIN_DIR . 'includes/settings.php';

			// licensing
			require_once PH_PDF_PLUGIN_DIR . 'includes/updates/ph-pdf-license-handler.php';
		}
	}

endif; // end if class_exists.

/**
 * The main function responsible for returning the one true PH_PDF_Mockups
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @since 1.0.0
 * @return object The one true PH_PDF_Mockups Instance
 */
// phpcs:ignore
function PH_PDF()
{
	return PH_PDF_Mockups::instance();
}
add_action('plugins_loaded', 'PH_PDF', 20);
