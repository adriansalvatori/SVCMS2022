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

use Merkulove\Liker\Unity\Plugin;
use Merkulove\Liker\Unity\Settings;
use Merkulove\Liker\Unity\TabAssignments;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

final class FrontScripts {

	/**
	 * The one true FrontScripts.
	 *
	 * @var FrontScripts
	 * @since 1.0.0
	 **/
	private static $instance;

	private static $options;

	/**
	 * Sets up a new FrontScripts instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

		/** Shorthand for options */
		self::$options = Settings::get_instance()->options;

		/** Add plugin scripts. */
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

	}

	/**
	 * Add plugin scripts.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 **/
	public function enqueue_scripts( $ignore_assignments = false ) {

		global $post;

		if ( has_shortcode( $post->post_content, 'liker' ) || self::$options[ 'assets' ] === 'on' ) {

			/** Add styles if [liker] shortcode is on page */
			$this->enqueue_js();

		} else if ( self::$options[ 'position' ] !== 'shortcode' ) {

			/** Add styles if position is not shortcode */
			if ( ! $ignore_assignments ) {
				if ( ! TabAssignments::get_instance()->display() ) { return; }
			}

			$this->enqueue_js();

		}

	}

	private function enqueue_js() {

		wp_enqueue_script( 'mdp-liker', Plugin::get_url() . 'js/liker' . Plugin::get_suffix() . '.js', array(), Plugin::get_version(), true );
		wp_localize_script( 'mdp-liker', 'mdpLiker', $this->inline_js() );

	}

	/**
	 * Get inline Java Script
	 * @return array
	 */
	private function inline_js() {

		/** Create security nonce */
		$nonce_process_like = wp_create_nonce('process_like');
		$nonce_get_like = wp_create_nonce('get_like');

		return [
			'url' => admin_url( 'admin-ajax.php' ),
			'nonceProcessLike' => $nonce_process_like,
			'nonceGetLike' => $nonce_get_like,
			'resetTimestamp' => get_option( 'mdp_liker_reset_timestamp' ),
			'results' => esc_attr( self::$options[ 'results' ] ),
			'displayBefore' => esc_attr( self::$options[ 'display' ] )
		];

	}

	/**
	 * Main FrontScripts Instance.
	 *
	 * Insures that only one instance of FrontScripts exists in memory at any one time.
	 *
	 * @static
	 * @return FrontScripts
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

}
