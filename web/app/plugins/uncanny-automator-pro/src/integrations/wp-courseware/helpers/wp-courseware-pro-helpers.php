<?php


namespace Uncanny_Automator_Pro;


use Uncanny_Automator\Wp_Courseware_Helpers;

/**
 * Class Wp_Courseware_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Wp_Courseware_Pro_Helpers extends Wp_Courseware_Helpers {
	/**
	 * Wp_Courseware_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Wp_Courseware_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}
	}

	/**
	 * @param Wp_Courseware_Pro_Helpers $pro
	 */
	public function setPro( Wp_Courseware_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}
}