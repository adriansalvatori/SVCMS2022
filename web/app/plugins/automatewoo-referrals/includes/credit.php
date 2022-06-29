<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Format;
use AutomateWoo\Temporary_Data;

/**
 * Store credit management class.
 *
 * @class Credit
 */
class Credit {

	/** @var string */
	static $credit_name;

	/** @var string */
	static $coupon_code;

	/** @var bool  */
	static $suppress_coupon_errors = true;


	/**
	 * Constructor
	 */
	static function init() {

		self::$credit_name = esc_html( apply_filters( 'automatewoo/referrals/referral_credit_name', __( 'Referral Credit', 'automatewoo-referrals' ) ) );
		self::$coupon_code = apply_filters( 'woocommerce_coupon_code', self::$credit_name );

		add_action( 'woocommerce_check_cart_items', [ __CLASS__, 'maybe_add_credit_to_cart' ], 50 );
		add_action( 'woocommerce_before_calculate_totals', [ __CLASS__, 'reorder_cart_coupons' ] );
		add_action( 'woocommerce_checkout_order_processed', [ __CLASS__, 'remove_credit_used_in_order' ], 100 );
		add_action( 'woocommerce_order_status_changed', [ __CLASS__, 'maybe_refund_credit_for_failed_order' ], 100, 3 );

		add_filter( 'woocommerce_cart_totals_coupon_label', [ __CLASS__, 'filter_cart_coupon_label' ], 10, 2 );
		add_filter( 'woocommerce_cart_totals_coupon_html', [ __CLASS__, 'filter_cart_coupon_html' ], 10, 2 );
		add_filter( 'woocommerce_coupon_message', [ __CLASS__, 'filter_coupon_message' ], 20, 3 );
		add_filter( 'woocommerce_coupon_error', [ __CLASS__, 'filter_coupon_error' ], 20, 3 );

		add_filter( 'woocommerce_get_shop_coupon_data', [ __CLASS__, 'filter_store_credit_coupon_data' ], 10, 2 );
	}


	/**
	 * Get available credit for a user.
	 *
	 * @param int $user_id
	 * @return float
	 */
	public static function get_available_credit( int $user_id ): float {
		if ( ! $user_id ) {
			return 0;
		}

		// check value cached in memory
		if ( Temporary_Data::exists( 'referrals_available_credit', $user_id ) ) {
			$credit = Temporary_Data::get( 'referrals_available_credit', $user_id );
		} else {
			$credit = 0;

			$referrals = AW_Referrals()->get_available_referrals_by_user( $user_id );

			foreach ( $referrals as $referral ) {
				if ( $referral->is_reward_store_credit() ) {
					$credit += $referral->get_reward_amount_remaining();
				}
			}

			$credit = (float) Format::decimal( $credit );

			Temporary_Data::set( 'referrals_available_credit', $user_id, $credit );
		}

		return (float) apply_filters( 'automatewoo/referrals/available_credit', $credit, $user_id );
	}



	/**
	 * Get total earned credit, includes used credit
	 *
	 * @param $user_id
	 * @return float
	 */
	static function get_total_credit( $user_id ) {

		$total_credit = 0;

		$query = ( new Referral_Query() )
			->where( 'advocate_id', $user_id )
			->where( 'status', 'approved' );

		$referrals = $query->get_results();

		if ( ! $referrals )
			return 0;

		foreach ( $referrals as $referral ) {
			if ( $referral->is_reward_store_credit() ) {
				$total_credit += $referral->get_reward_amount();
			}
		}

		return (float) Format::decimal( $total_credit );
	}


	/**
	 * Add or remove store credit coupon from the cart
	 */
	static function maybe_add_credit_to_cart() {
		if ( did_action( 'woocommerce_before_checkout_process' ) ) {
			return; // prevent notices from appearing on order received page
		}

		if ( WC()->cart->is_empty() ) {
			self::remove_credit_coupon_from_cart();
			return;
		}

		$total_credit = self::get_available_credit( get_current_user_id() );

		if ( $total_credit ) {

			if ( ! WC()->cart->has_discount( self::$coupon_code ) ) {

				self::$suppress_coupon_errors = false;
				$coupon                       = new \WC_Coupon( self::$coupon_code );

				if ( $coupon->is_valid() ) {
					WC()->cart->add_discount( self::$coupon_code );
				} else {
					if ( $error = $coupon->get_error_message()) {
						wc_add_notice( $error, 'notice' ); // deliberately not an error notice
					}
					self::remove_credit_coupon_from_cart();
				}

				self::$suppress_coupon_errors = true;

			}
		} else {
			self::remove_credit_coupon_from_cart();
		}
	}


	/**
	 * Remove store credit coupon from cart
	 */
	static function remove_credit_coupon_from_cart() {
		if ( WC()->cart->has_discount( self::$coupon_code ) ) {
			WC()->cart->remove_coupon( self::$coupon_code );
		}
	}


	/**
	 * Store credit should be last
	 */
	static function reorder_cart_coupons() {

		if ( in_array( self::$coupon_code, WC()->cart->applied_coupons ) ) {
			aw_array_remove_value( WC()->cart->applied_coupons, self::$coupon_code );
			WC()->cart->applied_coupons[] = self::$coupon_code ;
		}
	}


	/**
	 * @param $msg
	 * @param $msg_code
	 * @param \WC_Coupon $coupon
	 * @return bool
	 */
	static function filter_coupon_message( $msg, $msg_code, $coupon ) {
		if ( ! self::is_store_credit_coupon( $coupon ) ) {
			return $msg;
		}
		return false;
	}


	/**
	 * @param string $msg
	 * @param int $msg_code
	 * @param \WC_Coupon $coupon
	 * @return mixed
	 */
	static function filter_coupon_error( $msg, $msg_code, $coupon ) {
		if ( ! self::is_store_credit_coupon( $coupon ) ) {
			return $msg;
		}

		if ( self::$suppress_coupon_errors ) {
			return false;
		}

		switch ( $msg_code ) {
			case 108:
				$msg = sprintf( __( 'The minimum spend is %s.', 'automatewoo-referrals' ), wc_price( $coupon->get_minimum_amount() ) );
				break;
			default:
				$msg = false;
				break;
		}

		$total_credit = self::get_available_credit( get_current_user_id() );

		$msg = sprintf( __( 'You have %1$s referral credit available but it is not valid for your current cart. %2$s', 'automatewoo-referrals' ), wc_price( $total_credit ), $msg );

		return $msg;
	}


	/**
	 * @param bool   $coupon_data
	 * @param string $coupon_code
	 *
	 * @return array|bool
	 */
	static function filter_store_credit_coupon_data( $coupon_data, $coupon_code ) {
		if ( ! self::is_store_credit_coupon( $coupon_code ) ) {
			return $coupon_data;
		}

		$coupon_data = [
			'id'                 => true,
			'type'               => 'fixed_cart',
			'amount'             => self::get_available_credit( get_current_user_id() ),
			'individual_use'     => false,
			'exclude_sale_items' => false,
		];

		$coupon_data['minimum_amount'] = wc_format_decimal( AW_Referrals()->options()->reward_min_purchase, 2 );

		return apply_filters( 'automatewoo/referrals/store_credit/coupon_data', $coupon_data );
	}


	/**
	 * @param $label
	 * @param \WC_Coupon $coupon
	 * @return string
	 */
	static function filter_cart_coupon_label( $label, $coupon ) {
		if ( self::is_store_credit_coupon( $coupon ) ) {
			return self::$credit_name;
		}

		return $label;
	}


	/**
	 * @param $html
	 * @param \WC_Coupon $coupon
	 * @return string
	 */
	static function filter_cart_coupon_html( $html, $coupon ) {
		if ( ! self::is_store_credit_coupon( $coupon ) ) {
			return $html;
		}

		$amount = WC()->cart->get_coupon_discount_amount( $coupon->get_code(), self::is_display_ex_tax() );
		$html   = '-' . wc_price( $amount );

		if ( wc_tax_enabled() && self::is_display_ex_tax() ) {
			$html .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
		}

		return $html;
	}


	/**
	 * @param $coupon \WC_Coupon|string - coupon code object or coupon name
	 * @return bool
	 */
	static function is_store_credit_coupon( $coupon ) {
		if ( is_a( $coupon, 'WC_Coupon' ) ) {
			return $coupon->get_code() == self::$coupon_code;
		} else {
			return $coupon === self::$coupon_code;
		}
	}


	/**
	 * Reduce the amount the advocate's credit total based on the amount used in an order
	 *
	 * @param int $order_id
	 */
	static function remove_credit_used_in_order( $order_id ) {
		if ( ! $order = wc_get_order( $order_id ) ) {
			return;
		}

		// This meta flag is set after the order's credit is removed
		if ( $order->get_meta( '_aw_referrals_credit_processed' ) ) {
			return;
		}

		if ( ! $credit_used = self::get_credit_amount_in_order( $order ) ) {
			return;
		}

		self::decrease_advocate_credit_total( $order->get_user_id(), $credit_used );

		// since more credit is added allow this credit to be refunded if the order is cancelled
		// this is important when manually completing failed subscription renewal payments
		$order->delete_meta_data( '_aw_referrals_credit_refunded' );
		$order->update_meta_data( '_aw_referrals_credit_processed', true );
		$order->save_meta_data();
	}


	/**
	 * Return the credit from orders that are cancelled, failed or refunded
	 *
	 * @param int $order_id
	 * @param string $old_status
	 * @param string $new_status
	 */
	static function maybe_refund_credit_for_failed_order( $order_id, $old_status, $new_status ) {
		if ( ! in_array( $new_status, [ 'failed', 'refunded', 'cancelled' ] ) ) {
			return;
		}

		if ( ! $order = wc_get_order( $order_id ) ) {
			return;
		}

		if ( ! $credit_used = Credit::get_credit_amount_in_order( $order ) ) {
			return;
		}

		if ( $order->get_meta( '_aw_referrals_credit_refunded' ) ) {
			return;
		}

		Credit::increase_advocate_credit_total( $order->get_user_id(), $credit_used );

		$order->add_order_note(
			sprintf( _x( "The %s referral credit used in this order was refunded to the customer's balance.", 'Shows the amount of credit', 'automatewoo-referrals' ), wc_price( $credit_used ) )
		);

		// reset meta flag - important when manually completing failed subscription renewal payments
		$order->delete_meta_data( '_aw_referrals_credit_processed' );
		$order->update_meta_data( '_aw_referrals_credit_refunded', true );
		$order->save_meta_data();
	}


	/**
	 * Loop through an advocate's referrals until a set credit total has been reduced
	 *
	 * @param int $advocate_id
	 * @param float $credit_amount
	 */
	static function decrease_advocate_credit_total( $advocate_id, $credit_amount ) {
		$referrals = AW_Referrals()->get_available_referrals_by_user( $advocate_id );

		// Loop through available referrals until the store credit value has been used
		foreach ( $referrals as $referral ) {

			if ( $credit_amount <= 0 ) {
				break;
			}

			if ( ! $referral->is_reward_store_credit() ) {
				continue;
			}

			if ( $referral->get_reward_amount_remaining() < $credit_amount ) {
				$credit_amount -= $referral->get_reward_amount_remaining();
				$referral->set_reward_amount_remaining( 0 );
			} else {
				$referral->set_reward_amount_remaining( $referral->get_reward_amount_remaining() - $credit_amount );
				$credit_amount = 0;
			}

			$referral->save();
		}
	}


	/**
	 * Add a set amount of credit to an advocate.
	 *
	 * Important to note this method requires the advocate to have at least one approved referral
	 * due to the fact that this method is only used to refund credit after when an order is
	 * cancelled with credit on it.
	 *
	 * @param int $advocate_id
	 * @param float $credit_amount
	 */
	static function increase_advocate_credit_total( $advocate_id, $credit_amount ) {
		// Find the advocate's approved referrals to put store credit on
		$query = ( new Referral_Query() )
			->where_advocate( $advocate_id )
			->where_status( 'approved' )
			->set_ordering( 'date' );

		$referrals = $query->get_results();

		if ( ! $referrals ) {
			return; // we can't increase credit if the user has no referrals
		}

		// Loop through available referrals until the store credit value has been used
		foreach ( $referrals as $referral ) {

			if ( $credit_amount <= 0 ) {
				break;
			}

			if ( ! $referral->is_reward_store_credit() ) {
				continue;
			}

			// prevent the reward remaining from being larger than the reward amount
			$space = $referral->get_reward_amount() - $referral->get_reward_amount_remaining();

			if ( $space < $credit_amount ) {
				$credit_amount -= $space;
				$referral->set_reward_amount_remaining( $referral->get_reward_amount() );
			} else {
				$referral->set_reward_amount_remaining( $referral->get_reward_amount_remaining() + $credit_amount );
				$credit_amount = 0;
			}

			$referral->save();
		}

		// if there is remaining credit add it to the first referral
		if ( $credit_amount ) {
			/** @var Referral $referral */
			$referral = current( $referrals );
			$referral->set_reward_amount_remaining( $referral->get_reward_amount_remaining() + $credit_amount );
			$referral->save();
		}
	}


	/**
	 * @param $order \WC_Order
	 * @return bool|array|\WC_Order_Item_Coupon
	 */
	static function get_credit_coupon_from_order( $order ) {
		foreach ( $order->get_items( 'coupon' ) as $coupon ) {
			if ( $coupon->get_code() == self::$coupon_code ) {
				return $coupon;
			}
		}
		return false;
	}


	/**
	 * @param $order
	 * @return float
	 */
	static function get_credit_amount_in_order( $order ) {
		if ( ! $coupon = self::get_credit_coupon_from_order( $order ) ) {
			return 0;
		}

		return $coupon->get_discount() + $coupon->get_discount_tax();
	}


	/**
	 * Apply store credit to order after it has been created
	 * @param \WC_Order $order
	 * @param float $available_credit
	 */
	static function add_credit_to_order( $order, $available_credit ) {
		if ( version_compare( WC()->version, '3.2', '<' ) ) {
			self::legacy_pre_3_2_add_store_credit_to_order( $order, $available_credit );
			return;
		}

		// apply manual coupon code
		// data specified in self::filter_store_credit_coupon()
		$coupon = new \WC_Coupon( self::$coupon_code );
		$coupon->set_amount( $available_credit );

		$order->apply_coupon( $coupon );
	}


	/**
	 * @return bool
	 */
	private static function is_display_ex_tax() {
		return get_option( 'woocommerce_tax_display_cart' ) === 'excl';
	}






	/**
	 * Apply store credit to order after it has been created
	 * @param \WC_Order $order
	 * @param float $available_credit
	 * @return float
	 */
	private static function legacy_pre_3_2_add_store_credit_to_order( $order, $available_credit ) {

		// ensure the order doesn't already have a store credit coupon
		if ( $coupon = self::get_credit_coupon_from_order( $order ) ) {
			return 0;
		}

		$items = $order->get_items();

		$used_credit_total     = 0;
		$user_credit_total_tax = 0;

		foreach ( $items as $item ) {
			/** @var $item \WC_Order_Item_Product */

			$price = floatval( $item->get_total() ) + floatval( $item->get_total_tax() );

			if ( $available_credit < 0 ) {
				break;
			}

			$discount_amount   = min( $price, $available_credit );
			$available_credit -= $discount_amount;

			if ( wc_tax_enabled() ) {

				$tax_rates              = self::legacy_get_order_tax_rates( $order, $item->get_tax_class() );
				$discount_taxes         = \WC_Tax::calc_tax( $discount_amount, $tax_rates, true, true );
				$discount_tax           = wc_round_tax_total( array_sum( $discount_taxes ) );
				$discount_amount_ex_tax = $discount_amount - $discount_tax;

				$item->set_total( $item->get_total() - $discount_amount_ex_tax );
				$item->set_total_tax( $item->get_total_tax() - $discount_tax );

				$used_credit_total     += $discount_amount_ex_tax;
				$user_credit_total_tax += $discount_tax;
			} else {
				$item->set_total( $item->get_total() - $discount_amount );
				$used_credit_total += $discount_amount;
			}

			$item->save();
		}

		$used_credit_total     = wc_format_decimal( $used_credit_total );
		$user_credit_total_tax = wc_format_decimal( $user_credit_total_tax );

		$coupon = new \WC_Order_Item_Coupon();
		$coupon->set_code( self::$coupon_code );
		$coupon->set_discount( $used_credit_total );
		$coupon->set_discount_tax( $user_credit_total_tax );

		$order->add_item( $coupon );

		$order->calculate_totals();

		return $used_credit_total + $user_credit_total_tax;

	}


	/**
	 * @param \WC_Order $order
	 * @param string $tax_class
	 * @return array|bool
	 */
	private static function legacy_get_order_tax_rates( $order, $tax_class = '' ) {

		if ( ! wc_tax_enabled() ) {
			return false;
		}

		$tax_based_on = get_option( 'woocommerce_tax_based_on' );

		$country  = '';
		$state    = '';
		$postcode = '';
		$city     = '';

		if ( 'billing' === $tax_based_on ) {
			$country  = $order->get_billing_country();
			$state    = $order->get_billing_state();
			$postcode = $order->get_billing_postcode();
			$city     = $order->get_billing_city();
		} elseif ( 'shipping' === $tax_based_on ) {
			$country  = $order->get_shipping_country();
			$state    = $order->get_shipping_state();
			$postcode = $order->get_shipping_postcode();
			$city     = $order->get_shipping_city();
		}

		// Default to base
		if ( 'base' === $tax_based_on || empty( $country ) ) {
			$default = wc_get_base_location();
			$country = $default['country'];
			$state   = $default['state'];
		}

		$tax_rates = \WC_Tax::find_rates(
			[
				'country'   => $country,
				'state'     => $state,
				'postcode'  => $postcode,
				'city'      => $city,
				'tax_class' => $tax_class
			]
		);

		return $tax_rates;
	}


}
