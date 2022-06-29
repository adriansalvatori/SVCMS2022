<?php


namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Gamipress_Helpers;

/**
 * Class Gamipress_Pro_Helpers
 *
 * @package Uncanny_Automator_Pro
 */
class Gamipress_Pro_Helpers extends Gamipress_Helpers {
	/**
	 * Gamipress_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Gamipress_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}

		add_action(
			'wp_ajax_select_achievements_from_types_EARNSACHIEVEMENT',
			array(
				$this,
				'select_achievements_from_types_func',
			)
		);
		add_action( 'wp_ajax_select_ranks_from_types_EARNSRANK', array( $this, 'select_ranks_from_types_func' ) );
		add_action(
			'wp_ajax_select_achievements_from_types_REVOKEACHIEVEMENT',
			array(
				$this,
				'select_achievements_from_types_func',
			)
		);
		add_action( 'wp_ajax_select_ranks_from_types_REVOKERANK', array( $this, 'select_ranks_from_types_func' ) );
	}

	/**
	 * @param Gamipress_Pro_Helpers $pro
	 */
	public function setPro( Gamipress_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}
}
