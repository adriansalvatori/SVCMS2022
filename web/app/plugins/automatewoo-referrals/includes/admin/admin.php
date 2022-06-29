<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;
use Automattic\WooCommerce\Admin\Features\Navigation\Menu;

/**
 * @class Admin
 */
class Admin {


	function __construct() {

		add_action( 'automatewoo/admin/submenu_pages', [ $this, 'admin_pages' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_filter( 'automatewoo/settings/tabs', [ $this, 'settings_tab' ] );
		add_filter( 'automatewoo/reports/tabs', [ $this, 'reports_tab' ] );
		add_filter( 'automatewoo/admin/screen_ids', [ $this, 'register_screen_id' ] );
		add_filter( 'automatewoo/dashboard/chart_widgets', [ $this, 'dashboard_chart_widgets' ] );
		add_filter( 'automatewoo/admin/controllers/includes', [ $this, 'filter_controller_includes' ] );

		add_action( 'admin_head', [ $this, 'menu_referrals_count' ] );

		// email preview
		add_filter( 'automatewoo/email_preview/subject', [ $this, 'email_preview_subject' ], 10, 2 );
		add_action( 'automatewoo/email_preview/html', [ $this, 'email_preview_html' ], 10, 2 );
		add_action( 'automatewoo/email_preview/send_test', [ $this, 'email_preview_send_test' ], 10, 3 );
		add_action( 'automatewoo/email_preview/template', [ $this, 'email_preview_template' ], 10, 3 );
	}


	/**
	 * @param $slug string
	 */
	function admin_pages( $slug ) {
		$sub_menu = [];
		$navigation_enabled = class_exists( Menu::class );

		$sub_menu[ 'referrals' ] = [
			'page_title' => __('Referrals', 'automatewoo-referrals'),
			'menu_title' => __('Referrals', 'automatewoo-referrals'),
		];

		$sub_menu[ 'referral-advocates' ] = [
			'page_title' => __('Referral advocates', 'automatewoo-referrals'),
			'menu_title' => __('Advocates', 'automatewoo-referrals'),
		];

		$sub_menu[ 'referral-codes' ] = [
			'page_title' => __('Referral codes', 'automatewoo-referrals'),
			'menu_title' => __('Referral codes', 'automatewoo-referrals'),
		];

		$sub_menu[ 'referral-invites' ] = [
			'page_title' => __('Referral invites', 'automatewoo-referrals'),
			'menu_title' => __('Invites', 'automatewoo-referrals'),
		];

		if($navigation_enabled) {
			Menu::add_plugin_category(
				array(
					'id'         => 'automatewoo-refer-a-friend',
					'title'      => __( 'Refer A Friend', 'automatewoo-referrals' ),
					'capability' => 'manage_woocommerce',
					'url'        => 'automatewoo-referrals',
					'parent'     => 'automatewoo'
				)
			);
		}

		$index = 0;
		foreach ( $sub_menu as $key => $item ) {
			$index++;

			add_submenu_page(
				$slug, 
				$item[ 'page_title' ],
				$item[ 'menu_title' ],
				'manage_woocommerce',
				'automatewoo-' . $key,
				[ 'AutomateWoo\Admin', 'load_controller' ]
			);

			if ( $navigation_enabled ) {

				Menu::add_plugin_item(
					array(
						'id'         => 'automatewoo-' . $key,
						'parent'     => 'automatewoo-refer-a-friend',
						'title'      => $item[ 'menu_title' ],
						'capability' => 'manage_woocommerce',
						'url'        => 'automatewoo-' . $key,
						'order'      => $index,
					)
				);
			}

		}

	}


	/**
	 *
	 */
	function enqueue_scripts() {

		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$suffix = '';
			$dir    = '';
		} else {
			$suffix = '.min';
			$dir    = 'min/';
		}

		wp_register_style( 'automatewoo-referrals-admin', AW_Referrals()->url( '/assets/css/automatewoo-referrals-admin.css' ), [], AW_Referrals()->version );
		wp_register_script( 'automatewoo-referrals-admin', AW_Referrals()->url( "/assets/js/{$dir}automatewoo-referrals-admin{$suffix}.js" ), ['automatewoo'], AW_Referrals()->version );

		if ( in_array( $screen_id, AW()->admin->screen_ids() ) ) {
			wp_enqueue_style( 'automatewoo-referrals-admin' );
			wp_enqueue_script( 'automatewoo-referrals-admin' );
		}
	}


	/**
	 * @param $tabs
	 * @return array
	 */
	function settings_tab( $tabs ) {
		$tabs[] = AW_Referrals()->path( '/includes/admin/settings-tab.php' );
		return $tabs;
	}


	/**
	 * @param $tabs
	 * @return array
	 */
	function reports_tab( $tabs ) {
		$tabs[] = AW_Referrals()->path( '/includes/admin/reports-tab.php' );
		return $tabs;
	}


	/**
	 * @param $ids
	 * @return array
	 */
	function register_screen_id( $ids ) {
		$ids[] = 'automatewoo_page_automatewoo-referrals';
		$ids[] = 'automatewoo_page_automatewoo-referral-advocates';
		$ids[] = 'automatewoo_page_automatewoo-referral-invites';
		$ids[] = 'automatewoo_page_automatewoo-referral-codes';
		return $ids;
	}


	/**
	 * @param $widgets
	 * @return mixed
	 */
	function dashboard_chart_widgets( $widgets ) {
		$path      = AW_Referrals()->admin_path( '/dashboard-widgets/' );
		$widgets[] = $path . 'chart-invites.php';
		return $widgets;
	}


	/**
	 * @param $view
	 * @param array $args
	 */
	function get_view( $view, $args = [] ) {

		if ( $args && is_array( $args ) )
			extract( $args );

		$path = AW_Referrals()->path( '/includes/admin/views/' . $view );

		if ( file_exists( $path ) )
			include( $path );
	}


	/**
	 * @param $subject
	 * @param $type
	 * @return string
	 */
	function email_preview_subject( $subject, $type ) {
		if ( $type !== 'referral_share' )
			return $subject;

		return AW_Referrals()->options()->share_email_subject;
	}


	/**
	 *
	 */
	function email_preview_html( $type, $args ) {

		if ( $type !== 'referral_share' )
			return;

		$user = get_user_by( 'id', get_current_user_id() );

		$email = new Invite_Email( $user->user_email, Advocate_Factory::get( $user->ID ) );

		// phpcs:disable WordPress.Security.EscapeOutput
		// Don't escape email body HTML
		echo $email->get_html();
		// phpcs:enable
	}


	/**
	 * @param $template
	 * @param $type
	 * @param $args
	 * @return string
	 */
	function email_preview_template( $template, $type, $args ) {

		if ( $type !== 'referral_share' )
			return $template;

		return AW_Referrals()->options()->share_email_template;
	}


	/**
	 * @param $type
	 * @param $to
	 * @param $args
	 */
	function email_preview_send_test( $type, $to, $args ) {

		$sent = 0;

		if ( $type !== 'referral_share' )
			return;

		foreach ( $to as $email ) {
			$mailer = new Invite_Email( $email, Advocate_Factory::get( get_current_user_id() ) );
			$send   = $mailer->send( true );

			if ( $send === true ) $sent++;
		}

		if ( $sent === 0 ) {
			wp_send_json_success( [ 'message' => __( 'Error! No emails were sent.', 'automatewoo-referrals' ) ] );
		} else {
			wp_send_json_success(
				[
					'message' => sprintf( __( 'Success! Emails sent: %s', 'automatewoo-referrals' ), $sent )
				]
			);
		}
	}


	/**
	 * @param $page
	 * @param string $data
	 * @return string
	 */
	function page_url( $page, $data = '' ) {
		switch ( $page ) {
			case 'advocates':
				return admin_url( 'admin.php?page=automatewoo-referral-advocates' );

			case 'referrals':
				return admin_url( 'admin.php?page=automatewoo-referrals' );

			case 'invites':
				return admin_url( 'admin.php?page=automatewoo-referral-invites' );

			case 'view-referral':
				return add_query_arg(
					[
						'referral_id' => $data,
						'action'      => 'view',
					],
					admin_url( 'admin.php?page=automatewoo-referrals' )
				);
		}

		return '';
	}


	/**
	 * @param \WP_User $user
	 * @return string
	 */
	function get_formatted_customer_name( $user ) {

		if ( ! $user ) {
			return '-';
		}

		if ( $user->first_name ) {
			return sprintf( _x( '%1$s %2$s', 'full name', 'automatewoo-referrals' ), $user->first_name, $user->last_name );
		}

		return $user->user_email;
	}


	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	function get_formatted_customer_name_from_order( $order ) {

		if ( ! $order ) {
			return '-';
		}

		if ( $order->get_billing_first_name() ) {
			return $order->get_formatted_billing_full_name();
		}

		return $order->get_billing_email();
	}



	/**
	 * Adds the pending referrals count to the menu
	 */
	function menu_referrals_count() {

		global $submenu;

		if ( ! isset( $submenu['automatewoo'] ) ) {
			return;
		}

		foreach ( $submenu['automatewoo'] as &$menu_item ) {
			if ( $menu_item[2] === 'automatewoo-referrals' ) {

				$count = Referral_Manager::get_referrals_count( 'pending' ) + Referral_Manager::get_referrals_count( 'potential-fraud' );

				if ( current_user_can( 'manage_woocommerce' ) && $count ) {
					$menu_item[0] .= ' <span class="awaiting-mod update-plugins count-' . $count . '"><span class="processing-count">' . number_format_i18n( $count ) . '</span></span>';
				}
			}
		}
	}


	/**
	 * @param array $controllers
	 * @return array
	 */
	function filter_controller_includes( $controllers ) {
		$path                                = AW_Referrals()->admin_path( '/controllers/' );
		$controllers[ 'referrals' ]          = $path . 'referrals.php';
		$controllers[ 'referral-advocates' ] = $path . 'advocates.php';
		$controllers[ 'referral-invites' ]   = $path . 'invites.php';
		$controllers[ 'referral-codes' ]     = $path . 'referral-codes.php';
		return $controllers;
	}


}
