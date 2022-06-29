<?php
/**
 * Created by PhpStorm.
 * User: brandontassone
 * Date: 2019-01-07
 * Time: 13:44
 */

namespace Objectiv\Plugins\Checkout\Stats;

use Objectiv\Plugins\Checkout\Managers\SettingsManager;
use Objectiv\Plugins\Checkout\SingletonAbstract;
use \WC_Report_Sales_By_Date;

class StatCollection extends SingletonAbstract {

	/**
	 * The data to send to the CFW stat collection site
	 *
	 * @access private
	 */
	private $data = array();

	/**
	 * The stat collection url
	 *
	 * @var string
	 * @access private
	 */
	private $stat_collection_url = 'http://stats.checkoutwc.com/api/v1/stats';

	/**
	 * The development stat collection url
	 *
	 * @var string
	 * @access private
	 */
	private $dev_stat_collection_url = 'http://127.0.0.1:8000/api/v1/stats';

	/**
	 * The list of settings from CFW to grab
	 *
	 * @var array
	 * @access private
	 */
	private $approved_cfw_settings;

	/**
	 * The list of settings from WooCommerce to grab
	 * @var array
	 * @access private
	 */
	private $approved_woocommerce_settings;

	/**
	 * The list of store stats from WooCommerce to grab
	 *
	 * @var array
	 * @access private
	 */
	private $approved_woocomerce_store_stats;

	/**
	 * @var string
	 * @access private
	 */
	private $allow_tracking_key = 'allow_tracking';

	/**
	 * @var string
	 * @access private
	 */
	private $tracked_page_key = 'purchase_page';

	/**
	 * @var string
	 * @access private
	 */
	private $tracking_notice_key = 'tracking_notice';

	/**
	 * @var string
	 * @access private
	 */
	private $last_send_key = 'tracking_last_send';

	/**
	 * @var string
	 * @access private
	 */
	private $tracking_action_param = 'cfw_tracking_action';

	/**
	 * @var string
	 * @access private
	 */
	private $cfw_home_site_url = 'http://www.checkoutwc.com';

	/**
	 * @var array
	 * @access private
	 */
	private $woocommerce_settings = array();

	/**
	 * @var string
	 * @access private
	 */
	private $track_data_message = 'Something something track data. Something. No sensitive data is tracked.';

	/**
	 * @var SettingsManager
	 * @access private
	 */
	private $settings_manager = null;

	/**
	 * StatCollection constructor.
	 *
	 * @param SettingsManager $settings_manager
	 */
	public function init() {
		add_action( 'cfw_do_plugin_activation', array( $this, 'run_on_plugin_activation' ) );
		add_action( 'cfw_do_plugin_deactivation', array( $this, 'run_on_plugin_deactivation' ) );

		if ( defined( 'CFW_STATS_DEVELOP_URL' ) ) {
			$this->dev_stat_collection_url = CFW_STATS_DEVELOP_URL;
		}

		add_action( 'init', array( $this, 'schedule_send' ) );
		add_action( 'init', array( $this, 'tracking_opt_in_out_listener' ) );
		add_action( 'cfw_opt_into_tracking', array( $this, 'check_for_optin' ) );
		add_action( 'cfw_opt_out_of_tracking', array( $this, 'check_for_optout' ) );
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );
		add_filter( 'cron_schedules', array( $this, 'add_schedules' ) );
		add_filter( 'cfw_disable_tracking_checkin', array( $this, 'should_filter_dev_and_staging_domains' ), 10, 2 );

		$this->approved_cfw_settings = array(
			'active_template' => (object) array(
				'rename' => false,
				'name'   => null,
				'action' => null,
			),
			'enable'          => (object) array(
				'rename' => false,
				'name'   => null,
				'action' => null,
			),
			'header_scripts'  => (object) array(
				'rename' => true,
				'name'   => 'header_scripts_empty',
				'action' => function( $setting ) {
					return empty( $setting );
				},
			),
			'footer_scripts'  => (object) array(
				'rename' => true,
				'name'   => 'footer_scripts_empty',
				'action' => function( $setting ) {
					return empty( $setting );
				},
			),
		);

		$this->approved_woocommerce_settings = array(
			'woocommerce_default_country',
			'woocommerce_default_customer_address',
			'woocommerce_calc_taxes',
			'woocommerce_enable_coupons',
			'woocommerce_calc_discounts_sequentially',
			'woocommerce_currency',
			'woocommerce_prices_include_tax',
			'woocommerce_tax_based_on',
			'woocommerce_tax_round_at_subtotal',
			'woocommerce_tax_classes',
			'woocommerce_tax_display_shop',
			'woocommerce_tax_display_cart',
			'woocommerce_tax_total_display',
			'woocommerce_enable_shipping_calc',
			'woocommerce_shipping_cost_requires_address',
			'woocommerce_ship_to_destination',
			'woocommerce_enable_guest_checkout',
			'woocommerce_enable_checkout_login_reminder',
			'woocommerce_enable_signup_and_login_from_checkout',
			'woocommerce_registration_generate_username',
			'woocommerce_registration_generate_password',
		);

		$this->approved_woocomerce_store_stats = array(
			'total_sales',
			'total_orders',
			'total_items',
		);

		$this->settings_manager = SettingsManager::instance();

		if ( isset( $_POST['force-checkin'] ) ) {
			add_action(
				'init',
				function() {
					$this->send_checkin( true, true );
				}
			);
		}

		add_action(
			'cfw_general_admin_page_after_tracking_field',
			function() {
				if ( defined( 'CFW_DEV_MODE' ) && CFW_DEV_MODE ) {
					$this->setup_data();

					if ( function_exists( 'd' ) ) {
						d( $this->get_data() );
					}
					submit_button( 'Force Check-in', 'button', 'force-checkin' );
				}
			}
		);
	}

	/**
	 * Registers new cron schedules
	 *
	 * @since 1.6
	 *
	 * @param array $schedules The array of scheduled cron jobs.
	 *
	 * @return array
	 */
	public function add_schedules( $schedules = array() ): array {
		// Adds once weekly to the existing schedules.
		if ( ! isset( $schedules['weekly'] ) ) {
			$schedules['weekly'] = array(
				'interval' => 604800,
				'display'  => cfw__( 'Once Weekly' ),
			);
		}

		return $schedules;
	}

	/**
	 * Schedule a weekly checkin
	 *
	 * We send once a week (while tracking is allowed) to check in, which can be
	 * used to determine active sites.
	 *
	 * @return void
	 */
	public function schedule_send() {
		if ( $this->tracking_allowed() ) {
			add_action( 'cfw_weekly_scheduled_events_tracking', array( $this, 'send_checkin' ) );
		}
	}

	/**
	 * Send the data to the EDD server
	 *
	 * @access private
	 *
	 * @param  bool $override If we should override the tracking setting.
	 * @param  bool $ignore_last_checkin If we should ignore when the last check in was.
	 *
	 * @return bool
	 */
	public function send_checkin( bool $override = false, bool $ignore_last_checkin = false ): bool {
		$home_url = trailingslashit( home_url() );

		// Allows us to stop our own site from checking in, and a filter for our additional sites.
		if ( $home_url === $this->cfw_home_site_url ) {
			return false;
		}

		if ( apply_filters( 'cfw_disable_tracking_checkin', false, $home_url ) ) {
			return false;
		}

		if ( ! $this->tracking_allowed() && ! $override ) {
			return false;
		}

		// Send a maximum of once per week.
		$last_send = $this->get_last_send();
		if ( is_numeric( $last_send ) && $last_send > strtotime( '-1 week' ) && ! $ignore_last_checkin ) {
			return false;
		}

		$this->setup_data();
		$remote_url = CFW_DEV_MODE ? $this->dev_stat_collection_url : $this->stat_collection_url;

		$result = wp_remote_request(
			$remote_url,
			array(
				'method'      => 'POST',
				'headers'     => array(
					'Content-Type' => 'application/json',
				),
				'timeout'     => 8,
				'redirection' => 5,
				'httpversion' => '1.1',
				'body'        => wp_json_encode( $this->data ),
				'user-agent'  => 'CFW/' . CFW_VERSION . '; ' . get_bloginfo( 'url' ),
				'sslverify'   => ! CFW_DEV_MODE,
			)
		);

		$this->settings_manager->update_setting( $this->last_send_key, time() );

		return true;

	}

	/**
	 * If the tracking get parameter exists on the page lets grab the acton name and fire it off
	 *
	 * @return void
	 */
	public function tracking_opt_in_out_listener() {
		if ( key_exists( $this->tracking_action_param, $_GET ) && ! empty( $_GET[ $this->tracking_action_param ] ) ) {
			do_action( "cfw_{$_GET[$this->tracking_action_param]}" );
		} else {
			add_action( 'updated_option', array( $this, 'updated_option' ), 10, 3 );
		}
	}

	/**
	 * Check if the user has opted into tracking
	 *
	 * @access private
	 * @return bool
	 */
	private function tracking_allowed(): bool {
		return (bool) md5( trailingslashit( home_url() ) ) === $this->get_option( $this->allow_tracking_key );
	}

	/**
	 * Setup the data that is going to be tracked
	 *
	 * @access private
	 * @return void
	 */
	public function setup_data() {
		$data = array();

		// Retrieve memory limit info.
		$database_version = wc_get_server_database_version();
		$memory           = wc_let_to_num( WP_MEMORY_LIMIT );

		if ( function_exists( 'memory_get_usage' ) ) {
			$system_memory = wc_let_to_num( @ini_get( 'memory_limit' ) ); // phpcs:ignore
			$memory        = max( $memory, $system_memory );
		}

		$plugins                   = $this->get_plugins();
		$checkout_page             = $this->get_option( $this->tracked_page_key );
		$wp_data['wp_debug_mode']  = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'yes' : 'no';
		$wp_data['cfw_debug_mode'] = ( defined( 'CFW_DEV_MODE' ) && CFW_DEV_MODE ) ? 'yes' : 'no';

		$data['php_version']               = phpversion();
		$data['cfw_version']               = CFW_VERSION;
		$data['wp_version']                = get_bloginfo( 'version' );
		$data['mysql_version']             = $database_version['number'];
		$data['server']                    = $_SERVER['SERVER_SOFTWARE'] ?? '';
		$data['php_max_upload_size']       = size_format( wp_max_upload_size() );
		$data['php_default_timezone']      = date_default_timezone_get();
		$data['php_soap']                  = class_exists( 'SoapClient' ) ? 'yes' : 'no';
		$data['php_fsockopen']             = function_exists( 'fsockopen' ) ? 'yes' : 'no';
		$data['php_curl']                  = function_exists( 'curl_init' ) ? 'yes' : 'no';
		$data['memory_limit']              = size_format( $memory );
		$data['install_date']              = false !== $checkout_page ? get_post_field( 'post_date', $checkout_page ) : null;
		$data['multisite']                 = is_multisite();
		$data['locale']                    = get_locale();
		$data['theme']                     = $this->get_theme_info();
		$data['gateways']                  = self::get_active_payment_gateways();
		$data['wc_order_stats']            = $this->get_woo_order_stats();
		$data['wc_order_stats']['site_id'] = md5( get_site_url() );
		$data['shipping_methods']          = self::get_active_shipping_methods();
		$data['wc_settings']               = $this->get_woo_site_settings();
		$data['cfw_settings']              = $this->get_cfw_settings();
		$data['inactive_plugins']          = $plugins['inactive'] ?? array();
		$data['active_plugins']            = $plugins['active'] ?? array();
		$data['debug_modes']               = $wp_data;

		$this->data = $data;
	}

	/**
	 * @return mixed
	 */
	public function get_cfw_settings() {
		// Filter function for the cfw settings list
		$filter_settings = \Closure::bind(
			function( $setting ) {
				// Is the setting key in the settings approved list? Then allow it through
				return in_array( $setting, array_keys( $this->approved_cfw_settings ), true );
			},
			$this
		);

		$settings = array_filter( $this->settings_manager->settings, $filter_settings, ARRAY_FILTER_USE_KEY );

		return $this->prep_approved_cfw_settings( $settings );
	}

	/**
	 * Get list of active and inactive plugins
	 *
	 * @return array
	 */
	public function get_plugins() {
		// Retrieve current plugin information
		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugins        = array_keys( get_plugins() );
		$active_plugins = get_option( 'active_plugins', array() );

		$plugins_list = array();

		foreach ( $plugins as $key => $plugin ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				// Remove active plugins from list so we can show active and inactive separately
				$plugins_list['active'][] = $plugins[ $key ];
			} else {
				$plugins_list['inactive'][] = $plugins[ $key ];
			}
		}

		return $plugins_list;
	}

	/**
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function prep_approved_cfw_settings( $settings ) {
		foreach ( $settings as $key => $value ) {
			$setting_metadata = $this->approved_cfw_settings[ $key ];

			if ( $setting_metadata->rename ) {
				unset( $settings[ $key ] );
				$settings[ $setting_metadata->name ] = $value;
				$key                                 = $setting_metadata->name;
			}

			if ( $setting_metadata->action ) {
				$func             = $setting_metadata->action;
				$settings[ $key ] = $func( $value );
			}
		}

		return $settings;
	}

	/**
	 * @return mixed
	 */
	public function get_woo_site_settings() {
		$settings_pages = \WC_Admin_Settings::get_settings_pages();

		array_walk(
			$settings_pages,
			function( $item ) {
				$settings = $item->get_settings();

				array_walk(
					$settings,
					function( $setting ) {
						if ( empty( $setting['id'] ) ) {
							return;
						}

						$stats    = StatCollection::instance();
						$settings = $stats->get_woocommerce_settings();
						$id       = $setting['id'];

						if ( strpos( $id, 'woocommerce_' ) !== 0 ) {
							$id = "woocommerce_{$id}";
						}

						if ( ! in_array( $id, $this->approved_woocommerce_settings, true ) ) {
							return;
						}

						$setting_name = $id;
						$settings[]   = $setting_name;
						$stats->set_woocommerce_settings( $settings );
					}
				);
			}
		);

		$options      = (object) array( 'ops' => array() );
		$woo_settings = $this->get_woocommerce_settings();

		array_walk(
			$woo_settings,
			\Closure::bind(
				function( $setting ) {
					$op_value = get_option( $setting );

					if ( false === $op_value ) {
						return;
					}

					$this->ops[ $setting ] = get_option( $setting );
				},
				$options
			)
		);

		return $options->ops;
	}

	/**
	 * Get a list of all active shipping methods.
	 *
	 * @return array
	 */
	private static function get_active_shipping_methods(): array {
		$shipping_methods = WC()->shipping->load_shipping_methods();
		$active_methods   = array();
		foreach ( $shipping_methods as $id => $shipping_method ) {
			if ( isset( $shipping_method->enabled ) && 'yes' === $shipping_method->enabled ) {
				$method_title = ! empty( $shipping_method->title ) ? $shipping_method->title : $shipping_method->method_title;
				if ( 'international_delivery' === $id ) {
					$method_title .= ' (International)';
				}
				array_push( $active_methods, array( 'id' => $method_title ) );
			}
		}

		return $active_methods;
	}

	/**
	 * Get a list of all active payment gateways.
	 *
	 * @return array
	 */
	private static function get_active_payment_gateways(): array {
		$active_gateways = array();
		$gateways        = WC()->payment_gateways->payment_gateways();
		foreach ( $gateways as $id => $gateway ) {
			if ( isset( $gateway->enabled ) && 'yes' === $gateway->enabled ) {
				$active_gateways[] = $id;
			}
		}

		return $active_gateways;
	}

	/**
	 * Get the current theme info, theme name and version.
	 *
	 * @return array
	 */
	public static function get_theme_info() {
		$theme_data        = wp_get_theme();
		$theme_child_theme = wc_bool_to_string( is_child_theme() );
		$theme_wc_support  = wc_bool_to_string( current_theme_supports( 'woocommerce' ) );

		return array(
			'name'        => $theme_data->Name, // @phpcs:ignore
			'version'     => $theme_data->Version, // @phpcs:ignore
			'child_theme' => $theme_child_theme,
			'wc_support'  => $theme_wc_support,
		);
	}

	/**
	 * @param string $interval
	 *
	 * @return array
	 */
	public function get_woo_order_stats() {
		$wc_path = WC()->plugin_path();

		include_once "$wc_path/includes/admin/reports/class-wc-admin-report.php";
		include_once "$wc_path/includes/admin/reports/class-wc-report-sales-by-date.php";

		if ( ! class_exists( '\\WC_Report_Sales_By_Date' ) ) {
			return array();
		}

		add_filter( 'woocommerce_reports_get_order_report_data_args', array( $this, 'order_conditional' ) );

		$sales_by_date                 = new WC_Report_Sales_By_Date();
		$sales_by_date->start_date     = strtotime( '-7 days', strtotime( 'midnight', time() ) );
		$sales_by_date->end_date       = strtotime( 'midnight', time() );
		$sales_by_date->chart_groupby  = 'day';
		$sales_by_date->group_by_query = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';

		$report_data     = $sales_by_date->get_report_data();
		$report_data_arr = get_object_vars( $report_data );

		$collected_data = array();

		foreach ( $report_data_arr as $key => $value ) {
			if ( in_array( $key, $this->approved_woocomerce_store_stats, true ) ) {
				$collected_data[ $key ] = $value;
			}
		}

		remove_filter( 'woocommerce_reports_get_order_report_data_args', array( $this, 'order_conditional' ) );

		return $collected_data;
	}

	/**
	 * Order Conditional
	 *
	 * Conditional for excluding orders from report
	 * that don't have the postmeta "_cfw" set to true.
	 *
	 * @author Jason Witt
	 * @since  1.0.0
	 *
	 * @param array $args The arguments for setting up the order report query.
	 *
	 * @return array
	 */
	public function order_conditional( $args ) {
		$conditional = array(
			'meta_key'   => '_cfw',
			'meta_value' => 'true',
			'operator'   => '=',
		);

		$args['where_meta'] = array( $conditional );

		return $args;
	}

	/**
	 * Check for a new opt-in via the admin notice
	 *
	 * @return void
	 */
	public function check_for_optin() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->settings_manager->update_setting( $this->allow_tracking_key, md5( trailingslashit( home_url() ) ) );

		$this->send_checkin( true );

		$this->settings_manager->update_setting( $this->tracking_notice_key, '1' );

	}

	/**
	 * Check for a new opt-in via the admin notice
	 *
	 * @return void
	 */
	public function check_for_optout() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->settings_manager->delete_setting( $this->allow_tracking_key );
		$this->settings_manager->update_setting( $this->tracking_notice_key, '1' );
		wp_redirect( remove_query_arg( $this->tracking_action_param ) );
		exit;
	}

	/**
	 * Get the last time a checkin was sent
	 *
	 * @access private
	 * @return false|string
	 */
	private function get_last_send() {
		return $this->get_option( $this->last_send_key );
	}

	/**
	 * Display the admin notice to users that have not opted-in or out
	 *
	 * @return void
	 */
	public function admin_notice() {
		$hide_notice = $this->get_option( $this->tracking_notice_key );

		if ( $hide_notice ) {
			return;
		}

		if ( $this->settings_manager->get_setting( $this->allow_tracking_key ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if (
			stristr( network_site_url( '/' ), '.test' ) !== false ||
			stristr( network_site_url( '/' ), '.dev' ) !== false ||
			stristr( network_site_url( '/' ), 'localhost' ) !== false ||
			stristr( network_site_url( '/' ), ':8888' ) !== false // This is common with MAMP on OS X
		) {
			$this->settings_manager->update_setting( $this->tracking_notice_key, '1' );
		} else {
			$optin_url  = add_query_arg( 'cfw_tracking_action', 'opt_into_tracking' );
			$optout_url = add_query_arg( 'cfw_tracking_action', 'opt_out_of_tracking' );
			?>
				<div class="updated" style="display: block !important;">
					<p>
						<?php echo sprintf( cfw__( 'Allow Checkout for WooCommerce to track plugin usage? Help make Checkout for WooCommerce better.', 'checkout-wc' ) ); ?><?php echo ' <a target="_blank" href="https://www.checkoutwc.com/checkout-for-woocommerce-usage-tracking/">' . cfw_esc_html__( 'Read more about what we collect.', 'woocommerce' ) . '</a>'; ?>
					</p>
					<p>
						<a href="<?php echo esc_url( $optin_url ); ?>" class="button-secondary"><?php echo cfw__( 'Allow', 'checkout-wc' ); ?></a>
						&nbsp;<a href="<?php echo esc_url( $optout_url ); ?>" class="button-secondary"><?php echo cfw__( 'Do not allow', 'checkout-wc' ); ?></a>
					</p>
				</div>
			<?php
		}
	}

	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	public function get_option( $key ) {
		return $this->settings_manager->get_setting( $key );
	}

	/**
	 * @param $key
	 * @param $old_value
	 * @param $value
	 */
	public function updated_option( $key, $old_value, $value ) {
		if ( '_cfw__settings' === $key ) {
			if ( is_array( $value ) && isset( $value[ $this->allow_tracking_key ] ) && null !== $value[ $this->allow_tracking_key ] && isset( $old_value[ $this->allow_tracking_key ] ) && $old_value[ $this->allow_tracking_key ] !== $value[ $this->allow_tracking_key ] ) {
				if ( '0' === $value[ $this->allow_tracking_key ] ) {
					$this->settings_manager->delete_setting( $this->allow_tracking_key );
					$this->settings_manager->update_setting( $this->tracking_notice_key, '0' );

					if ( isset( $_GET[ $this->tracking_action_param ] ) ) {
						wp_redirect( remove_query_arg( $this->tracking_action_param ) );
						exit;
					}
				}

				if ( $this->tracking_allowed() ) {
					if ( ! current_user_can( 'manage_options' ) ) {
						return;
					}

					$this->send_checkin( true );

					$this->settings_manager->update_setting( $this->tracking_notice_key, '1' );
				}
			}
		}
	}

	/**
	 * @param bool $track
	 * @param string $home_url
	 *
	 * @return bool
	 */
	public function should_filter_dev_and_staging_domains( bool $track, string $home_url ): bool {
		$parts = parse_url( $home_url );

		$true_conditions = array(
			defined( 'CFW_DEV_MODE' ) && CFW_DEV_MODE,
			stripos( $home_url, '.test' ) !== false,
			'localhost' === $parts['host'],
			stripos( $home_url, '.local' ) !== false,
			stripos( $home_url, 'staging.' ) !== false,
			stripos( $home_url, '/staging/' ) !== false,
			stripos( $home_url, '//stage.' ) !== false,
			stripos( $home_url, '//test.' ) !== false,
			stripos( $home_url, '.development' ) !== false,
			stripos( $home_url, 'wpengine.com' ) !== false,
			stripos( $home_url, 'cloudwaysapps.com' ) !== false,
			stripos( $home_url, 'pantheonsite.io' ) !== false,
			stripos( $home_url, 'flywheelsites.com' ) !== false,
			stripos( $home_url, 'liquidwebsites.com' ) !== false,
			stripos( $home_url, 'c9users.io' ) !== false,
			stripos( $home_url, 'kinsta.com' ) !== false,
			stripos( $home_url, 'kinsta.cloud' ) !== false,
			stripos( $home_url, 'dev.' ) !== false,
			ip2long( $parts['host'] ),
		);

		return count( array_filter( $true_conditions ) ) ? true : $track;
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public function set_option( $key, $value ) {
		$this->settings_manager->add_setting( $key, $value );
	}

	/**
	 * @param $key
	 */
	public function delete_option( $key ) {
		$this->settings_manager->delete_setting( $key );
	}

	/**
	 * @return array
	 */
	public function get_woocommerce_settings(): array {
		return $this->woocommerce_settings;
	}

	/**
	 * @param array $woocommerce_settings
	 */
	public function set_woocommerce_settings( array $woocommerce_settings ) {
		$this->woocommerce_settings = $woocommerce_settings;
	}

	/**
	 * @return mixed
	 */
	public function get_data(): array {
		return $this->data;
	}

	/**
	 * @return string
	 */
	public function get_tracked_page_key(): string {
		return $this->tracked_page_key;
	}

	/**
	 * @return string
	 */
	public function get_last_send_key(): string {
		return $this->last_send_key;
	}

	/**
	 * @return string
	 */
	public function get_tracking_action_param(): string {
		return $this->tracking_action_param;
	}

	/**
	 * @return string
	 */
	public function get_track_data_message(): string {
		return $this->track_data_message;
	}

	public function run_on_plugin_activation() {
		if ( wp_next_scheduled( 'cfw_weekly_scheduled_events_tracking' ) ) {
			return;
		}

		wp_schedule_event( time(), 'weekly', 'cfw_weekly_scheduled_events_tracking' );
	}

	public function run_on_plugin_deactivation() {
		wp_clear_scheduled_hook( 'cfw_weekly_scheduled_events_tracking' );
	}
}
