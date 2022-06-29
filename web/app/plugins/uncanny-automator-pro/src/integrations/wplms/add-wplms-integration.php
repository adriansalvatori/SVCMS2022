<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Add_WPLMS_Integration
 *
 * @package Uncanny_Automator_Pro
 */
class Add_WPLMS_Integration {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WPLMS';

	/**
	 * Add_Integration constructor.
	 */
	public function __construct() {

	}

	/**
	 * Only load this integration and its triggers and actions if the related plugin is active
	 *
	 * @param $status
	 * @param $plugin
	 *
	 * @return bool
	 */
	public function plugin_active( $status, $plugin ) {

		if ( function_exists( 'is_wplms_4_0' ) || class_exists( 'WPLMS_Front_End' ) ) {
			$status = true;
		}

		return $status;
	}

	/**
	 * Set the directories that the auto loader will run in
	 *
	 * @param $directory
	 *
	 * @return array
	 */
	public function add_integration_directory_func( $directory ) {

		$directory[] = dirname( __FILE__ ) . '/helpers';
		$directory[] = dirname( __FILE__ ) . '/actions';
		$directory[] = dirname( __FILE__ ) . '/triggers';
		$directory[] = dirname( __FILE__ ) . '/tokens';

		return $directory;
	}

	/**
	 * Register the integration by pushing it into the global automator object
	 */
	public function add_integration_func() {

		global $uncanny_automator;

		$uncanny_automator->register->integration( self::$integration, array(
			'name'     => 'WP LMS',
			'icon_svg' => \Uncanny_Automator\Utilities::get_integration_icon( 'wplms-icon.svg' ),
		) );
	}
}
