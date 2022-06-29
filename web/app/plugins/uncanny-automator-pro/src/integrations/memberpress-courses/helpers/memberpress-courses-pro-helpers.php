<?php

namespace Uncanny_Automator;

use memberpress\courses\lib as lib;
use memberpress\courses\models as models;
use Uncanny_Automator\Memberpress_Courses_Helpers;

/**
 * Class Memberpress_Courses_Pro_Helpers
 *
 * @package Uncanny_Automator
 */
class Memberpress_Courses_Pro_Helpers extends Memberpress_Courses_Helpers {

	/**
	 * @var Memberpress_Courses_Pro_Helpers
	 */
	public $pro;

	/**
	 * @var bool
	 */
	public $load_options;

	/**
	 * Memberpress_Courses_Pro_Helpers constructor.
	 */
	public function __construct() {
		$this->load_options = Automator()->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
	}

	/**
	 * @param Memberpress_Courses_Pro_Helpers $pro
	 */
	public function setPro( Memberpress_Courses_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}


}
