<?php
/**
 * Data transfer object for connection status on child website
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PH_Child_Connection_Status {
	/**
	 * Project ID
	 *
	 * @var boolean
	 */
	public $id = 0;

	/**
	 * Is it connected?
	 *
	 * @var integer
	 */
	public $connected = false;

	/**
	 * Is the script installed
	 *
	 * @var string
	 */
	public $installed = false;


	/**
	 * Is the plugin activated on the child site
	 *
	 * @var string
	 */
	public $activated = false;

	/**
	 * Is the data valid for this project
	 *
	 * @var string
	 */
	public $valid = false;

	public function __construct( $id ) {
		// if id is 0, don't set anything
		if ( ! $id ) {
			return;
		}
		$this->id        = $id;
		$this->connected = (bool) get_post_meta( $id, 'ph-child-connected', true );
		$this->installed = (bool) get_post_meta( $id, 'ph_installed', true );
		$this->valid     = (bool) get_post_meta( $id, 'ph-child-valid', true );
		$this->activated = (bool) get_post_meta( $id, 'ph-child-plugin-activated', true );
	}

	/**
	 * Resets status in database
	 *
	 * @return void
	 */
	public function reset() {
		$this->save_connected( false );
		$this->save_activated( false );
		$this->save_installed( false );
		$this->save_valid( false );
	}

	public function save_connected( $value ) {
		update_post_meta( $this->id, 'ph-child-connected', $value );
		$this->connected = $value;
	}

	public function save_activated( $value ) {
		update_post_meta( $this->id, 'ph-child-plugin-activated', $value );
		$this->activated = $value;
	}

	public function save_valid( $value ) {
		update_post_meta( $this->id, 'ph-child-valid', $value );
		$this->valid = $value;
	}

	public function save_installed( $value ) {
		update_post_meta( $this->id, 'ph_installed', $value );
		$this->installed = $value;
	}

	public function get_installed_from_db() {
		$this->installed = (bool) get_post_meta( $this->id, 'ph_installed', true );
		return $this->installed;
	}
}
