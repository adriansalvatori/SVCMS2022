<?php

namespace AutomateWoo\Birthdays;

use AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * Class Customer_Next_Birthday_Rule
 *
 * @package AutomateWoo\Birthdays
 */
class Customer_Next_Birthday_Rule extends Rules\Abstract_Date {

	/**
	 * What data we're validating.
	 *
	 * @var string
	 */
	public $data_item = 'customer';

	/**
	 * Customer_Next_Birthday_Rule constructor.
	 */
	public function __construct() {
		$this->has_is_future_comparision = true;

		parent::__construct();

		unset( $this->select_choices['hours'] );
	}

	/**
	 * Init.
	 */
	public function init() {
		$this->title = __( 'Customer - Next Birthday Date', 'automatewoo-birthdays' );
		$this->group = __( 'Customer', 'automatewoo-birthdays' );
	}

	/**
	 * Validates rule.
	 *
	 * @param \AutomateWoo\Customer $customer
	 * @param string                $compare
	 * @param array                 $value
	 *
	 * @return bool
	 */
	public function validate( $customer, $compare, $value ) {
		$birthday = false;

		if ( $customer->is_registered() ) {
			$birthday = AW_Birthdays()->get_date_from_birthday_array( AW_Birthdays()->get_user_birthday( $customer->get_user_id(), 'next' ) );
			$birthday->convert_to_utc_time();
		}

		return $this->validate_date( $compare, $value, $birthday );
	}

}

return new Customer_Next_Birthday_Rule();
