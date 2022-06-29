<?php


namespace Uncanny_Automator_Pro;


/**
 * Class Uoa_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Uoa_Pro_Helpers {
	/**
	 * @var Uoa_Pro_Helpers
	 */
	public $options;
	/**
	 * @var Uoa_Pro_Helpers
	 */
	public $pro;

	/**
	 * @param Uoa_Pro_Helpers $pro
	 */
	public function setPro( Uoa_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}

	/**
	 * @param Uoa_Pro_Helpers $options
	 */
	public function setOptions( Uoa_Pro_Helpers $options ) {
		$this->options = $options;
	}

	/**
	 * Wp_Pro_Helpers constructor.
	 */
	public function __construct() {

	}
}
