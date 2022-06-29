<?php

namespace AutomateWoo\Birthdays;

use AutomateWoo\Clean;
use AutomateWoo\Privacy_Abstract;

defined( 'ABSPATH' ) || exit;

/**
 * Class Privacy
 *
 * @package AutomateWoo\Birthdays
 */
class Privacy extends Privacy_Abstract {

	/**
	 * Privacy constructor.
	 */
	public function __construct() {
		parent::__construct( __( 'AutomateWoo - Birthdays', 'automatewoo-birthdays' ) );

		add_action( 'automatewoo/privacy/erase_user_meta', [ $this, 'erase_user_meta' ] );
		add_filter( 'automatewoo/privacy/exported_customer_data', [ $this, 'filter_exported_customer_data' ], 10, 2 );
	}

	/**
	 * Add suggested privacy policy content.
	 */
	public function get_privacy_message() {
		return Privacy_Policy_Guide::get_content();
	}

	/**
	 * Erase birthday meta fields.
	 *
	 * @param \WP_User $user
	 */
	public function erase_user_meta( $user ) {
		if ( ! $user instanceof \WP_User ) {
			return;
		}

		delete_user_meta( $user->ID, '_automatewoo_birthday_full' );
		delete_user_meta( $user->ID, '_automatewoo_birthday_md' );
	}

	/**
	 * Filter exported customer data.
	 *
	 * @param array  $data
	 * @param string $email
	 *
	 * @return array
	 */
	public function filter_exported_customer_data( $data, $email ) {
		$user = get_user_by( 'email', $email );

		if ( ! $user instanceof \WP_User ) {
			return $data;
		}

		// We don't use \AW_Birthdays_Addon::get_user_birthday as it may not reveal all stored birthday fields
		$full = Clean::string( get_user_meta( $user->ID, '_automatewoo_birthday_full', true ) );
		$md   = Clean::string( get_user_meta( $user->ID, '_automatewoo_birthday_md', true ) );

		if ( $full || $md ) {
			if ( $full ) {
				$value = "$full, $md";
			} else {
				$value = $md;
			}

			$data[ __( 'Customer Birthday', 'automatewoo-birthdays' ) ] = $value;
		}

		return $data;
	}

}
