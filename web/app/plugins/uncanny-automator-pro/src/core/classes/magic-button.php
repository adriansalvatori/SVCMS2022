<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Magic_Button
 *
 * @package Uncanny_Automator_Pro
 */
class Magic_Button {

	/**
	 * Constructor.
	 */

	public function __construct() {
		add_shortcode( 'automator_button', [ __CLASS__, 'automator_button' ] );
		add_shortcode( 'automator_link', [ __CLASS__, 'automator_link' ] );
		add_action( 'init', [ __CLASS__, 'automator_action' ] );
		add_action( 'init', [ __CLASS__, 'automator_link_action' ] );
	}

	/**
	 * Return the HTML template that is displayed by the shortcode
	 *
	 * @param array $atts The attributes passed in the the shortcode
	 * @param string $content The content contained by the shortcode
	 *
	 * @return string  The HTML template loaded
	 * @since 2.0
	 *
	 */
	public static function automator_button( $atts, $content = null ) {
		$atts = shortcode_atts( [
			'id'    => 0,
			/* translators: Button. Default label of the magic button. Non-personal infinitive verb */
			'label' => __( 'Click here', 'uncanny-automator-pro' ),
		],
			$atts,
			'automator_button' );

		if ( empty( $atts['id'] ) || 0 === $atts['id'] ) {
			return;
		}

//		commented user_id condition for anonymous trigger
//		$user_id = get_current_user_id();
//		if ( 0 === $user_id ) {
//			return;
//		}

		global $post;
		if ( ! empty( $post ) && isset( $post->ID ) && isset( $post->post_title ) ) {
			$button_vars = '<input type="hidden" name="automator_button_post_id" value="' . $post->ID . '" />';
			$button_vars .= '<input type="hidden" name="automator_button_post_title" value="' . $post->post_title . '" />';
		} else {
			$button_vars = '';
		}

		$button_form = '<form method="post" class="automator_button_form" id="automator_button_form_' . $atts['id'] . '">';
		$button_form .= '<input type="hidden" name="automator_trigger_id" value="' . $atts['id'] . '" />';
		$button_form .= $button_vars;
		$button_form .= '<input type="hidden" name="automator_nonce" value="' . wp_create_nonce( AUTOMATOR_PRO_ITEM_NAME ) . '"/>';
		$button_form .= '<button type="submit" class="automator_button">' . $atts['label'] . '</button>';
		$button_form .= '</form>';

		return $button_form;
	}

	/**
	 *
	 */
	public static function automator_action() {
		if ( isset( $_POST['automator_nonce'] ) && wp_verify_nonce( $_POST['automator_nonce'], AUTOMATOR_PRO_ITEM_NAME ) ) {

//			commented user_id condition for anonymous trigger
			$user_id = get_current_user_id();
//			if ( 0 === $user_id ) {
//				return;
//			}

			$automator_trigger_id = absint( $_POST['automator_trigger_id'] );

			do_action( 'automator_magic_button_action', $automator_trigger_id, $user_id );
		}
	}

	/**
	 * Return the HTML template that is displayed by the shortcode
	 *
	 * @param array $atts The attributes passed in the the shortcode
	 * @param string $content The content contained by the shortcode
	 *
	 * @return string  The HTML template loaded
	 * @since 2.6
	 *
	 */
	public static function automator_link( $atts, $content = null ) {
		$atts = shortcode_atts( [
			'id'   => 0,
			/* translators: Link. Default label of the magic link. Non-personal infinitive verb */
			'text' => __( 'Click here', 'uncanny-automator-pro' ),
		],
			$atts,
			'automator_button' );

		if ( empty( $atts['id'] ) || 0 === $atts['id'] ) {
			return;
		}

//		commented user_id condition for anonymous trigger
//		$user_id = get_current_user_id();
//		if ( 0 === $user_id ) {
//			return;
//		}

		$query_args = [];
		global $post;
		if ( ! empty( $post ) && isset( $post->ID ) && isset( $post->post_title ) ) {
			$query_args['automator_button_post_id'] = $post->ID;
		}
		$query_args['automator_trigger_id'] = $atts['id'];
		$query_args['automator_nonce']      = wp_create_nonce( AUTOMATOR_PRO_ITEM_NAME );
		$link                               = add_query_arg( $query_args );

		$link_html = '<a class="automator_link" href="' . $link . '">' . $atts['text'] . '</a>';

		return $link_html;
	}

	/**
	 *
	 */
	public static function automator_link_action() {
		if ( isset( $_GET['automator_nonce'] ) && wp_verify_nonce( $_GET['automator_nonce'], AUTOMATOR_PRO_ITEM_NAME ) ) {

			$user_id = get_current_user_id();
//			commented user_id condition for anonymous trigger
//			if ( 0 === $user_id ) {
//				return;
//			}

			$automator_trigger_id = absint( $_GET['automator_trigger_id'] );

			do_action( 'automator_magic_button_action', $automator_trigger_id, $user_id );
			$refresh = remove_query_arg( [ 'automator_trigger_id', 'automator_nonce', 'automator_button_post_id' ] );

			wp_safe_redirect( $refresh );
			exit();
		}
	}
}
