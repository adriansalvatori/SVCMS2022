<?php
/**
 * Liker
 * Liker helps you rate and like articles on a website and keep track of results.
 * Exclusively on https://1.envato.market/liker
 *
 * @encoding        UTF-8
 * @version         2.2.3
 * @copyright       (C) 2018 - 2022 Merkulove ( https://merkulov.design/ ). All rights reserved.
 * @license         Envato License https://1.envato.market/KYbje
 * @contributors    Nemirovskiy Vitaliy (nemirovskiyvitaliy@gmail.com), Dmitry Merkulov (dmitry@merkulov.design)
 * @support         help@merkulov.design
 **/

namespace Merkulove\Liker;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * SINGLETON: UninstallHelper class run when the users deletes the plugin.
 *
 * @since 1.0.0
 *
 **/
final class UninstallHelper {

	/**
	 * The one true UninstallHelper.
	 *
     * @since 1.0.0
     * @access private
	 * @var UninstallHelper
	 **/
	private static $instance;

    /**
     * Implement plugin uninstallation.
     *
     * @param string $uninstall_mode - Uninstall mode: plugin, plugin+settings, plugin+settings+data
     *
     * @since  1.0.0
     * @access public
     *
     * @return void
     **/
    public function uninstall( $uninstall_mode ) {

        /** Remove Plugin. */
        if ( 'plugin' === $uninstall_mode ) {

        /** Remove Plugin and Settings. */
        } elseif ( 'plugin+settings' === $uninstall_mode ) {

        	$this->remove_settings();

        /** Remove Plugin, Settings and all created data. */
        } elseif ( 'plugin+settings+data' === $uninstall_mode ) {

	        $this->remove_settings();
	        $this->remove_mySQL_table();

	        /** Remove all liker meta-data */
	        PostMeta::get_instance()->remove_liker_meta();

        }

    }

	/**
	 * Delete Plugin Options.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function remove_settings() {

		$settings = [
			'mdp_liker_status_settings',
			'widget_mdp_liker_widget'
		];

		foreach ( $settings as $key ) {

			if ( is_multisite() ) { // For Multisite.
				if ( get_site_option( $key ) ) {
					delete_site_option( $key );
				}
			} else if ( get_option( $key ) ) {
				delete_option( $key );
			}

		}

	}

	/**
	 * Remove Plugin Voting Results Table.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return mixed
	 */
	public function remove_mySQL_table() {

		global $wpdb;

		/** Remove liker table from DB. */
		/** @noinspection SqlDialectInspection */
		/** @noinspection SqlNoDataSourceInspection */
		$sql = 'DROP TABLE ' . $wpdb->prefix . 'liker';

		return $wpdb->query(
			$wpdb->prepare( $sql )
		);

	}

	/**
	 * Main UninstallHelper Instance.
	 * Insures that only one instance of UninstallHelper exists in memory at any one time.
	 *
	 * @static
     * @since 1.0.0
     * @access public
     *
	 * @return UninstallHelper
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

}
