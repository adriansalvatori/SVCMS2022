<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

/**
 * @class AW_Variable_Advocate_ID
 */
class AW_Variable_Advocate_ID extends AutomateWoo\Variable {


	function load_admin_details() {
		$this->description = __( "Displays the user ID of the advocate.", 'automatewoo-referrals' );
	}


	/**
	 * @param $advocate AutomateWoo\Referrals\Advocate
	 * @return string
	 */
	function get_value( $advocate, $parameters ) {
		return $advocate->get_id();
	}
}

return new AW_Variable_Advocate_ID();

