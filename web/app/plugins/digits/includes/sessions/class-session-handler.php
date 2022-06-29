<?php

defined( 'ABSPATH' ) || exit;

/**
 * Session handler class.
 */
class Digits_Session_Handler {

	/**
	 * Cookie name used for the session.
	 *
	 * @var string cookie name
	 */
	protected $_cookie;

	protected $_has_cookie = false;

	public function __construct() {

	}

	public function get( $key ) {
		return ! isset( $_COOKIE[ $key ] ) ? false : $_COOKIE[ $key ];

	}

	public function set( $key, $value ) {
		if ( headers_sent() ) {
			return;
		}
		$path = '/';
        $secure = is_ssl();

        $expire = strtotime( '+3 day' );
        if (PHP_VERSION_ID < 70300) {
            setcookie($key, $value, $expire, "$path; samesite=None");
        }
        else {
            setcookie($key, $value, [
                'expires' => $expire,
                'path' => $path,
                'samesite' => 'None',
                'secure' => $secure,
            ]);
        }

	}


}
