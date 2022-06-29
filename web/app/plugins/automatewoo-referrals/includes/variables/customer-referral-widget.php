<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * @class Variable_Customer_Referral_Widget
 */
class Variable_Customer_Referral_Widget extends AutomateWoo\Variable {


	function load_admin_details() {
		$this->description = __( "Displays the AutomateWoo Refer A Friend Share Widget.", 'automatewoo-referrals' );
	}


	/**
	 * @param $customer AutomateWoo\Customer
	 * @return string
	 */
	function get_value( $customer, $parameters ) {
		if ( $customer->is_registered() ) {
			$advocate = Advocate_Factory::get( $customer->get_user_id() );
		} else {
			$advocate = false;
		}

		ob_start();

		AW_Referrals()->frontend->output_email_share_widget( $advocate );

		return ob_get_clean();
	}
}

return new Variable_Customer_Referral_Widget();
