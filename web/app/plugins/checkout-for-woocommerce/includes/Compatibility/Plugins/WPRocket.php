<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Managers\SettingsManager;

class WPRocket {
	public function init() {
		// Exclude CSS from WP Rocket
		add_filter( 'rocket_exclude_css', array( $this, 'exclude_css' ) );

		// Exclude our JavaScript from WP Rocket
		add_filter( 'rocket_exclude_js', array( $this, 'exclude_js' ) );
		add_filter( 'rocket_delay_js_exclusions', array( $this, 'exclude_js' ) );
		add_filter( 'rocket_exclude_defer_js', array( $this, 'exclude_js' ) );

		add_action( SettingsManager::instance()->prefix . '_settings_saved', array( $this, 'maybe_delete_cache_empty_cart' ), 10, 1 );
		add_action( 'cfw_before_plugin_data_upgrades', array( $this, 'delete_cache_empty_cart' ) );
	}

	public function exclude_css( array $excluded_css ): array {
		$excluded_css[] = str_ireplace( home_url(), '', trailingslashit( CFW_PATH_ASSETS ) . '(.*).css' );

		return $excluded_css;
	}

	public function exclude_js( array $excluded_js ): array {
		$excluded_js[] = str_ireplace( home_url(), '', trailingslashit( CFW_PATH_ASSETS ) . '(.*).js' );

		return $excluded_js;
	}

	public function maybe_delete_cache_empty_cart( array $new_settings ) {
		if ( ! defined( 'WP_ROCKET_VERSION' ) ) {
			return;
		}

		if ( ! isset( $new_settings['enable_side_cart'] ) ) {
			return;
		}

		$this->delete_cache_empty_cart();
	}

	/** Copied from wp-rocket/inc/ThirdParty/Plugins/Ecommerce/WooCommerceSubscriber.php */
	public function delete_cache_empty_cart() {
		if ( ! defined( 'WP_ROCKET_VERSION' ) ) {
			return;
		}

		$langs = get_rocket_i18n_code();

		if ( $langs ) {
			foreach ( $langs as $lang ) {
				delete_transient( 'rocket_get_refreshed_fragments_cache_' . $lang );
			}
		}

		delete_transient( 'rocket_get_refreshed_fragments_cache' );
	}
}
