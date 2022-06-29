<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * @class Variable_Customer_Referral_Link
 */
class Variable_Customer_Referral_Link extends AutomateWoo\Variable {


	function load_admin_details() {
		$this->description = __( "Displays a link that the customer can use to refer their friends. It is recommended to set a fallback because if the customer is a guest no coupon can be displayed.", 'automatewoo-referrals' );
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
		return $advocate->get_shareable_link();
	}
}

return new Variable_Customer_Referral_Link();
