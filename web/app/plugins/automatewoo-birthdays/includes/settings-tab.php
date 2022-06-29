<?php

namespace AutomateWoo\Birthdays;

use AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings_Tab.
 *
 * @package AutomateWoo\Birthdays
 */
class Settings_Tab extends AutomateWoo\Admin_Settings_Tab_Abstract {

	/**
	 * Show the tab title?
	 *
	 * @var bool
	 */
	public $show_tab_title = false;

	/**
	 * The option prefix.
	 *
	 * @var string
	 */
	public $prefix = 'aw_birthdays_';

	/**
	 * Settings_Tab constructor.
	 */
	public function __construct() {
		$this->id   = 'birthdays';
		$this->name = __( 'Birthdays', 'automatewoo-birthdays' );
	}

	/**
	 * Load plugin settings.
	 */
	public function load_settings() {
		$this->section_start( 'main', __( 'Birthdays Options', 'automatewoo-birthdays' ) );

		$this->add_setting(
			'show_field_on_checkout',
			[
				'title' => __( 'Show birthday field on checkout', 'automatewoo-birthdays' ),
				'type'  => 'checkbox',
			]
		);

		$this->add_setting(
			'show_field_in_account_details',
			[
				'title'   => __( 'Show birthday field in account area', 'automatewoo-birthdays' ),
				'tooltip' => __( 'The field will be shown under My Account > Account details.', 'automatewoo-birthdays' ),
				'type'    => 'checkbox',
			]
		);

		$this->add_setting(
			'birthday_field_description',
			[
				'title'   => __( 'Birthday field description', 'automatewoo-birthdays' ),
				'tooltip' => __( 'This description will be shown with the birthday field. It can be used to explain what the birthday field is used for.', 'automatewoo-birthdays' ),
				'type'    => 'text',
			]
		);

		$this->add_setting(
			'require_year_of_birth',
			[
				'title'   => __( 'Collect year of birth', 'automatewoo-birthdays' ),
				'type'    => 'checkbox',
				'tooltip' => __( 'Since the year of birth is not required for most birthday features collecting it is optional. If disabled, customers will not enter the year of their birth. Only the month and day will be stored.', 'automatewoo-birthdays' ),
			]
		);

		$this->add_setting(
			'checkout_field_placement',
			[
				'title'   => __( 'Checkout field placement', 'automatewoo-birthdays' ),
				'type'    => 'select',
				'options' => [
					'after_order_notes'     => __( 'After order notes', 'automatewoo-birthdays' ),
					'before_order_notes'    => __( 'Before order notes', 'automatewoo-birthdays' ),
					'after_billing_details' => __( 'After billing details', 'automatewoo-birthdays' ),
				],
				'tooltip' => __( 'This field determines where the birthday field will appear on the checkout page.', 'automatewoo-birthdays' ),
			]
		);

		$this->section_end( 'main' );
	}

	/**
	 * Get the setting default.
	 *
	 * @param string $id
	 * @return mixed
	 */
	protected function get_default( $id ) {
		return isset( AW_Birthdays()->options()->defaults[ $id ] ) ? AW_Birthdays()->options()->defaults[ $id ] : false;
	}

}

return new Settings_Tab();
