<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class PH_Setup
{
    protected function validate_request()
    {
        check_ajax_referer('ph_setup_nonce');

        if (!current_user_can('manage_ph_settings')) {
            wp_send_json_error();
        }
    }
}
