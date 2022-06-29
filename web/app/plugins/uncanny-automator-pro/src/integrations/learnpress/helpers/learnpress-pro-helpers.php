<?php


namespace Uncanny_Automator_Pro;


use Uncanny_Automator\Learnpress_Helpers;

/**
 * Class Learnpress_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Learnpress_Pro_Helpers extends Learnpress_Helpers {
	/**
	 * Learnpress_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Learnpress_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}

	}

	/**
	 * @param Learnpress_Pro_Helpers $pro
	 */
	public function setPro( Learnpress_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}
}