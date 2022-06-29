<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Clean;
use AutomateWoo\Format;

/**
 * @class Referral_Manager
 */
class Referral_Manager {


	/**
	 * @param string|bool $status
	 * @return int
	 */
	static function get_referrals_count( $status = false ) {

		$query = new Referral_Query();

		if ( $status ) {
			if ( ! array_key_exists( $status, AW_Referrals()->get_referral_statuses() ) ) {
				return 0;
			}
			$query->where( 'status', $status );
		}

		return $query->get_count();
	}


	/**
	 * Get the referrals matching the customer / friend.
	 * In most cases this should return one result.
	 *
	 * @param string|int $customer - user id or email
	 * @param string $return 'objects', 'ids'
	 * @return array|Referral[]
	 */
	static function get_referrals_by_customer( $customer, $return = 'ids' ) {
		$orders = wc_get_orders(
			[
				'type'     => 'shop_order',
				'customer' => $customer,
				'status'   => aw_get_counted_order_statuses( false ),
				'return'   => 'ids',
				'limit'    => - 1
			]
		);

		if ( ! $orders ) {
			return [];
		}

		$query = new Referral_Query();
		$query->where_status( 'rejected', '!=' );
		$query->where_order( $orders );

		return 'ids' === $return ? $query->get_results_as_ids() : $query->get_results();
	}


	/**
	 * Checks if a checkout order is a referral.
	 *
	 * Only handles orders created via the checkout process.
	 *
	 * @param int $order_id
	 */
	static function check_order_for_referral( $order_id ) {
		if ( ! $order = wc_get_order( $order_id ) ) {
			return;
		}

		if ( $order->get_meta( '_aw_referral_processed' ) ) {
			return;
		}

		$advocate_id = self::get_advocate_from_checkout_order( $order );
		$advocate    = Advocate_Factory::get( $advocate_id );

		if ( $advocate ) {
			// store the advocate id even though this may not be a valid referral
			// this is needed later for subscriptions
			$order->update_meta_data( '_aw_referrals_advocate_id', $advocate->get_id() );
			self::maybe_create_referral_for_checkout_order( $order, $advocate );
		}

		$order->update_meta_data( '_aw_referral_processed', true );
		$order->save();
	}


	/**
	 * Attempts to create a referral for an order created via checkout.
	 *
	 * If using link tracking this is where validation will occur.
	 *
	 * @param \WC_Order $order
	 * @param Advocate $advocate
	 * @return Referral|bool
	 */
	static function maybe_create_referral_for_checkout_order( $order, $advocate ) {
		if ( ! $advocate || ! $order ) {
			return false;
		}

		if ( Referral_Validator::is_excluded_subscription_checkout_order( $order ) ) {
			return false;
		}

		// link referrals must now be validated
		// coupon referrals are validated earlier
		if ( AW_Referrals()->options()->type === 'link' ) {
			$valid = Referral_Validator::is_order_a_valid_referral( $order, $advocate );

			if ( $valid !== true ) {
				return false;
			}
		}

		return self::create_referral_for_purchase( $order, $advocate );
	}


	/**
	 * @param int $user_id
	 */
	static function check_signup_for_referral( $user_id ) {
		if ( AW_Referrals()->options()->type !== 'link' ) {
			return;
		}

		$user = get_user_by( 'id', $user_id );

		update_user_meta( $user_id, '_automatewoo_referral_ip_address', \WC_Geolocation::get_ip_address() );

		$advocate_key = self::get_advocate_key_from_cookie();
		self::clear_cookie(); // clear the cookie, only used once

		if ( $advocate_key ) {
			$advocate = Advocate_Factory::get( $advocate_key->get_advocate_id() );

			if ( $advocate ) {
				self::create_referral_for_signup( $user, $advocate );
			}
		}

	}


	/**
	 * Get the advocate ID from an order placed during checkout.
	 *
	 * Coupons have already passed validation at this point but links have not.
	 *
	 * @param \WC_Order $order
	 * @return false|int
	 */
	static function get_advocate_from_checkout_order( $order ) {
		$advocate_id = false;

		if ( AW_Referrals()->options()->type === 'coupon' ) {
			foreach ( $order->get_items( 'coupon' ) as $coupon ) {
				if ( $advocate_id = Coupons::is_valid_referral_coupon( $coupon->get_code() ) ) {
					continue;
				}
			}
		} elseif ( AW_Referrals()->options()->type === 'link' ) {
			if ( $key = self::get_advocate_key_from_cookie() ) {
				self::clear_cookie(); // clear the cookie
				$advocate_id = $key->get_advocate_id();
			}
		}

		/**
		 * This filter allows complete control over which advocate refers an order.
		 * By returning false to this filter no referral will be created for the order.
		 *
		 * @since 1.7.11
		 */
		return apply_filters( 'automatewoo/referrals/referred_order_advocate', $advocate_id, $order->get_id() );
	}


	/**
	 * Retrieve advocate key object from cookie
	 * Note that cookies are only used when using link tracking
	 * @return Advocate_Key|bool
	 */
	static function get_advocate_key_from_cookie() {
		if ( empty( $_COOKIE['aw_referral_key'] ) ) {
			return false;
		}

		$key_object = Advocate_Key_Factory::get_by_key( Clean::string( $_COOKIE['aw_referral_key'] ) );

		if ( ! $key_object || ! $key_object->is_valid() ) {
			return false;
		}

		return $key_object;
	}


	/**
	 * @return bool
	 */
	static function clear_cookie() {
		if ( ! headers_sent() ) {
			wc_setcookie( 'aw_referral_key', '', time() - HOUR_IN_SECONDS );
			return true;
		}
		return false;
	}


	/**
	 * @param Advocate $advocate
	 * @return Referral
	 */
	static function create_base_referral( $advocate ) {
		$referral = new Referral();
		$referral->set_advocate_id( $advocate->get_id() );
		$referral->set_date( new \DateTime() );
		$referral->set_reward_type( AW_Referrals()->options()->reward_type );
		return $referral;
	}


	/**
	 * @param $order \WC_Order
	 * @param $advocate Advocate
	 * @return Referral
	 */
	static function create_referral_for_purchase( $order, $advocate ) {

		$referral = self::create_base_referral( $advocate );

		$referral->set_order_id( $order->get_id() );
		$referral->set_user_id( $order->get_user_id() );

		if ( AW_Referrals()->options()->type === 'coupon' ) {
			$referral->set_offer_type( AW_Referrals()->options()->offer_type );
			$referral->set_offer_amount( AW_Referrals()->options()->offer_amount );
		}

		$reward_amount = self::get_referral_reward_amount( $advocate, $order );

		$referral->set_initial_reward_amount( $reward_amount );

		$referral->save();

		if ( $referral->is_potential_fraud() ) {
			$referral->update_status( 'potential-fraud' );
		} else {
			$referral->update_status( 'pending' );
		}

		$order->update_meta_data( '_aw_referral_id', $referral->get_id() );
		$order->save();

		do_action( 'automatewoo/referrals/referral_created', $referral );

		return $referral;
	}



	/**
	 * @param $user \WP_User
	 * @param $advocate Advocate
	 * @return Referral|false
	 */
	static function create_referral_for_signup( $user, $advocate ) {
		if ( AW_Referrals()->options()->type !== 'link' ) {
			return false;
		}

		$referral = self::create_base_referral( $advocate );

		$referral->set_user_id( $user->ID );

		$reward_amount = self::get_referral_reward_amount( $advocate );

		$referral->set_initial_reward_amount( $reward_amount );

		$referral->save();

		if ( $referral->is_potential_fraud() ) {
			$referral->update_status( 'potential-fraud' );
		} elseif ( AW_Referrals()->options()->auto_approve ) {
			$referral->update_status( 'approved' );
		} else {
			$referral->update_status( 'pending' );
		}

		do_action( 'automatewoo/referrals/referral_created', $referral );

		return $referral;
	}


	/**
	 * @param Advocate $advocate
	 * @param bool|\WC_Order $order
	 * @return float
	 */
	static function get_referral_reward_amount( $advocate, $order = false ) {

		$reward_amount = 0;

		switch ( AW_Referrals()->options()->reward_type ) {

			case 'credit':
				$reward_amount = AW_Referrals()->options()->reward_amount;
				break;

			case 'credit_percentage':
				if ( $order ) {
					$reward_percentage = AW_Referrals()->options()->reward_amount;
					$order_total       = $order->get_total();

					if ( apply_filters( 'automatewoo/referrals/exclude_shipping_from_order_total', true ) ) {
						$order_total -= ( (float) $order->get_shipping_total() + (float) $order->get_shipping_tax() );
					}

					$reward_amount = $order_total * $reward_percentage / 100;
				}
				break;
		}

		return (float) Format::decimal( apply_filters( 'automatewoo/referrals/reward_amount', $reward_amount, $advocate, $order ) );
	}


	/**
	 * @param $order_id
	 * @param $old_status
	 * @param $new_status
	 */
	static function update_referral_status_on_order_status_change( $order_id, $old_status, $new_status ) {
		if ( ! $order = wc_get_order( $order_id ) ) {
			return;
		}

		if ( ! $referral = Referral_Factory::get_by_order_id( $order_id ) ) {
			return;
		}

		$approved_statuses = [];
		$pending_statuses  = [ 'pending', 'on-hold' ];

		if ( AW_Referrals()->options()->auto_approve === 'paid' ) {
			$approved_statuses = wc_get_is_paid_statuses();
		} elseif ( AW_Referrals()->options()->auto_approve === 'completed' ) {
			$pending_statuses[] = 'processing';
			$approved_statuses  = [ 'completed' ];
		}

		$approved_statuses = apply_filters( 'automatewoo/referrals/approved_referral_order_statuses', $approved_statuses );
		$pending_statuses  = apply_filters( 'automatewoo/referrals/pending_referral_order_statuses', $pending_statuses );
		$rejected_statuses = apply_filters( 'automatewoo/referrals/rejected_referral_order_statuses', [ 'cancelled', 'failed', 'refunded' ] );

		if ( in_array( $new_status, $approved_statuses ) ) {
			if ( ! $referral->is_potential_fraud() ) {
				$referral->update_status( 'approved' );
			}
		} elseif ( in_array( $new_status, $pending_statuses ) ) {
			if ( $referral->is_potential_fraud() ) {
				$referral->update_status( 'potential-fraud' );
			} else {
				$referral->update_status( 'pending' );
			}
		} elseif ( in_array( $new_status, $rejected_statuses ) ) {
			$referral->update_status( 'rejected' );
		}
	}


}
