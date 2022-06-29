<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

use Objectiv\Plugins\Checkout\Managers\SettingsManager;

class PageController {
	protected $pages = array();

	public function __construct( PageAbstract ...$pages ) {
		$this->pages = $pages;
	}

	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 1000 );

		$this->maybe_add_body_class();

		foreach ( $this->pages as $page ) {
			$page->init();
		}
	}

	public function is_cfw_admin_page(): bool {
		foreach ( $this->pages as $page ) {
			if ( $page->is_current_page() ) {
				return true;
			}
		}

		return false;
	}

	public function enqueue_scripts() {
		if ( ! $this->is_cfw_admin_page() ) {
			return;
		}

		// Minified extension
		$min = ( ! CFW_DEV_MODE ) ? '.min' : '';

		// Version extension
		$version = CFW_VERSION;

		wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
		wp_enqueue_script( 'objectiv-cfw-admin', CFW_URL . "assets/dist/js/checkoutwc-admin-{$version}{$min}.js", array( 'jquery', 'wp-color-picker', 'wc-enhanced-select' ), CFW_VERSION );

		if ( $this->is_cfw_admin_page() ) {
			wp_enqueue_style( 'objectiv-cfw-admin-styles', CFW_URL . "assets/dist/css/checkoutwc-admin-$version}{$min}.css", array(), CFW_VERSION );
		}

		wp_enqueue_style( 'woocommerce_admin_styles' );

		$settings_array = array(
			'logo_attachment_id' => SettingsManager::instance()->get_setting( 'logo_attachment_id' ),
			'i18n_nav_warning'   => cfw__( 'The changes you made will be lost if you navigate away from this page.', 'woocommerce' ),
		);
		wp_localize_script( 'objectiv-cfw-admin', 'objectiv_cfw_admin', $settings_array );
	}

	protected function maybe_add_body_class() {
		if ( ! $this->is_cfw_admin_page() ) {
			return;
		}

		add_filter(
			'admin_body_class',
			function( $classes ) {
				return $classes . ' cfw-admin-page';
			},
			10000
		);
	}
}
