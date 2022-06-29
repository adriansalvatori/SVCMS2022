<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * @class Variable_Referral_Status
 */
class Variable_Referral_Status extends AutomateWoo\Variable {


	function load_admin_details() {
		$this->description = __( "Displays the human readable status of the referral.", 'automatewoo-referrals' );
	}


	/**
	 * @param $referral AutomateWoo\Referrals\Referral
	 * @return string
	 */
	function get_value( $referral, $parameters ) {
		return $referral->get_status_name();
	}
}

return new Variable_Referral_Status();
