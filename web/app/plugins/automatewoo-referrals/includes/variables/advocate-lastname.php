<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * @class Variable_Advocate_Lastname
 */
class Variable_Advocate_Lastname extends AutomateWoo\Variable {


	function load_admin_details() {
		$this->description = __( "Displays the advocate's last name.", 'automatewoo-referrals' );
	}


	/**
	 * @param $advocate Advocate
	 * @return string
	 */
	function get_value( $advocate ) {
		return $advocate->get_last_name();
	}
}

return new Variable_Advocate_Lastname();

