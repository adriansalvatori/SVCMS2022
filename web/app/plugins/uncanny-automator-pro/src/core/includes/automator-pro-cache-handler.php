<?php

namespace Uncanny_Automator_Pro;

/**
 *
 */
class Automator_Pro_Cache_Handler {
	/**
	 *
	 */
	public function __construct() {
		add_action(
			'automator_cache_reset_integrations_directory',
			array(
				$this,
				'reset_integrations_directory',
			),
			20,
			2
		);
		add_action( 'activated_plugin', array( $this, 'reset_integrations_directory' ), 99999, 2 );
		add_action( 'deactivated_plugin', array( $this, 'reset_integrations_directory' ), 99999, 2 );

		add_action( 'automator_cache_recipe_post_status_changed', array( $this, 'recipe_post_status_changed' ) );
		add_action( 'transition_post_status', array( $this, 'recipe_post_status_changed' ), 99999, 3 );
		add_action( 'automator_recipe_action_created', array( $this, 'recipe_post_status_changed' ), 99999 );
		add_action( 'automator_recipe_trigger_created', array( $this, 'recipe_post_status_changed' ), 99999 );
		add_action( 'automator_recipe_closure_created', array( $this, 'recipe_post_status_changed' ), 99999 );

		add_action( 'admin_init', array( $this, 'admin_remove_all' ) );
		add_action( 'automator_cache_remove_all', array( $this, 'remove_all' ) );
	}

	/**
	 * @param $plugin
	 * @param $network_wide
	 *
	 * @return void
	 */
	public function reset_integrations_directory( $plugin, $network_wide ) {
		Automator()->cache->remove( 'automator_pro_get_all_integrations' );
		Automator()->cache->remove( 'automator_pro_integration_directories_loaded' );
	}

	/**
	 * @param ...$args
	 *
	 * @return void
	 */
	public function recipe_post_status_changed( ...$args ) {
		$this->reset_integrations_directory( null, null );
	}

	/**
	 * @return void
	 */
	public function admin_remove_all() {
		if ( ! automator_filter_has_var( 'automator_flush_all' ) ) {
			return;
		}
		if ( ! wp_verify_nonce( automator_filter_input( '_wpnonce' ), AUTOMATOR_BASE_FILE ) ) {
			return;
		}
		$this->remove_all();
	}

	/**
	 * @return void
	 */
	public function remove_all() {
		wp_cache_flush();
		$this->reset_integrations_directory( null, null );
	}
}
