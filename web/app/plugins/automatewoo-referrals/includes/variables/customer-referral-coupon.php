<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * @class Variable_Customer_Referral_Coupon
 */
class Variable_Customer_Referral_Coupon extends AutomateWoo\Variable {


	function load_admin_details() {
		$this->description = __( "Displays a coupon that the customer can use to refer their friends. If you are using this with an order trigger it is recommended to set a fallback because if the order is placed by a guest user no coupon will be displayed.", 'automatewoo-referrals' );
	}


	/**
	 * @param $customer AutomateWoo\Customer
	 * @return string|bool
	 */
	function get_value( $customer, $parameters ) {

		if ( ! $customer->is_registered() ) {
			return false;
		}

		$advocate = Advocate_Factory::get( $customer->get_user_id() );
		return $advocate->get_shareable_coupon();
	}
}

return new Variable_Customer_Referral_Coupon();
