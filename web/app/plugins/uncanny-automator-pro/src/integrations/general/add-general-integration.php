<?php

namespace Uncanny_Automator_Pro;

/**
 * Class General_Integration
 *
 * @package Uncanny_Automator_Pro
 */
class Add_General_Integration {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'GEN';

	/**
	 * Add_Integration constructor.
	 */
	public function __construct() {}

	/**
	 * Always load this integration
	 *
	 * @param $status
	 * @param $code
	 *
	 * @return bool
	 */
	public function plugin_active( $status, $code ) {

		if ( self::$integration === $code ) {
			return true;
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

		// $directory[] = dirname( __FILE__ ) . '/helpers';
		// $directory[] = dirname( __FILE__ ) . '/actions';
		// $directory[] = dirname( __FILE__ ) . '/triggers';
		$directory[] = dirname( __FILE__ ) . '/conditions';

		return $directory;
	}

	/**
	 * Register the integration by pushing it into the global automator object
	 */
	public function add_integration_func() {
		Automator()->register->integration(
			self::$integration,
			array(
				'name'     => 'General',
				'icon_svg' => plugins_url( 'src/integrations/general/img/general-icon.svg', AUTOMATOR_PRO_FILE ),
			)
		);
	}
}
