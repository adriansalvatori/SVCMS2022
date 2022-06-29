<?php

namespace Objectiv\Plugins\Checkout\Features;

use Objectiv\Plugins\Checkout\Admin\Pages\PageAbstract;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 */
class FetchifyAddressAutocomplete extends FeaturesAbstract {
	protected function run_if_cfw_is_enabled() {
		add_action( 'cfw_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'cfw_event_data', array( $this, 'add_localized_settings' ) );
		add_filter( 'cfw_enable_zip_autocomplete', '__return_false' );
	}

	public function enqueue_scripts() {
		if ( ! cfw_is_checkout() ) {
			return;
		}

		wp_enqueue_script( 'cfw-fetchify', 'https://cc-cdn.com/generic/scripts/v1/cc_c2a.min.js', array( 'woocommerce' ) );
	}

	/**
	 * @param array $event_data
	 * @return array
	 */
	public function add_localized_settings( array $event_data ): array {
		$event_data['settings']['enable_fetchify_address_autocomplete'] = $this->enabled;

		/**
		 * Filter list of shipping country restrictions for Google Maps address autocomplete
		 *
		 * @since 3.0.0
		 *
		 * @param array $address_autocomplete_shipping_countries List of country restrictions for Google Maps address autocomplete
		 */
		$event_data['settings']['fetchify_address_autocomplete_countries'] = apply_filters( 'cfw_fetchify_address_autocomplete_countries', false );

		$event_data['settings']['fetchify_access_token'] = $this->settings_getter->get_setting( 'fetchify_access_token' );

		$event_data['settings']['fetchify_enable_geolocation'] = apply_filters( 'cfw_fetchify_address_autocomplete_enable_geolocation', true );
		$event_data['settings']['fetchify_default_country']    = apply_filters( 'cfw_fetchify_address_autocomplete_default_country', 'gbr' );

		return $event_data;
	}

	public function init() {
		parent::init();

		add_action( 'cfw_do_plugin_activation', array( $this, 'run_on_plugin_activation' ) );
		add_action( 'cfw_after_admin_page_address_options_section', array( $this, 'output_admin_setting' ) );
	}

	public function output_admin_setting( PageAbstract $checkout_admin_page ) {
		if ( ! $this->available ) {
			$notice = $checkout_admin_page->get_upgrade_required_notice( $this->required_plans_list );
		}

		$checkout_admin_page->output_checkbox_row(
			'enable_fetchify_address_autocomplete',
			cfw__( 'Enable Fetchify Address Autocomplete', 'checkout-wc' ),
			sprintf( '%s <a href="%s" class="text-blue-600 underline" target="_blank">%s</a>', cfw__( 'Enable or disable Fetchify address autocomplete feature.', 'checkout-wc' ), 'https://fetchify.com', cfw__( 'Requires Fetchify access token.', 'checkout-wc' ) ),
			array(
				'enabled' => $this->available,
				'notice'  => $notice ?? '',
			)
		);

		$checkout_admin_page->output_text_input_row(
			'fetchify_access_token',
			cfw__( 'Fetchify Access Token', 'checkout-wc' ),
			cfw__( 'Your Fetchify access token.', 'checkout-wc' ),
			array( 'nested' => true )
		);
	}

	public function run_on_plugin_activation() {
		SettingsManager::instance()->add_setting( 'enable_fetchify_address_autocomplete', 'no' );
	}
}
