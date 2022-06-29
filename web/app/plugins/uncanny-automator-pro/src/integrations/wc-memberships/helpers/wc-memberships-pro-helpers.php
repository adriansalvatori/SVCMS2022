<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Wc_Memberships_Helpers;

/**
 * Class Wc_Memberships_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Wc_Memberships_Pro_Helpers extends Wc_Memberships_Helpers{

	/**
	 * @var Wc_Memberships_Helpers
	 */
	public $options;
	/**
	 * @var bool
	 */
	public $load_options;

	/**
	 * @var bool
	 */
	public $pro;

	/**
	 * Wc_Memberships_Pro_Helpers constructor.
	 */
	public function __construct() {
		global $uncanny_automator;
		$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
	}

	/**
	 * @param Wc_Memberships_Pro_Helpers $pro
	 */
	public function setPro( Wc_Memberships_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}

}