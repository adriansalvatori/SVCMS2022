<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use Objectiv\Plugins\Checkout\Admin;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

class BeaverThemer extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\FLThemeBuilderLayoutRenderer' );
	}

	public function pre_init() {
		add_action( 'cfw_admin_integrations_settings', array( $this, 'admin_integration_setting' ) );
	}

	public function run() {
		if ( SettingsManager::instance()->get_setting( 'enable_beaver_themer_support' ) === 'yes' ) {
			add_action( 'cfw_custom_header', 'FLThemeBuilderLayoutRenderer::render_header' );
			add_action( 'cfw_custom_footer', 'FLThemeBuilderLayoutRenderer::render_footer' );
		}
	}

	/**
	 * @param Admin\Pages\PageAbstract $integrations
	 */
	public function admin_integration_setting( Admin\Pages\PageAbstract $integrations ) {
		if ( ! $this->is_available() ) {
			return;
		}

		$integrations->output_checkbox_row(
			'enable_beaver_themer_support',
			cfw__( 'Enable Beaver Themer support.', 'checkout-wc' ),
			cfw__( 'Allow Beaver Themer to replace header and footer.', 'checkout-wc' )
		);
	}
}
