<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Clean;
use AutomateWoo\Customer_Factory;
use AutomateWoo\Integrations;
use AutomateWoo\Error;
use AutomateWoo\Exception;

/**
 * Class Referral_Validator.
 *
 * @since 1.9
 */
class Referral_Validator {


	/**
	 * Validate if an order and advocate key can be a valid referral.
	 *
	 * This is used for link based referrals and subscription first payment referrals.
	 *
	 * NOTE this doesn't actually check the referral coupon or link cookie.
	 *
	 * @param \WC_Order $order
	 * @param Advocate $advocate
	 * @return bool|Error
	 */
	public static function is_order_a_valid_referral( $order, $advocate ) {
		if ( ! $advocate || ! $order || ! $customer = Customer_Factory::get_by_order( $order ) ) {
			return false;
		}

		$valid = true;

		try {
			self::validate_advocate_own_order( $advocate, $customer );
			self::validate_min_purchase_amount( $order );

			if ( AW_Referrals()->options()->allow_existing_customer_referrals ) {
				// if existing customer referrals are allowed, limit to 1 referral per customer
				self::validate_is_customers_first_referred_order( $order );
			} else {
				// check that the referred customer isn't existing
				self::validate_new_customer_order_count( $customer, $order );
			}

			self::validate_product_category_restrictions( $order );
			self::validate_advocate_limit( $advocate );

		} catch ( Exception $e ) {
			$valid = new Error( $e->getMessage() );
		}

		$valid = apply_filters( 'automatewoo/referrals/is_order_a_valid_referral', $valid, $order, $advocate, $customer );

		if ( $valid instanceof Error ) {
			self::log_validation_error( $valid, $order, $advocate );
		}

		return $valid;
	}

	/**
	 * Validates if a checkout order is a type of subscription order that is excluded from being a referral.
	 *
	 * Note this method is not validated in self::is_order_a_valid_referral()
	 *
	 * Orders that are excluded are initial free trial or sync orders.
	 *
	 * @since 2.3.1
	 *
	 * @param \WC_Order $order
	 *
	 * @return bool
	 */
	public static function is_excluded_subscription_checkout_order( $order ) {
		if ( ! Integrations::is_subscriptions_active() ) {
			return false;
		}

		$is_excluded = false;

		// Don't exclude orders with a total > 0
		if ( $order->get_total() <= 0 ) {

			// Get subscriptions that order is a parent of
			$subscriptions = wcs_get_subscriptions_for_order( $order, [ 'order_type' => 'parent' ] );

			try {
				foreach ( $subscriptions as $subscription ) {
					/** @var \WC_Subscription $subscription */

					/**
					 * 1. Check if order is an initial free trial order
					 *
					 * - subscription is in the free trial date range
					 * - is subscription parent
					 */
					if ( $subscription->get_time( 'trial_end' ) > gmdate( 'U' ) ) {
						throw new Exception();
					}

					/**
					 * 2. Check if order is an initial synced order
					 *
					 * - contains synced products
					 * - is subscription parent
					 */
					if ( \WC_Subscriptions_Synchroniser::subscription_contains_synced_product( $subscription ) ) {
						throw new Exception();
					}
				}
			} catch ( Exception $e ) {
				$is_excluded = true;
			}
		}

		return apply_filters( 'automatewoo/referrals/is_excluded_subscription_checkout_order', $is_excluded, $order );
	}

	/**
	 * Validate that the order doesn't actually belong to the advocate.
	 *
	 * @param Advocate $advocate
	 * @param \AutomateWoo\Customer $customer
	 * @throws Exception
	 */
	public static function validate_advocate_own_order( $advocate, $customer ) {
		if ( $customer->is_registered() ) {
			if ( $advocate->get_id() == $customer->get_user_id() ) {
				throw new Exception( __( 'Advocate ID matches the Customer ID.', 'automatewoo-referrals' ) );
			}
		}
	}


	/**
	 * @param \WC_Order $order
	 * @throws Exception
	 */
	public static function validate_min_purchase_amount( $order ) {
		if ( ! $min_spend = floatval( AW_Referrals()->options()->offer_min_purchase ) ) {
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


	/**
	 * Validates that the referred customer doesn't have existing orders.
	 *
	 * @param \AutomateWoo\Customer $customer
	 * @param \WC_Order $order
	 * @throws Exception
	 */
	public static function validate_new_customer_order_count( $customer, $order ) {
		$is_subscription_renewal = false;

		if ( Integrations::is_subscriptions_active() ) {
			$is_subscription_renewal = wcs_order_contains_renewal( $order );
		}

		// if the order is a subscription renewal the customer can now have 2 orders,
		// one parent and this renewal, so we only block the referral is there are more than 2 orders
		$allowed_existing_orders = $is_subscription_renewal ? 2 : 1;

		// TODO when customer order queries are more scalable use an order query that excludes
		// TODO subscription parents, using the total count here is not a perfect solution
		if ( $customer->get_order_count() > $allowed_existing_orders ) {
			throw new Exception( __( 'Customer is not new.', 'automatewoo-referrals' ) );
		}
	}


	/**
	 * @param \WC_Order $order
	 * @throws Exception
	 */
	public static function validate_product_category_restrictions( $order ) {
		if ( ! AW_Referrals()->options()->referral_required_categories && ! AW_Referrals()->options()->referral_excluded_categories ) {
			return; // no need to validate
		}

		$every_product_must_be_valid = apply_filters( 'automatewoo/referrals/referral_restriction/every_product_must_be_valid', false );
		$valid                       = $every_product_must_be_valid;

		// if one product is valid, then the order is valid
		// this is the same as how coupon validation works
		foreach ( $order->get_items() as $item ) {
			/** @var \WC_Order_Item_Product $item */
			$product = $item->get_product();

			if ( self::is_valid_for_product( $product ) ) {
				if ( ! $every_product_must_be_valid ) {
					// one product is valid
					$valid = true;
					break;
				}
			} else {
				if ( $every_product_must_be_valid ) {
					// if one product is not valid
					$valid = false;
					break;
				}
			}
		}

		if ( ! $valid ) {
			throw new Exception( __( 'Order items were not valid.', 'automatewoo-referrals' ) );
		}
	}


	/**
	 * @param \WC_Product $product
	 * @return bool
	 */
	public static function is_valid_for_product( $product ) {
		$valid         = true;
		$required_cats = Clean::ids( AW_Referrals()->options()->referral_required_categories );
		$excluded_cats = Clean::ids( AW_Referrals()->options()->referral_excluded_categories );

		if ( ! $required_cats && ! $excluded_cats ) {
			return $valid;
		}

		$product_id   = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
		$product_cats = array_unique( wc_get_product_cat_ids( $product_id ) );

		if ( $required_cats && count( array_intersect( $product_cats, $required_cats ) ) == 0 ) {
			$valid = false;
		}

		if ( $excluded_cats && count( array_intersect( $product_cats, $excluded_cats ) ) ) {
			$valid = false;
		}

		return $valid;
	}


	/**
	 * Is the order the customers first/only referred order?
	 *
	 * @param \WC_Order $order
	 * @throws Exception
	 */
	public static function validate_is_customers_first_referred_order( $order ) {
		$referred_order_ids = [];
		$customer           = $order->get_user_id() ? $order->get_user_id() : $order->get_billing_email();

		foreach ( Referral_Manager::get_referrals_by_customer( $customer, 'objects' ) as $referral ) {
			$referred_order_ids[] = $referral->get_order_id();
		}

		aw_array_remove_value( $referred_order_ids, $order->get_id() );

		if ( count( $referred_order_ids ) !== 0 ) {
			throw new Exception( __( 'Customer has already been referred.', 'automatewoo-referrals' ) );
		}
	}

	/**
	 * Validate that the advocate hasn't reached their referral limit.
	 *
	 * @param Advocate $advocate
	 * @throws Exception
	 */
	public static function validate_advocate_limit( $advocate ) {
		try {
			$advocate->validate_referral_limit();
		} catch ( Exception $e ) {
			// Tweak the exception message to be relevant when attempting to use the referral code.
			throw new Exception( __( 'Advocate referral limit has been reached.', 'automatewoo-referrals' ) );
		}
	}


	/**
	 * @param Error $error
	 * @param \WC_Order $order
	 * @param Advocate $advocate
	 */
	public static function log_validation_error( $error, $order, $advocate ) {
		$log     = new \WC_Logger();
		$message = sprintf(
			'Order: %s, Advocate: %s, Response: %s',
			$order->get_id(),
			$advocate->get_id(),
			$error->get_message()
		);
		$log->add( 'automatewoo-referrals-check-order', $message );
	}


}
