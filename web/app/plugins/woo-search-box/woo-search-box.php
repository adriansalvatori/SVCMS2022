<?php
/*
Plugin Name:       WooCommerce Search Engine
Plugin URI:        https://guaven.com/woo-search-box
Description:       Ultimate WordPress plugin which turns a simple search box of your WooCommerce Store to the smart, powerful and multifunctional magic box that helps you to sell more products.
Version:           2.2.11
WC requires at least: 4.0
WC tested up to: 5.5
Author:            Guaven Labs
Author URI:        https://guaven.com
Text Domain:       guaven_woo_search
Domain Path:       /languages
*/

if (!defined('ABSPATH')) {
    die;
}

define('GUAVEN_WOO_SEARCH_SCRIPT_VERSION',2.21100);
define('GUAVEN_WOO_SEARCH_PLUGIN_PATH', plugin_dir_path(__FILE__));

require_once GUAVEN_WOO_SEARCH_PLUGIN_PATH . 'admin/class-admin-settings.php';
require_once GUAVEN_WOO_SEARCH_PLUGIN_PATH . 'admin/class-search-analytics.php';
require_once GUAVEN_WOO_SEARCH_PLUGIN_PATH . 'public/class-front.php';
require_once GUAVEN_WOO_SEARCH_PLUGIN_PATH . 'public/class-backend.php';
require_once GUAVEN_WOO_SEARCH_PLUGIN_PATH . 'public/class-restapi.php';
require_once GUAVEN_WOO_SEARCH_PLUGIN_PATH . 'admin/updater.php';
require_once GUAVEN_WOO_SEARCH_PLUGIN_PATH . 'guaven_woos_init.php';

$guaven_woos_run=new Guaven_Woos_Init();