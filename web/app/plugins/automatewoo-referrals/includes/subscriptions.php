<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

/**
 * Allow a users referral credit to be applied to a recurring subscription payment
 *
 * @class Subscriptions
 * @since 1.2
 */
class Subscriptions {


	/**
	 * @param \WC_Subscription $subscription
	 * @return int
	 */
	static function get_subscription_advocate_id( $subscription ) {
		$parent_order = $subscription->get_parent();

		if ( $parent_order ) {
			return (int) $parent_order->get_meta( '_aw_referrals_advocate_id' );
		}

		return 0;
	}


	/**
	 * @param $order \WC_Order
	 * @param $subscription \WC_Subscription
	 *
	 * @return \WC_Order
	 */
	static function maybe_add_referral_credit( $order, $subscription ) {
		if ( ! $subscription->payment_method_supports( 'subscription_amount_changes' ) ) {
			return $order;
		}

		// allow third party to modify the credit available for a subscription renewal
		$credit = apply_filters( 'automatewoo/referrals/subscription_renewal_available_credit', Credit::get_available_credit( $order->get_user_id() ), $order, $subscription );

		if ( ! $credit  ) {
			return $order;
		}

		$valid = Credit_Validator::is_order_valid_for_credit( $order );

		if ( $valid !== true ) {
			return $order;
		}


		Credit::add_credit_to_order( $order, $credit );
		Credit::remove_credit_used_in_order( $order->get_id() );

		return $order;
	}


	/**
	 * Maybe create a referral after a subscription payment.
	 *
	 * If subscription was synchronised or had free trial we delay the referral until the first payment.
	 * So now that a payment has been made maybe create a referral and therefore reward the advocate.
	 *
	 * @hook woocommerce_subscription_renewal_payment_complete
	 *
	 * @param \WC_Subscription $subscription
	 * @param \WC_Order        $order
	 */
	static function maybe_create_referral_for_subscription_payment( $subscription, $order ) {
		if ( ! $subscription || ! $order ) {
			return;
		}

		// If the subscription is a referral an advocate ID will be save to the parent order meta
		$advocate = Advocate_Factory::get( self::get_subscription_advocate_id( $subscription ) );

		if ( ! $advocate ) {
			return;
		}

		// check if parent order was a referral
		// if yes don't try to create a new referral
		if ( Referral_Factory::get_by_order_id( $subscription->get_parent_id() ) ) {
			return;
		}

		$valid = Referral_Validator::is_order_a_valid_referral( $order, $advocate );

		if ( $valid === true ) {
			Referral_Manager::create_referral_for_purchase( $order, $advocate );
		}
	}



	/**
	 * Override WC_Subscriptions_Payment_Gateways::gateway_scheduled_subscription_payment()
	 */
	static function override_gateway_payment_method() {
		remove_action( 'woocommerce_scheduled_subscription_payment', [ 'WC_Subscriptions_Payment_Gateways', 'gateway_scheduled_subscription_payment' ], 10 );
		add_action( 'woocommerce_scheduled_subscription_payment', [ __CLASS__, 'gateway_scheduled_subscription_payment' ], 10 );
	}


	/**
	 * Replacement for WC_Subscriptions_Payment_Gateways::gateway_scheduled_subscription_payment()
	 */
	static function gateway_scheduled_subscription_payment( $subscription_id, $deprecated = null ) {

		// Passing the old $user_id/$subscription_key parameters
		if ( null != $deprecated ) {
			_deprecated_argument( __METHOD__, '2.0', 'Second parameter is deprecated' );
			$subscription = wcs_get_subscription_from_key( $deprecated );
		} else {
			$subscription = wcs_get_subscription( $subscription_id );
		}

		if ( ! $subscription->is_manual() && $subscription->get_total() > 0 && ! empty( $subscription->payment_method ) ) {

			/** @var $last_renewal_order\WC_Order */
			$last_renewal_order = $subscription->get_last_order( 'all' );

			if ( ! empty( $last_renewal_order ) ) {
				if ( $last_renewal_order->needs_payment() ) {
					do_action( 'woocommerce_scheduled_subscription_payment_' . $subscription->payment_method, $last_renewal_order->get_total(), $last_renewal_order );
				} else {
					$last_renewal_order->payment_complete();
				}
			}
		}
	}

}
