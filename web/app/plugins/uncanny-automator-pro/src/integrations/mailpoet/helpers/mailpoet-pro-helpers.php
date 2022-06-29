<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Mailpoet_Helpers;

/**
 * Class Mailpoet_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Mailpoet_Pro_Helpers extends Mailpoet_Helpers {


	public function __construct() {
	}

	/**
	 * @param Mailpoet_Pro_Helpers $pro
	 */
	public function setPro( Mailpoet_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}

}