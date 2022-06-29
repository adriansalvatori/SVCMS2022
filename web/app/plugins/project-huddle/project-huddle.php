<?php

/**
 * Plugin Name: ProjectHuddle Admin Site
 * Plugin URI: http://projecthuddle.com
 * Description: A collaboration tool to collect timely feedback and approvals from clients and teammates for all your design projects.
 * Author: Brainstorm Force
 * Author URI: https://www.brainstormforce.com
 * Version: 4.5.1
 *
 * Requires at least: 4.7
 * Tested up to: 6.0
 *
 * Text Domain: project-huddle
 * Domain Path: languages
 *
 * @package ProjectHuddle
 * @category Core
 * @author Brainstorm Force, Andre Gagnon
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('Project_Huddle')) :

	/**
	 * Main Project_Huddle Class
	 * Uses singleton design pattern
	 *
	 * @since 1.0.0
	 */
	final class Project_Huddle
	{
		/**
		 * Holds only one Project_Huddle instance
		 *
		 * @var $instance
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * Main Project_Huddle Instance
		 *
		 * Insures that only one instance of Project_Huddle exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since  1.0.0
		 * @static var array $instance
		 * @uses   Project_Huddle::setup_constants() Setup the constants needed
		 * @uses   Project_Huddle::includes() Include the required files
		 * @uses   Project_Huddle::load_textdomain() load the language files
		 * @see    PH()
		 * @return Project_Huddle $instance The one true Project_Huddle
		 */
		public static function instance()
		{
			if (!isset(self::$instance) && !(self::$instance instanceof Project_Huddle)) {
				self::$instance = new Project_Huddle();

				// set up constants immediately.
				self::$instance->setup_constants();

				// load textdomain on plugins_loaded hook.
				add_action('plugins_loaded', array(self::$instance, 'load_textdomain'));
				add_action('plugins_loaded', array(self::$instance, 'error_reporting'));

				// check versions.
				self::$instance->check_versions();
				self::$instance->check_secure();

				// get all includes.
				self::$instance->includes();

				self::$instance->activity_emails = new \PH\Controllers\Mail\EmailController();

				self::$instance->config = include(self::$instance->path('includes/App/Config/app.php'));

				if (self::$instance->config['providers']) {
					foreach (self::$instance->config['providers'] as $name => $class) {
						if (is_string($name)) {
							self::$instance->$name = new $class();
						} else {
							new $class();
						}
					}
				}

				self::$instance->session         = new PH_Session();
				self::$instance->project_admin   = new PH_Project_Admin();
				self::$instance->roles           = new PH_Roles();
				self::$instance->meta            = new PH_Admin_Meta_Boxes();

				self::$instance->mockup          = new PH_Mockup_Project();
				self::$instance->website         = new PH_Website_Project();
				self::$instance->image           = new PH_Mockup_Image();
				self::$instance->page            = new PH_Website_Page();
				self::$instance->mockup_thread   = new PH_Mockup_Thread();
				self::$instance->website_thread  = new PH_Website_Thread();
				self::$instance->comment         = new PH_Comment();
				self::$instance->user            = new PH_User();

				self::$instance->emails          = new PH_User_Email_Options();
				self::$instance->status          = new PH_System_Status();
				self::$instance->license 		 = ph_licensing();

				// Loaded action.
				do_action('projecthuddle_loaded');
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
			_doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'project-huddle'), '1.0.0');
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
			_doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'project-huddle'), '1.0.0');
		}

		/**
		 * Helper function to get path relative to plugin
		 *
		 * @return void
		 */
		public function path($directory = '')
		{
			return plugin_dir_path(__FILE__) . $directory;
		}

		/**
		 * Helper function to get url relative to plugin
		 *
		 * @param string $path
		 * @return void
		 */
		public function url($path = '')
		{
			return plugin_dir_url(__FILE__) . $path;
		}

		/**
		 * Double check WordPress and php versions
		 */
		private function check_versions()
		{
			global $wp_version;
			if (version_compare($wp_version, '5.0', '<')) {
				add_action('admin_notices', array($this, 'update_wordpress'));
			}
			if (version_compare(phpversion(), '5.6.20', '<')) {
				wp_die(esc_html__('Sorry, versions of PHP 5.6.20 or less are not supported by ProjectHuddle. Please upgrade PHP to activate.', 'project-huddle'), 403);
			}
			if (version_compare(phpversion(), '7.0', '<')) {
				add_action('admin_notices', array($this, 'update_php'));
			}
		}

		private function check_secure()
		{
			if (!is_ssl()) {
				add_action('admin_notices', array($this, 'secure_notice'));
			}
		}

		/**
		 * Update WordPress notice
		 */
		public function update_wordpress()
		{
			if (!get_option('dismissed-ph-wp-version', false)) {
				echo '<div class="notice notice-error is-dismissible ph-notice" data-notice="ph-wp-version">
					<p>' . esc_html__('You need WordPress version 4.7 or higher to use ProjectHuddle.', 'project-huddle') . '</p>
				</div>';
				ph_dismiss_js();
			}
		}

		public function secure_notice()
		{
			if (!get_option('dismissed-ph-secure-notice', false)) {
				echo '<div class="notice notice-error is-dismissible ph-notice" data-notice="ph-secure-notice">
					<p><strong>ProjectHuddle</strong><br>' .
					wp_kses_post(sprintf(__('Your site does not appear to be using a secure connection. A HTTPS SSL connection is required for ProjectHuddle to work with external website connections. <div><a href="%s" class="button button-primary">Learn more</a></div>', 'project-huddle'), 'https://help.projecthuddle.com/article/91-ssl-and-https'))
					. '</p>
				</div>';
				ph_dismiss_js();
			}
		}

		/**
		 * Update PHP notice
		 */
		public function update_php()
		{
			if (!get_option('dismissed-ph-php-version', false)) {
				echo '<div class="notice notice-error is-dismissible ph-notice" data-notice="ph-php-version">
					<p>' . esc_html(sprintf(__('ProjectHuddle detected you are running an older version of PHP that WordPress will soon not support. Please update your current version of php (%s) to 7.0+ to make sure your can update WordPress in the future!', 'project-huddle'), phpversion())) . '</p>
					<p><a class="button button-primary" href="https://wordpress.org/support/update-php/" target="_blank">Read More</a><p>
				</div>';
				ph_dismiss_js();
			}
		}

		/**
		 * Setup plugin constants
		 *
		 * @access private
		 * @since 1.0.0
		 * @return void
		 */
		private function setup_constants()
		{
			// Plugin version.
			if (!defined('PH_VERSION')) {
				define('PH_VERSION', '4.5.1');
			}

			// Plugin Folder Path.
			if (!defined('PH_PLUGIN_DIR')) {
				define('PH_PLUGIN_DIR', plugin_dir_path(__FILE__));
			}

			// Plugin Folder URL.
			if (!defined('PH_PLUGIN_URL')) {
				define('PH_PLUGIN_URL', plugin_dir_url(__FILE__));
			}

			// Plugin Root File.
			if (!defined('PH_PLUGIN_FILE')) {
				define('PH_PLUGIN_FILE', __FILE__);
			}

			// set template path.
			if (!defined('PH_TEMPLATE_PATH')) {
				define('PH_TEMPLATE_PATH', apply_filters('ph_template_path', 'project-huddle/'));
			}

			// this is the URL our updater / license checker pings. Do not change.
			if (!defined('PH_SL_STORE_URL')) {
				define('PH_SL_STORE_URL', 'http://projecthuddle.com');
			}

			// item name (for updates) do no change.
			if (!defined('PH_SL_ITEM_NAME')) {
				define('PH_SL_ITEM_NAME', 'ProjectHuddle');
			}

			// item id.
			if (!defined('PH_SL_ITEM_ID')) {
				define('PH_SL_ITEM_ID', 54);
			}

			// Debug plugin.
			if (!defined('SCRIPT_DEBUG')) {
				define('SCRIPT_DEBUG', false);
			}

			if (!defined('PROJECT_HUDDLE_DEBUG')) {
				define('PROJECT_HUDDLE_DEBUG', false);
			}
		}

		/**
		 * Run crash reporting if user has this enabled
		 */
		public function error_reporting()
		{
			require_once PH_PLUGIN_DIR . 'includes/class-ph-error-reporting.php';
			self::$instance->reporting = PH_Error_Reporting::get_instance();
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
			global $ph_options;

			require_once __DIR__ . '/vendor/autoload.php';

			require_once PH_PLUGIN_DIR . 'includes/ph-i8ln.php';

			// license handler.
			require_once PH_PLUGIN_DIR . 'includes/ph-license-handler.php';

			require_once PH_PLUGIN_DIR . 'includes/ph-logging-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/settings/settings-fields.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/post-type-ui.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-style-options.php';

			$ph_options = new PH_Settings();

			// classes.	
			require_once PH_PLUGIN_DIR . 'includes/class-ph-roles.php';
			require_once PH_PLUGIN_DIR . 'includes/class-ph-permissions-controller.php';

			// translations.
			require_once PH_PLUGIN_DIR . 'includes/ph-mockup-translations.php';

			// sessions.
			require_once PH_PLUGIN_DIR . 'includes/class-ph-session.php';

			// misc.
			require_once PH_PLUGIN_DIR . 'includes/scripts.php';
			require_once PH_PLUGIN_DIR . 'includes/post-types.php';
			require_once PH_PLUGIN_DIR . 'includes/templates.php';

			// functions.
			require_once PH_PLUGIN_DIR . 'includes/ph-template-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-form-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-cache-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-project-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-image-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-comment-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-version-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-approval-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-misc-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/compatibility.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-permission-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/transient-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-notice-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/ph-url-rewrites.php';
			require_once PH_PLUGIN_DIR . 'includes/membership/ph-member-functions.php';

			// email.
			require_once PH_PLUGIN_DIR . 'includes/email/class-ph-mail-v2.php';
			require_once PH_PLUGIN_DIR . 'includes/email/email-utility-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/email/email-send-functions.php';
			require_once PH_PLUGIN_DIR . 'includes/email/background-emails.php';
			require_once PH_PLUGIN_DIR . 'includes/email/class-ph-user-email-options.php';

			// shortcodes.
			require_once PH_PLUGIN_DIR . 'includes/shortcodes.php';

			// endpoints.
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-users-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-posts-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-batch-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-multiple-posttype-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-manual-notifications-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-comments-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-versions-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-attachments-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-multiple-posttype-controller.php';
			require_once PH_PLUGIN_DIR . 'includes/api/ph-ajax-actions.php';

			// auth endpoints
			foreach (glob(PH_PLUGIN_DIR . 'includes/libraries/php-jwt/*.php') as $filename) {
				require_once $filename;
			}
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-token.php';
			require_once PH_PLUGIN_DIR . 'includes/api/endpoints/class-ph-rest-key-pair.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/jwt/class-ph-key-pair-list-table.php';

			// admin includes.
			require_once PH_PLUGIN_DIR . 'includes/admin/class-ph-admin-menu.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/class-ph-project-admin.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/meta-boxes/class-ph-admin-meta-boxes.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/meta-boxes/class-ph-project-meta-box-options.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/meta-boxes/class-ph-project-meta-box-activity.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/meta-boxes/class-ph-meta-box-images.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/meta-boxes/class-ph-project-meta-box-email-notifications.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/meta-boxes/class-ph-project-meta-box-members.php';

			// models.
			require_once PH_PLUGIN_DIR . 'includes/models/services/class-ph-rest-request.php';
			require_once PH_PLUGIN_DIR . 'includes/models/abstract-class-ph-rest-object.php';
			require_once PH_PLUGIN_DIR . 'includes/models/abstract-class-ph-project.php';
			require_once PH_PLUGIN_DIR . 'includes/models/abstract-class-ph-item.php';
			require_once PH_PLUGIN_DIR . 'includes/models/abstract-class-ph-thread.php';
			require_once PH_PLUGIN_DIR . 'includes/models/class-ph-user.php';
			require_once PH_PLUGIN_DIR . 'includes/models/class-ph-comment.php';
			require_once PH_PLUGIN_DIR . 'includes/models/class-ph-mockup-thread.php';
			require_once PH_PLUGIN_DIR . 'includes/models/class-ph-mockup-image.php';
			require_once PH_PLUGIN_DIR . 'includes/models/class-ph-mockup-project.php';
			require_once PH_PLUGIN_DIR . 'includes/models/class-ph-website-project.php';
			require_once PH_PLUGIN_DIR . 'includes/models/class-ph-website-page.php';
			require_once PH_PLUGIN_DIR . 'includes/models/class-ph-website-thread.php';
			require_once PH_PLUGIN_DIR . 'includes/models/class-ph-website-user.php';

			// addons.
			if (is_file(PH_PLUGIN_DIR . 'addons/ph-website-comments/ph-website-comments.php')) {
				include_once PH_PLUGIN_DIR . 'addons/ph-website-comments/ph-website-comments.php';
			}

			// system status.
			require_once PH_PLUGIN_DIR . 'includes/tools/status/system-status.php';

			// run installation.
			require_once PH_PLUGIN_DIR . 'includes/install.php';

			// Include Upgrade Base Class
			require_once PH_PLUGIN_DIR . 'includes/admin/upgrade-processing/class-ph-upgrade.php';

			// Include Upgrades
			require_once PH_PLUGIN_DIR . 'includes/admin/upgrade-processing/upgrade-functions.php';

			// Include Upgrade Handler
			require_once PH_PLUGIN_DIR . 'includes/admin/upgrade-processing/class-ph-upgrade-handler-page.php';
			require_once PH_PLUGIN_DIR . 'includes/admin/upgrade-processing/class-ph-upgrade-handler.php';

		}

		/**
		 * Get the template path.
		 *
		 * @return string
		 */
		public function template_path()
		{
			return apply_filters('ph_template_path', 'project-huddle/');
		}

		/**
		 * Loads the plugin language files
		 *
		 * @access public
		 * @since 1.0.0
		 * @return void
		 */
		public function load_textdomain()
		{
			// Set filter for plugin's languages directory.
			$ph_lang_dir = PH_PLUGIN_DIR . '/languages/';
			$ph_lang_dir = apply_filters('ph_languages_directory', $ph_lang_dir);

			// Traditional WordPress plugin locale filter.
			$locale = apply_filters('plugin_locale', get_locale(), 'ph');
			$mofile = sprintf('%1$s-%2$s.mo', 'project-huddle', $locale);

			// Setup paths to current locale file.
			$mofile_local  = $ph_lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/ph/' . $mofile;

			if (file_exists($mofile_global)) {
				// Look in global /wp-content/languages/ph folder.
				load_textdomain('project-huddle', $mofile_global);
			} elseif (file_exists($mofile_local)) {
				// Look in local /wp-content/plugins/project-huddle/languages/ folder.
				load_textdomain('project-huddle', $mofile_local);
			} else {
				// Load the default language files.
				load_plugin_textdomain('project-huddle', false, $ph_lang_dir);
			}
		}
	}
else :
	/**
	 * ProjectHuddle already activated
	 *
	 * @return void
	 */
	function ph_already_activated_error_notice()
	{
		$message = __('You have both Pro and Lite versions of ProjectHuddle activated. Please deactivate one of the plugins in order for ProjectHuddle to work properly.', 'project-huddle');
		echo '<div class="error"> <p>' . esc_html($message) . '</p></div>';
	}
	add_action('admin_notices', 'ph_already_activated_error_notice');
endif;

/**
 * The main function responsible for returning the one true Project_Huddle
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $ph = PH(); ?>
 *
 * @since 1.0.0
 * @return object The one true Project_Huddle Instance
 */
if (!function_exists('PH')) {
	// phpcs:ignore
	function PH($abstract = null, array $parameters = [])
	{
		return Project_Huddle::instance();
	}

	// Get PH Running.
	PH();
}
