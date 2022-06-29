<?php


namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Integromat_Helpers;
/**
 * Class Integromat_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Integromat_Pro_Helpers extends Integromat_Helpers{

	public $load_options;

	/**
	 * Integromat_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( method_exists( '\Uncanny_Automator\Automator_Helpers_Recipe', 'maybe_load_trigger_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		} else {
			$this->load_options = true;
		}

	}

	/**
	 * @param Integromat_Pro_Helpers $pro
	 */
	public function setPro( Integromat_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}
}
