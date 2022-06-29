<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Add_Gk_Integration
 *
 * @package Uncanny_Automator_Pro
 */
class Add_Gk_Integration {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'GK';

	/**
	 * Add_Advanced_Coupons constructor.
	 */
	public function __construct() {

	}

	/**
	 * Only load this integration and its triggers and actions if the related
	 * plugin is active
	 *
	 * @param $status
	 * @param $code
	 *
	 * @return bool
	 */
	public function plugin_active( $status, $code ) {

		if ( self::$integration === $code ) {
			if ( class_exists( 'GravityView_Plugin' ) ) {
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
		Automator()->register->integration(
			self::$integration,
			array(
				'name'     => 'GravityKit',
				'icon_svg' => plugins_url( 'src/integrations/gravity-kit/img/gravitykit-icon.svg', AUTOMATOR_PRO_FILE ),
			)
		);
	}
}
