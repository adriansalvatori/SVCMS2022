<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Add_Edd_Integration
 * @package Uncanny_Automator_Pro
 */
class Add_Edd_Integration {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'EDD';

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

		if ( self::$integration === $plugin ) {
			if ( function_exists( 'EDD' ) ) {
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
		Automator()->register->integration( self::$integration, array(
			'name'     => 'Easy Digital Downloads',
			'icon_svg' => \Uncanny_Automator\Utilities::get_integration_icon( __DIR__ . '/img/easy-digital-downloads-icon.svg' ),
		) );
	}
}
