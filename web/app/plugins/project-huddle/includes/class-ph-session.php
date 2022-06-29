<?php
/**
 * ProjectHuddle Session
 *
 * This is a wrapper class for WP_Session / PHP $_SESSION and handles the storage of access tokens,
 * comment location redirects, etc
 *
 * @package     ProjectHuddle
 * @subpackage  Classes/Session
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// use cookies for WPEngine or Flywheel, native for everything else
if ( defined( 'WPE_APIKEY' ) ) {
	if ( ! defined( 'PH_SESSION_TYPE' ) ) {
		define( 'PH_SESSION_TYPE', 'cookie' );
	}
	require_once PH_PLUGIN_DIR . 'includes/class-ph-session-cookies.php';
} else {
	if ( ! defined( 'PH_SESSION_TYPE' ) ) {
		define( 'PH_SESSION_TYPE', 'native' );
	}
	require_once PH_PLUGIN_DIR . 'includes/class-ph-session-native.php';
}
