<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * @class Variable_Referral_ID
 */
class Variable_Referral_ID extends AutomateWoo\Variable {


	function load_admin_details() {
		$this->description = __( "Displays the ID of the referral.", 'automatewoo-referrals' );
	}

	/**
	 * @param $referral Referral
	 * @return string
	 */
	function get_value( $referral, $parameters ) {
		return $referral->get_id();
	}
}

return new Variable_Referral_ID();

