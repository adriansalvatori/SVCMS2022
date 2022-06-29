<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Model;
use AutomateWoo\Clean;

defined( 'ABSPATH' ) || exit;

/**
 * @class Advocate_Key
 * @since 1.1.4
 */
class Advocate_Key extends Model {

	/** @var string  */
	public $table_id = 'referral-advocate-keys';

	/** @var string  */
	public $object_type = 'referral-advocate-key';


	/**
	 * @param $id
	 */
	function __construct( $id = false ) {
		if ( $id ) $this->get_by( 'id', $id );
	}


	/**
	 * @return mixed
	 */
	function get_key() {
		return Clean::string( $this->get_prop( 'advocate_key' ) );
	}


	/**
	 * @param string $key
	 */
	function set_key( $key ) {
		$this->set_prop( 'advocate_key', Clean::string( $key ) );
	}


	/**
	 * @return int
	 */
	function get_advocate_id() {
		return Clean::id( $this->get_prop( 'advocate_id' ) );
	}


	/**
	 * @param int $id
	 */
	function set_advocate_id( $id ) {
		$this->set_prop( 'advocate_id', Clean::id( $id ) );
	}


	/**
	 * @return \DateTime|false
	 */
	function get_date_created() {
		return $this->get_date_column( 'created' );
	}


	/**
	 * @param $date \DateTime
	 */
	function set_date_created( $date ) {
		$this->set_date_column( 'created', $date );
	}


	/**
	 * @return bool
	 */
	function is_expired() {

		if ( ! AW_Referrals()->options()->is_advocate_key_expiry_enabled() ) {
			return false;
		}

		$expiry_date = $this->get_date_expires();

		if ( ! $expiry_date ) {
			return false;
		}

		return $expiry_date->getTimestamp() < time();
	}


	/**
	 * @return \DateTime|false
	 */
	function get_date_expires() {

		if ( ! AW_Referrals()->options()->is_advocate_key_expiry_enabled() ) {
			return false;
		}

		$expiry = AW_Referrals()->options()->get_advocate_key_expiry();
		$date   = $this->get_date_created();

		if ( ! $date ) {
			return false;
		}

		$date->modify( "+$expiry weeks" );
		return $date;
	}


	/**
	 * @return Advocate
	 */
	function get_advocate() {
		return Advocate_Factory::get( $this->get_advocate_id() );
	}

	/**
	 * Determine whether this key is valid based on expiration and whether the advocate is blocked.
	 *
	 * @return bool
	 */
	public function is_valid() {
		return ! ( $this->is_expired() || $this->get_advocate()->is_blocked() );
	}
}
