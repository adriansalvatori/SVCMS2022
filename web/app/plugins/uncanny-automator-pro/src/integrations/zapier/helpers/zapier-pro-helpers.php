<?php


namespace Uncanny_Automator_Pro;


/**
 * Class Zapier_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Zapier_Pro_Helpers {

	/**
	 * @var Zapier_Pro_Helpers
	 */
	public $options;
	/**
	 * @var Zapier_Pro_Helpers
	 */
	public $pro;
	/**
	 * @var bool
	 */
	public $load_options;

	/**
	 * Zapier_Pro_Helpers constructor.
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
	 * @param Zapier_Pro_Helpers $options
	 */
	public function setOptions( Zapier_Pro_Helpers $options ) {
		$this->options = $options;
	}

	/**
	 * @param Zapier_Pro_Helpers $pro
	 */
	public function setPro( Zapier_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}
}
