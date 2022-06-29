<?php
// phpcs:ignoreFile

/**
 * Referral functions
 *
 * These functions are intended for external usage.
 */

use AutomateWoo\Referrals;

defined( 'ABSPATH' ) || exit;


/**
 * Cookies are only used when using link tracking,
 * Returns false if the advocate key has expired
 * The advocate id is the same as the advocate's user id
 * @return int|bool
 */
function aw_referrals_get_advocate_id_from_current_cookie() {
	if ( ! $key = Referrals\Referral_Manager::get_advocate_key_from_cookie() ) {
		return false;
	}
	return $key->get_advocate_id();
}

/**
 * Check if an advocate key exists.
 *
 * @since 2.5.0
 *
 * @param string $key
 *
 * @return bool
 */
function aw_referrals_advocate_key_exists( $key ) {
	$query = new Referrals\Advocate_Key_Query();
	$query->where( 'advocate_key', $key );
	return $query->has_results();
}
