<?php

namespace Objectiv\Plugins\Checkout\Action;

/**
 * Class LogInAction
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout\Action
 * @author Brandon Tassone <brandontassone@gmail.com>
 */
class LogInAction extends CFWAction {

	/**
	 * LogInAction constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct( 'login' );
	}

	/**
	 * Logs in the user based on the information passed. If information is incorrect it returns an error message
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function action() {
		$info                  = array();
		$info['user_login']    = $_POST['email'] ?? '';
		$info['user_password'] = $_POST['password'] ?? '';
		$info['remember']      = true;

		$user        = wp_signon( $info, is_ssl() );
		$alt_message = __( 'There was an error logging in. Please check your credentials and try again.', 'checkout-wc' );

		$validation_error = new \WP_Error();

		/**
		 * Filters validation error (empty by default)
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Error The error object
		 * @param string $user_login User login
		 * @param string $user_password User password
		 */
		$validation_error = apply_filters( 'woocommerce_process_login_errors', $validation_error, $info['user_login'], $info['user_password'] );

		$out = array();

		if ( is_wp_error( $user ) ) {
			$out['logged_in'] = false;

			/**
			 * Filters failed login error message
			 *
			 * @since 3.0.0
			 *
			 * @param string $error The error message
			 */
			$out['message'] = apply_filters( 'cfw_failed_login_error_message', ( $user->get_error_message() ) ? $user->get_error_message() : $alt_message );
		} elseif ( $validation_error->get_error_code() ) {
			$out['logged_in'] = false;

			/**
			 * Filters failed login error message
			 *
			 * @since 3.0.0
			 *
			 * @param string $error The error message
			 */
			$out['message'] = apply_filters( 'cfw_failed_login_error_message', $validation_error->get_error_message() );
		} else {
			$out['logged_in'] = true;
			$out['message']   = 'Login successful';
		}

		$this->out( $out );
	}
}
