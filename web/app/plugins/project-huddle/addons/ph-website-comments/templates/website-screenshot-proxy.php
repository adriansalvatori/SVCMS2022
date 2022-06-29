<?php
/**
 * The for building cross origin website screenshots
 *
 * @package     ProjectHuddle
 * @subpackage  Website Comments
 * @copyright   Copyright (c) 2016, Andre Gagnon
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// simlulate ajax to prevent other plugins from outputting html here
define( 'DOING_AJAX', true );

// dynamic javascript output
header( 'Access-Control-Max-Age:' . 5 * 60 * 1000 );
header( 'Access-Control-Allow-Origin: *' );
header( 'Access-Control-Request-Method: *' );
header( 'Access-Control-Allow-Methods: OPTIONS, GET' );
header( 'Access-Control-Allow-Headers: *' );
header( 'Content-Type: application/javascript' );

// Url params
$url      = isset( $_GET['url'] ) ? $_GET['url'] : '';
$post     = isset( $_GET['post'] ) ? $_GET['post'] : '';
$nonce    = isset( $_GET['nonce'] ) ? $_GET['nonce'] : '';
$callback = isset( $_GET['callback'] ) ? $_GET['callback'] : '';

// if ( ! wp_verify_nonce( $nonce, 'wp_rest1' ) ) {
	die( 'Security check' );
// }

// Retrieve file details
$file_details = ph_get_screenshot_url_details( $url, $post, 1, $callback );

if ( ! in_array( $file_details['mime_type'], array( 'image/jpg', 'image/jpeg', 'image/png' ) ) ) {
	print 'error:Application error';
} else {
	$re_encoded_image = sprintf(
		'data:%s;base64,%s',
		$file_details['mime_type'],
		base64_encode( $file_details['data'] )
	);
	print "{$callback}(" . json_encode( $re_encoded_image ) . ')';
}

function ph_get_screenshot_url_details( $url, $post, $attempt = 1, $callback = '' ) {
	$pathinfo     = pathinfo( $url );
	$max_attempts = 10;
	$ch           = curl_init( $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	curl_setopt( $ch, CURLOPT_NOBODY, 0 );

	// if site requires username/password
	if ( $post ) {
		if ( $username = get_post_meta( 'website_username' ) && $password = get_post_meta( 'website_password' ) ) {
			curl_setopt( $ch, CURLOPT_PROXYUSERPWD, $username + ':' + $password ); // set username and password to access
		}
	}

	$data      = curl_exec( $ch );
	$error     = curl_error( $ch );
	$mime_type = curl_getinfo( $ch, CURLINFO_CONTENT_TYPE );

	if ( ! in_array( $mime_type, array( 'image/jpg', 'image/jpeg', 'image/png' ) ) && $max_attempts != $attempt ) {
		return ph_get_screenshot_url_details( $url, $post, $attempt++, $callback );
	}

	return array(
		'pathinfo'  => $pathinfo,
		'error'     => $error,
		'data'      => $data,
		'mime_type' => $mime_type,
	);
}
