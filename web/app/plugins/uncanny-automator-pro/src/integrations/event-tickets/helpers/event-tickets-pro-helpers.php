<?php


namespace Uncanny_Automator_Pro;


use Uncanny_Automator\Event_Tickets_Helpers;

/**
 * Class Event_Tickets_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Event_Tickets_Pro_Helpers extends Event_Tickets_Helpers {
	/**
	 * Event_Tickets_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Event_Tickets_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}

	}

	/**
	 * @param Event_Tickets_Pro_Helpers $pro
	 */
	public function setPro( Event_Tickets_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}
}