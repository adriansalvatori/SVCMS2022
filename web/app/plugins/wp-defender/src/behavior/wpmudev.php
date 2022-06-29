<?php

namespace WP_Defender\Behavior;

use Calotes\Component\Behavior;
use WP_Defender\Traits\IO;
use WP_Defender\Traits\Formats;
use WP_Defender\Traits\Defender_Dashboard_Client;
use WP_Defender\Traits\Defender_Hub_Client;
use WP_Defender\Behavior\WPMUDEV_Const_Interface;

/**
 * This class contains everything relate to WPMUDEV.
 * Class WPMUDEV
 * @package WP_Defender\Behavior
 * @since 2.2
 */
class WPMUDEV extends Behavior implements WPMUDEV_Const_Interface {
	use IO, Formats, Defender_Dashboard_Client, Defender_Hub_Client;

	/**
	 * Get membership status.
	 *
	 * @return bool
	 */
	public function is_pro() {
		return $this->get_apikey() !== true;
	}

	/**
	 * Get WPMUDEV API KEY.
	 *
	 * @return bool|string
	 */
	public function get_apikey() {
		if ( ! class_exists( '\WPMUDEV_Dashboard' ) ) {
			return false;
		}
		\WPMUDEV_Dashboard::instance();
		$membership_status = \WPMUDEV_Dashboard::$api->get_membership_data();
		if (
			isset( $membership_status['membership'] )
			&& '' !== $membership_status['membership']
			&& 'free' !== $membership_status['membership']
		) {
			return \WPMUDEV_Dashboard::$api->get_key();
		} else {
			return false;
		}
	}

	/**
	 * Get the current membership status using Dash plugin.
	 *
	 * @return string
	 */
	public function membership_status() {
		if ( ! class_exists( '\WPMUDEV_Dashboard' ) ) {
			return 'free';
		}
		\WPMUDEV_Dashboard::instance();
		$status = \WPMUDEV_Dashboard::$api->get_membership_data();
		// Check if API key is available.
		if ( isset( $status['membership'] ) ) {
			$status = ( 'free' === $status['membership'] && \WPMUDEV_Dashboard::$api->has_key() )
				? 'expired'
				: $status['membership'];
		} else {
			$status = 'free';
		}

		return $status;
	}

	/**
	 * @since 2.5.5 Use Whitelabel filters instead of calling the whitelabel functions directly.
	 * @return bool
	 */
	public function is_whitelabel_enabled() {
		if ( $this->get_apikey() ) {
			// Use backward compatibility.
			if ( \WPMUDEV_Dashboard::$version > '4.11.1' ) {
				$settings = apply_filters( 'wpmudev_branding', array() );

				return ! empty( $settings );
			} else {
				$site     = \WPMUDEV_Dashboard::$site;
				$settings = $site->get_whitelabel_settings();

				return $settings['enabled'];
			}
		}

		return false;
	}

	/**
	 * Show support links if:
	 * plugin version isn't Free,
	 * Whitelabel is disabled.
	 *
	 * @return bool
	 * @since 2.5.5
	 */
	public function show_support_links() {
		if ( $this->get_apikey() ) {
			// Use backward compatibility.
			if ( \WPMUDEV_Dashboard::$version > '4.11.1' ) {
				$settings = apply_filters( 'wpmudev_branding', array() );

				return empty( $settings );
			} else {
				$site     = \WPMUDEV_Dashboard::$site;
				$settings = $site->get_whitelabel_settings();

				return ! $settings['enabled'];
			}
		}

		return false;
	}
}