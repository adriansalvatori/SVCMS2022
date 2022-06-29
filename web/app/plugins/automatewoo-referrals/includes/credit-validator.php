<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Error;
use AutomateWoo\Exception;

/**
 * Class Credit_Validator.
 *
 * Currently this is only used when adding credit to subscription renewals.
 *
 * @since 1.9
 */
class Credit_Validator {


	/**
	 * @param \WC_Order $order
	 * @return Error|bool
	 */
	static function is_order_valid_for_credit( $order ) {
		if ( ! $order || ! $advocate = Advocate_Factory::get( $order->get_user_id() ) ) {
			return false;
		}

		$valid = true;

		try {
			self::validate_min_purchase_amount( $order );

		} catch ( Exception $e ) {
			$valid = new Error( $e->getMessage() );
		}

		$valid = apply_filters( 'automatewoo/referrals/is_credit_valid_for_order', $valid, $order, $advocate );

		return $valid;
	}


	/**
	 * @param \WC_Order $order
	 * @throws Exception
	 */
	static function validate_min_purchase_amount( $order ) {
		if ( ! $min_spend = floatval( AW_Referrals()->options()->reward_min_purchase ) ) {
			return;
		}

		$order_total = $order->get_total();

		if ( apply_filters( 'automatewoo/referrals/exclude_shipping_from_order_total', true ) ) {
			$order_total -= ( (float) $order->get_shipping_total() + (float) $order->get_shipping_tax() );
		}

		if ( $order_total < $min_spend ) {
			throw new Exception( __( 'Order does not meet the minimum purchase amount.', 'automatewoo-referrals' ) );
		}

		return;
	}


}
