<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * @class Rule_Advocate_Pending_Referral_Count
 */
class Rule_Advocate_Pending_Referral_Count extends Rules\Abstract_Number {

	public $data_item = 'advocate';

	public $support_floats = false;


	function init() {
		$this->title = __( 'Advocate - Pending Referral Count', 'automatewoo-referrals' );
		$this->group = __( 'Refer A Friend', 'automatewoo-referrals' );
	}


	/**
	 * @param $advocate Advocate
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $advocate, $compare, $value ) {
		return $this->validate_number( $advocate->get_referral_count( 'pending' ), $compare, $value );
	}
}

return new Rule_Advocate_Pending_Referral_Count();
