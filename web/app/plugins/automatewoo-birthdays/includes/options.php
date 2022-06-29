<?php

namespace AutomateWoo\Birthdays;

use AutomateWoo\Options_API;
use AutomateWoo\Clean;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin options class.
 */
class Options extends Options_API {

	/**
	 * The prefix used in the WP options table.
	 *
	 * @var string
	 */
	public $prefix = 'aw_birthdays_';

	/**
	 * Options constructor.
	 */
	public function __construct() {
		$this->defaults = [
			'show_field_on_checkout'        => 'yes',
			'show_field_in_account_details' => 'yes',
			'birthday_field_description'    => __( 'Enter to receive a special offer for your birthday.', 'automatewoo-birthdays' ),
			'require_year_of_birth'         => 'no',
			'checkout_field_placement'      => 'after_order_notes',
		];
	}

	/**
	 * Returns the version of the database to handle migrations.
	 * Is autoloaded.
	 *
	 * @return string
	 */
	public function database_version() {
		return Clean::string( $this->__get( 'version' ) );
	}

	/**
	 * Show the birthday field on the checkout?
	 *
	 * @return bool
	 */
	public function show_field_on_checkout() {
		return (bool) $this->__get( 'show_field_on_checkout' );
	}

	/**
	 * Show the birthday field in the account area?
	 *
	 * @return bool
	 */
	public function show_field_in_account_details() {
		return (bool) $this->__get( 'show_field_in_account_details' );
	}

	/**
	 * The birthday field description.
	 *
	 * @return bool
	 */
	public function birthday_field_description() {
		return wp_kses_post( $this->__get( 'birthday_field_description' ) );
	}

	/**
	 * Require customer's year of birth?
	 *
	 * @return bool
	 */
	public function require_year_of_birth() {
		return (bool) $this->__get( 'require_year_of_birth' );
	}

	/**
	 * Get checkout field location.
	 *
	 * @return string
	 */
	public function checkout_field_placement() {
		return Clean::string( $this->__get( 'checkout_field_placement' ) );
	}
}

