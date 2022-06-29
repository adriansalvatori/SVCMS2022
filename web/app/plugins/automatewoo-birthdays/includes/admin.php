<?php

namespace AutomateWoo\Birthdays;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin Admin class.
 *
 * @package AutomateWoo\Birthdays
 */
class Admin {

	/**
	 * Init plugin admin area.
	 */
	public static function init() {
		/**
		 * Class name (for IDE).
		 *
		 * @var $self Admin
		 */
		$self = __CLASS__;

		add_filter( 'automatewoo/settings/tabs', [ $self, 'register_settings_tab' ] );
		add_action( 'current_screen', [ $self, 'conditional_includes' ] );
	}

	/**
	 * Include admin files conditionally.
	 *
	 * @param \WP_Screen $screen
	 */
	public static function conditional_includes( $screen ) {
		switch ( $screen->id ) {
			case 'users':
			case 'user':
			case 'profile':
			case 'user-edit':
				new Admin_Edit_User();
				break;
		}
	}

	/**
	 * Register plugin settings tab.
	 *
	 * @param array $tabs
	 *
	 * @return array
	 */
	public static function register_settings_tab( $tabs ) {
		$tabs[] = AW_Birthdays()->path( '/includes/settings-tab.php' );
		return $tabs;
	}

	/**
	 * Output admin view.
	 *
	 * @param string $view
	 * @param array  $args
	 */
	public static function output_view( $view, $args = [] ) {
		if ( $args && is_array( $args ) ) {
			// phpcs:disable WordPress.PHP.DontExtract.extract_extract
			extract( $args );
			// phpcs:enable
		}

		$path = AW_Birthdays()->path( '/includes/views/' . $view );

		if ( file_exists( $path ) ) {
			include $path;
		}
	}

}
