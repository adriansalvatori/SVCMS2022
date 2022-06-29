<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

/**
 * @class Account_Endpoint
 */
class Account_Endpoint {

	/** @var string */
	public static $endpoint = 'referrals';


	function __construct() {
		add_action( 'init', [ $this, 'add_endpoints' ] );
		add_filter( 'query_vars', [ $this, 'add_query_vars' ], 0 );
		add_action( 'template_redirect', [ $this, 'init_shortcodes' ] );

		if ( AW_Referrals()->is_enabled() ) {
			add_filter( 'the_title', [ $this, 'endpoint_title' ] );
			add_filter( 'woocommerce_account_menu_items', [ $this, 'new_menu_items' ] );
			add_action( 'woocommerce_account_' . self::$endpoint .  '_endpoint', [ $this, 'endpoint_content' ] );
		}
	}


	/**
	 * @return string
	 */
	function get_title() {
		return apply_filters( 'automatewoo/referrals/account_tab_title', __( 'Referrals', 'automatewoo-referrals' ) );
	}


	/**
	 * @return string
	 */
	function get_share_link_html() {
		$text = apply_filters( 'automatewoo/referrals/account_tab_share_link_text', __( 'Refer a friend here.', 'automatewoo-referrals' ) );
		return '<a href="' . esc_url( AW_Referrals()->get_share_page_url() ) . '">' . $text . '</a>';
	}


	/**
	 * Register new endpoint to use inside My Account page.
	 */
	function add_endpoints() {
		add_rewrite_endpoint( self::$endpoint, EP_PAGES );
	}


	/**
	 * @param array $vars
	 * @return array
	 */
	function add_query_vars( $vars ) {
		$vars[] = self::$endpoint;
		return $vars;
	}


	/**
	 * Insert the new endpoint into the My Account menu.
	 *
	 * @param array $items
	 * @return array
	 */
	function new_menu_items( $items ) {

		$logout_item = false;

		if ( isset( $items['customer-logout'] ) ) {
			$logout_item = $items['customer-logout'];
			unset( $items['customer-logout'] );
		}

		$items[ self::$endpoint ] = $this->get_title();

		if ( $logout_item ) {
			$items['customer-logout'] = $logout_item;
		}

		return $items;
	}


	/**
	 * Set endpoint title.
	 *
	 * @param string $title
	 * @return string
	 */
	function endpoint_title( $title ) {
		global $wp_query;

		if ( isset( $wp_query->query_vars[ self::$endpoint ] ) && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() && is_user_logged_in() ) {

			$title = $this->get_title();
			remove_filter( 'the_title', [ $this, 'endpoint_title' ] );
		}

		return $title;
	}


	/**
	 * Endpoint HTML content.
	 */
	function endpoint_content() {
		$data = [];

		$user_id = get_current_user_id();

		$data['referrals']              = AW_Referrals()->get_available_referrals_by_user( $user_id );
		$data['available_store_credit'] = Credit::get_available_credit( $user_id );
		$data['used_referrals']         = AW_Referrals()->get_used_referrals_by_user( $user_id );
		$data['advocate']               = Advocate_Factory::get( $user_id );
		$data['share_link']             = $this->get_share_link_html();

		AW_Referrals()->get_template( 'account-tab.php', $data );
	}

	/**
	 * Init shortcodes. Only called on frontend.
	 *
	 * @since 2.3.2
	 */
	public function init_shortcodes() {
		add_shortcode( 'automatewoo_referrals_account_tab', [ $this, 'shortcode_content' ] );
	}

	/**
	 * Callback for the account tab shortcode.
	 *
	 * This shortcode is an optional secondary method of showing the account tab.
	 *
	 * @return string
	 */
	public function shortcode_content() {
		if ( ! AW()->is_request( 'frontend' ) || ! AW_Referrals()->is_enabled() ) {
			return '';
		}

		ob_start();
		$this->endpoint_content();
		return ob_get_clean();
	}

}
