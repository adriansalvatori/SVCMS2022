<?php

namespace AutomateWoo\Birthdays;

use AutomateWoo\Variable_Abstract_Datetime;

defined( 'ABSPATH' ) || exit;

/**
 * Class Customer_Birthday_Variable
 *
 * @package AutomateWoo\Birthdays
 */
class Customer_Birthday_Variable extends Variable_Abstract_Datetime {

	/**
	 * Load admin details
	 */
	public function load_admin_details() {
		$type_options = [
			'next' => __( 'Next birthday date', 'automatewoo-birthdays' ),
			'last' => __( 'Last birthday date', 'automatewoo-birthdays' ),
		];

		if ( AW_Birthdays()->options()->require_year_of_birth() ) {
			$type_options['dob'] = __( 'Date of birth', 'automatewoo-birthdays' );
		}

		parent::add_parameter_select_field( 'type', __( 'Changes which date will be shown.', 'automatewoo-birthdays' ), $type_options, true );

		parent::load_admin_details();

		$this->description = __( "Shows the customer's birthday if they have provided it.", 'automatewoo-birthdays' ) . ' ' . $this->_desc_format_tip;
	}

	/**
	 * Get options for the date format select parameter.
	 *
	 * @return array
	 */
	protected function get_date_format_options() {
		$options = [];

		// add day+month only options
		$options['F j'] = false;
		$options['m-d'] = false;
		$options['m/d'] = false;
		$options['d/m'] = false;

		return $options + parent::get_date_format_options();
	}

	/**
	 * Get variable value.
	 *
	 * @param \AutomateWoo\Customer $customer
	 * @param array                 $parameters
	 *
	 * @return string|bool
	 */
	public function get_value( $customer, $parameters ) {
		if ( ! $customer->is_registered() ) {
			return false;
		}

		$type = empty( $parameters['type'] ) ? 'next' : $parameters['type'];

		if ( 'dob' === $type ) {
			$type = 'stored';
		}

		$date = AW_Birthdays()->get_date_from_birthday_array( AW_Birthdays()->get_user_birthday( $customer->get_user_id(), $type ) );

		return $this->format_datetime( $date, $parameters );
	}

}

return new Customer_Birthday_Variable();
