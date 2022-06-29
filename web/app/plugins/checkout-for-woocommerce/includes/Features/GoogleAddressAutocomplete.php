<?php

namespace Objectiv\Plugins\Checkout\Features;

use Objectiv\Plugins\Checkout\Admin\Pages\PageAbstract;
use Objectiv\Plugins\Checkout\Interfaces\SettingsGetterInterface;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 */
class GoogleAddressAutocomplete extends FeaturesAbstract {
	protected $google_api_key_settings_page_url;

	public function __construct( bool $enabled, bool $available, string $required_plans_list, SettingsGetterInterface $settings_getter, string $google_api_key_settings_page_url ) {
		$this->google_api_key_settings_page_url = $google_api_key_settings_page_url;

		parent::__construct( $enabled, $available, $required_plans_list, $settings_getter );
	}

	protected function run_if_cfw_is_enabled() {
		add_action( 'cfw_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'cfw_event_data', array( $this, 'add_localized_settings' ) );
		add_filter( 'cfw_enable_zip_autocomplete', '__return_false' );
	}

	public function enqueue_scripts() {
		if ( ! cfw_is_checkout() ) {
			return;
		}

		if ( apply_filters( 'cfw_google_maps_compatibility_mode', false ) ) {
			return;
		}

		$locale         = get_locale();
		$parsed_locale  = strstr( $locale, '_', true );
		$language       = $parsed_locale ? $parsed_locale : $locale;
		$language       = apply_filters( 'cfw_google_maps_language_code', $language );
		$google_api_key = $this->settings_getter->get_setting( 'google_places_api_key' );

		wp_enqueue_script( 'cfw-google-places', "https://maps.googleapis.com/maps/api/js?key={$google_api_key}&libraries=places&language={$language}", array( 'woocommerce' ) );
	}

	/**
	 * @param array $event_data
	 * @return array
	 */
	public function add_localized_settings( array $event_data ): array {
		$event_data['settings']['enable_address_autocomplete'] = true;

		/**
		 * Filter list of shipping country restrictions for Google Maps address autocomplete
		 *
		 * @since 3.0.0
		 *
		 * @param array $address_autocomplete_shipping_countries List of country restrictions for Google Maps address autocomplete
		 */
		$event_data['settings']['address_autocomplete_shipping_countries'] = apply_filters( 'cfw_address_autocomplete_shipping_countries', array() );
		$event_data['settings']['google_address_autocomplete_type']        = apply_filters( 'cfw_google_address_autocomplete_type', 'geocode' );

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
			'enable_address_autocomplete',
			cfw__( 'Enable Google Address Autocomplete', 'checkout-wc' ),
			sprintf( '%s <a href="%s" class="text-blue-600 underline">%s</a>', cfw__( 'Enable or disable address autocomplete feature.', 'checkout-wc' ), $this->google_api_key_settings_page_url, cfw__( 'Requires Google API key.', 'checkout-wc' ) ),
			array(
				'enabled' => $this->available,
				'notice'  => $notice ?? '',
			)
		);
	}

	public function run_on_plugin_activation() {
		SettingsManager::instance()->add_setting( 'enable_address_autocomplete', 'no' );
	}
}
