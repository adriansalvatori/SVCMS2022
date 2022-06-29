<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Background_Processes;
use AutomateWoo\Clean;
use AutomateWoo\Integrations;
use AutomateWoo\Language;

/**
 * @class Hooks
 * @since 1.2.14
 */
class Hooks {

	/**
	 * Add 'init' actions here means we can load less files at 'init'
	 */
	function __construct() {

		add_filter( 'automatewoo/factories', [ $this , 'factories' ] );

		// Referrals
		if ( AW_Referrals()->options()->get_reward_event() === 'purchase' ) {
			add_action( 'woocommerce_checkout_order_processed', [ 'AutomateWoo\Referrals\Referral_Manager', 'check_order_for_referral' ], 110 ); // must run after subscriptions are created
			add_action( 'woocommerce_order_status_changed', [ 'AutomateWoo\Referrals\Referral_Manager', 'update_referral_status_on_order_status_change' ], 20, 3 );

			if ( Integrations::is_subscriptions_active() ) {
				add_action( 'woocommerce_subscription_renewal_payment_complete', [ 'AutomateWoo\Referrals\Subscriptions', 'maybe_create_referral_for_subscription_payment' ], 20, 2 );
			}
		} elseif ( AW_Referrals()->options()->get_reward_event() === 'signup' ) {
			add_action( 'automatewoo/user_registered', [ 'AutomateWoo\Referrals\Referral_Manager', 'check_signup_for_referral' ], 100 );
		}

		// Referral coupons
		if ( AW_Referrals()->options()->type === 'coupon' ) {

			add_filter( 'woocommerce_get_shop_coupon_data', [ 'AutomateWoo\Referrals\Coupons', 'catch_referral_coupons' ], 10, 3 );
			add_filter( 'woocommerce_coupon_is_valid', [ 'AutomateWoo\Referrals\Coupons', 'validate_referral_coupon' ], 10, 2 );
			add_filter( 'woocommerce_coupon_error', [ 'AutomateWoo\Referrals\Coupons', 'filter_coupon_errors' ], 10, 3 );

			add_action( 'woocommerce_after_checkout_validation', [ 'AutomateWoo\Referrals\Coupons', 'check_customer_coupons' ] );
		}

		// advocate keys
		add_action( 'automatewoo/referrals/clean_advocate_keys', [ 'AutomateWoo\Referrals\Advocate_Key_Manager', 'clean_advocate_keys' ] );

		// Workflows
		add_filter( 'automatewoo/triggers', [ 'AutomateWoo\Referrals\Workflows', 'triggers' ], 5 );
		add_filter( 'automatewoo/actions', [ 'AutomateWoo\Referrals\Workflows', 'actions' ], 5 );
		add_filter( 'automatewoo/data_types/includes', [ 'AutomateWoo\Referrals\Workflows', 'inject_data_types' ] );
		add_filter( 'automatewoo/variables', [ 'AutomateWoo\Referrals\Workflows', 'inject_variables' ] );
		add_filter( 'automatewoo/rules/includes', [ 'AutomateWoo\Referrals\Workflows', 'include_rules' ] );
		add_filter( 'automatewoo/preview_data_layer', [ 'AutomateWoo\Referrals\Workflows', 'inject_preview_data' ], 10, 2 );
		add_filter( 'automatewoo/log/data_layer_storage_keys', [ 'AutomateWoo\Referrals\Workflows', 'log_data_layer_storage_keys' ] );
		add_filter( 'automatewoo/formatted_data_layer', [ 'AutomateWoo\Referrals\Workflows', 'filter_formatted_data_layer' ], 10, 2 );

		// background processes
		add_filter( 'automatewoo/background_processes/includes', [ $this, 'register_background_processes' ] );
		add_action( 'automatewoo/referrals/send_invite_email', [ $this, 'handle_send_invite_email_event' ], 10, 3 );

		// Adding store credit to subscription renewals
		if ( Integrations::is_subscriptions_active() && AW_Referrals()->options()->use_credit_on_subscription_renewals ) {

			add_filter( 'wcs_renewal_order_created', [ 'AutomateWoo\Referrals\Subscriptions', 'maybe_add_referral_credit' ], 10, 2 );

			// ensure the renewal order total is passed through to the payment hooks, fixed in subscription v2.1
			if ( ! Integrations::is_subscriptions_active( '2.1.0' ) ) {
				add_action( 'woocommerce_scheduled_subscription_payment', [ 'AutomateWoo\Referrals\Subscriptions', 'override_gateway_payment_method' ], 5 );
			}
		}

		add_action( 'automatewoo/referrals/settings_updated_async', [ $this, 'maybe_anonymize_invite_emails' ] );
		add_action( 'automatewoo/privacy/loaded', [ $this, 'load_privacy_class' ] );

		add_action( 'wp_loaded', [ $this, 'check_for_action_endpoint' ] );
	}


	/**
	 * @param array $includes
	 * @return array
	 */
	function register_background_processes( $includes ) {
		$includes[ 'referrals_anonymize_invite_emails' ] = AW_Referrals()->path( '/includes/background-processes/anonymize-invite-emails.php' );
		return $includes;
	}


	/**
	 * @param array $types
	 * @return array
	 */
	function factories( $types ) {
		$types[ 'referral' ]              = 'AutomateWoo\Referrals\Referral_Factory';
		$types[ 'referral-advocate-key' ] = 'AutomateWoo\Referrals\Advocate_Key_Factory';
		$types[ 'referral-invite' ]       = 'AutomateWoo\Referrals\Invite_Factory';
		return $types;
	}



	function maybe_anonymize_invite_emails() {
		if ( ! AW_Referrals()->options()->anonymize_invited_emails ) {
			return;
		}

		$query = new Invite_Query();
		$results = $query->get_results_as_ids();

		if ( ! $results ) {
			return;
		}

		/** @var Background_Process_Anonymize_Invite_Emails $process */
		$process = Background_Processes::get( 'referrals_anonymize_invite_emails' );

		foreach ( $results as $result ) {
			$process->push_to_queue(
				[
					'invite' => $result
				]
			);
		}

		$process->start();
	}


	/**
	 * @since 2.0
	 */
	function load_privacy_class() {
		new Privacy();
	}


	/**
	 * Action endpoints
	 */
	static function check_for_action_endpoint() {
		if ( empty( $_GET[ 'aw-referrals-action' ] ) || is_ajax() || is_admin() ) {
			return;
		}

		Frontend_Endpoints::handle();
	}


	/**
	 * Send invite emails async.
	 *
	 * @since 2.5.0
	 *
	 * @param string $email
	 * @param int    $advocate_id
	 * @param string $language
	 */
	public function handle_send_invite_email_event( $email, $advocate_id, $language = null ) {
		$email    = Clean::email( $email );
		$advocate = Advocate_Factory::get( $advocate_id );
		$language = Clean::string( $language );

		if ( ! $email || ! $advocate ) {
			return;
		}

		if ( $language ) {
			Language::set_current( $language );
		}

		$mailer = new Invite_Email( $email, $advocate );
		$mailer->send();

		if ( $language ) {
			Language::set_original();
		}
	}

}
