<?php


namespace Uncanny_Automator_Pro;

use WP_Error;
use WP_User;

/**
 * Class WP_Auto_Login_Link
 * @package Uncanny_Automator_Pro
 */
class WP_Auto_Login_Link {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WP';

	/**
	 * @var string[]
	 */
	public $error = array(
		'code'    => 'hash_error',
		'message' => 'The link used is invalid.',
	);

	/**
	 * This token is set up in the advanced token drop which is hardcoded in uncanny-automator/src/recipe-ui/src/components/Utilities.js
	 * It is only initialized if Automator Pro is active.
	 * The parser is in uncanny-automator/src/integrations/wp/tokens/wp-auto-login-token.php @see case 'AUTOLOGINLINK':
	 * This class only the auto login process once the user uses the link
	 */
	public function __construct() {

		add_action( 'login_init', array( $this, 'login_page_init' ) );
		add_filter( 'automator_localized_strings', array( $this, 'uap_localized_strings_func' ), 99 );
		add_filter( 'automator_pre_defined_tokens', array( $this, 'add_auto_login' ), 99 );
		add_filter( 'automator_maybe_parse_AUTOLOGINLINK', array( $this, 'generate_hash_auto_login' ), 99, 4 );
	}

	/**
	 * @param $strings
	 *
	 * @return mixed
	 */
	public function uap_localized_strings_func( $strings ) {

		// UncannyAutomator.i18n.tokens.global.advanced
		/* translators: Token name */
		$strings['tokens']['global']['autoLoginLink'] = _x( 'Automatic login link', 'Token', 'uncanny-automator-pro' );

		return $strings;
	}

	/**
	 * @param $tokens
	 *
	 * @return mixed
	 */
	public function add_auto_login( $tokens ) {
		$tokens[] = 'AUTOLOGINLINK';

		return $tokens;
	}

	/**
	 * @param $replaceable
	 * @param $field_text
	 * @param $match
	 * @param $user_id
	 */
	public function generate_hash_auto_login( $replaceable, $field_text, $match, $user_id ) {

		if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {
			return esc_attr__( 'For security reasons, automatic login links cannot be generated for Administrator or Editor users.', 'uncanny-automator-pro' );
		}

		$current_user    = get_user_by( 'ID', $user_id );
		$unix_day        = 24 * 60 * 60;
		$days_expired_in = apply_filters( 'AUTOLOGINLINK_expires_in', 7, $current_user ); //phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase

		$hash = $this->generate_magic_hash();
		update_user_meta( $current_user->ID, $hash, time() + $unix_day * $days_expired_in );

		$auto_login_url = add_query_arg( 'ua_login', $hash, wp_login_url() );
		$replaceable    = $auto_login_url;

		return $replaceable;
	}

	/**
	 * @param bool $length
	 * @param string $separator
	 *
	 * @return string
	 */
	private function generate_magic_hash( $length = false, $separator = '-' ) {
		if ( ! is_array( $length ) || is_array( $length ) && empty( $length ) ) {
			$length = array( 8, 4, 8, 8, 4, 8 );
		}
		$hash = '';
		foreach ( $length as $key => $string_length ) {
			if ( $key > 0 ) {
				$hash .= $separator;
			}
			$hash .= $this->s4generator( $string_length );
		}

		return $hash;
	}

	/**
	 * @param $length
	 *
	 * @return string
	 */
	private function s4generator( $length ) {
		$token         = '';
		$code_alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$max           = strlen( $code_alphabet );
		for ( $i = 0; $i < $length; $i ++ ) {
			$token .= $code_alphabet[ $this->crypto_rand_secure( 0, $max - 1 ) ];
		}

		return $token;
	}

	/**
	 * @param $min
	 * @param $max
	 *
	 * @return int
	 */
	private function crypto_rand_secure( $min, $max ) {
		$range = $max - $min;
		if ( $range < 1 ) {
			return $min;
		}
		$log    = ceil( log( $range, 2 ) );
		$bytes  = (int) ( $log / 8 ) + 1;
		$bits   = (int) $log + 1;
		$filter = (int) ( 1 << $bits ) - 1;
		do {
			$rnd = hexdec( bin2hex( openssl_random_pseudo_bytes( $bytes ) ) );
			$rnd = $rnd & $filter;
		} while ( $rnd > $range );

		return $min + $rnd;
	}

	/**
	 * @return void|WP_Error
	 */
	public function login_page_init() {

		if ( ! isset( $_GET['ua_login'] ) ) {
			return;
		}

		$hash = (string) $_GET['ua_login'];

		global $wpdb;

		$results = $wpdb->get_row(
			$wpdb->prepare( "SELECT user_id, meta_value AS expiry FROM $wpdb->usermeta WHERE meta_key = %s", $hash )
		);

		if ( empty( $results ) ) {
			$this->error['code']    = 'hash_not_found';
			$this->error['message'] = esc_attr__( 'The auto login link is incorrect.', 'uncanny-automator-pro' );

			add_filter(
				'wp_login_errors',
				function ( $errors, $redirect_to ) {
					return new WP_Error( $this->error['code'], $this->error['message'] );
				},
				20,
				2
			);
		}

		if ( time() > absint( $results->expiry ) ) {
			$this->error['code']    = 'hash_expired';
			$this->error['message'] = esc_attr__( 'The auto login link has expired.', 'uncanny-automator-pro' );
			delete_user_meta( absint( $results->user_id ), $hash );

			add_filter(
				'wp_login_errors',
				function ( $errors, $redirect_to ) {
					return new WP_Error( $this->error['code'], $this->error['message'] );
				},
				20,
				2
			);
		}

		$user = get_user_by( 'ID', $results->user_id );

		if ( ! $user instanceof WP_User ) {
			$this->error['code']    = 'user_not_found';
			$this->error['message'] = esc_attr__( 'The user you tried to login as does not exist.', 'uncanny-automator-pro' );
			delete_user_meta( absint( $results->user_id ), $hash );

			add_filter(
				'wp_login_errors',
				function ( $errors, $redirect_to ) {
					return new WP_Error( $this->error['code'], $this->error['message'] );
				},
				20,
				2
			);
		}

		wp_clear_auth_cookie();
		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID );
		delete_user_meta( absint( $user->ID ), $hash );
		do_action( 'uap_auto_login_link_success' );
		if ( isset( $_GET['redirect_to'] ) ) {
			$url = $_GET['redirect_to'];
		} else {
			$url = admin_url( 'profile.php' );
		}
		wp_safe_redirect( apply_filters( 'uap_auto_login_link_success_redirect', $url, $user ) );
		exit();
	}
}
