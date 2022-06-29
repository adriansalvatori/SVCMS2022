<?php

namespace Uncanny_Automator_Pro;

/**
 * Divi Pro Integration
 */
class Add_Divi_Integration {
	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'DIVI';

	/**
	 * Only load this integration and its triggers and actions if the related plugin is active
	 *
	 * @param $status
	 * @param $plugin
	 *
	 * @return bool
	 */
	public function plugin_active( $status, $plugin ) {

		$theme = wp_get_theme(); // gets the current theme
		// If Automator is > 3.4.0.2 AND DIVI as a theme is active
		if ( version_compare( AUTOMATOR_PLUGIN_VERSION, '3.4.0.2', '>' ) && 'Divi' === $theme->get_template() ) {
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

		$uncanny_automator->register->integration(
			self::$integration,
			array(
				'name'     => 'Divi',
				'icon_svg' => \Uncanny_Automator\Utilities::get_integration_icon( 'divi-icon.svg' ),
			)
		);
	}
}
