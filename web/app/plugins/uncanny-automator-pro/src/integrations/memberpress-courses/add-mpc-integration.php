<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Add_Mpc_Integration
 * @package Uncanny_Automator_Pro
 */
class Add_Mpc_Integration {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'MPC';

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
	public function plugin_active() {

		$pluginList = get_option( 'active_plugins' );
		$plugin     = 'memberpress-courses/main.php';

		if ( in_array( $plugin, $pluginList ) && class_exists( 'MeprCtrlFactory' ) ) {
			return true;
		}

		return false;
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
			'name'        => 'MemberPress Courses',
			'icon_16'     => \Uncanny_Automator\Utilities::get_integration_icon( 'integration-memberpress-icon-16.png' ),
			'icon_32'     => \Uncanny_Automator\Utilities::get_integration_icon( 'integration-memberpress-icon-32.png' ),
			'icon_64'     => \Uncanny_Automator\Utilities::get_integration_icon( 'integration-memberpress-icon-64.png' ),
			'logo'        => \Uncanny_Automator\Utilities::get_integration_icon( 'integration-memberpress.png' ),
			'logo_retina' => \Uncanny_Automator\Utilities::get_integration_icon( 'integration-memberpress@2x.png' ),
		) );
	}
}
