<?php

/**
 * Plugin Name: ProjectHuddle — File Uploads Addon
 * Plugin URL: http://projecthuddle.io
 * Description: Adds file upload capabilities to ProjectHuddle
 * Version: 1.0.8-beta2
 * Author: Andre Gagnon
 * Author URI: http://projecthuddle.io
 * Text Domain: ph-file-uploads
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
if (!defined('PH_UPLOADS_PLUGIN_DIR')) {
	define('PH_UPLOADS_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Plugin Folder URL.
if (!defined('PH_UPLOADS_PLUGIN_URL')) {
	define('PH_UPLOADS_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Plugin Root File.
if (!defined('PH_UPLOADS_PLUGIN_FILE')) {
	define('PH_UPLOADS_PLUGIN_FILE', __FILE__);
}

// Plugin Folder Path.
if (!defined('PH_UPLOADS_PLUGIN_VERSION')) {
	define('PH_UPLOADS_PLUGIN_VERSION', '1.0.8-beta2');
}

// this is the URL our updater / license checker pings. Do not change.
if (!defined('PH_UPLOADS_SL_STORE_URL')) {
	define('PH_UPLOADS_SL_STORE_URL', 'http://projecthuddle.com');
}

// item name (for updates) do no change.
if (!defined('PH_UPLOADS_SL_ITEM_NAME')) {
	define('PH_UPLOADS_SL_ITEM_NAME', 'ProjectHuddle');
}

// item id.
if (!defined('PH_UPLOADS_SL_ITEM_ID')) {
	define('PH_UPLOADS_SL_ITEM_ID', 12101);
}


if (!class_exists('PH_File_Uploads')) :
	/**
	 * Main PH_File_Uploads Class
	 * Uses singleton design pattern
	 *
	 * @since 1.0.0
	 */
	final class PH_File_Uploads
	{

		/**
		 * Holds only one PH_File_Uploads instance
		 *
		 * @var $instance
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * Main PH_File_Uploads Instance
		 *
		 * Insures that only one instance of PH_File_Uploads exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since  1.0.0
		 * @static var array $instance
		 * @uses   PH_File_Uploads::includes() Include the required files
		 * @uses   PH_File_Uploads::load_textdomain() load the language files
		 * @see    PHW()
		 * @return PH_File_Uploads|bool $instance The one true PH_File_Uploads
		 */
		public static function instance()
		{
			if (!isset(self::$instance) && !(self::$instance instanceof PH_File_Uploads)) {
				// start instance.
				self::$instance = new PH_File_Uploads();

				// do nothing if ProjectHuddle is not activated.
				if (!class_exists('Project_Huddle', false)) {
					add_action('admin_notices', array(self::$instance, 'projecthuddle_required'));
					return false;
				}

				// make sure we have the correct minimum version.
				if (version_compare(PH_VERSION, '3.0.4') < 0) {
					add_action('admin_notices', array(self::$instance, 'update_required'));
					return false;
				}

				// load includes.
				self::$instance->includes();

				// classes.
				self::$instance->scripts = new PHF_Scripts();
				self::$instance->upload  = new PHF_File_Upload_Comment();

				// make sure we have the correct minimum version.
				if (!get_option('phf_license_data', false)) {
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
			_doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'ph-file-uploads'), '1.0.0');
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
			_doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'ph-file-uploads'), '1.0.0');
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
				<p><?php esc_html_e('You need to run the ProjectHuddle plugin in order to use the File Uploads extension.', 'ph-file-uploads'); ?></p>
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
				<p><?php esc_html_e('The ProjectHuddle file uploads addon requires the ProjectHuddle plugin to be at least version 3.0.4. Please update your ProjectHuddle plugin.', 'ph-file-uploads'); ?></p>
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
			if (!get_option('dismissed-phf-license-data', false)) {
				if (isset($_GET['page']) && $_GET['page'] == 'project_huddle_settings') {
					return;
				}
				if (version_compare(PH_VERSION, '3.2.0', '<')) {
					echo '<div class="notice update-nag is-dismissible ph-notice" data-notice="phf-license-data">
					<p>' . esc_html__('Please activate your File Uploads Addon license from the updates page to enable automatic updates!', 'project-huddle') . '</p>
					<p><a href="' . admin_url('edit.php?post_type=ph-project&page=project_huddle_settings&tab=updates') . '">' . esc_html__('Activate License', 'ph-file-uploads') . '</a> </p>
				</div>';
				} else {
					echo '<div class="notice update-nag is-dismissible ph-notice" data-notice="phf-license-data">
					<p>' . esc_html__('Please activate your File Uploads Addon license from the updates page to enable automatic updates!', 'project-huddle') . '</p>
					<p><a href="' . admin_url('admin.php?page=project_huddle_settings&tab=updates') . '">' . esc_html__('Activate License', 'ph-file-uploads') . '</a> </p>
					</div>';
				}
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
			// class autoloader.
			require_once PH_UPLOADS_PLUGIN_DIR . 'includes/class-phf-autoloader.php';

			// functions.
			require_once PH_UPLOADS_PLUGIN_DIR . 'includes/phf-template-functions.php';

			// settings.
			require_once PH_UPLOADS_PLUGIN_DIR . 'includes/phf-settings.php';

			// licensing
			require_once PH_UPLOADS_PLUGIN_DIR . 'includes/updates/phf-license-handler.php';
		}
	}

endif; // end if class_exists.

/**
 * The main function responsible for returning the one true PH_File_Uploads
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @since 1.0.0
 * @return object The one true PH_File_Uploads Instance
 */
// phpcs:ignore
function PH_Files()
{
	return PH_File_Uploads::instance();
}
add_action('plugins_loaded', 'PH_Files', 20);
