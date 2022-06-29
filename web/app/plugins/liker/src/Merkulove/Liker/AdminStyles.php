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
use Merkulove\Liker\Unity\Settings;

/**
 * SINGLETON: Class adds admin styles.
 *
 * @since 1.0.0
 *
 **/
final class AdminStyles {

	/**
	 * The one true AdminStyles.
	 *
	 * @var AdminStyles
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Sets up a new AdminStyles instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

		/** Add plugin styles. */
        add_action( 'admin_enqueue_scripts', [$this, 'admin_styles'] );

	}

	/**
	 * Add plugin styles.
	 *
	 * @since 1.0.0
	 * @return void
	 **/
	public function admin_styles() {

        /** Add styles only on setting page */
        $screen = get_current_screen();
        if ( null === $screen ) { return; }

        /** Add styles only on setting page */
        if ( $screen->base === 'toplevel_page_mdp_liker_settings' ) {

            wp_enqueue_style( 'mdp-du-dialog', Plugin::get_url() . 'css/duDialog' . Plugin::get_suffix() . '.css', [], Plugin::get_version() );

        }

        /** Pages and Posts list page. */
        if ( in_array( $screen->id, $this->edit_cpt() ) ) {

            wp_enqueue_style( 'mdp-liker-admin-edit', Plugin::get_url() . 'css/admin-edit' . Plugin::get_suffix() . '.css', [], Plugin::get_version() );

        }

	}

    /**
     * Return array to add style to custom post types.
     *
     * @since   1.1.0
     * @return array
     **/
    private function edit_cpt() {

        $edit_cpt = [];

        foreach ( Settings::get_instance()->options['cpt_support'] as $cpt ) {
            $edit_cpt[] = "edit-{$cpt}";
        }

        return $edit_cpt;

    }

	/**
	 * Main AdminStyles Instance.
	 *
	 * Insures that only one instance of AdminStyles exists in memory at any one time.
	 *
	 * @static
	 * @return AdminStyles
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

} // End Class AdminStyles.
