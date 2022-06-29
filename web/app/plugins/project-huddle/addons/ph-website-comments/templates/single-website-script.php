<?php

/**
 * The template creating the website comment iframe
 *
 * @package     ProjectHuddle
 * @subpackage  Website Comments
 * @copyright   Copyright (c) 2016, Andre Gagnon
 * @since       1.0
 */

use PH\Controllers\WebsiteScriptController;

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

// simlulate ajax to prevent other plugins from outputting html here
define('DOING_AJAX', true);

// make sure we're not php outputting errors on this page, since it's js
@ini_set('log_errors', 'On');
@ini_set('display_errors', 'Off');
@ini_set('error_reporting', E_ALL);

// dynamic javascript output
header('Content-type: text/javascript');
// CORS request header
header("Access-Control-Allow-Origin: " . esc_url_raw(get_post_meta(get_the_ID(), 'ph_website_url', true)));

$username  = isset($_GET['ph_user_name']) ? $_GET['ph_user_name'] : '';
$email     = isset($_GET['ph_user_email']) ? $_GET['ph_user_email'] : '';
$signature = isset($_GET['ph_signature']) ? $_GET['ph_signature'] : '';
$token     = isset($_GET['ph_access_token']) ? $_GET['ph_access_token'] : '';

$script = new WebsiteScriptController(get_the_ID(), $username, $email, $signature, $token);
$script = $script->load();
if (is_wp_error($script)) {
	echo "console.log('{$script->get_error_message()}');";
	exit;
}
echo $script;
exit;
