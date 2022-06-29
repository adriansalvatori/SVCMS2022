<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Add_Bp_Integration
 *
 * @package Uncanny_Automator_Pro
 */
class Add_Bp_Integration {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'BP';

	/**
	 * Add_Integration constructor.
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'migrate_bpprofile_to_bpprofiledata' ) );

		// Add directories to auto loader
		// add_filter( 'automator_pro_integration_directory', [ $this, 'add_integration_directory_func' ], 11 );

		// Add code, name and icon set to automator
		// $this->add_integration_func();

		// Verify is the plugin is active based on integration code
//		add_filter( 'uncanny_automator_maybe_add_integration', [
//			$this,
//			'plugin_active',
//		], 30, 2 );
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
			if ( class_exists( 'BuddyPress' ) ) {
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
			'name'        => 'BuddyPress',
			'icon_16'     => \Uncanny_Automator\Utilities::get_integration_icon( 'integration-buddypress-icon-16.png' ),
			'icon_32'     => \Uncanny_Automator\Utilities::get_integration_icon( 'integration-buddypress-icon-32.png' ),
			'icon_64'     => \Uncanny_Automator\Utilities::get_integration_icon( 'integration-buddypress-icon-64.png' ),
			'logo'        => \Uncanny_Automator\Utilities::get_integration_icon( 'integration-buddypress.png' ),
			'logo_retina' => \Uncanny_Automator\Utilities::get_integration_icon( 'integration-buddypress@2x.png' ),
		) );
	}

	/**
	 * @return void
	 */
	public function migrate_bpprofile_to_bpprofiledata() {
		if ( 'yes' === get_option( 'automator_bp_profile_trigger_moved' ) ) {
			return;
		}

		global $wpdb;
		$current_triggers = $wpdb->get_results( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'code' && meta_value= 'BPSETUSERPROFILEDATA'" );
		if ( empty( $current_triggers ) ) {
			update_option( 'automator_bp_profile_trigger_moved', 'yes', false );

			return;
		}
		foreach ( $current_triggers as $t ) {
			$trigger_id = $t->post_id;
			$sentence   = maybe_serialize( 'Set the user\'s Xprofile {{data:BPPROFILE}}' );
			update_post_meta( $trigger_id, 'sentence', $sentence );
		}

		update_option( 'automator_bp_profile_trigger_moved', 'yes', false );

	}
}
