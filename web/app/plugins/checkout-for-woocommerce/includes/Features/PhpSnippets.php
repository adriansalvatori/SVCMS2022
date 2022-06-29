<?php

namespace Objectiv\Plugins\Checkout\Features;

use Objectiv\Plugins\Checkout\Admin\Pages\PageAbstract;
use Objectiv\Plugins\Checkout\Interfaces\SettingsGetterInterface;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

class PhpSnippets extends FeaturesAbstract {
	protected $php_snippets_field_name;

	public function __construct( bool $enabled, bool $available, string $required_plans_list, SettingsGetterInterface $settings_getter, string $php_snippets_field_name ) {
		$this->php_snippets_field_name = $php_snippets_field_name;

		parent::__construct( $enabled, $available, $required_plans_list, $settings_getter );
	}

	protected function run_if_cfw_is_enabled() {
		$php_snippets = $this->sanitize_snippet( $this->settings_getter->get_setting( 'php_snippets' ) );

		if ( empty( $php_snippets ) ) {
			return;
		}

		if ( class_exists( '\\ParseError' ) ) {
			try {
				eval( $php_snippets ); // phpcs:ignore
			} catch( \ParseError $e ) { // phpcs:ignore
				error_log( 'CheckoutWC: Failed to load PHP snippets. Parse Error: ' . $e->getMessage() );
			}
		} else {
			eval( $php_snippets ); // phpcs:ignore
		}
	}

	/**
	 * @param string $code
	 * @return string
	 */
	private function sanitize_snippet( string $code ): string {
		/* Remove <?php and <? from beginning of snippet */
		$code = preg_replace( '|^[\s]*<\?(php)?|', '', $code );

		/* Remove ?> from end of snippet */
		$code = preg_replace( '|\?>[\s]*$|', '', $code );

		return strval( $code );
	}

	public function init() {
		parent::init();

		add_action( 'cfw_do_plugin_activation', array( $this, 'run_on_plugin_activation' ) );
		add_action( 'cfw_advanced_scripts_after_admin_page_controls', array( $this, 'output_admin_settings' ) );
	}

	/**
	 * @param PageAbstract $advanced_admin_page
	 */
	public function output_admin_settings( PageAbstract $advanced_admin_page ) {
		$advanced_admin_page->output_textarea_row(
			'php_snippets',
			cfw__( 'PHP Snippets', 'checkout-wc' ),
			sprintf( cfw__( 'Add PHP snippets to modify your checkout page here. If you have lots of snippets, you may want to consider using <a target="_blank" href="%s">Code Snippets</a>.', 'checkout-wc' ), 'https://wordpress.org/plugins/code-snippets/' ),
			array(
				'textarea_rows' => 10,
			)
		);
	}

	public function run_on_plugin_activation() {
		SettingsManager::instance()->add_setting( 'php_snippets', '' );
	}
}
