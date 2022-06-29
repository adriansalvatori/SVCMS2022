<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;
use AutomateWoo\Clean;

defined( 'ABSPATH' ) || exit;

/**
 * @class Referral
 */
class Referral extends AutomateWoo\Model {

	/** @var string  */
	public $table_id = 'referrals';

	/** @var string  */
	public $object_type = 'referral';


	/**
	 * @param $id
	 */
	function __construct( $id = false ) {
		if ( $id ) $this->get_by( 'id', $id );
	}


	/**
	 * @return string
	 */
	function get_status() {
		return Clean::string( $this->get_prop( 'status' ) );
	}


	/**
	 * @param string $status
	 */
	function set_status( $status ) {
		$this->set_prop( 'status', Clean::string( $status ) );
	}


	/**
	 * @param string|array $status
	 * @return bool
	 */
	function has_status( $status ) {
		return in_array( $this->get_status(), (array) $status );
	}


	/**
	 * @return int
	 */
	function get_advocate_id() {
		return Clean::id( $this->get_prop( 'advocate_id' ) );
	}


	/**
	 * @param int $id
	 */
	function set_advocate_id( $id ) {
		$this->set_prop( 'advocate_id', Clean::id( $id ) );
	}


	/**
	 * @return string
	 */
	function get_reward_type() {
		return Clean::string( $this->get_prop( 'reward_type' ) );
	}


	/**
	 * @param $type string
	 */
	function set_reward_type( $type ) {
		$this->set_prop( 'reward_type', Clean::string( $type ) );
	}


	/**
	 * @return string
	 */
	function get_offer_type() {
		return Clean::string( $this->get_prop( 'offer_type' ) );
	}


	/**
	 * @param string $offer_type
	 */
	function set_offer_type( $offer_type ) {
		$this->set_prop( 'offer_type', Clean::string( $offer_type ) );
	}


	/**
	 * @param string|float $amount
	 */
	function set_offer_amount( $amount ) {
		$this->set_prop( 'offer_amount', wc_format_decimal( $amount, wc_get_price_decimals() ) );
	}


	/**
	 * @return float
	 */
	function get_offer_amount() {
		return (float) $this->get_prop( 'offer_amount' );
	}


	/**
	 * @return Advocate|false
	 */
	function get_advocate() {
		return Advocate_Factory::get( $this->get_advocate_id() );
	}


	/**
	 * @return int
	 */
	function get_order_id() {
		return Clean::id( $this->get_prop( 'order_id' ) );
	}


	/**
	 * @param int $id
	 */
	function set_order_id( $id ) {
		$this->set_prop( 'order_id', Clean::id( $id ) );
	}


	/**
	 * @param $id
	 */
	function set_user_id( $id ) {
		$this->set_prop( 'user_id', Clean::id( $id ) );
	}


	/**
	 * @return int
	 */
	function get_user_id() {
		return Clean::id( $this->get_prop( 'user_id' ) );
	}


	/**
	 * @param \DateTime $datetime
	 */
	function set_date( $datetime ) {
		$this->set_date_column( 'date', $datetime );
	}


	/**
	 * @return bool|\DateTime
	 */
	function get_date() {
		return $this->get_date_column( 'date' );
	}


	/**
	 * @param string|float $amount
	 */
	function set_reward_amount( $amount ) {
		$this->set_prop( 'reward_amount', wc_format_decimal( $amount, wc_get_price_decimals() ) );
	}


	/**
	 * @return float
	 */
	function get_reward_amount() {
		return (float) $this->get_prop( 'reward_amount' );
	}


	/**
	 * @param string|float $amount
	 */
	function set_reward_amount_remaining( $amount ) {
		$this->set_prop( 'reward_amount_remaining', wc_format_decimal( $amount, wc_get_price_decimals() ) );
	}


	/**
	 * @return float
	 */
	function get_reward_amount_remaining() {
		return (float) $this->get_prop( 'reward_amount_remaining' );
	}



	/**
	 * @param string $amount
	 */
	function set_initial_reward_amount( $amount ) {
		$this->set_reward_amount( $amount );
		$this->set_reward_amount_remaining( $amount );
	}


	/**
	 * @return \WC_Order|false
	 */
	function get_order() {
		return wc_get_order( $this->get_order_id() );
	}


	/**
	 * @return int
	 */
	function get_discounted_amount() {

		$order = $this->get_order();

		foreach ( $order->get_items( 'coupon' ) as $coupon ) {
			/** @var \WC_Order_Item_Coupon $coupon */
			if ( Coupons::matches_referral_coupon_pattern( $coupon->get_code() ) ) {
				return $coupon->get_discount();
			}
		}

		return 0;
	}


	/**
	 * @return string|false
	 */
	function get_customer_ip_address() {

		if ( AW_Referrals()->options()->get_reward_event() === 'purchase' ) {
			if ( $order = $this->get_order() ) {
				return $order->get_customer_ip_address();
			}
		} elseif ( AW_Referrals()->options()->get_reward_event() === 'signup' ) {
			if ( $user = $this->get_customer() ) {
				return get_user_meta( $user->ID, '_automatewoo_referral_ip_address', true );
			}
		}

		return false;
	}


	/**
	 * @return string|false
	 */
	function get_advocate_ip_address() {

		if ( $advocate = $this->get_advocate() ) {
			return $advocate->get_stored_ip();
		}
		return false;
	}


	/**
	 * @return bool
	 */
	function is_customer_registered_user() {
		if ( $order = $this->get_order() ) {
			return $order->get_user_id() !== 0;
		}
		return false;
	}


	/**
	 * If customer is a user return WP_User. False for guests
	 *
	 * @return \WP_User|false
	 */
	function get_customer() {
		if ( AW_Referrals()->options()->get_reward_event() === 'purchase' ) {
			if ( $order = $this->get_order() ) {
				return $order->get_user();
			}
		} elseif ( AW_Referrals()->options()->get_reward_event() === 'signup' ) {
			return get_user_by( 'id', $this->get_user_id() );
		}

		return false;
	}


	/**
	 * @return string|false
	 */
	function get_customer_name() {

		if ( $order = $this->get_order() ) {
			if ( $order->get_billing_first_name() ) {
				return $order->get_formatted_billing_full_name();
			}

			if ( $user = $order->get_user() ) {
				return sprintf( _x( '%1$s %2$s', 'full name', 'automatewoo-referrals' ), $user->first_name, $user->last_name );
			}
		}

		return false;
	}


	/**
	 * @return string|false
	 */
	function get_advocate_name() {
		if ( $advocate = $this->get_advocate() ) {
			return $advocate->get_user()->first_name . ' ' . $advocate->get_user()->last_name;
		}
		return false;
	}


	/**
	 * @return string|false
	 */
	function get_status_name() {
		$statuses = AW_Referrals()->get_referral_statuses();

		if ( empty( $statuses[ $this->get_status() ] ) ) {
			return false;
		}

		return $statuses[ $this->get_status() ];
	}


	/**
	 * @return bool
	 */
	function is_reward_store_credit() {
		return in_array( $this->get_reward_type(), [ 'credit', 'credit_percentage' ] );
	}


	/**
	 * @return bool
	 */
	function ip_addresses_match() {

		if ( ! $this->get_customer_ip_address() ) {
			return false;
		}

		return $this->get_advocate_ip_address() == $this->get_customer_ip_address();
	}


	/**
	 * @param $new_status
	 */
	function update_status( $new_status ) {
		$new_status = Clean::string( $new_status );

		if ( $new_status == $this->get_status() ) {
			return; // bail if status has not changed
		}

		$old_status = $this->get_status();
		$this->set_status( $new_status );
		$this->save();

		do_action( 'automatewoo/referrals/referral_status_changed', $this, $old_status, $new_status );
	}


	/**
	 * @return bool
	 */
	function is_potential_fraud() {

		$fraud = false;

		if ( $this->ip_addresses_match() ) {
			$fraud = true;
		}

		return apply_filters( 'automatewoo/referral/is_potential_fraud', $fraud, $this );
	}


}
