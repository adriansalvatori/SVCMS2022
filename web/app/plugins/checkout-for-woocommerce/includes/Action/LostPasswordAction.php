<?php

namespace Objectiv\Plugins\Checkout\Action;

/**
 * Class LostPasswordAction
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout\Action
 * @author Brandon Tassone <brandontassone@gmail.com>
 */
class LostPasswordAction extends CFWAction {

	/**
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct( 'cfw_lost_password' );
	}

	/**
	 * Logs in the user based on the information passed. If information is incorrect it returns an error message
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function action() {
		parse_str( $_POST['fields'], $post_data );

		$nonce_value = wc_get_var( $post_data['woocommerce-lost-password-nonce'], wc_get_var( $post_data['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.
		$error       = array(
			'result'  => false,
			'message' => 'An error occurred. Please contact site administrator.',
		);

		if ( ! wp_verify_nonce( $nonce_value, 'lost_password' ) ) {
			$this->out( $error );
		}

		$_POST['user_login'] = $post_data['user_login'];
		$success             = \WC_Shortcode_My_Account::retrieve_password();

		if ( ! $success ) {
			$all_notices = WC()->session->get( 'wc_notices', array() );

			$notice_type = 'error';
			$notices     = array();

			if ( wc_notice_count( $notice_type ) > 0 && isset( $all_notices[ $notice_type ] ) ) {
				// In WooCommerce 3.9+, messages can be an array with two properties:
				// - notice
				// - data
				foreach ( $all_notices[ $notice_type ] as $index => $notice ) {
					$notices[] = $notice['notice'] ?? $notice;
					unset( $all_notices[ $notice_type ][ $index ] );
				}
			}

			WC()->session->set( 'wc_notices', $all_notices );

			$this->out(
				array(
					'result'  => false,
					'message' => join( ' ', $notices ),
				)
			);
		}

		$this->out(
			array(
				'result'  => true,
				'message' => esc_html( apply_filters( 'woocommerce_lost_password_confirmation_message', cfw_esc_html__( 'A password reset email has been sent to the email address on file for your account, but may take several minutes to show up in your inbox. Please wait at least 10 minutes before attempting another reset.', 'woocommerce' ) ) ),
			)
		);
	}
}
