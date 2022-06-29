<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use Objectiv\Plugins\Checkout\Admin;
use ElementorPro\Modules\ThemeBuilder\Module as Theme_Builder_Module;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

class ElementorPro extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'ELEMENTOR_PRO_VERSION' );
	}

	public function pre_init() {
		add_action( 'cfw_admin_integrations_settings', array( $this, 'admin_integration_setting' ) );
	}

	public function run() {
		$this->maybe_load_elementor_header_footer();
	}

	public function run_on_thankyou() {
		$this->maybe_load_elementor_header_footer();
	}

	public function maybe_load_elementor_header_footer() {
		if ( SettingsManager::instance()->get_setting( 'enable_elementor_pro_support' ) === 'yes' ) {

			/** @var Theme_Builder_Module $theme_builder_module */
			$theme_builder_module = Theme_Builder_Module::instance();

			$header_documents_by_conditions = $theme_builder_module->get_conditions_manager()->get_documents_for_location( 'header' );
			$footer_documents_by_conditions = $theme_builder_module->get_conditions_manager()->get_documents_for_location( 'footer' );

			if ( ! empty( $header_documents_by_conditions ) ) {
				add_action(
					'cfw_custom_header',
					function() {
						elementor_theme_do_location( 'header' );
					}
				);
			}

			if ( ! empty( $footer_documents_by_conditions ) ) {
				add_action(
					'cfw_custom_footer',
					function() {
						elementor_theme_do_location( 'footer' );
					}
				);
			}
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
			'enable_elementor_pro_support',
			cfw__( 'Enable Elementor Pro support.', 'checkout-wc' ),
			cfw__( 'Allow Elementor Pro to replace header and footer.', 'checkout-wc' )
		);
	}
}
