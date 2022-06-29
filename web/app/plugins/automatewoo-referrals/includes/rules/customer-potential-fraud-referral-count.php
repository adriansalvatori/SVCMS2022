<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * @class Rule_Customer_Potential_Fraud_Referral_Count
 */
class Rule_Customer_Potential_Fraud_Referral_Count extends Rules\Abstract_Number {

	public $data_item = 'customer';

	public $support_floats = false;


	function init() {
		$this->title = __( 'Customer - Potential Fraud Referral Count', 'automatewoo-referrals' );
		$this->group = __( 'Refer A Friend', 'automatewoo-referrals' );
	}


	/**
	 * @param $customer \AutomateWoo\Customer
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $customer, $compare, $value ) {

		if ( $customer->is_registered() ) {
			$advocate = Advocate_Factory::get( $customer->get_user_id() );
			$count    = $advocate->get_referral_count( 'potential-fraud' );
		} else {
			$count = 0;
		}

		return $this->validate_number( $count, $compare, $value );
	}
}

return new Rule_Customer_Potential_Fraud_Referral_Count();
