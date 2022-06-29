<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Clean;
use AutomateWoo\Customer_Factory;
use AutomateWoo\Exception;
use AutomateWoo\Session_Tracker;

/**
 * @class Coupons
 */
class Coupons {

	const E_INVALID                   = 100;
	const E_COUPON_IS_OWN             = 101;
	const E_CUSTOMER_IS_EXISTING      = 102;
	const E_CUSTOMER_ALREADY_REFERRED = 103;
	const E_ADVOCATE_REACHED_LIMIT    = 104;


	/**
	 * A prefix is required to distinguish normal coupons from referral coupons.
	 * WARNING! If you change the prefix all existing coupons will no longer work.
	 * @return string
	 */
	static function get_prefix() {
		return apply_filters( 'automatewoo/referrals/coupon_prefix', 'REF' );
	}


	/**
	 * Length of the coupon excluding the prefix.
	 * @int string
	 */
	static function get_key_length() {
		return (int) apply_filters( 'automatewoo/referrals/coupon_key_length', 10 );
	}


	/**
	 * Creates virtual referral coupons on the fly.
	 *
	 * This is done by replacing the coupon data based on whether the code matches the format of a referral
	 * coupon and a the coupon corresponds to a valid Advocate_Key.
	 *
	 * Some but not all of the coupon validation is handled here.
	 * The remaining referral validation is handled in self::validate_referral_coupon()
	 *
	 * Filter: woocommerce_get_shop_coupon_data
	 *
	 * @param array            $coupon_data
	 * @param string           $coupon_code
	 * @param \WC_Coupon|false $coupon_object This arg was added in WC 3.6
	 *
	 * @return array
	 */
	static function catch_referral_coupons( $coupon_data, $coupon_code, $coupon_object = false ) {
		$advocate_id = self::is_valid_referral_coupon( $coupon_code );

		if ( ! $advocate_id ) {
			// Coupon is not a referral coupon
			return $coupon_data;
		}

		$coupon_data = [];

		switch ( AW_Referrals()->options()->offer_type ) {

			case 'coupon_discount':
				$coupon_data['discount_type'] = 'fixed_cart';
				break;

			case 'coupon_percentage_discount':
				$coupon_data['discount_type'] = 'percent_product';
				break;
		}

		$coupon_data['minimum_amount'] = wc_format_decimal( AW_Referrals()->options()->offer_min_purchase, 2 );

		if ( AW_Referrals()->options()->referral_required_categories ) {
			$coupon_data['product_categories'] = Clean::ids( AW_Referrals()->options()->referral_required_categories );
		}

		if ( AW_Referrals()->options()->referral_excluded_categories ) {
			$coupon_data['excluded_product_categories'] = Clean::ids( AW_Referrals()->options()->referral_excluded_categories );
		}

		$coupon_data['id']             = true;
		$coupon_data['amount']         = wc_format_decimal( AW_Referrals()->options()->offer_amount, 2 );
		$coupon_data['individual_use'] = true;

		return apply_filters( 'automatewoo/referrals/coupon_data', $coupon_data, $coupon_code, $advocate_id, $coupon_object );
	}

	/**
	 * Performs additional referral coupon validation that isn't native to WC coupons.
	 *
	 * Ensures the customer is 'permitted' to use the referral coupon based.
	 *
	 * Filter: woocommerce_coupon_is_valid
	 *
	 * @param bool       $valid
	 * @param \WC_Coupon $coupon
	 *
	 * @return bool
	 */
	static function validate_referral_coupon( $valid, $coupon ) {
		// not a referral coupon.
		if ( ! $advocate_id = self::is_valid_referral_coupon( $coupon->get_code() ) ) {
			return $valid;
		}

		// Validate that the advocate hasn't reached the limit.
		try {
			self::validate_advocate_referral_limit( $advocate_id );
		} catch ( \Exception $e ) {
			wc_add_notice( $e->getMessage(), 'error' );
			return false;
		}

		// can't validate without the customer's email
		if ( ! $session_user_id = Session_Tracker::get_detected_user_id() ) {
			return $valid;
		}

		$validate = self::validate_referral_for_customer_id( $session_user_id, $advocate_id );
		if ( is_wp_error( $validate ) ) {
			wc_add_notice( $validate->get_error_message(), 'error' );
			return false;
		}

		return $valid;
	}


	/**
	 * Filter coupon error messages and override them in certain cases.
	 *
	 * @param string     $err
	 * @param int        $err_code
	 * @param \WC_Coupon $coupon
	 *
	 * @return string
	 */
	static function filter_coupon_errors( $err, $err_code, $coupon ) {

		switch ( $err_code ) {
			case \WC_Coupon::E_WC_COUPON_INVALID_FILTERED:

				if ( self::is_valid_referral_coupon( $coupon->get_code() ) ) {
					$err = false; // error messages have been already added
				}

				break;

			case \WC_Coupon::E_WC_COUPON_NOT_EXIST:

				if ( self::matches_referral_coupon_pattern( $coupon->get_code() ) ) {
					$err = __( 'Your coupon is not valid. It may have expired.', 'automatewoo-referrals' );
				}

				break;
		}

		return $err;
	}


	/**
	 * @param $coupon_code
	 * @return bool
	 */
	static function matches_referral_coupon_pattern( $coupon_code ) {
		return stripos( $coupon_code, self::get_prefix() ) === 0;
	}

	/**
	 * Checks if a coupon code is a valid referral coupon.
	 *
	 * Valid implies the coupon:
	 * - is not currently expired
	 * - belongs to an advocate who is not blocked
	 * - has not been deleted, advocate keys are deleted after they expire
	 *
	 * Return the advocate's user ID if coupon is valid.
	 *
	 * @param string $coupon_code
	 *
	 * @return false|int
	 */
	static function is_valid_referral_coupon( $coupon_code ) {
		$key = self::get_advocate_key_by_coupon_code( $coupon_code );

		if ( ! $key || ! $key->is_valid() ) {
			return false;
		}

		return $key->get_advocate_id();
	}

	/**
	 * Find a matching advocate key for a given coupon code.
	 *
	 * @since 2.5.0
	 *
	 * @param string $coupon_code
	 *
	 * @return Advocate_Key|false
	 */
	static function get_advocate_key_by_coupon_code( $coupon_code ) {
		if ( ! self::matches_referral_coupon_pattern( $coupon_code ) ) {
			return false;
		}

		$advocate_key = substr( $coupon_code, strlen( self::get_prefix() ) );

		return Advocate_Key_Factory::get_by_key( $advocate_key );
	}

	/**
	 * Check for user coupons (now that we have billing email). If a coupon is invalid, add an error.
	 *
	 * @param array $posted
	 */
	static function check_customer_coupons( $posted ) {

		if ( empty( WC()->cart->applied_coupons ) )
			return;

		foreach ( WC()->cart->applied_coupons as $code ) {

			if ( ! $advocate_id = self::is_valid_referral_coupon( $code ) )
				continue;

			$coupon = new \WC_Coupon( $code );

			if ( ! $coupon->is_valid() )
				return;

			$error = false;

			// support checkouts with no billing email field
			if ( $billing_email = sanitize_email( $posted['billing_email'] ) ) {

				$validate = self::validate_referral_for_customer_email( $billing_email, $advocate_id );

				if ( is_wp_error( $validate ) ) {
					wc_add_notice( $validate->get_error_message(), 'error' );
					$error = true;
				}
			}

			$session_user_id = Session_Tracker::get_detected_user_id();

			// validate by customer ID if not already invalid
			if ( ! $error && $session_user_id ) {

				$validate = self::validate_referral_for_customer_id( $session_user_id, $advocate_id );

				if ( is_wp_error( $validate ) ) {
					wc_add_notice( $validate->get_error_message(), 'error' );
					$error = true;
				}
			}


			if ( $error ) {
				// Remove the coupon
				WC()->cart->remove_coupon( $code );

				// Flag totals for refresh
				WC()->session->set( 'refresh_totals', true );
			}
		}
	}



	/**
	 * @param int $customer_user_id
	 * @param int $advocate_user_id
	 * @return \WP_Error|true
	 */
	static function validate_referral_for_customer_id( $customer_user_id, $advocate_user_id ) {
		$valid = true;

		try {
			self::validate_advocate_not_customer( $customer_user_id, $advocate_user_id );
			self::validate_new_customer_referral( $customer_user_id );
		} catch ( \Exception $e ) {
			$valid = new \WP_Error( 'coupon-invalid', $e->getMessage() );
		}

		return apply_filters( 'automatewoo/referrals/validate_coupon_for_user', $valid, $customer_user_id, $advocate_user_id );
	}


	/**
	 * @param $customer_email
	 * @param $advocate_id
	 * @return bool|\WP_Error
	 */
	static function validate_referral_for_customer_email( $customer_email, $advocate_id ) {

		$valid = true;

		try {

			if ( ! $advocate_id || ! $customer_email ) {
				throw new \Exception( self::get_error( self::E_INVALID ) );
			}

			$customer_user = get_user_by( 'email', $customer_email );

			if ( $customer_user ) {
				// if email matches existing customer validated by ID
				return self::validate_referral_for_customer_id( $customer_user->ID, $advocate_id );
			}

			$advocate_user = get_user_by( 'id', $advocate_id );

			if ( $customer_email == $advocate_user->user_email ) {
				throw new \Exception( self::get_error( self::E_COUPON_IS_OWN ) );
			}


			if ( AW_Referrals()->options()->allow_existing_customer_referrals ) {
				if ( count( Referral_Manager::get_referrals_by_customer( $customer_email ) ) !== 0 ) {
					throw new \Exception( self::get_error( self::E_CUSTOMER_ALREADY_REFERRED ) );
				}
			} else {
				// previous orders with the same email address?
				$customer = Customer_Factory::get_by_email( $customer_email );
				if ( $customer && $customer->get_order_count() ) {
					throw new \Exception( self::get_error( self::E_CUSTOMER_IS_EXISTING ) );
				}
			}
		} catch ( \Exception $e ) {
			$valid = new \WP_Error( 'coupon-invalid', $e->getMessage() );
		}

		return apply_filters( 'automatewoo/referrals/validate_coupon_for_guest', $valid, $customer_email, $advocate_id );
	}


	/**
	 * @param $error_code
	 * @return string
	 */
	static function get_error( $error_code = 100 ) {

		switch ( $error_code ) {

			case self::E_INVALID:
				$message = __( 'There is a problem with this referral coupon.', 'automatewoo-referrals' );
				break;

			case self::E_COUPON_IS_OWN:
				$message = __( 'It appears you are trying to use your own referral coupon.', 'automatewoo-referrals' );
				break;

			case self::E_CUSTOMER_IS_EXISTING:
				$message = __( 'You don\'t appear to be a new customer which is required to use a referral coupon.', 'automatewoo-referrals' );
				break;

			case self::E_CUSTOMER_ALREADY_REFERRED:
				$message = __( 'It appears you have already used a referral coupon before.', 'automatewoo-referrals' );
				break;

			case self::E_ADVOCATE_REACHED_LIMIT:
				$message = __( 'It appears that this coupon has reached its referral limit and is no longer available.', 'automatewoo-referrals' );
				break;

			default:
				$message = '';
				break;
		}

		return $message;
	}

	/**
	 * Validate that the IDs are valid, and that the advocate is not the customer.
	 *
	 * @param int $customer_user_id
	 * @param int $advocate_user_id
	 *
	 * @throws \Exception
	 */
	private static function validate_advocate_not_customer( $customer_user_id, $advocate_user_id ) {
		if ( ! $advocate_user_id || ! $customer_user_id ) {
			throw new \Exception( self::get_error( self::E_INVALID ) );
		}

		if ( $advocate_user_id == $customer_user_id ) {
			throw new \Exception( self::get_error( self::E_COUPON_IS_OWN ) );
		}
	}

	/**
	 * Validate that the referral is a new customer, or that the coupon hasn't been used yet.
	 *
	 * @param int $customer_user_id
	 *
	 * @throws \Exception
	 */
	private static function validate_new_customer_referral( $customer_user_id ) {
		if ( AW_Referrals()->options()->allow_existing_customer_referrals ) {
			if ( count( Referral_Manager::get_referrals_by_customer( $customer_user_id ) ) !== 0 ) {
				throw new \Exception( self::get_error( self::E_CUSTOMER_ALREADY_REFERRED ) );
			}
		} else {
			$customer = Customer_Factory::get_by_user_id( $customer_user_id );

			// previous orders for the user?
			if ( $customer->get_order_count() !== 0 ) {
				throw new \Exception( self::get_error( self::E_CUSTOMER_IS_EXISTING ) );
			}
		}
	}

	/**
	 * Validate that the advocate hasn't reached the referral limit.
	 *
	 * @param int $advocate_id
	 *
	 * @throws \Exception
	 */
	private static function validate_advocate_referral_limit( $advocate_id ) {
		try {
			$advocate = Advocate_Factory::get( $advocate_id );
			$advocate->validate_referral_limit();
		} catch ( Exception $e ) {
			// Tweak the exception message to be relevant when attempting to use the referral code.
			throw new \Exception( self::get_error( self::E_ADVOCATE_REACHED_LIMIT ) );
		}
	}
}
