<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * @class Variable_Advocate_Generate_Coupon
 */
class Variable_Advocate_Generate_Coupon extends AutomateWoo\Variable_Abstract_Generate_Coupon {

	/**
	 * @param $advocate Advocate
	 * @param $parameters
	 * @param $workflow
	 * @return string
	 */
	function get_value( $advocate, $parameters, $workflow ) {
		return $this->generate_coupon( $advocate->get_email(), $parameters, $workflow );
	}
}

return new Variable_Advocate_Generate_Coupon();
