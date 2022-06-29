<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Advanced_Coupons_Helpers;

/**
 * Class Memberpress_Courses_Pro_Helpers
 *
 * @package Uncanny_Automator
 */
class Advanced_Coupons_Pro_Helpers extends Advanced_Coupons_Helpers {

	/**
	 * Load options variable.
	 *
	 * @var bool
	 */
	public $load_options;

	/**
	 * Advanced_Coupons_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Advanced_Coupons_Helpers', 'load_options' ) ) {
			$this->load_options = Automator()->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}
	}

	/**
	 * Setpro function is used to set pro variable.
	 *
	 * @param Advanced_Coupons_Pro_Helpers $pro
	 */
	// phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
	public function setPro( Advanced_Coupons_Pro_Helpers $pro ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		parent::setPro( $pro );
	}

}
