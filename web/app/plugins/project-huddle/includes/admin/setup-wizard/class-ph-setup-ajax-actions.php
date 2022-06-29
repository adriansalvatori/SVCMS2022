<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

include_once 'abstract-class-ph-setup.php';
include_once 'class-ph-hosting-check.php';
include_once 'class-ph-plugin-check.php';
include_once 'class-ph-wordpress-settings-check.php';
include_once 'class-ph-jetpack-install.php';

class PH_Setup_Ajax_Actions extends PH_Setup
{
    protected $jetpack;

    public function __construct()
    {
        add_action('wp_ajax_ph_validate_license', [$this, 'validate_license']);
        add_action('wp_ajax_ph_error_reporting', [$this, 'error_reporting']);
        add_action('wp_ajax_ph_server_check', [$this, 'server_check']);
        add_action('wp_ajax_ph_plugin_check', [$this, 'plugin_check']);
        add_action('wp_ajax_ph_wordpress_check', [$this, 'wordpress_check']);
        add_action('wp_ajax_ph_set_branding', [$this, 'set_branding']);
        add_action('wp_ajax_ph_email_options', [$this, 'set_email_options']);
        add_action('wp_ajax_ph_enable_jetpack', [$this, 'enable_jetpack']);
        add_action('wp_ajax_ph_setup_complete', [$this, 'complete']);
    }

    public function validate_license()
    {
        $this->validate_request();

        if (isset($_POST['license'])) {
            $license = ph_activate_edd_license(wp_kses_post($_POST['license']));
            if (is_wp_error($license)) {
                wp_send_json_error($license, 500);
            }
        }

        // validate license here
        wp_send_json_success();
    }

    public function error_reporting()
    {
        $this->validate_request();

        if (isset($_POST['enabled'])) {
            $enabled = (bool) $_POST['enabled'];
            update_option('ph_error_reporting', $enabled);
        }

        wp_send_json_success();
    }

    public function server_check()
    {
        $this->validate_request();

        $hosting = new PH_Hosting_Check();
        $messages = $hosting->check();
        wp_send_json_success(
            $messages
        );
    }

    public function plugin_check()
    {
        $this->validate_request();

        $plugins = new PH_Plugin_Check();
        $messages = $plugins->check();
        wp_send_json_success(
            $messages
        );
    }

    public function wordpress_check()
    {
        $this->validate_request();

        $settings = new PH_WordPress_Settings_check();
        $messages = $settings->check();
        wp_send_json_success(
            $messages
        );
    }

    public function set_branding()
    {
        $this->validate_request();

        // dark logo
        if (isset($_POST['dark_logo'])) {
            $id = (int) $_POST['dark_logo'];
            update_option('ph_control_logo', $id);
        }
        if (isset($_POST['dark_logo_retina'])) {
            $val = filter_var($_POST['dark_logo_retina'], FILTER_VALIDATE_BOOLEAN);
            update_option('ph_control_logo_retina', $val);
        }

        // light logo
        if (isset($_POST['light_logo'])) {
            $id = (int) $_POST['light_logo'];
            update_option('ph_login_logo', $id);
        }
        if (isset($_POST['light_logo_retina'])) {
            $val = filter_var($_POST['light_logo_retina'], FILTER_VALIDATE_BOOLEAN);
            update_option('ph_login_logo_retina', $val);
        }

        // highlight color
        if (isset($_POST['color'])) {
            $color = sanitize_hex_color($_POST['color']);
            update_option('ph_highlight_color', $color);
        }

        wp_send_json_success();
    }

    public function set_email_options()
    {
        $this->validate_request();

        if (isset($_POST['sender_name'])) {
            $val = sanitize_text_field($_POST['sender_name']);
            update_option('ph_email_from_name', $val);
        }
        if (isset($_POST['sender_email'])) {
            $val = sanitize_email($_POST['sender_email']);
            update_option('ph_email_from_address', $val);
        }
        if (isset($_POST['delivery'])) {
            $val = sanitize_text_field($_POST['delivery']);
            $val = intval($val) ? intval($val) : $val; // convert to int or string
            update_option('ph_email_throttle', $val);
        }
        if (isset($_POST['daily'])) {
            $val = filter_var($_POST['daily'], FILTER_VALIDATE_BOOLEAN);
            update_option('ph_daily_email', $val);
        }
        if (isset($_POST['weekly'])) {
            $val = filter_var($_POST['weekly'], FILTER_VALIDATE_BOOLEAN);
            update_option('ph_weekly_email', $val);
        }

        wp_send_json_success();
    }

    public function enable_jetpack()
    {
        $this->validate_request();

        $jetpack = new PH_Jetpack_Install();
        $installed = $jetpack->install();
        $jetpack->enable_monitoring();
        wp_send_json_success($installed);
    }

    public function complete()
    {
        $this->validate_request();
        update_option('ph_setup_completed', true);
        wp_send_json_success();
    }
}
