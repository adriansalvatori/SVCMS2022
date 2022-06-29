<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Add_Masterstudy_Integration
 * @package Uncanny_Automator_Pro
 */
class Add_Masterstudy_Integration {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'MSLMS';

	/**
	 * Add_Integration constructor.
	 */
	public function __construct() {
	}

	/**
	 * Only load this integration and its triggers and actions if the related plugin is active
	 *
	 * @param $status
	 * @param $code
	 *
	 * @return bool
	 */
	public function plugin_active( $status, $code ) {
		if ( self::$integration === $code ) {
			if ( defined( 'STM_LMS_FILE' ) ) {
				$status = true;
			} else {
				$status = false;
			}
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
			'name'     => 'MasterStudy LMS',
			'logo_svg' => \Uncanny_Automator\Utilities::get_integration_icon( 'masterstudy-lms.svg' ),
			'icon_svg' => \Uncanny_Automator\Utilities::get_integration_icon( 'masterstudy-lms.svg' ),
		) );
	}
}
