<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Boot
 *
 * @package Uncanny_Automator_Pro
 */
class Boot {

	/**
	 * The instance of the class
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Boot
	 */
	private static $instance = null;

	/**
	 * @var Automator_Pro_Helpers_Recipe
	 */
	public static $internal_helpers;

	/**
	 * @var
	 */
	public $internal_process;

	/**
	 * @var Internal_Triggers_Actions
	 */
	public $internal_triggers_actions;

	/**
	 * @var Automator_Pro_Cache_Handler
	 */
	public $automator_pro_cache_handler;

	/**
	 * @var array
	 */
	public static $core_class_inits = array();

	/**
	 * class constructor
	 */
	public function __construct() {

		if ( file_exists( dirname( AUTOMATOR_PRO_FILE ) . '/vendor/woocommerce/action-scheduler/action-scheduler.php' ) ) {
			include_once dirname( AUTOMATOR_PRO_FILE ) . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
		}

		$this->require_class_files();

		add_action( 'plugins_loaded', array( $this, 'boot_child_plugin' ), 9 );
		add_filter( 'upgrader_pre_install', array( $this, 'upgrader_pre_install' ), 99, 2 );
		// Show upgrade notice from readme.txt
		add_action(
			'in_plugin_update_message-' . plugin_basename( AUTOMATOR_PRO_FILE ),
			array(
				$this,
				'in_plugin_update_message',
			),
			10,
			2
		);

		add_action( 'admin_notices', array( $this, 'automator_free_version_check' ) );

		if ( file_exists( UAPro_ABSPATH . 'src/libraries/autoload.php' ) ) {
			include_once UAPro_ABSPATH . 'src/libraries/autoload.php';
		}
	}

	/**
	 *
	 */
	public function automator_free_version_check() {
		if ( defined( 'AUTOMATOR_PLUGIN_VERSION' ) && version_compare( AUTOMATOR_PLUGIN_VERSION, '3.1', '<' ) ) {
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'thickbox' );
			$class   = 'notice notice-error';
			$url     = admin_url( 'plugin-install.php?tab=plugin-information&plugin=uncanny-automator&section=changelog&TB_iframe=true&width=850&height=946' );
			$message = sprintf(
				'<strong style="color:red;">%s</strong> %s',
				sprintf( __( 'WARNING: Your version of %s is out of date.', 'uncanny-automator-pro' ), 'Uncanny Automator' ),
				sprintf(
					__( 'Please %s to ensure your recipes continue to run.', 'uncanny-automator-pro' ),
					sprintf( '<a href="%s" class="thickbox open-plugin-details-modal">%s</a>', $url, __( 'update immediately', 'uncanny-automator-pro' ) )
				)
			);

			printf( '<div class="%1$s"><p><span class="dashicons dashicons-warning" style="color: red"></span>%2$s</p></div>', esc_attr( $class ), $message );
		}
	}

	/**
	 * Creates singleton instance of Boot class and defines which directories are autoloaded
	 *
	 * @return Boot
	 * @since 1.0.0
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			// Lets boot up!
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * SPL Auto Loader functions
	 *
	 * @param string $class
	 *
	 * @since 1.0.0
	 */
	private function require_class_files() {
		/**
		 * Webhook related files - Start
		 */
		self::$core_class_inits['Webhook_Rest_Handler']        = __DIR__ . '/core/webhook/webhook-rest-handler.php';
		self::$core_class_inits['Webhook_Rest_Sample_Handler'] = __DIR__ . '/core/webhook/webhook-rest-sample-handler.php';
		self::$core_class_inits['Webhook_Ajax_Handler']        = __DIR__ . '/core/webhook/webhook-ajax-handler.php';
		self::$core_class_inits['Webhook_Static_Content']      = __DIR__ . '/core/webhook/webhook-static-content.php';
		self::$core_class_inits['Webhook_Common_Options']      = __DIR__ . '/core/webhook/webhook-common-options.php';
		/**
		 * Webhook related files - End
		 */
		self::$core_class_inits['Automator_Pro_Handle_Anonymous'] = __DIR__ . '/core/classes/automator-pro-handle-anonymous.php';
		self::$core_class_inits['Magic_Button']                   = __DIR__ . '/core/classes/magic-button.php';
		self::$core_class_inits['Pro_Filters']                    = __DIR__ . '/core/classes/pro-filters.php';
		self::$core_class_inits['Pro_Ui']                         = __DIR__ . '/core/classes/pro-ui.php';
		self::$core_class_inits['Activity_Log_Settings']          = __DIR__ . '/core/extensions/activity-log-settings.php';
		self::$core_class_inits['Async_Actions']                  = __DIR__ . '/core/classes/async-actions.php';
		self::$core_class_inits['Actions_Conditions']             = __DIR__ . '/core/classes/actions-conditions.php';

		// Licensing
		self::$core_class_inits['Licensing'] = __DIR__ . '/core/admin/licensing/licensing.php';
	}

	/**
	 * Looks through all defined directories and modifies file name to create new class instance.
	 *
	 * @since 1.0.0
	 */
	private function auto_initialize_classes() {
		foreach ( self::$core_class_inits as $class_name => $file ) {
			require_once $file;
			$class = __NAMESPACE__ . '\\' . $class_name;
			Utilities::add_class_instance( $class, new $class() );
		}
	}

	/**
	 * @param $args
	 * @param $response
	 */
	public function in_plugin_update_message( $args, $response ) {
		$upgrade_notice = '';
		if ( isset( $response->upgrade_notice ) && ! empty( $response->upgrade_notice ) ) {
			$upgrade_notice .= '<p class="ua_plugin_upgrade_notice">';
			$upgrade_notice .= sprintf( '<strong>%s</strong> %s', __( 'Heads up!', 'uncanny-automator' ), preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $response->upgrade_notice ) );
			$upgrade_notice .= '</p>';
		}

		echo apply_filters( 'uap_pro_in_plugin_update_message', $upgrade_notice ? '</p>' . wp_kses_post( $upgrade_notice ) . '<p class="dummy">' : '' ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Placeholder function for future use
	 *
	 * @param $response
	 * @param $extra
	 *
	 * @return mixed
	 *
	 * @since  2.8
	 * @author Saad S.
	 */
	public function upgrader_pre_install( $response, $extra ) {

		return $response;
	}

	/**
	 *
	 */
	public function boot_child_plugin() {

		if ( ! defined( 'AUTOMATOR_BASE_FILE' ) && ! defined( 'UAP_AUTOMATOR_FILE_' ) ) {
			add_action( 'admin_notices', array( $this, 'free_needs_to_be_installed' ) );

			return;
		}

		if ( ! $this->is_php8_compat() ) {
			add_action( 'admin_notices', array( $this, 'check_automator32_php8_compat_message' ) );

			return;
		}

		if ( ! defined( 'AUTOMATOR_BASE_FILE' ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'free_needs_to_be_upgraded' ) );

			return;
		}

		if ( defined( 'AUTOMATOR_BASE_FILE' ) ) {
			if ( class_exists( '\Uncanny_Automator\Automator_Recipe_Process' ) ) {
				include_once Utilities::get_include( 'automator-pro-recipe-process.php' );
				$this->internal_process = new Automator_Pro_Recipe_Process();
			}
			if ( class_exists( '\Uncanny_Automator\Automator_Recipe_Process_User' ) ) {
				include_once Utilities::get_include( 'automator-pro-recipe-process-user.php' );
				include_once Utilities::get_include( 'automator-pro-recipe-process-anon.php' );
				Automator_Pro()->process->anon = new Automator_Pro_Recipe_Process_Anon();
			}
			if ( class_exists( '\Uncanny_Automator\Automator_Recipe_Process_Complete' ) ) {
				include_once Utilities::get_include( 'automator-pro-recipe-process-complete.php' );
				Automator_Pro()->complete->anon = new Automator_Pro_Recipe_Process_Complete();
			}

			include_once Utilities::get_include( 'automator-pro-helpers-recipe.php' );
			self::$internal_helpers = new Automator_Pro_Helpers_Recipe();
		} else {
			Automator_Pro()->helpers                  = new \stdClass();
			Automator_Pro()->process                  = new \stdClass();
			Automator_Pro()->helpers->recipe          = new \stdClass();
			Automator_Pro()->helpers->recipe->field   = Automator_Pro()->options;
			Automator_Pro()->helpers->recipe->options = Automator_Pro()->options;
		}

		include_once Utilities::get_include( 'automator-pro-cache-handler.php' );
		$this->automator_pro_cache_handler = new Automator_Pro_Cache_Handler();

		include_once Utilities::get_include( 'internal-triggers-actions.php' );
		$this->internal_triggers_actions = new Internal_Triggers_Actions();

		// Initialize all classes in given directories
		$this->auto_initialize_classes();

		/* Licensing */
		// URL of store powering the plugin
		/**
		 * @deprecated v3.1 use AUTOMATOR_PRO_STORE_URL
		 */
		define( 'AUTOMATOR_AUTOMATOR_PRO_STORE_URL', AUTOMATOR_PRO_STORE_URL ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

		// Store download name/title
		/**
		 * @deprecated v3.1 use AUTOMATOR_PRO_ITEM_NAME
		 */
		define( 'AUTOMATOR_AUTOMATOR_PRO_ITEM_NAME', AUTOMATOR_PRO_ITEM_NAME ); // you should use your own CONSTANT name, and be sure to replace it throughout this file
		/**
		 * Make sure to force true in Free IF Pro >= 3.9 And Free is < 3.9
		 */
		add_filter(
			'automator_do_load_options',
			function ( $status, $class ) {
				$migrated = apply_filters(
					'automator_pro_load_options_override',
					array(
						'Uncanny_Automator\Woocommerce_Helpers',
						'Uncanny_Automator\Divi_Helpers',
						'Uncanny_Automator\Elementor_Helpers',
						'Uncanny_Automator\Wp_Helpers',
						'Uncanny_Automator\Wpforms_Helpers',
						'Uncanny_Automator\Wp_Fusion_Helpers',
						'Uncanny_Automator\Edd_Helpers',
					)
				);
				if ( array_intersect( array( $class ), $migrated ) ) {
					return true;
				}

				return $status;
			},
			99,
			2
		);
	}

	/**
	 * Run error notice in Uncanny Automator is not installed
	 */
	public function free_needs_to_be_installed() {

		$class = 'notice notice-error';

		if ( defined( 'AUTOMATOR_BASE_FILE' ) ) {
			// An old version of Uncanny Automator is running
			/* translators: 1. Trademarked term. 2. Trademarked term */
			$message = sprintf( __( '%1$s needs to be updated before it can be used with the new updates and enhancements of %2$s.', 'uncanny-automator-pro' ), 'Uncanny Automator Pro', 'Uncanny Automator' );

		} else {
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'thickbox' );
			$class = 'notice notice-error';
			// An old version of Uncanny Automator is running
			$link = admin_url( 'plugin-install.php?tab=plugin-information&plugin=uncanny-automator&section=changelog&TB_iframe=true&width=850&height=946' );
			/* translators: 1. Trademarked term. 2. Trademarked term */
			$url = sprintf( '<a href="%s" class="thickbox open-plugin-details-modal">%s</a>', $link, __( 'Please install & activate Uncanny Automator.', 'uncanny-automator-pro' ) );
			/* translators: 1. Trademarked term. 2. Trademarked term */
			$message = sprintf( __( '%2$s is inactive. The %1$s plugin must be active for %2$s to work. %3$s', 'uncanny-automator-pro' ), 'Uncanny Automator', 'Uncanny Automator Pro', $url );
		}

		printf( '<div class="%1$s"><h3>%2$s</h3></div>', esc_attr( $class ), $message ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Run error notice in Uncanny Automator is not installed
	 */
	public static function free_needs_to_be_upgraded() {
		if ( version_compare( AUTOMATOR_PLUGIN_VERSION, '3.2', '>=' ) ) {
			return;
		}
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'thickbox' );
		$class   = 'notice notice-error';
		$version = '3.2';
		// An old version of Uncanny Automator is running
		$url = admin_url( 'plugin-install.php?tab=plugin-information&plugin=uncanny-automator&section=changelog&TB_iframe=true&width=850&height=946' );

		/* translators: 1. Trademarked term. 2. Trademarked term */
		$message        = sprintf( __( 'The version of %1$s you have installed is not compatible with this version of %2$s.', 'uncanny-automator-pro' ), 'Uncanny Automator', 'Uncanny Automator Pro' );
		$message_update = sprintf( __( 'Please update %1$s to version %2$s or later.', 'uncanny-automator-pro' ), 'Uncanny Automator', $version );

		printf( '<div class="%1$s"><h3 style="font-weight: bold; color: red"><span class="dashicons dashicons-warning"></span>%2$s <a href="%3$s" class="thickbox open-plugin-details-modal">' . $message_update . '</a></h3></div>', esc_attr( $class ), esc_html( $message ), $url );
	}

	/**
	 * is_php8_compat
	 *
	 * Checks and displays an admin notices if php is version 8
	 * or above and both automator free and pro is version 3.2 or above.
	 *
	 * @return boolean True if version is 8 and both free or pro is less than 3.2. Otherwise, false.
	 */
	protected function is_php8_compat() {
		if ( ! defined( 'AUTOMATOR_PLUGIN_VERSION' ) ) {
			return false;
		}

		// Check if the php version is 8.0 and above.
		$is_php8 = version_compare( PHP_VERSION, '8.0.0', '>=' );

		if ( ! $is_php8 ) {
			return true;
		}

		$automator_version_is_less_than_3_2 = version_compare( AUTOMATOR_PLUGIN_VERSION, '3.2', '<' );

		// If > php8.
		// If either of free and pro is < 3.2.
		if ( $automator_version_is_less_than_3_2 ) {
			return false;
		}

		return true;
	}

	/**
	 * check_automator32_php8_compat_message
	 *
	 * Callback function from check_automator32_php8_compat. Shows an admin notice.
	 *
	 * @return void
	 */
	public function check_automator32_php8_compat_message() {
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'thickbox' );
		$class   = 'notice notice-error';
		$version = '3.2';
		// An old version of Uncanny Automator is running
		$url = admin_url( 'plugin-install.php?tab=plugin-information&plugin=uncanny-automator&section=changelog&TB_iframe=true&width=850&height=946' );

		/* translators: 1. Trademarked term. 2. Trademarked term */
		$message        = sprintf( __( "%1\$s recipes have been disabled because your version of PHP (%3\$s) is not fully compatible with the version of %2\$s that's installed.", 'uncanny-automator-pro' ), 'Uncanny Automator Pro', 'Uncanny Automator', PHP_VERSION );
		$message_update = sprintf( __( 'Please update %1$s to version %2$s or later.', 'uncanny-automator-pro' ), 'Uncanny Automator', $version );

		printf( '<div class="%1$s"><h3 style="font-weight: bold; color: red"><span class="dashicons dashicons-warning"></span>%2$s <a href="%3$s" class="thickbox open-plugin-details-modal">' . $message_update . '</a></h3></div>', esc_attr( $class ), esc_html( $message ), $url );
	}
}
