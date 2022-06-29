<?php


namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Badgeos_Helpers;

/**
 * Class Badgeos_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Badgeos_Pro_Helpers extends Badgeos_Helpers {
	/**
	 * Badgeos_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Badgeos_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}
		
		add_action( 'wp_ajax_select_achievements_from_types_BOAWARDACHIEVEMENT', [
			$this,
			'select_achievements_from_types_func'
		] );
		add_action( 'wp_ajax_select_ranks_from_types_BOAWARDRANKS', [ $this, 'select_ranks_from_types_func' ] );
		add_action( 'wp_ajax_select_ranks_from_types_EARNSRANK', array( $this, 'select_ranks_from_types_func' ) );
		add_action( 'wp_ajax_select_achievements_from_types_REVOKEACHIEVEMENT', [
			$this,
			'select_achievements_from_types_func'
		] );
		add_action( 'wp_ajax_select_ranks_from_types_REVOKERANK', [ $this, 'select_ranks_from_types_func' ] );
	}

	/**
	 * @param Badgeos_Pro_Helpers $pro
	 */
	public function setPro( Badgeos_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}

}