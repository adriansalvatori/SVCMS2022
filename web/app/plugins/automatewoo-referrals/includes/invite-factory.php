<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Factory;

defined( 'ABSPATH' ) || exit;

/**
 * @class Invite_Factory
 */
class Invite_Factory extends Factory {

	static $model = 'AutomateWoo\Referrals\Invite';


	/**
	 * @param int $id
	 * @return Invite|bool
	 */
	static function get( $id ) {
		return parent::get( $id );
	}

}
