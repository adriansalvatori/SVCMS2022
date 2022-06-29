<?php
/**
 * Permission checker abstract class
 */

abstract class PH_Permission_Checker {
	/**
	 * Store our successor
	 *
	 * @var PH_Permission_Checker
	 */
	protected $successor;


	/**
	 * Store our permissions data
	 *
	 * @var PH_Permissions_Data
	 */
	protected $data;

	/**
	 * Need to validate
	 *
	 * @return boolean
	 */
	abstract public function validate();

	/**
	 * Set permission data
	 *
	 * @param PH_Permissions_Data $data (required)
	 */
	public function __construct( PH_Permissions_Data $data ) {
		$this->data = $data;
	}

	/**
	 * Need to check permission
	 *
	 * @param PH_Permissions_Status $status
	 * @return boolean
	 */
	public function check() {
		// bail if error
		if ( is_wp_error( $valid = $this->validate() ) ) {
			return $valid;
		}

		// check up chain if not valid
		if ( ! $valid ) {
			return $this->next();
		}

		// return validity if valid
		return $valid;
	}

	/**
	 * Fail with another successor
	 *
	 * @param PH_Permission_Checker $successor
	 * @return void
	 */
	public function failWith( PH_Permission_Checker $successor ) {
		$this->successor = $successor;
	}

	/**
	 * Next successor to check
	 *
	 * @return boolean
	 */
	public function next() {
		if ( $this->successor ) {
			return $this->successor->check();
		}

		return false;
	}
}
