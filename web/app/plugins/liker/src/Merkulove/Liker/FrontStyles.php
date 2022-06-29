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

final class FrontStyles {

	/**
	 * The one true FrontStyles.
	 *
	 * @var FrontStyles
	 * @since 1.0.0
	 **/
	private static $instance;

	private static $options;

	/**
	 * Sets up a new FrontStyles instance
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

		self::$options = Settings::get_instance()->options;

		/** Add plugin styles */
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );

	}

	/**
	 * Add plugin styles.
	 *
	 * @since 1.0.0
	 * @return void
	 **/
	public function enqueue_styles( $ignore_assignments = false ) {

		global $post;
		$post_content = isset( $post->post_content ) ? $post->post_content : '';

		if ( has_shortcode( $post_content, 'liker' ) || self::$options[ 'assets' ] === 'on' ) {

			/** Add styles if [liker] shortcode is on page */
			$this->enqueue_css();

		} else if ( self::$options[ 'position' ] !== 'shortcode' ) {

			/** Add styles if position is not shortcode */
			if ( ! $ignore_assignments ) {
				if ( ! TabAssignments::get_instance()->display() ) { return; }
			}

			$this->enqueue_css();

		}

	}

	/**
	 * Enqueue CSS
	 *
	 * @return void
	 */
	private function enqueue_css() {

		wp_enqueue_style( 'mdp-liker', Plugin::get_url() . 'css/liker' . Plugin::get_suffix() . '.css', [], Plugin::get_version() );
		wp_add_inline_style( 'mdp-liker', $this->inline_css() );

	}

	/**
	 * Add inline CSS
	 * @return string
	 */
	private function inline_css() {

		return $this->inline_positions() . $this->inline_shortcode() . self::$options[ 'custom_css' ];

	}

	/**
	 * Inline css variables for positions
	 * @return string
	 */
	private function inline_positions()
	{

		if ( self::$options[ 'style' ] === 'style-unset' ) { return ''; }

		return "
		.mdp-liker-box {
			--mdp-liker-text-color: " . self::$options[ 'text_color' ] . ";
			--mdp-liker-text-color-active: " . self::$options[ 'text_color_active' ] . ";
			--mdp-liker-text-color-hover: " . self::$options[ 'text_color_hover' ] . ";
			--mdp-liker-text-size: " . self::$options[ 'size' ] . "px;
			--mdp-liker-padding: " . self::$options[ 'padding-vertical' ] . "px " . self::$options[ 'padding-horizontal' ] . "px;
			--mdp-liker-bg-color: " . self::$options[ 'bg_color' ] . ";
			--mdp-liker-bg-color-active: " . self::$options[ 'bg_color_active' ] . ";
			--mdp-liker-bg-color-hover: " . self::$options[ 'bg_color_hover' ] . ";
			--mdp-liker-radius: " . self::$options[ 'radius' ] . "px;
			--mdp-liker-border: " . self::$options[ 'border' ] . "px;
		}";

	}

	/**
	 * Inline CSS variables for TOP shortcode
	 * @return string
	 */
	private function inline_shortcode()
	{

		return "
		.mdp-liker-top {
            --mdp-liker-size: " . self::$options[ 'top_size' ] . "px;
            --mdp-liker-gutter: " . self::$options[ 'top_gutter' ] . "px;
        }";

	}

	/**
	 * Main FrontStyles Instance
	 *
	 * Insures that only one instance of FrontStyles exists in memory at any one time.
	 *
	 * @static
	 * @return FrontStyles
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

}
