<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Variable;

defined( 'ABSPATH' ) || exit;

/**
 * @class Variable_Advocate_Referral_Coupon
 */
class Variable_Advocate_Referral_Coupon extends Variable {


	function load_admin_details() {
		$this->description = __( "Displays a coupon that the advocate can use to refer their friends.", 'automatewoo-referrals' );
	}


	/**
	 * @param $advocate Advocate
	 * @return string|bool
	 */
	function get_value( $advocate, $parameters ) {
		return $advocate->get_shareable_coupon();
	}
}

return new Variable_Advocate_Referral_Coupon();
