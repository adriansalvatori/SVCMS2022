<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;
use AutomateWoo\Clean;

defined( 'ABSPATH' ) || exit;

/**
 * @class Invite
 * @since 1.3
 */
class Invite extends AutomateWoo\Model {

	/** @var string  */
	public $table_id = 'referral-invites';

	/** @var string  */
	public $object_type = 'referral-invite';


	/**
	 * @param $id
	 */
	function __construct( $id = false ) {
		if ( $id ) $this->get_by( 'id', $id );
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
	 * @param \DateTime $date
	 */
	function set_date( $date ) {
		$this->set_date_column( 'date', $date );
	}


	/**
	 * @return \DateTime|false
	 */
	function get_date() {
		return $this->get_date_column( 'date' );
	}


	/**
	 * @return string
	 */
	function get_email() {
		return Clean::string( $this->get_prop( 'email' ) );
	}


	/**
	 * @param string $email
	 */
	function set_email( $email ) {
		$email = Clean::email( $email );
		if ( AW_Referrals()->options()->anonymize_invited_emails ) {
			$email = aw_anonymize_email( $email );
		}
 		$this->set_prop( 'email', $email );
	}

	
}
