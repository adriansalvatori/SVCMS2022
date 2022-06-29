<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * @class Advocate_Key_Query
 * @since 1.6
 */
class Advocate_Key_Query extends AutomateWoo\Query_Abstract {

	/** @var string  */
	public $table_id = 'referral-advocate-keys';

	/** @var string  */
	protected $model = 'AutomateWoo\Referrals\Advocate_Key';

	/**
	 * Filter by expired keys.
	 *
	 * @since 2.5.0
	 *
	 * @return $this
	 */
	public function where_expired() {
		if ( ! AW_Referrals()->options()->is_advocate_key_expiry_enabled() ) {
			return $this;
		}

		return $this->where( 'created', $this->get_expiry_date(), '<' );
	}

	/**
	 * Filter by not expired keys.
	 *
	 * @since 2.5.0
	 *
	 * @return $this
	 */
	public function where_not_expired() {
		if ( ! AW_Referrals()->options()->is_advocate_key_expiry_enabled() ) {
			return $this;
		}

		return $this->where( 'created', $this->get_expiry_date(), '>' );
	}

	/**
	 * Get advocate key expiry date based on options.
	 *
	 * @since 2.5.0
	 *
	 * @return AutomateWoo\DateTime
	 */
	protected function get_expiry_date() {
		return new AutomateWoo\DateTime( '-' . AW_Referrals()->options()->get_advocate_key_expiry(). 'weeks' );
	}

	/**
	 * @return Advocate_Key[]
	 */
	function get_results() {
		return parent::get_results();
	}

}
