<?php

namespace Objectiv\Plugins\Checkout;

use Objectiv\Plugins\Checkout\Admin\Pages\PageController;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

class Onboarding {

	protected $page_controller;

	public function __construct( PageController $page_controller ) {
		$this->page_controller = $page_controller;
	}

	public function init() {
		add_action( 'cfw_do_plugin_activation', array( $this, 'plugin_activation' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'maybe_enqueue_scripts' ), 1000 );
	}

	public function plugin_activation() {
		$already_ran = false !== SettingsManager::instance()->get_setting( 'onboarding_type' );

		if ( $already_ran ) {
			return;
		}

		$is_bulk_activation   = isset( $_GET['activate-multi'] );
		$immediate_onboarding = ! is_network_admin() && ! $is_bulk_activation;
		$onboarding_type      = $immediate_onboarding ? 'immediate' : 'on_cfw_admin_page';

		SettingsManager::instance()->update_setting( 'onboarding_type', $onboarding_type );
	}

	public function maybe_enqueue_scripts() {
		$type = SettingsManager::instance()->get_setting( 'onboarding_type' );

		if ( 'immediate' === $type && is_admin() ) {
			$this->enqueue_scripts();
			return;
		}

		if ( 'on_cfw_admin_page' === $type && $this->page_controller->is_cfw_admin_page() ) {
			$this->enqueue_scripts();
		}
	}

	protected function enqueue_scripts() {
		// Minified extension
		$min = ( ! CFW_DEV_MODE ) ? '.min' : '';

		// Version extension
		$version = CFW_VERSION;

		wp_enqueue_script( 'objectiv-cfw-admin-onboarding', CFW_URL . "assets/dist/js/checkoutwc-admin-onboarding-{$version}{$min}.js", array( 'jquery', 'wp-color-picker', 'wc-enhanced-select' ), CFW_VERSION );

		wp_enqueue_style( 'objectiv-cfw-admin-styles', CFW_URL . "assets/dist/css/checkoutwc-admin-onboarding-$version}{$min}.css", array(), CFW_VERSION );

		$settings_array = array(
			'root'  => esc_url_raw( rest_url() ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		);

		wp_localize_script( 'objectiv-cfw-admin-onboarding', 'cfwEnv', $settings_array );
	}
}
