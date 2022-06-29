<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\DateTime;

/**
 * @class Advocate_Key_Manager
 */
class Advocate_Key_Manager {

	/**
	 * Delete advocate keys 90 days after they expire.
	 */
	public static function clean_advocate_keys() {

		$expiry = AW_Referrals()->options()->get_advocate_key_expiry();

		// never expire keys
		if ( $expiry === 0 ) {
			return;
		}

		$deletion_date = new DateTime();
		$deletion_date->modify( '-' . ( ( $expiry * 7 ) + self::get_days_to_keep_expired_keys_for() ) . ' days' );

		$query = new Advocate_Key_Query();
		$query->where( 'created', $deletion_date, '<' );
		$query->set_limit( 50 );

		foreach ( $query->get_results() as $result ) {
			$result->delete();
		}
	}

	/**
	 * Get number of days to keep expired keys for.
	 *
	 * @since 2.5.0
	 *
	 * @return int
	 */
	public static function get_days_to_keep_expired_keys_for() {
		return (int) apply_filters( 'automatewoo/referrals/days_to_keep_expired_advocate_keys_for', 90 );
	}

}
