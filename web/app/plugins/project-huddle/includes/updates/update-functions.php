<?php

function ph_activate_edd_license($license = '')
{
    // trim whitespaces
    $license = trim($license);

    // Call the custom API.
    $response = wp_remote_post(
        PH_SL_STORE_URL,
        array(
            'timeout'   => 15,
            'sslverify' => false,
            'body'      => [
                'edd_action' => 'activate_license',
                'license'    => $license,
                'item_id'    => PH_SL_ITEM_ID,
                'url'        => home_url(),
            ],
        )
    );

    // make sure the response came back okay
    if (is_wp_error($response)) {
        $message = (is_wp_error($response) && !empty($response->get_error_message())) ? $response->get_error_message() : __('An error occurred, please contact support.', 'project-huddle');
    } else {
        // decode the license data
        $license_data = json_decode(wp_remote_retrieve_body($response));

        if (false === $license_data->success) {
            switch ($license_data->error) {
                case 'expired':
                    $message = sprintf(
                        __('Your license key expired on %s. Please renew your license for updates.', 'project-huddle'),
                        date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
                    );
                    break;
                case 'revoked':
                    $message = __('Your license key has been disabled. Please contact support@projecthuddle.io for more information.', 'project-huddle');
                    break;
                case 'missing':
                    $message = __('Invalid license. Please double-check to make sure you\'ve entered it correctly!', 'project-huddle');
                    break;
                case 'invalid':
                case 'site_inactive':
                    $message = __('Your license is not active for this URL. Please update the active site on projecthuddle.com.', 'project-huddle');
                    break;
                case 'item_name_mismatch':
                    $message = __('This appears to be an invalid license key.', 'project-huddle');
                    break;
                case 'no_activations_left':
                    $message = __('Your license key has reached its activation limit. You can upgrade your account on projecthuddle.com to enable more activations.', 'project-huddle');
                    break;
                default:
                    $message = __('An error occurred, please contact support.', 'project-huddle');
                    break;
            }
        }
    }

    if ($message) {
        return new WP_Error($license_data->error, $message);
    }

    // store all data
    update_option('ph_license_data', $license_data);

    // save license
    update_option('ph_license_key', $license);

    // $license_data->license will be either "active" or "inactive"
    update_option('ph_license_status', $license_data->license);

    // update key
    update_option('ph_license_key', $license);

    // license activated
    do_action('ph_license_activated', $license_data, $license);

    return true;
}
