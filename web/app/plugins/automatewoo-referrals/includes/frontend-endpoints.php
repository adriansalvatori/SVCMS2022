<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Clean;
use AutomateWoo\Customer_Factory;
use AutomateWoo\Error;

/**
 * @class Frontend_Endpoints
 * @since 2.1
 */
class Frontend_Endpoints {


	static function handle() {
		$action = sanitize_key( aw_request( 'aw-referrals-action' ) );

		switch ( $action ) {

			case 'redirect-to-social-share':
				self::redirect_to_social_share();
				break;

		}
	}


	static function redirect_to_social_share() {
		$customer_key = Clean::string( aw_request( 'customer' ) );
		$integration  = false;
		$advocate     = false;

		if ( aw_request( 'social' ) ) {
			$integration = Social_Integrations::get( Clean::string( aw_request( 'social' ) ) );
		}

		if ( is_user_logged_in() ) {
			$customer = Customer_Factory::get_by_user_id( get_current_user_id() );
		} else {
			$customer = Customer_Factory::get_by_key( $customer_key );
		}

		if ( $customer ) {
			$advocate = Advocate_Factory::get( $customer->get_user_id() );
		}

		if ( ! $advocate || ! $integration ) {
			wp_die( esc_html__( 'Invalid share URL.', 'automatewoo-referrals' ) );
		}

		$can_share = $advocate->can_share();

		if ( $can_share instanceof Error ) {
			wp_die( esc_html( $can_share->get_message() ) );
		}

		$advocate->store_ip(); // update advocate IP

		wp_redirect( $integration->get_share_url( $advocate ) );
		exit;
	}


}
