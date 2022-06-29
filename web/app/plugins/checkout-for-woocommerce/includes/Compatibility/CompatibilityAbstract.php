<?php

namespace Objectiv\Plugins\Checkout\Compatibility;
use Objectiv\Plugins\Checkout\SingletonAbstract;

abstract class CompatibilityAbstract extends SingletonAbstract {
	final public function init() {
		if ( $this->is_available() && ( ! is_admin() || wp_doing_ajax() ) ) {
			// Allow scripts and styles for certain plugins
			add_filter( 'cfw_blocked_style_handles', array( $this, 'remove_styles' ), 10, 1 );
			add_filter( 'cfw_blocked_script_handles', array( $this, 'remove_scripts' ), 10, 1 );
			add_filter( 'cfw_typescript_compatibility_classes_and_params', array( $this, 'typescript_class_and_params' ), 10, 1 );

			// Run if on checkout
			$this->run_immediately();
			add_action( 'wp', array( $this, 'queue_checkout_and_order_pay_page_actions' ), 0 );
			add_action( 'wp', array( $this, 'queue_order_received_actions' ), 0 );
			add_action( 'cfw_checkout_update_order_review', array( $this, 'run' ) );
			add_action( 'wp_loaded', array( $this, 'run_on_wp_loaded' ), 0 );
		}
	}

	/**
	 * Allow some things to be run before init
	 *
	 * SHOULD BE AVOIDED
	 */
	public function pre_init() {
		// Silence is golden
	}

	final public function queue_checkout_and_order_pay_page_actions() {
		if ( cfw_is_checkout() || is_checkout_pay_page() ) {
			$this->run();
		}
	}

	/***
	 * Kick-off everything here
	 */
	public function run() {
		// Silence be golden
	}

	final public function queue_order_received_actions() {
		if ( is_order_received_page() ) {
			$this->run_on_thankyou();
		}
	}

	/**
	 * Only run on order-received page
	 */
	public function run_on_thankyou() {
		// Silence is golden
	}

	/***
	 * Kick-off everything here immediately
	 */
	public function run_immediately() {
		// Silence be golden
	}

	/**
	 * Kick-off everything here on wp_loaded hook
	 */
	public function run_on_wp_loaded() {
		// Silence is golden
	}

	/**
	 * Is dependency for this compatibility class available?
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		return false;
	}

	/**
	 * @param array $compatibility
	 *
	 * @return array
	 */
	public function typescript_class_and_params( array $compatibility ): array {
		return $compatibility;
	}

	/**
	 * @param $styles array Array of handles to remove from styles queue.
	 *
	 * @return array
	 */
	public function remove_styles( array $styles ): array {
		return $styles;
	}

	/**
	 * @param $scripts array Array of handles to remove from scripts queue.
	 *
	 * @return mixed
	 */
	public function remove_scripts( array $scripts ) {
		return $scripts;
	}
}
