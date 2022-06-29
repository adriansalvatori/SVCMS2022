<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'AutomateWoo\Addon' ) ) {
	include WP_PLUGIN_DIR . '/automatewoo/includes/abstracts/addon.php';
}


class AW_Referrals_Addon extends AutomateWoo\Addon {

	/** @var AutomateWoo\Referrals\Options */
	private $options;

	/**
	 * @var AutomateWoo\Referrals\Credit
	 * @deprecated
	 * */
	public $store_credit;

	/** @var AutomateWoo\Referrals\Frontend */
	public $frontend;

	/** @var AutomateWoo\Referrals\Admin */
	public $admin;

	/** @var array */
	public $db_updates = [
		'1.1.4',
		'1.8.0',
	];


	/**
	 * @param AW_Referrals_Plugin_Data $plugin_data
	 */
	function __construct( $plugin_data ) {
		parent::__construct( $plugin_data );

		spl_autoload_register( [ $this, 'autoload' ] );
		add_filter( 'automatewoo/database_tables', [ $this , 'database_tables' ] );
		add_action( 'init', [ $this, 'set_plugin_name' ] );
	}

	/**
	 * Translatable plugin name must be defined after load_plugin_textdomain() is called.
	 *
	 * @since 2.6.2
	 */
	public function set_plugin_name() {
		$this->name = __( 'AutomateWoo - Refer A Friend', 'automatewoo-referrals' );
	}


	/**
	 * Only initiates if license is active
	 */
	function init() {

		$this->includes();

		$this->frontend = new AutomateWoo\Referrals\Frontend();

		AutomateWoo\Referrals\Credit::init();

		new AutomateWoo\Referrals\Account_Endpoint();

		if ( is_admin() ) {
			$this->admin = new AutomateWoo\Referrals\Admin();

			add_action( 'admin_init', [ $this, 'register_cron_events'] );
		}

		new AutomateWoo\Referrals\Hooks();

		do_action( 'automatewoo/referrals/after_init' );
	}


	/**
	 * @since 1.9
	 * @param $class
	 */
	function autoload( $class ) {
		$path = $this->get_autoload_path( $class );

		if ( $path && file_exists( $path ) ) {
			include $path;
		}
	}


	/**
	 * @param $class
	 * @return string|false
	 */
	function get_autoload_path( $class ) {
		if ( 0 !== strpos( $class, 'AutomateWoo\Referrals\\' ) ) {
			return false;
		}

		$file = str_replace( 'AutomateWoo\\Referrals\\', '', $class );
		$file = str_replace( '_', '-', $file );
		$file = strtolower( $file );
		$file = str_replace( '\\', '/', $file );

		return $this->path( "/includes/$file.php" );
	}



	/**
	 * Includes
	 */
	function includes() {

		include_once $this->path( '/includes/referral-functions.php' );
		include_once $this->path( '/includes/referral-manager.php' );

		if ( AW()->is_request( 'admin' ) ) {
			include_once $this->path( '/includes/admin/admin.php' );
		}

		include_once $this->path( '/includes/deprecated.php' );
	}


	/**
	 * Register cron events
	 */
	function register_cron_events() {
		if ( ! wp_next_scheduled( 'automatewoo/referrals/clean_advocate_keys' ) ) {
			wp_schedule_event( time(), 'daily', 'automatewoo/referrals/clean_advocate_keys' );
		}
	}


	/**
	 * @return AutomateWoo\Referrals\Options
	 */
	function options() {
		if ( ! isset( $this->options ) ) {
			include_once $this->path( '/includes/options.php' );
			$this->options = new AutomateWoo\Referrals\Options();
		}

		return $this->options;
	}


	/**
	 * Database tables must be registered even if the addon is not installed
	 *
	 * @param array $includes
	 * @return array
	 */
	function database_tables( $includes ) {
		$includes[ 'referrals' ]              = $this->path( '/includes/database-tables/referrals.php' );
		$includes[ 'referral-advocate-keys' ] = $this->path( '/includes/database-tables/advocate-keys.php' );
		$includes[ 'referral-invites' ]       = $this->path( '/includes/database-tables/invites.php' );
		return $includes;
	}


	/**
	 * @return array
	 */
	function get_offer_types() {
		return apply_filters(
			'automatewoo/referrals/offer_types',
			[
				'coupon_discount'            => __( 'Coupon Discount - Fixed Amount', 'automatewoo-referrals' ),
				'coupon_percentage_discount' => __( 'Coupon Discount - Percentage', 'automatewoo-referrals' ),
			]
		);
	}


	/**
	 * @return array
	 */
	function get_reward_types() {
		return apply_filters(
			'automatewoo/referrals/reward_types',
			[
				'credit'            => __( 'Store Credit - Fixed Amount', 'automatewoo-referrals' ),
				'credit_percentage' => __( 'Store Credit - Percentage Of Referral Order', 'automatewoo-referrals' ),
				'none'              => __( 'No Reward', 'automatewoo-referrals' ),
			]
		);
	}


	/**
	 * @return bool
	 */
	function is_using_store_credit() {
		return in_array( AW_Referrals()->options()->reward_type, [ 'credit', 'credit_percentage' ] );
	}


	/**
	 * @return array
	 */
	function get_referral_statuses() {
		return apply_filters(
			'automatewoo/referrals/referral_statuses',
			[
				'approved'        => __( 'Approved', 'automatewoo-referrals' ),
				'potential-fraud' => __( 'Potential Fraud', 'automatewoo-referrals' ),
				'pending'         => __( 'Pending', 'automatewoo-referrals' ),
				'rejected'        => __( 'Rejected', 'automatewoo-referrals' )
			]
		);
	}


	/**
	 * @return string;
	 */
	function get_share_page_url() {
		$url = '';

		if ( $this->options()->referrals_page ) {
			$url = get_permalink( $this->options()->referrals_page );
		}

		return apply_filters( 'automatewoo/referrals/share_url', $url );
	}


	/**
	 * @param $user_id
	 * @return AutomateWoo\Referrals\Referral[]
	 */
	function get_available_referrals_by_user( $user_id ) {

		$query = ( new AutomateWoo\Referrals\Referral_Query() )
			->where( 'advocate_id', $user_id )
			->where( 'status', 'approved' )
			->where( 'reward_amount_remaining', [ '0.00', '0' ], 'NOT IN' )
			->set_ordering( 'date' );

		return $query->get_results();
	}


	/**
	 * @param $user_id
	 * @return AutomateWoo\Referrals\Referral[]
	 */
	function get_used_referrals_by_user( $user_id ) {

		$used_query = ( new AutomateWoo\Referrals\Referral_Query() )
			->where( 'advocate_id', $user_id )
			->where( 'status', 'approved' )
			->where( 'reward_amount_remaining', [ '0.00', '0' ], 'IN' )
			->set_ordering( 'date' );

		return $used_query->get_results();
	}


	/**
	 * @param $template string
	 * @param $args array
	 */
	function get_template( $template, $args = [] ) {
		wc_get_template( $template, $args, 'automatewoo/referrals', $this->path( '/templates/' ) );
	}


	/**
	 * @return bool
	 */
	function is_enabled() {
		return $this->options()->enabled;
	}


	/**
	 * @return string
	 */
	function get_getting_started_url() {
		return AutomateWoo\Admin::get_docs_link( 'refer-a-friend/getting-started', 'activation-notice' );
	}


	/**
	 * @param string $end
	 * @return string
	 */
	function admin_path( $end = '' ) {
		return untrailingslashit( $this->plugin_path ) . '/includes/admin' . $end;
	}


	/**
	 * @param $id
	 * @return AutomateWoo\Referrals\Referral|bool
	 */
	function get_referral( $id ) {
		return AutomateWoo\Referrals\Referral_Factory::get( $id );
	}


	/**
	 * @param $id
	 * @return AutomateWoo\Referrals\Advocate_Key|bool
	 */
	function get_advocate_key( $id ) {
		return AutomateWoo\Referrals\Advocate_Key_Factory::get( $id );
	}


	/**
	 * @param $key
	 * @return AutomateWoo\Referrals\Advocate_Key|false
	 */
	function get_advocate_key_by_key( $key ) {
		return AutomateWoo\Referrals\Advocate_Key_Factory::get_by_key( $key );
	}


	/**
	 * @param $id
	 * @return AutomateWoo\Referrals\Invite|bool
	 */
	function get_invite( $id ) {
		return AutomateWoo\Referrals\Invite_Factory::get( $id );
	}


	/**
	 * Install
	 */
	function install() {
		AutomateWoo\Database_Tables::install_tables();
	}


	/** @var AW_Referrals_Addon */
	protected static $_instance;




	/**
	 * Get the customers orders that were referrals, should usually just be a single order
	 *
	 * @deprecated
	 * @param string|int $customer - user id or email
	 * @return array - ids only
	 */
	function get_referred_orders_by_customer( $customer ) {
		$referred_order_ids = [];

		foreach ( AutomateWoo\Referrals\Referral_Manager::get_referrals_by_customer( $customer, 'objects' ) as $referral ) {
			$referred_order_ids[] = $referral->get_order_id();
		}

		return $referred_order_ids;
	}

}


/**
 * @return AW_Referrals_Addon
 */
function AW_Referrals() {
	return AW_Referrals_Addon::instance( new AW_Referrals_Plugin_Data() );
}
AW_Referrals();
