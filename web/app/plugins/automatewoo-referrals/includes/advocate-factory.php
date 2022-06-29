<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

defined( 'ABSPATH' ) || exit;

use AutomateWoo\Clean;

/**
 * Factory for getting advocates.
 *
 * This factory is based on the WP user data so there isn't really a need to add object caching.
 *
 * @since 1.9
 */
class Advocate_Factory {

	/**
	 * @param int $id
	 * @return Advocate|false
	 */
	static function get( $id ) {
		$id = Clean::id( $id );
		if ( ! $id ) {
			return false;
		}

		// allows third-party code to replace advocate objects such as for a team membership
		// @since 2.1
		$id = apply_filters( 'automatewoo/referrals/advocate_id', $id );

		$advocate = new Advocate( $id );

		if ( ! $advocate->exists ) {
			return false; // missing advocate
		}

		return $advocate;
	}

}
