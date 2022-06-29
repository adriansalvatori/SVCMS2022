<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Add_Uoa_Integration
 * @package Uncanny_Automator_Pro
 */
class Add_Uoa_Integration {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'UOA';

	/**
	 * Add_Integration constructor.
	 */
	public function __construct() {

		// Add directories to auto loader
		// add_filter( 'automator_pro_integration_directory', [ $this, 'add_integration_directory_func' ], 11 );

		// Update previous triggers moved to this integration
		$this->update_script();

		// Add code, name and icon set to automator
		// $this->add_integration_func();

		// Verify is the plugin is active based on integration code
		//      add_filter( 'uncanny_automator_maybe_add_integration', [
		//          $this,
		//          'plugin_active',
		//      ], 30, 2 );

		// A patch for magic triggers.
		add_action( 'automator_recipe_trigger_created', array( $this, 'magic_meta_add' ), 10, 3 );
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
		return true;
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
		$directory[] = dirname( __FILE__ ) . '/closures';

		return $directory;
	}

	/**
	 * Register the integration by pushing it into the global automator object
	 */
	public function add_integration_func() {

		global $uncanny_automator;

		$uncanny_automator->register->integration(
			'UOA',
			array(
				'name'     => 'Automator Core',
				'icon_svg' => \Uncanny_Automator_Pro\Utilities::get_integration_icon( 'automator-core-icon.svg' ),
			)
		);
	}

	/**
	 * Update previous triggers moved to this integration
	 */
	public function update_script() {
		if ( false === get_option( '_uoa_wpmagicbutton_wp_uoa_any', false ) ) {
			$args         = array(
				'post_type'   => 'uo-trigger',
				'post_status' => 'any',
				'meta_query'  => array(
					'relation' => 'AND',
					array(
						'key'     => 'integration',
						'value'   => 'WP',
						'compare' => 'LIKE',
					),
					array(
						'key'     => 'code',
						'value'   => 'WPMAGICBUTTON',
						'compare' => 'LIKE',
					),
				),
			);
			$old_triggers = get_posts( $args );
			if ( ! empty( $old_triggers ) ) {
				foreach ( $old_triggers as $old_trigger ) {
					update_post_meta( $old_trigger->ID, 'integration', self::$integration );
					update_post_meta( $old_trigger->ID, 'integration_name', 'Automator Core' );
				}
			}
			update_option( '_uoa_wpmagicbutton_wp_uoa_any', 'updated' );
		}

		if ( false === get_option( '_uoa_wpanonwebhook_wp_uoa_any', false ) ) {
			$args         = array(
				'post_type'   => 'uo-trigger',
				'post_status' => 'any',
				'meta_query'  => array(
					'relation' => 'AND',
					array(
						'key'     => 'integration',
						'value'   => 'WP',
						'compare' => 'LIKE',
					),
					array(
						'key'     => 'code',
						'value'   => 'WP_ANON_WEBHOOKS',
						'compare' => 'LIKE',
					),
				),
			);
			$old_triggers = get_posts( $args );
			if ( ! empty( $old_triggers ) ) {
				foreach ( $old_triggers as $old_trigger ) {
					update_post_meta( $old_trigger->ID, 'integration', self::$integration );
					update_post_meta( $old_trigger->ID, 'integration_name', 'Automator Core' );
				}
			}
			update_option( '_uoa_wpanonwebhook_wp_uoa_any', 'updated' );
		}
	}

	/**
	 * @param $trigger_id
	 * @param $item_code
	 * @param \WP_REST_Request $request
	 */
	public function magic_meta_add( $trigger_id, $item_code, $request ) {

		if ( 'WPMAGICLINK' === (string) $item_code || 'WPMAGICBUTTON' === (string) $item_code ||
		     'ANONWPMAGICLINK' === (string) $item_code || 'ANONWPMAGICBUTTON' === (string) $item_code ) {
			update_post_meta( $trigger_id, $item_code, $trigger_id );
		}

	}
}
