<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * @class Variable_Advocate_Firstname
 */
class Variable_Advocate_Firstname extends AutomateWoo\Variable {

	function load_admin_details() {
		$this->description = __( "Displays the advocate's first name.", 'automatewoo-referrals' );
	}


	/**
	 * @param $advocate Advocate
	 * @return string
	 */
	function get_value( $advocate ) {
		return $advocate->get_first_name();
	}
}

return new Variable_Advocate_Firstname();

