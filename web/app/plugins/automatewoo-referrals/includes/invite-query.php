<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * @class Invite_Query
 * @since 1.6
 */
class Invite_Query extends AutomateWoo\Query_Abstract {

	/** @var string  */
	public $table_id = 'referral-invites';

	/** @var string  */
	protected $model = 'AutomateWoo\Referrals\Invite';


	/**
	 * @return Invite[]
	 */
	function get_results() {
		return parent::get_results();
	}

}
