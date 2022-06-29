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

use Merkulove\Liker\Unity\Plugin;

final class AdminScripts {

	/**
	 * The one true AdminScripts.
	 *
	 * @var AdminScripts
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Sets up a new AdminScripts instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

		/** Add plugin scripts. */
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );

	}

	/**
	 * Add plugin admin scripts.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 **/
	public function admin_scripts() {

        /** Add styles only on setting page */
        $screen = get_current_screen();
        if ( null === $screen ) { return; }

        /** Add styles only on plugin settings page */
        if ( ! in_array( $screen->base, Plugin::get_menu_bases(), true ) ) { return; }

		wp_enqueue_script( 'mdp-du-dialog', Plugin::get_url() . 'js/du_dialog' . Plugin::get_suffix() . '.js', [], Plugin::get_version(), true );

        wp_localize_script('mdp-liker-admin', 'mdpLikerReset', [
            'ajaxURL' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('reset_liker')
        ] );

	}

	/**
	 * Main AdminScripts Instance.
	 *
	 * Insures that only one instance of AdminScripts exists in memory at any one time.
	 *
	 * @static
	 * @return AdminScripts
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

} // End Class AdminScripts.
