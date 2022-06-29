<?php

namespace AutomateWoo\Birthdays;

defined( 'ABSPATH' ) || exit;

/**
 * Admin: Edit user class.
 *
 * Allow editing user birthdays from admin area.
 *
 * @package AutomateWoo\Birthdays
 */
class Admin_Edit_User {

	/**
	 * Add hooks.
	 */
	public function __construct() {
		add_action( 'show_user_profile', [ $this, 'output_birthday_field' ] );
		add_action( 'edit_user_profile', [ $this, 'output_birthday_field' ] );

		add_action( 'user_profile_update_errors', [ $this, 'save_birthday_field' ], 10, 3 );
	}

	/**
	 * Output the birthday field.
	 *
	 * @param \WP_User $user
	 */
	public function output_birthday_field( $user ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$args = [
			'current_birthday' => AW_Birthdays()->get_user_birthday( $user->ID ),
		];

		Admin::output_view( 'edit-user-birthday-field.php', $args );
	}

	/**
	 * Save the birthday field.
	 *
	 * @param \WP_Error $errors
	 * @param bool      $update
	 * @param \stdClass $user
	 */
	public function save_birthday_field( $errors, $update, $user ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$year  = absint( aw_get_post_var( 'automatewoo_birthday_year' ) );
		$month = absint( aw_get_post_var( 'automatewoo_birthday_month' ) );
		$day   = absint( aw_get_post_var( 'automatewoo_birthday_day' ) );

		if ( AW_Birthdays()->is_date_empty( $day, $month, $year ) ) {
			if ( isset( $user->ID ) ) {
				AW_Birthdays()->clear_user_birthday( $user->ID );
			}
			return;
		}

		if ( ! AW_Birthdays()->validate_date( $day, $month, $year ) ) {
			$errors->add( 1, AW_Birthdays()->get_form_response_message( 'invalid' ) );
			return;
		}

		AW_Birthdays()->set_user_birthday( $user->ID, $day, $month, $year );
	}

}
