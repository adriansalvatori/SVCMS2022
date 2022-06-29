<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once dirname(__FILE__) . '/api_settings.php';
require_once dirname(__FILE__) . '/custom_fields.php';
require_once dirname(__FILE__) . '/shortcodes.php';
require_once dirname(__FILE__) . '/users.php';

add_action('wp_ajax_digits_save_settings', 'digits_save_settings');
function digits_save_settings()
{
    digits_update_data(false);
    wp_die();
}

/**
 * update data.
 */
function digits_update_data($gs)
{
    if (!current_user_can('manage_options')) {

        die();
    }
    $digpc = dig_get_option('dig_purchasecode');


    do_action('digits_save_settings_data');


    $data = array(
        'digits_sameorigin_protection',
        'enable_guest_checkout_verification',
        'enable_billing_phone_verification'
    );
    foreach ($data as $key) {
        if (isset($_POST[$key])) {
            $posted_value = sanitize_text_field($_POST[$key]);
            update_option($key, $posted_value);
        }
    }
    if (isset($_POST['dig_login_rememberme'])) {
        $dig_login_rememberme = sanitize_text_field($_POST['dig_login_rememberme']);
        update_option('dig_login_rememberme', $dig_login_rememberme);
    }
    if (isset($_POST['dig_custom_field_data'])) {
        $login_fields_array = array();
        foreach (digit_default_login_fields() as $login_field => $values) {
            $login_fields_array[$login_field] = sanitize_text_field($_POST[$login_field]);
        }
        update_option('dig_login_fields', $login_fields_array);

        $reg_default_fields_array = array();
        foreach (digit_get_reg_fields() as $reg_field => $values) {

            $reg_default_fields_array[$reg_field] = sanitize_text_field($_POST[$reg_field]);
        }
        update_option('dig_reg_fields', $reg_default_fields_array);


        if (isset($_POST['dig_reg_uname'])) {
            $dig_reg_uname = $_POST['dig_reg_uname'];
            if ($dig_reg_uname == 0) {
                update_option('woocommerce_registration_generate_username', 'yes');

            } else {
                update_option('woocommerce_registration_generate_username', 'no');
            }
        }
        if (isset($_POST['dig_reg_password'])) {
            $dig_reg_password = $_POST['dig_reg_password'];
            if ($dig_reg_password == 0) {
                update_option('woocommerce_registration_generate_password', 'yes');
            } else {
                update_option('woocommerce_registration_generate_password', 'no');
            }
        }


        $dig_reg_custom_field_data = $_POST['dig_reg_custom_field_data'];
        $dig_reg_custom_field_data_decode = json_decode(stripslashes($dig_reg_custom_field_data));
        foreach ($dig_reg_custom_field_data_decode as $reg_custom_field_datum) {
            $label = $reg_custom_field_datum->label;

            do_action('wpml_register_single_string', 'digits', $label, $label);
            foreach ($reg_custom_field_datum->options as $option) {
                do_action('wpml_register_single_string', 'digits', $option, $option);
            }


        }

        $field_data = base64_encode($dig_reg_custom_field_data);


        update_option('dig_reg_custom_field_data', $field_data);

        do_action("after_dig_update_data", $_POST);

    }


    if (isset($_POST['dig_sortorder'])) {
        $dig_sortorder = sanitize_text_field($_POST['dig_sortorder']);
        if (!empty($dig_sortorder)) {
            $dig_sortorderArray = explode(",", sanitize_text_field($_POST['dig_sortorder']));
            $dig_sortorderArraySan = array();

            foreach ($dig_sortorderArray as $sort) {
                $dig_sortorderArraySan[] = "dig_cs_" . cust_dig_filter_string(str_replace("dig_cs_", "", $sort));
            }
            $dig_sortorder = implode(",", $dig_sortorderArraySan);
        }
        update_option('dig_sortorder', $dig_sortorder);
    }

    
        $purchasecode = '8699958a-77f3-4db8-9422-126b0836e1c5';

        $pcsave = true;

                delete_site_option('dig_purchasecode');
                delete_site_option('dig_license_type');
                delete_site_option('dig_hid_activate_notice');
                delete_site_option('dig_nt_time');

                $t = dig_get_option('dig_unr', -1);

                if ($t == -1) {
                    update_site_option('dig_unr', time());
                }





        if ($pcsave) {


                update_site_option('dig_purchasecode', $purchasecode);

                delete_site_option('dig_purchasefail');
                delete_site_option('dig_unr');
                delete_site_option('dig_dsb');

                update_site_option('dig_license_type', sanitize_textarea_field($_POST['dig_license_type']));

                if ($gs == 1) {
                    wp_redirect(esc_url_raw(admin_url("index.php?page=digits-setup&step=documentation")));
                    exit();
                }
            
        }


   

    if (isset($_POST['dig_save'])) {

        $digit_tapp = sanitize_text_field($_POST['digit_tapp']);

        if (get_option('digit_tapp') !== false) {
            update_option('digit_tapp', $digit_tapp);
        } else {
            add_option('digit_tapp', $digit_tapp);
        }


        global $wpdb;
        $tb = $wpdb->prefix . 'digits_mobile_otp';
        $tb2 = $wpdb->prefix . 'digits_requests_log';
        $tb3 = $wpdb->prefix . 'digits_blocked_ip';
        if ($wpdb->get_var("SHOW TABLES LIKE '$tb'") != $tb) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $tb (
		          countrycode MEDIUMINT(8) NOT NULL,
		          mobileno VARCHAR(20) NOT NULL,
		          otp VARCHAR(32) NOT NULL,
		          time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		          UNIQUE ID(mobileno)
	            ) $charset_collate;";


            $sql2 = "CREATE TABLE $tb2 (
		          ip VARCHAR(32) NOT NULL,
		          requests VARCHAR(32) NOT NULL,
		          time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		          UNIQUE ID(ip)
	            ) $charset_collate;";


            $sql3 = "CREATE TABLE $tb3 (
		          ip VARCHAR(32) NOT NULL,
		          block VARCHAR(32) NOT NULL,
		          time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		          UNIQUE ID(ip)
	            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta(array($sql, $sql2, $sql3));


        }


        if (isset($_POST['appid']) && isset($_POST['appsecret'])) {
            $appid = sanitize_text_field($_POST['appid']);
            $appsecret = sanitize_text_field($_POST['appsecret']);
            $accountkit_type = sanitize_text_field($_POST['accountkit_type']);
            $app = array(
                'appid' => $appid,
                'appsecret' => $appsecret,
                'accountkit_type' => $accountkit_type,
            );
            update_option('digit_api', $app);

            if (get_option('digit_api') !== false) {
                update_option('digit_api', $app);
            } else {
                add_option('digit_api', $app);
            }


        }


        if (isset($_POST['twiliosid'])) {
            $twiliosid = sanitize_text_field($_POST['twiliosid']);
            $twiliotoken = sanitize_text_field($_POST['twiliotoken']);
            $twiliosenderid = sanitize_text_field($_POST['twiliosenderid']);


            $tiwilioapicred = array(
                'twiliosid' => $twiliosid,
                'twiliotoken' => $twiliotoken,
                'twiliosenderid' => $twiliosenderid
            );

            if (get_option('digit_twilio_api') !== false) {
                update_option('digit_twilio_api', $tiwilioapicred);
            } else {
                add_option('digit_twilio_api', $tiwilioapicred);
            }


        }

        if (isset($_POST['msg91authkey'])) {
            $msg91authkey = sanitize_text_field($_POST['msg91authkey']);
            $msg91senderid = sanitize_text_field($_POST['msg91senderid']);
            $msg91route = sanitize_text_field($_POST['msg91route']);
            $msg91dlt_te_id = sanitize_text_field($_POST['msg91dlt_te_id']);

            $msg91apicred = array(
                'msg91authkey' => $msg91authkey,
                'msg91senderid' => $msg91senderid,
                'msg91route' => $msg91route,
                'msg91dlt_te_id' => $msg91dlt_te_id
            );
            if (get_option('digit_msg91_api') !== false) {
                update_option('digit_msg91_api', $msg91apicred);
            } else {
                add_option('digit_msg91_api', $msg91apicred);
            }

        }

        if (isset($_POST['yunpianapikey'])) {
            $yunpianapikey = sanitize_text_field($_POST['yunpianapikey']);
            update_option('digit_yunpianapi', $yunpianapikey);
        }


        digits_update_api_settings();
        if ($gs == 1) {
            wp_redirect(esc_url_raw(admin_url('index.php?page=digits-setup&step=shortcodes')));
            exit();
        }

    }


    if (isset($_POST['diglogintrans'])) {
        $diglogintrans = sanitize_text_field($_POST['diglogintrans']);
        $digregistertrans = sanitize_text_field($_POST['digregistertrans']);
        $digforgottrans = sanitize_text_field($_POST['digforgottrans']);
        $digmyaccounttrans = sanitize_text_field($_POST['digmyaccounttrans']);
        $diglogouttrans = sanitize_text_field($_POST['diglogouttrans']);

        $digonlylogintrans = sanitize_text_field($_POST['digonlylogintrans']);


        if (get_option('diglogintrans') !== false) {
            update_option('digonlylogintrans', $digonlylogintrans);

            update_option('diglogintrans', $diglogintrans);
            update_option('digregistertrans', $digregistertrans);
            update_option('digforgottrans', $digforgottrans);
            update_option('digmyaccounttrans', $digmyaccounttrans);
            update_option('diglogouttrans', $diglogouttrans);
        } else {
            add_option('digonlylogintrans', $digonlylogintrans);

            add_option('diglogintrans', $diglogintrans);
            add_option('digregistertrans', $digregistertrans);
            add_option('digforgottrans', $digforgottrans);
            add_option('digmyaccounttrans', $digmyaccounttrans);
            add_option('diglogouttrans', $diglogouttrans);
        }


    }

    if (isset($_POST['dig_otp_size']) && isset($_POST['dig_messagetemplate'])) {
        $dig_otp_size = sanitize_text_field($_POST['dig_otp_size']);
        $dig_messagetemplate = sanitize_textarea_field($_POST['dig_messagetemplate']);
        $dig_whatsapp_messagetemplate = sanitize_textarea_field($_POST['dig_whatsapp_messagetemplate']);

        if ($dig_otp_size > 3 && $dig_otp_size < 11 && !empty($dig_messagetemplate)) {
            if (get_option('dig_otp_size') !== false) {
                update_option('dig_messagetemplate', $dig_messagetemplate);
                update_option('dig_whatsapp_messagetemplate', $dig_whatsapp_messagetemplate);
                update_option('dig_otp_size', $dig_otp_size);
            } else {
                add_option('dig_messagetemplate', $dig_messagetemplate);
                add_option('dig_whatsapp_messagetemplate', $dig_whatsapp_messagetemplate);
                add_option('dig_otp_size', $dig_otp_size);
            }
        }

    }


    if (!empty($digpc)) {
        if (isset($_POST['digit_custom_css'])) {
            $css = sanitize_textarea_field($_POST['digit_custom_css']);

            update_option("digit_custom_css", $css);
        }
    }
    if (isset($_POST['digpassaccep']) && isset($_POST['digemailaccep'])) {
        $passaccep = sanitize_text_field($_POST['digpassaccep']);
        $digemailaccep = sanitize_text_field($_POST['digemailaccep']);

        if (get_option('digpassaccep') !== false) {
            update_option('digpassaccep', $passaccep);
        } else {
            add_option('digpassaccep', $passaccep);
        }

        if (get_option('digemailaccep') !== false) {
            update_option('digemailaccep', $digemailaccep);
        } else {
            add_option('digemailaccep', $digemailaccep);
        }

    }

    if (isset($_POST['dig_mobilein_uname'])) {
        $dig_mobilein_uname = sanitize_text_field($_POST['dig_mobilein_uname']);
        update_option('dig_mobilein_uname', $dig_mobilein_uname);
    }


    if (isset($_POST['dig_wp_login_inte'])) {
        $dig_wp_login_inte = sanitize_text_field($_POST['dig_wp_login_inte']);
        update_option('dig_wp_login_inte', $dig_wp_login_inte);
    }

    if (isset($_POST['dig_redirect_wc_to_dig'])) {
        $dig_redirect_wc_to_dig = sanitize_text_field($_POST['dig_redirect_wc_to_dig']);
        update_option('dig_redirect_wc_to_dig', $dig_redirect_wc_to_dig);
    }

    if (isset($_POST['dig_mobile_no_formatting'])) {
        $dig_mobile_no_formatting = sanitize_text_field($_POST['dig_mobile_no_formatting']);
        update_option('dig_mobile_no_formatting', $dig_mobile_no_formatting);
    }


    if (isset($_POST['dig_enable_forgotpass'])) {
        $digforgotpass = sanitize_text_field($_POST['dig_enable_forgotpass']);
        $dig_overwrite_forgotpass_link = sanitize_text_field($_POST['dig_overwrite_forgotpass_link']);

        if (get_option('digforgotpass') !== false) {
            update_option('digforgotpass', $digforgotpass);
            update_option('dig_overwrite_forgotpass_link', $dig_overwrite_forgotpass_link);

        } else {
            add_option('digforgotpass', $digforgotpass);
            add_option('dig_overwrite_forgotpass_link', $dig_overwrite_forgotpass_link);
        }
    }


    if (isset($_POST['dig_enable_registration'])) {
        $dig_enable_registration = sanitize_text_field($_POST['dig_enable_registration']);
        $show_asterisk = sanitize_text_field($_POST['dig_show_asterisk']);


        if (get_option('dig_enable_registration') !== false) {
            update_option('dig_enable_registration', $dig_enable_registration);
            update_option('dig_show_asterisk', $show_asterisk);
        } else {
            add_option('dig_enable_registration', $dig_enable_registration);
            add_option('dig_show_asterisk', $show_asterisk);
        }
    }

    if (isset($_POST['dig_mob_otp_resend_time'])) {
        $dig_mob_otp_resend_time = preg_replace("/[^0-9]/", "", $_POST['dig_mob_otp_resend_time']);
        if ($dig_mob_otp_resend_time > 19) {
            if (get_option('dig_mob_otp_resend_time') !== false) {
                update_option('dig_mob_otp_resend_time', $dig_mob_otp_resend_time);
            } else {
                add_option('dig_mob_otp_resend_time', $dig_mob_otp_resend_time);
            }
        }
    }
    if (isset($_POST['dig_enable_strongpass'])) {
        $dig_use_strongpass = sanitize_text_field($_POST['dig_enable_strongpass']);
        if (get_option('dig_use_strongpass') !== false) {
            update_option('dig_use_strongpass', $dig_use_strongpass);
        } else {
            add_option('dig_use_strongpass', $dig_use_strongpass);
        }
    }

    if (isset($_POST['login_reg_success_msg'])) {
        update_option('login_reg_success_msg', sanitize_text_field($_POST['login_reg_success_msg']));
    }

    if (isset($_POST['enable_autofillcustomerdetails'])) {
        $enable_autofillcustomerdetails = $_POST['enable_autofillcustomerdetails'];
        update_option('dig_autofill_wc_billing', $enable_autofillcustomerdetails);
    }


    if (isset($_POST['dig_reqfieldbilling'])) {
        $dig_reqfieldbilling = sanitize_text_field($_POST['dig_reqfieldbilling']);

        if (get_option('dig_reqfieldbilling') !== false) {
            update_option('dig_reqfieldbilling', $dig_reqfieldbilling);
        } else {
            add_option('dig_reqfieldbilling', $dig_reqfieldbilling);
        }
    }
    if (isset($_POST['enable_createcustomeronorder']) && isset($_POST['defaultuserrole'])) {

        $enable_createcustomeronorder = sanitize_text_field($_POST['enable_createcustomeronorder']);
        $defaultuserrole = sanitize_text_field($_POST['defaultuserrole']);

        if (get_option('enable_createcustomeronorder') !== false) {
            update_option('enable_createcustomeronorder', $enable_createcustomeronorder);
            update_option('defaultuserrole', $defaultuserrole);
        } else {
            add_option('enable_createcustomeronorder', $enable_createcustomeronorder);
            add_option('defaultuserrole', $defaultuserrole);
        }

        if (get_option('defaultuserrole') !== false) {
            update_option('defaultuserrole', $defaultuserrole);
        } else {
            add_option('defaultuserrole', $defaultuserrole);
        }


        if (isset($_POST['default_ccode'])) {
            $default_ccode = sanitize_text_field($_POST['default_ccode']);
            if (get_option('dig_default_ccode') !== false) {
                update_option('dig_default_ccode', $default_ccode);
            } else {
                add_option('dig_default_ccode', $default_ccode);
            }
        }
        $whitelistCountryCodes = array();
        if (isset($_POST['whitelistcountrycodes'])) {

            $whitelistCountryCodes = dig_sanitize($_POST['whitelistcountrycodes']);
            if (sizeof($whitelistCountryCodes) > 0) {
                if (get_option('whitelistcountrycodes') !== false) {
                    update_option('whitelistcountrycodes', $whitelistCountryCodes);
                } else {
                    add_option('whitelistcountrycodes', $whitelistCountryCodes);
                }
            } else {
                delete_option("whitelistcountrycodes");
            }
        } else {
            delete_option("whitelistcountrycodes");
        }

        if (isset($_POST['dig_hide_countrycode'])) {
            $dig_hide_countrycode = sanitize_text_field($_POST['dig_hide_countrycode']);
            if (sizeof($whitelistCountryCodes) != 1) {
                $dig_hide_countrycode = 0;
            }
            update_option('dig_hide_countrycode', $dig_hide_countrycode);
        }


        if (isset($_POST['blacklistcountrycodes'])) {

            $blacklistcountrycodes = dig_sanitize($_POST['blacklistcountrycodes']);
            if (sizeof($blacklistcountrycodes) > 0) {
                if (get_option('dig_blacklistcountrycodes') !== false) {
                    update_option('dig_blacklistcountrycodes', $blacklistcountrycodes);
                } else {
                    add_option('dig_blacklistcountrycodes', $blacklistcountrycodes);
                }
            } else {
                delete_option("dig_blacklistcountrycodes");
            }
        } else {
            delete_option("dig_blacklistcountrycodes");
        }

        if (isset($_POST['phonenumberdenylist'])) {

            $denylistphones = dig_sanitize($_POST['phonenumberdenylist']);
            if (sizeof($denylistphones) > 0) {
                update_option('dig_phonenumberdenylist', $denylistphones);
            } else {
                delete_option("dig_phonenumberdenylist");
            }
        }

        if ($gs == 1) {

            wp_redirect(esc_url_raw(admin_url("index.php?page=digits-setup&step=shortcodes")));

            exit();
        }

    }

    if (!empty($digpc)) {

        if (isset($_POST['lb_x']) && isset($_POST['bg_color'])) {
            $bgcolor = sanitize_text_field($_POST['bg_color']);
            $lbxbg_color = sanitize_text_field($_POST['lbxbg_color']);
            $lb_x = preg_replace("/[^0-9]/", "", $_POST['lb_x']);
            $lb_y = preg_replace("/[^0-9]/", "", $_POST['lb_y']);
            $lb_blur = preg_replace("/[^0-9]/", "", $_POST['lb_blur']);
            $lb_spread = preg_replace("/[^0-9]/", "", $_POST['lb_spread']);
            $lb_radius = preg_replace("/[^0-9]/", "", $_POST['lb_radius']);
            $lb_color = sanitize_text_field($_POST['lb_color']);
            $fontcolor1 = sanitize_text_field($_POST['fontcolor1']);
            $fontcolor2 = sanitize_text_field($_POST['fontcolor2']);
            $backcolor = sanitize_text_field($_POST['backcolor']);
            $left_color = sanitize_text_field($_POST['left_color']);

            $type = preg_replace("/[^0-9]/", "", $_POST['dig_page_type']);


            $input_bg_color = sanitize_text_field($_POST['input_bg_color']);
            $input_border_color = sanitize_text_field($_POST['input_border_color']);
            $input_text_color = sanitize_text_field($_POST['input_text_color']);
            $button_bg_color = sanitize_text_field($_POST['button_bg_color']);
            $signup_button_color = sanitize_text_field($_POST['signup_button_color']);
            $signup_button_bg_color = sanitize_text_field($_POST['signup_button_border_color']);
            $button_text_color = sanitize_text_field($_POST['button_text_color']);
            $signup_button_text_color = sanitize_text_field($_POST['signup_button_text_color']);

            $left_bg_size = sanitize_text_field($_POST['left_bg_size']);
            $left_bg_position = sanitize_text_field($_POST['left_bg_position']);


            $color = array(
                'bgcolor' => $bgcolor,
                'loginboxcolor' => $lbxbg_color,
                'sx' => $lb_x,
                'sy' => $lb_y,
                'sblur' => $lb_blur,
                'sspread' => $lb_spread,
                'sradius' => $lb_radius,
                'scolor' => $lb_color,
                'fontcolor1' => $fontcolor1,
                'fontcolor2' => $fontcolor2,
                'backcolor' => $backcolor,
                'type' => $type,
                'left_color' => $left_color,
                'input_bg_color' => $input_bg_color,
                'input_border_color' => $input_border_color,
                'input_text_color' => $input_text_color,
                'button_bg_color' => $button_bg_color,
                'signup_button_color' => $signup_button_color,
                'signup_button_border_color' => $signup_button_bg_color,
                'button_text_color' => $button_text_color,
                'signup_button_text_color' => $signup_button_text_color,
                'left_bg_size' => $left_bg_size,
                'left_bg_position' => $left_bg_position,
            );

            update_option('digit_color', $color);


            $bgcolor = sanitize_text_field($_POST['bg_color_modal']);
            $lbxbg_color = sanitize_text_field($_POST['lbxbg_color_modal']);
            $lb_x = preg_replace("/[^0-9]/", "", $_POST['lb_x_modal']);
            $lb_y = preg_replace("/[^0-9]/", "", $_POST['lb_y_modal']);
            $lb_blur = preg_replace("/[^0-9]/", "", $_POST['lb_blur_modal']);
            $lb_spread = preg_replace("/[^0-9]/", "", $_POST['lb_spread_modal']);
            $lb_radius = preg_replace("/[^0-9]/", "", $_POST['lb_radius_modal']);
            $lb_color = sanitize_text_field($_POST['lb_color_modal']);
            $fontcolor1 = sanitize_text_field($_POST['fontcolor1_modal']);
            $fontcolor2 = sanitize_text_field($_POST['fontcolor2_modal']);
            $type = preg_replace("/[^0-9]/", "", $_POST['dig_modal_type']);
            $left_color = sanitize_text_field($_POST['left_color_modal']);
            $button_text_color = sanitize_text_field($_POST['button_text_color_modal']);
            $signup_button_text_color = sanitize_text_field($_POST['signup_button_text_color_modal']);


            $input_bg_color = sanitize_text_field($_POST['input_bg_color_modal']);
            $input_border_color = sanitize_text_field($_POST['input_border_color_modal']);
            $input_text_color = sanitize_text_field($_POST['input_text_color_modal']);
            $button_bg_color = sanitize_text_field($_POST['button_bg_color_modal']);
            $signup_button_color = sanitize_text_field($_POST['signup_button_color_modal']);
            $signup_button_border_color = sanitize_text_field($_POST['signup_button_border_color_modal']);
            $left_bg_size = sanitize_text_field($_POST['left_bg_size_modal']);
            $left_bg_position = sanitize_text_field($_POST['left_bg_position_modal']);


            $color = array(
                'bgcolor' => $bgcolor,
                'loginboxcolor' => $lbxbg_color,
                'sx' => $lb_x,
                'sy' => $lb_y,
                'sblur' => $lb_blur,
                'sspread' => $lb_spread,
                'sradius' => $lb_radius,
                'scolor' => $lb_color,
                'fontcolor1' => $fontcolor1,
                'fontcolor2' => $fontcolor2,
                'type' => $type,
                'left_color' => $left_color,
                'input_bg_color' => $input_bg_color,
                'input_border_color' => $input_border_color,
                'input_text_color' => $input_text_color,
                'button_bg_color' => $button_bg_color,
                'signup_button_color' => $signup_button_color,
                'signup_button_border_color' => $signup_button_border_color,
                'button_text_color' => $button_text_color,
                'signup_button_text_color' => $signup_button_text_color,
                'left_bg_size' => $left_bg_size,
                'left_bg_position' => $left_bg_position,
            );


            update_option('digit_color_modal', $color);


            // Save attachment ID
            if (isset($_POST['image_attachment_id'])):
                update_option('digits_logo_image', sanitize_text_field($_POST['image_attachment_id']));
            endif;


            if (isset($_POST['bg_image_attachment_id_modal'])):
                update_option('digits_bg_image_modal', sanitize_text_field($_POST['bg_image_attachment_id_modal']));
            endif;


            if (isset($_POST['bg_image_attachment_id'])):
                update_option('digits_bg_image', sanitize_text_field($_POST['bg_image_attachment_id']));
            endif;


            if (isset($_POST['bg_image_attachment_id_left'])):
                update_option('digits_left_bg_image', sanitize_text_field($_POST['bg_image_attachment_id_left']));
            endif;

            if (isset($_POST['bg_image_attachment_id_left_modal'])):
                update_option('digits_left_bg_image_modal', sanitize_text_field($_POST['bg_image_attachment_id_left_modal']));
            endif;


            if (isset($_POST['dig_preset'])):
                update_option('dig_preset', absint($_POST['dig_preset']));
            endif;


            if (isset($_POST['login_page_footer'])) {
                $login_page_footer = base64_encode(str_replace("\n", "<br />", $_POST['login_page_footer']));
                update_option('login_page_footer', $login_page_footer);


                update_option('login_page_footer_text_color', sanitize_text_field($_POST['login_page_footer_text_color']));
            }
            if ($gs == 1) {

                wp_redirect(esc_url_raw(admin_url("index.php?page=digits-setup&step=shortcodes")));

                exit();
            }

        }


    }
    if (isset($_POST['digits_loginred'])) {
        $digits_loginred = sanitize_text_field($_POST['digits_loginred']);

        $digits_regred = sanitize_text_field($_POST['digits_regred']);
        $digits_forgotred = sanitize_text_field($_POST['digits_forgotred']);
        $digits_logoutred = sanitize_text_field($_POST['digits_logoutred']);

        update_option('digits_myaccount_redirect', sanitize_text_field($_POST['digits_myaccount_redirect']));
        update_option('digits_loginred', $digits_loginred);
        update_option('digits_regred', $digits_regred);
        update_option('digits_forgotred', $digits_forgotred);
        update_option('digits_logoutred', $digits_logoutred);

    }

}


function digit_addons($active_tab)
{


    $data = dig_doCurl("https://digits.unitedover.com/?get=products&type=json&data=addons&version=" . digits_version() . "&purchasecode=" . dig_get_option('dig_purchasecode'));

    if (empty($data)) {

        ?>
        <div class="dig_addons_coming_soon"><?php _e('Unexpected error occured while getting addons', 'digits'); ?></div>

        <?php
        return;
    }

    ?>
    <div class="digits-addons-container">
        <?php
        $purchased_addons = $data['purchased'];
        if (!empty($purchased_addons)) {
            echo '<div class="digits-addons-purchased">';

            echo '<div class="dig_addons_title">' . __('Available Addons', 'digits') . '</div>';

            $plugin_updates = get_plugin_updates();
            foreach ($purchased_addons as $key => $plugin) {


                ?>


                <div class="dig-addon-item dig-addon-item_purchased" data-plugin="<?php echo $plugin['plugin']; ?>">


                    <div class="dig-addon-par">

                        <table>
                            <tr>
                                <td class="dig_addon_img_act_img">
                                    <div class="dig_addon_img">
                                        <img src="<?php echo $plugin['thumbnail']; ?>" draggable="false"/>
                                    </div>
                                </td>
                                <td>
                                    <div class="dig_addon_details">
                                        <div class="dig_addon_name"><?php echo $plugin['name']; ?></div>
                                    </div>
                                </td>
                                <td class="dig_addon_int_btn">
                                    <input type="hidden" class="dig_addon_nounce"
                                           value="<?php echo wp_create_nonce('dig_addon' . $plugin['plugin']) ?>">
                                    <input type="hidden" class="dig_plugin_slug"
                                           value="<?php $basename = explode('/', $plugin['plugin']);
                                           echo $basename[0]; ?>">


                                    <?php
                                    if (is_plugin_active($plugin['plugin'])) {
                                        $function_key = str_replace('-', '_', $key);
                                        $addon_function = 'digits_addon_' . $function_key;


                                        ?>
                                        <div class="digmodifyaddon icon-group icon-group-dims" type="-1"></div>
                                        <?php
                                        if (function_exists($addon_function)) {
                                            $addon_settings = call_user_func($addon_function);
                                            ?>
                                            <div class="dig_ngmc updatetabview icon-setting icon-setting-dims <?php echo $active_tab == $addon_settings ? 'dig-nav-tab-active' : ''; ?>"
                                                 tab="<?php echo $addon_settings; ?>tab"></div>
                                            <?php
                                        }
                                        ?>
                                        <?php
                                        if (isset($plugin_updates[$plugin['plugin']])) {
                                            echo '<div class="digmodifyaddon icon-update icon-update-dims" type="10"></div>';
                                        }


                                    } else {
                                        echo '<div class="digmodifyaddon icon-upload icon-upload-dims" type="1"></div>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>


                    </div>
                </div>


                <?php


            }

            echo '</div>';
        }
        $new_addons = $data['rem'];

        if (!empty($new_addons)) {
            echo '<div class="digits-addons-new"><div class="dig_addons_title">' . __('New Addons', 'digits') . '</div>';
            foreach ($new_addons as $plugin) {

                if (is_plugin_active($plugin['plugin'])) {
                    if (!$plugin['multi_site'] || empty($plugin['multi_site']) || $plugin['multi_site'] == 0)
                        deactivate_plugins($plugin['plugin']);
                }
                ?>


                <a href="<?php echo $plugin['location']; ?>" target="_blank">

                    <div class="dig-addon-item" data-plugin="<?php echo $plugin['plugin']; ?>">
                        <div class="dig-addon-par">
                            <div class="dig_addon_img">
                                <img src="<?php echo $plugin['thumbnail']; ?>" draggable="false"/>

                                <?php
                                if (isset($plugin['allow_direct_install']) && $plugin['allow_direct_install'] == 1) {
                                    echo '<a href="#" class="digits-addons-allow_direct_install digmodifyaddon">';
                                }
                                ?>
                                <input type="hidden" class="dig_addon_nounce"
                                       value="<?php echo wp_create_nonce('dig_addon' . $plugin['plugin']) ?>">
                                <input type="hidden" class="dig_plugin_slug"
                                       value="<?php $basename = explode('/', $plugin['plugin']);
                                       echo $basename[0]; ?>">

                                <div class="dig_addon_btn_con">
                                    <div class="dig_addon_btn">
                                        <?php
                                        echo $plugin['price'];
                                        ?>
                                    </div>
                                </div>
                                <?php
                                if (isset($plugin['allow_direct_install']) && $plugin['allow_direct_install'] == 1) {
                                    echo '</a>';
                                }
                                ?>
                            </div>
                            <div class="dig_addon_details">
                                <div class="dig_addon_name"><?php echo $plugin['name']; ?></div>
                                <div class="dig_addon_sep"></div>
                                <div class="dig_addon_btm_pnl">

                                    <div class="dig_addon_dsc">
                                        <?php echo $plugin['desc']; ?>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                </a>


                <?php
            }
            echo '</div>';
        }

        ?>
    </div>
    <?php

}


function digit_activation($form = true)
{
    if ($form) {
        echo '<form class="dig_activation_form" method="post">';
        ?>
        <h1><?php _e("Activate Digits", "digits"); ?></h1>


        <?php
    }
    $code = dig_get_option('dig_purchasecode');
    $license_type = dig_get_option('dig_license_type', 1);;

    $plugin_version = digits_version();

    $list = apply_filters('digits_addon', array());
    ?>

    <input type="hidden" name="dig_addons_list" value="<?php echo implode(",", $list); ?>"/>


    <input type="hidden" name="dig_license_type" value="<?php echo dig_get_option('dig_license_type', 1); ?>"/>

    <input type="hidden" name="dig_domain" value="<?php echo dig_network_home_url(); ?>"/>

    <input type="hidden" name="dig_version" value="<?php echo $plugin_version; ?>"/>

    <table class="form-table">
        <tr class="dig_domain_type" <?php if (!empty($code)) {
            echo 'style="display:none;"';
        } ?>>
            <th scope="row"><label for="dig_purchasecode"><?php _e("Is this domain your", "digits"); ?> </label></th>
            <td>
                <button class="button" type="button" val="1"><?php _e('Live Server', 'digits'); ?></button>
                <button class="button" type="button" val="2"><?php _e('Testing Server', 'digits'); ?></button>
            </td>
        </tr>
        <tr class="dig_prchcde" <?php if (!empty($code)) {
            echo 'style="display:table-row;"';
        } ?>>
            <th scope="row"><label for="dig_purchasecode"><?php _e("Purchase code", "digits"); ?> </label></th>
            <td>
                <div class="digits_shortcode_tbs digits_shortcode_stb">
                    <input class="dig_inp_wid31" nocop="1" type="text" name="dig_purchasecode"
                           id="dig_purchasecode"
                           placeholder="<?php _e("Purchase Code", "digits"); ?>" autocomplete="off"
                           value="<?php echo $code ?>" readonly>
                    <button class="button dig_btn_unregister"
                            type="button"><?php _e('DEREGISTER', 'digits'); ?></button>
                    <img class="dig_prc_ver"
                         src="<?php echo get_digits_asset_uri('/admin/assets/images/check_animated.svg'); ?>"
                         draggable="false" <?php if (!empty($code)) {
                        echo 'style="display:block;"';
                    } ?>>
                    <img class="dig_prc_nover"
                         src="<?php echo get_digits_asset_uri('/admin/assets/images/cross_animated.svg'); ?>"
                         draggable="false">
                </div>
            </td>
        </tr>
    </table>

    <div class="dig_desc_sep_pc dig_prchcde" <?php if (!empty($code)) {
        echo 'style="display:block;"';
    } ?>></div>
    <p class="dig_ecr_desc dig_cntr_algn_clr dig_prchcde" <?php if (!empty($code)) {
        echo 'style="display:block;"';
    } ?>>
        <?php _e('Please activate your plugin to receive updates', 'digits'); ?>
    </p>


    <table class="form-table dig_prchcde" <?php if (!empty($code)) {
        echo 'style="display:table-row;"';
    } ?>>
        <tr>
            <td>
                <p class="dig_ecr_desc dig_cntr_algn dig_sme_lft_algn request_live_server_addition" <?php if ($license_type == 1) {
                    echo 'style="display:none;"';
                } ?>>
                    <?php _e('If you want to use same purchase code on your live server then please click the below button to request for it. Our team will take less than 12 hours to respond to your request, and will notify via email.', 'digits'); ?>
                </p>
                <p class="dig_ecr_desc dig_cntr_algn dig_sme_lft_algn request_testing_server_addition" <?php if ($license_type == 2) {
                    echo 'style="display:none;"';
                } ?>>
                    <?php _e('If you want to use same purchase code on your testing server then please click the below button to request for it. Our team will take less than 12 hours to respond to your request, and will notify via email.', 'digits'); ?>
                </p>
                <button href="https://help.unitedover.com/request-additional-site/"
                        class="button dig_request_server_addition request_live_server_addition"
                        type="button" <?php if ($license_type == 1) {
                    echo 'style="display:none;"';
                } ?>><?php _e('Request Live Server Addition', 'digits'); ?></button>
                <button href="https://help.unitedover.com/request-additional-site/"
                        class="button dig_request_server_addition request_testing_server_addition"
                        type="button" <?php if ($license_type == 2) {
                    echo 'style="display:none;"';
                } ?>><?php _e('Request Testing Server Addition', 'digits'); ?></button>
            </td>
        </tr>
    </table>
    <?php
    if (!$form) {
        return;
    }

    ?>


    <br/>

    <p class="digits-setup-action step">
        <Button type="submit" href="<?php echo admin_url('index.php?page=digits-setup&step=documentation'); ?>"
                class="button-primary button button-large button-next regular-text"
        ><?php _e("Activate", "digits"); ?></Button>
        <a href="<?php echo admin_url('index.php?page=digits-setup&step=documentation'); ?>"
           class="button"><?php _e("Skip", "digits"); ?></a>
    </p>
    </form>

    <?php
}


function digit_customize($isWiz = true)
{
    $color = get_option('digit_color');
    $bgcolor = "#4cc2fc";
    $fontcolor = 0;

    $loginboxcolor = "rgba(255,255,255,1)";
    $sx = 0;
    $sy = 2;
    $sspread = 0;
    $sblur = 4;
    $scolor = "rgba(0, 0, 0, 0.5)";

    $fontcolor2 = "rgba(255,255,255,1)";
    $fontcolor1 = "rgba(20,20,20,1)";

    $sradius = 4;


    $color_modal = get_option('digit_color_modal');


    $input_bg_color = "rgba(0,0,0,0)";
    $input_border_color = "rgba(0,0,0,0)";
    $input_text_color = "rgba(0,0,0,0)";
    $button_bg_color = "rgba(0,0,0,0)";
    $signup_button_color = "rgba(0,0,0,0)";
    $signup_button_border_color = "rgba(0,0,0,0)";
    $button_text_color = "rgba(0,0,0,0)";
    $signup_button_text_color = "rgba(0,0,0,0)";
    $backcolor = 'rgba(0,0,0,1)';


    $page_type = 1;
    $modal_type = 1;
    $leftcolor = "rgba(255,255,255,1)";

    $left_bg_position = 'Center Center';
    $left_bg_size = 'auto';
    if ($color !== false) {
        $bgcolor = $color['bgcolor'];


        if (isset($color['fontcolor'])) {
            $fontcolor = $color['fontcolor'];
            if ($fontcolor == 1) {
                $fontcolor1 = "rgba(20,20,20,1)";
                $fontcolor2 = "rgba(255,255,255,1)";
            }
        }
        if (isset($color['sx'])) {
            $sx = $color['sx'];
            $sy = $color['sy'];
            $sspread = $color['sspread'];
            $sblur = $color['sblur'];
            $scolor = $color['scolor'];
            $fontcolor1 = $color['fontcolor1'];
            $fontcolor2 = $color['fontcolor2'];
            $loginboxcolor = $color['loginboxcolor'];
            $sradius = $color['sradius'];
            $backcolor = $color['backcolor'];
        }
        if (isset($color['type'])) {
            $page_type = $color['type'];
            if ($page_type == 2) {
                $leftcolor = $color['left_color'];
            }
            $modal_type = $color_modal['type'];


            $input_bg_color = $color['input_bg_color'];
            $input_border_color = $color['input_border_color'];
            $input_text_color = $color['input_text_color'];
            $button_bg_color = $color['button_bg_color'];
            $signup_button_color = $color['signup_button_color'];
            $signup_button_border_color = $color['signup_button_border_color'];
            $button_text_color = $color['button_text_color'];
            $signup_button_text_color = $color['signup_button_text_color'];
            $left_bg_position = $color['left_bg_position'];
            $left_bg_size = $color['left_bg_size'];

        }


    }
    if ($isWiz) {
        echo '<form method="post" enctype="multipart/form-data">';
    }

    $positions_bg = array(
        'Left Top',
        'Left Center',
        'Left Bottom',
        'Center Top',
        'Center Center',
        'Center Bottom',
        'Right Top',
        'Right Center',
        'Right Bottom'
    );
    $size_bg = array('auto', 'cover', 'contain');


    $preset = get_option('dig_preset', 1);

    ?>

    <div class="dig_admin_head"><span><?php _e('Preset Design', 'digits'); ?></span></div>

    <div class="dig_presets_modal" id="dig_presets_box">
        <div id="dig_presets_modal_box">
            <div id="dig_presets_modal_head">
                <div id="dig_presets_modal_head_title"><?php _e('PRESET LIBRARY', 'digits'); ?></div>
                <div id="dig_presets_modal_head_close"
                     class="dig_presets_modal_head_close"><?php _e('CLOSE', 'digits'); ?></div>
            </div>

            <?php
            $presets_array = array(
                '0' => array('name' => __('CUSTOM', 'digits')),
                '1' => array('name' => 'CLAVIUS'),
                '2' => array('name' => 'APOLLO'),
                '3' => array('name' => 'ARISTARCHUS'),
                '4' => array('name' => 'SHACKLETON'),
                '5' => array('name' => 'ALPHONSUS'),
                '6' => array('name' => 'THEOPHILUS'),
            );
            ?>
            <input type="radio" id="dig_preset_custom" class="dig_preset" name="dig_preset" style="display: none;"
                   value="0" data-lab="<?php _e('CUSTOM', 'digits'); ?>" <?php if ($preset == 0) {
                echo 'checked';
            } ?> />


            <div id="dig_presets_modal_body">
                <div id="dig_presets_list">

                    <?php
                    foreach ($presets_array as $key => $preset_v) {
                        if ($key == 0) {
                            continue;
                        }
                        ?>
                        <div class="dig_preset_item">
                            <label for="preset<?php echo $key; ?>">
                                <div class="dig_preset_item_list">
                                    <input class="dig_preset" name="dig_preset" id="preset<?php echo $key; ?>"
                                           value="<?php echo $key; ?>" type="radio" <?php if ($key == $preset) {
                                        echo 'checked';
                                    } ?>>
                                    <div class="dig_preset_sel">
                                        <div class="dig_tick_center">
                                            <img class="dig_preset_sel_tick"
                                                 src="<?php echo get_digits_asset_uri('/admin/assets/images/preset-tick.svg'); ?>"
                                                 draggable="false"/>
                                        </div>
                                    </div>
                                    <div class="dig_preset_img_smp">
                                        <img src="<?php echo get_digits_asset_uri('/admin/assets/images/preset' . $key . '.jpg'); ?>"
                                             draggable="false"/>

                                        <a class="dig_preset_big_img"
                                           href="<?php echo get_digits_asset_uri('/admin/assets/images/preset' . $key . '.jpg'); ?>">
                                        </a>
                                    </div>
                                    <div class="dig_preset_name"><?php echo $preset_v['name']; ?></div>
                                </div>
                            </label>
                        </div>
                        <?php
                    }
                    ?>

                </div>
            </div>
        </div>
    </div>

    <table class="form-table">
        <tr>
            <th scope="row"><label for="dig_preset"><?php _e('Preset', 'digits'); ?> </label></th>
            <td class="dig_prst_btns">
                <input class="dig_prst_name" type="text" readonly
                       value="<?php if (array_key_exists($preset, $presets_array)) {
                           echo $presets_array[$preset]['name'];
                       } ?>">
                <Button id="dig_open_preset_box" type="button"
                        class="button"><?php _e('Select Preset', 'digits'); ?></Button>

                <input type="hidden"
                       value='{"dig_modal_type" : "1", "dig_page_type":"1","bg_image_attachment_id_modal":"","bg_image_attachment_id" :"","backcolor": "#fff","bg_color": "#2ac5fc","lbxbg_color": "#fff","lb_x": "0","lb_y": "2","lb_blur": "4","lb_spread": "0","lb_radius": "4","lb_color": "rgba(0, 0, 0, 0.5)","fontcolor2": "rgba(255,255,255,1)","fontcolor1": "rgba(20,20,20,1)","bg_color_modal": "rgba(6, 6, 6, 0.8)","lbxbg_color_modal": "#fff","lb_x_modal": "0","lb_y_modal": "0","lb_blur_modal": "20","lb_spread_modal": "0","lb_radius_modal": "0","lb_color_modal": "rgba(0, 0, 0, 0.3)","fontcolor1_modal": "rgba(20,20,20,1)","fontcolor2_modal": "rgba(255,255,255,1)"}'
                       id="dig_preset1"/>
                <input type="hidden"
                       value='{"dig_modal_type" : "1", "dig_page_type":"1","bg_image_attachment_id_modal":"","bg_image_attachment_id" :"","backcolor": "#fff","bg_color": "#050210","lbxbg_color": "rgba(0,0,0,0)","lb_x": "0","lb_y": "0","lb_blur": "0","lb_spread": "0","lb_radius": "0","lb_color": "rgba(0, 0, 0, 0)","fontcolor2": "rgba(20,20,20,1)","fontcolor1": "rgba(255,255,255,1)","bg_color_modal": "rgba(6, 6, 6, 0.8)","lbxbg_color_modal": "#050210","lb_x_modal": "0","lb_y_modal": "0","lb_blur_modal": "20","lb_spread_modal": "0","lb_radius_modal": "0","lb_color_modal": "rgba(0, 0, 0, 0.3)","fontcolor1_modal": "rgba(255,255,255,1)","fontcolor2_modal": "rgba(20,20,20,1)"}'
                       id="dig_preset2"/>
                <input type="hidden"
                       value='{"dig_modal_type" : "1", "dig_page_type":"1","bg_image_attachment_id_modal":"","bg_image_attachment_id":"<?php echo get_digits_asset_uri('/assets/images/bg.jpg'); ?>", "backcolor": "#fff","bg_color": "rgba(0,0,0,0)","lbxbg_color": "rgba(17,17,17,0.87)","lb_x": "0","lb_y": "2","lb_blur": "4","lb_spread": "0","lb_radius": "4","lb_color": "rgba(0, 0, 0, 0.5)","fontcolor2": "rgba(51,51,51,1)","fontcolor1": "rgba(255,255,255,1)","bg_color_modal": "rgba(6, 6, 6, 0.8)","lbxbg_color_modal": "#111","lb_x_modal": "0","lb_y_modal": "0","lb_blur_modal": "20","lb_spread_modal": "0","lb_radius_modal": "4","lb_color_modal": "rgba(0, 0, 0, 0.3)","fontcolor1_modal": "rgba(255,255,255,1)","fontcolor2_modal": "rgba(51,51,51,1)"}'
                       id="dig_preset3"/>
                <input type="hidden"
                       value='{"dig_modal_type" : "1", "dig_page_type":"1","bg_image_attachment_id_modal":"","bg_image_attachment_id" :"","backcolor": "#fff","bg_color": "#0d0d0d","lbxbg_color": "#fff","lb_x": "0","lb_y": "2","lb_blur": "4","lb_spread": "0","lb_radius": "0","lb_color": "rgba(0, 0, 0, 0.5)","fontcolor2": "rgba(255,255,255,1)","fontcolor1": "rgba(20,20,20,1)","bg_color_modal": "rgba(6, 6, 6, 0.8)","lbxbg_color_modal": "#fff","lb_x_modal": "0","lb_y_modal": "0","lb_blur_modal": "20","lb_spread_modal": "0","lb_radius_modal": "0","lb_color_modal": "rgba(0, 0, 0, 0.3)","fontcolor1_modal": "rgba(20,20,20,1)","fontcolor2_modal": "rgba(255,255,255,1)"}'
                       id="dig_preset4"/>

                <input type="hidden"
                       value='{"dig_modal_type" : "1", "dig_page_type":"1","bg_image_attachment_id_modal":"","bg_image_attachment_id" :"","backcolor": "#0d0d0d","bg_color": "#fff","lbxbg_color": "#fff","lb_x": "0","lb_y": "2","lb_blur": "4","lb_spread": "0","lb_radius": "0","lb_color": "rgba(0, 0, 0, 0.5)","fontcolor2": "rgba(255,255,255,1)","fontcolor1": "rgba(20,20,20,1)","bg_color_modal": "rgba(6, 6, 6, 0.8)","lbxbg_color_modal": "#fff","lb_x_modal": "0","lb_y_modal": "0","lb_blur_modal": "20","lb_spread_modal": "0","lb_radius_modal": "0","lb_color_modal": "rgba(0, 0, 0, 0.3)","fontcolor1_modal": "rgba(20,20,20,1)","fontcolor2_modal": "rgba(255,255,255,1)"}'
                       id="dig_preset5"/>

                <input type="hidden"
                       value='{"dig_modal_type" : "2", "dig_page_type":"2","bg_image_attachment_id_modal":"","bg_image_attachment_id" :"","bg_image_attachment_id_left":"<?php echo get_digits_asset_uri('/assets/images/cart.png'); ?>","bg_image_attachment_id_left_modal":"<?php echo get_digits_asset_uri('/assets/images/cart.png'); ?>", "backcolor": "rgba(0, 0, 0, 0.75)","bg_color": "rgba(237, 230, 234, 1)","lbxbg_color": "rgba(255, 255, 255, 1)","fontcolor1": "rgba(109, 109, 109, 1)","lb_x": "0","lb_y": "3","lb_blur": "6","lb_spread": "0","lb_radius": "4","lb_color": "rgba(0, 0, 0, 0.16)","bg_color_modal": "rgba(6, 6, 6, 0.8)","lbxbg_color_modal": "rgba(250, 250, 250, 1)","lb_x_modal": "0","lb_y_modal": "0","lb_blur_modal": "20","lb_spread_modal": "0","lb_radius_modal": "4","lb_color_modal": "rgba(0, 0, 0, 0.3)","fontcolor1_modal": "rgba(109, 109, 109, 1)","fontcolor2_modal": "rgba(51,51,51,1)","left_color":"rgba(165, 62, 96, 1)","left_color_modal":"rgba(165, 62, 96, 1)","input_bg_color":"rgba(255, 255, 255, 1)","input_border_color":"rgba(153, 153, 153, 1)","input_text_color":"rgba(0, 0, 0, 1)","button_bg_color":"rgba(255, 188, 0, 1)","signup_button_color":"rgba(242, 242, 242, 1)","signup_button_border_color":"rgba(214, 214, 214, 1)","button_text_color":"rgba(255, 255, 255, 1)","signup_button_text_color":"rgba(109, 109, 109, 1)","input_bg_color_modal":"rgba(255, 255, 255, 1)","input_border_color_modal":"rgba(153, 153, 153, 1)","input_text_color_modal":"rgba(0, 0, 0, 1)","button_bg_color_modal":"rgba(255, 188, 0, 1)","signup_button_color_modal":"rgba(242, 242, 242, 1)","signup_button_border_color_modal":"rgba(214, 214, 214, 1)","button_text_color_modal":"rgba(255, 255, 255, 1)","signup_button_text_color_modal":"rgba(109, 109, 109, 1)"}'
                       id="dig_preset6"/>
            </td>
        </tr>
    </table>


    <div class="dig_admin_head dig_prst_clse_scrl"><span><?php _e('Form Type', 'digits'); ?></span></div>
    <table class="form-table dig_image_checkbox">
        <tr>
            <th scope="row"><label><?php _e('Page', 'digits'); ?> </label></th>
            <td>
                <div class="digits-form-type dig_trans">

                    <label class="dig_type_item" for="dig_page_type1">
                        <div class="dig_style_types_gs">
                            <input value="1" name="dig_page_type" id="dig_page_type1" class="dig_type"
                                   type="radio" <?php if ($page_type == 1) {
                                echo 'checked';
                            } ?> />
                            <div class="dig_preset_sel">
                                <div class="dig_tick_center">
                                    <img class="dig_preset_sel_tick"
                                         src="<?php echo get_digits_asset_uri('/admin/assets/images/preset-tick.svg'); ?>"
                                         draggable="false"/>
                                </div>
                            </div>
                            <div class="dig-page-type1 dig-type-dims"></div>
                        </div>
                    </label>
                    <label class="dig_type_item" for="dig_page_type2">
                        <div class="dig_style_types_gs">
                            <input value="2" name="dig_page_type" id="dig_page_type2" class="dig_type"
                                   type="radio" <?php if ($page_type == 2) {
                                echo 'checked';
                            } ?> />
                            <div class="dig_preset_sel">
                                <div class="dig_tick_center">
                                    <img class="dig_preset_sel_tick"
                                         src="<?php echo get_digits_asset_uri('/admin/assets/images/preset-tick.svg'); ?>"
                                         draggable="false"/>
                                </div>
                            </div>
                            <div class="dig-page-type2 dig-type-dims"></div>
                        </div>
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row"><label><?php _e('Modal', 'digits'); ?> </label></th>
            <td>
                <div class="digits-form-type">
                    <label class="dig_type_item" for="dig_modal_type1">
                        <div class="dig_style_types_gs">
                            <input value="1" name="dig_modal_type" id="dig_modal_type1" class="dig_type"
                                   type="radio" <?php if ($modal_type == 1) {
                                echo 'checked';
                            } ?> />
                            <div class="dig_preset_sel">
                                <div class="dig_tick_center">
                                    <img class="dig_preset_sel_tick"
                                         src="<?php echo get_digits_asset_uri('/admin/assets/images/preset-tick.svg'); ?>"
                                         draggable="false"/>
                                </div>
                            </div>
                            <div class="dig-modal-type1 dig-type-dims"></div>
                        </div>
                    </label>

                    <label class="dig_type_item" for="dig_modal_type2">
                        <div class="dig_style_types_gs">
                            <input value="2" name="dig_modal_type" id="dig_modal_type2" class="dig_type"
                                   type="radio" <?php if ($modal_type == 2) {
                                echo 'checked';
                            } ?> />
                            <div class="dig_preset_sel">
                                <div class="dig_tick_center">
                                    <img class="dig_preset_sel_tick"
                                         src="<?php echo get_digits_asset_uri('/admin/assets/images/preset-tick.svg'); ?>"
                                         draggable="false"/>
                                </div>
                            </div>
                            <div class="dig-modal-type2 dig-type-dims"></div>
                        </div>
                    </label>
                </div>
            </td>
        </tr>
    </table>


    <div class="dig_admin_head"><span><?php _e('Page', 'digits'); ?></span></div>


    <table class="form-table">
        <tr>
            <th scope="row"><label><?php _e('Logo', 'digits'); ?> </label></th>
            <td>

                <?php
                $imgid = get_option('digits_logo_image');
                $remstyle = "";
                if (empty($imgid)) {
                    $imagechoose = __("Select image", 'digits');
                    $remstyle = 'style="display:none;"';
                } else {
                    $imagechoose = __("Remove Image", 'digits');
                }


                $wid = "";
                if (is_numeric($imgid)) {
                    $wid = wp_get_attachment_url($imgid);
                }
                ?>
                <div class='image-preview-wrapper'>
                    <img id='image-preview' src='<?php if (is_numeric($imgid)) {
                        echo $wid;
                    } else {
                        echo $imgid;
                    } ?>'
                         style="max-height:100px;max-width:250px;">
                </div>

                <input type="text" name="image_attachment_id" id='image_attachment_id'
                       value='<?php if (is_numeric($imgid)) {
                           if ($wid) {
                               echo $wid;
                           }
                       } else {
                           echo $imgid;
                       } ?>' placeholder="<?php _e("URL", "digits"); ?>" class="dig_url_img"/>
                <Button id="upload_image_button" type="button" class="button dig_img_chn_btn dig_imsr"
                ><?php echo $imagechoose; ?></Button>


            </td>
        </tr>

        <tr class="dig_page_type_2">
            <th scope="row"><label for="bgcolor"><?php _e('Login Page Left Background Color', 'digits'); ?> </label>
            </th>
            <td>
                <input name="left_color" type="text" class="bg_color" value="<?php echo $leftcolor; ?>"
                       autocomplete="off"
                       required data-alpha="true">

            </td>
        </tr>

        <tr class="dig_page_type_2">
            <th scope="row"><label><?php _e('Login Page Left Image', 'digits'); ?> </label></th>
            <td>

                <?php
                $imgid = get_option('digits_left_bg_image');
                $remstyle = "";
                if (empty($imgid)) {
                    $imagechoose = __("Select image", 'digits');
                    $remstyle = 'style="display:none;"';
                } else {
                    $imagechoose = __("Remove Image", 'digits');
                }
                $wid = "";
                if (is_numeric($imgid)) {
                    $wid = wp_get_attachment_url($imgid);
                }
                ?>
                <div class='image-preview-wrapper'>
                    <img id='bg_image-preview_left' src='<?php if (is_numeric($imgid)) {
                        echo $wid;
                    } else {
                        echo $imgid;
                    } ?>'
                         style="max-height:100px;">
                </div>

                <input type="text" name="bg_image_attachment_id_left" id='bg_image_attachment_id_left'
                       value='<?php if (is_numeric($imgid)) {
                           if ($wid) {
                               echo $wid;
                           }
                       } else {
                           echo $imgid;
                       } ?>' placeholder="<?php _e("URL", "digits"); ?>" class="dig_url_img"/>

                <Button id="bg_upload_image_button_left" type="button" class="button dig_img_chn_btn dig_imsr"
                ><?php echo $imagechoose; ?></Button>


            </td>
        </tr>


        <tr class="dig_page_type_2">
            <th scope="row"><label><?php _e('Login Page Left Background Size', 'digits'); ?></label></th>
            <td>
                <select name="left_bg_size">
                    <?php
                    foreach ($size_bg as $size) {
                        $sel = '';
                        if ($left_bg_size == $size) {
                            $sel = 'selected';
                        }
                        echo '<option value="' . $size . '" ' . $sel . '>' . $size . '</option>';

                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr class="dig_page_type_2">
            <th scope="row"><label><?php _e('Login Page Left Background Position', 'digits'); ?></label></th>
            <td>
                <select name="left_bg_position">
                    <?php
                    foreach ($positions_bg as $position) {
                        $sel = '';
                        if ($left_bg_position == $position) {
                            $sel = 'selected';
                        }
                        echo '<option value="' . $position . '" ' . $sel . '>' . $position . '</option>';

                    }
                    ?>
                </select>
            </td>
        </tr>


        <tr class="dig_page_type_2">
            <th scope="row"><label for="login_page_footer"><?php _e('Login Page Footer', 'digits'); ?> </label></th>
            <td>
            <textarea name="login_page_footer" id="login_page_footer" type="text" rows="3"><?php
                $footer = trim(get_option('login_page_footer'));
                if (!empty($footer)) {
                    echo stripslashes(str_replace("<br />", "\n", base64_decode($footer)));
                }
                ?></textarea>
            </td>
        </tr>

        <tr class="dig_page_type_2">
            <th scope="row"><label for="bgcolor"><?php _e('Login Page Footer Text Color', 'digits'); ?> </label></th>
            <td>
                <input name="login_page_footer_text_color" type="text" class="bg_color"
                       value="<?php echo get_option('login_page_footer_text_color', 'rgba(255,255,255,1)'); ?>"
                       autocomplete="off"
                       required data-alpha="true">
            </td>
        </tr>


        <tr>
            <th scope="row"><label for="bgcolor"><?php _e('Login Page Background Color', 'digits'); ?> </label></th>
            <td>
                <input name="bg_color" type="text" class="bg_color" value="<?php echo $bgcolor; ?>" autocomplete="off"
                       required data-alpha="true">

            </td>
        </tr>


        <tr>
            <th scope="row"><label><?php _e('Login Page Background Image', 'digits'); ?> </label></th>
            <td>

                <?php
                $imgid = get_option('digits_bg_image');
                $remstyle = "";
                if (empty($imgid)) {
                    $imagechoose = __("Select image", 'digits');
                    $remstyle = 'style="display:none;"';
                } else {
                    $imagechoose = __("Remove Image", 'digits');
                }
                $wid = "";
                if (is_numeric($imgid)) {
                    $wid = wp_get_attachment_url($imgid);
                }
                ?>
                <div class='image-preview-wrapper'>
                    <img id='bg_image-preview' src='<?php if (is_numeric($imgid)) {
                        echo $wid;
                    } else {
                        echo $imgid;
                    } ?>'
                         style="max-height:100px;">
                </div>

                <input type="text" name="bg_image_attachment_id" id='bg_image_attachment_id'
                       value='<?php if (is_numeric($imgid)) {
                           if ($wid) {
                               echo $wid;
                           }
                       } else {
                           echo $imgid;
                       } ?>' placeholder="<?php _e("URL", "digits"); ?>" class="dig_url_img"/>

                <Button id="bg_upload_image_button" type="button" class="button dig_img_chn_btn dig_imsr"
                ><?php echo $imagechoose; ?></Button>


            </td>
        </tr>


        <tr>
            <th scope="row"><label for="lbxbgcolor"><?php _e('Login Box Background Color', 'digits'); ?> </label></th>
            <td>
                <input name="lbxbg_color" type="text" class="bg_color" value="<?php echo $loginboxcolor; ?>"
                       autocomplete="off"
                       required data-alpha="true">

            </td>
        </tr>
        <tr>
            <th scope="row"><label for="lb_x"><?php _e('Login Box Shadow', 'digits'); ?> </label></th>
            <td>

                <table class="digotlbr">
                    <tr class="dignochkbxra">
                        <td><input id="lb_x" name="lb_x" type="number" value="<?php echo $sx; ?>" autocomplete="off"
                                   required maxlength="2">
                            <div class="digno-tr_dt"><label for="lb_x"><?php _e('X', 'digits'); ?></label></div>
                        </td>
                        <td><input id="lb_y" name="lb_y" type="number" value="<?php echo $sy; ?>" autocomplete="off"
                                   required maxlength="2">
                            <div class="digno-tr_dt"><label for="lb_y"><?php _e('Y', 'digits'); ?></label></div>
                        </td>
                        <td><input id="lb_blur" name="lb_blur" type="number" value="<?php echo $sblur; ?>"
                                   autocomplete="off" required maxlength="2">
                            <div class="digno-tr_dt"><label for="lb_blur"><?php _e('Blur', 'digits'); ?></label></div>
                        </td>
                        <td><input id="lb_spread" name="lb_spread" type="number" value="<?php echo $sspread; ?>"
                                   autocomplete="off" required maxlength="2">
                            <div class="digno-tr_dt"><label for="lb_spread"><?php _e('Spread', 'digits'); ?></label>
                            </div>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>


        <tr>
            <th scope="row"><label for="lb_color"><?php _e('Login Box Shadow Color', 'digits'); ?> </label></th>
            <td>
                <input name="lb_color" class="bg_color" type="text" value="<?php echo $scolor; ?>" autocomplete="off"
                       required data-alpha="true">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bgcolor"><?php _e('Login Box Radius', 'digits'); ?> </label></th>
            <td>
                <div class="dig_gs_nmb_ovr_spn">
                    <input class="dignochkbx" name="lb_radius" type="number" value="<?php echo $sradius; ?>"
                           autocomplete="off" required maxlength="2" dig-min="42" placeholder="0">
                    <span style="left:42px;">px</span>
                </div>

            </td>
        </tr>


        <tr class="dig_page_type_1_2">
            <th scope="row"><label data-type1="<?php _e('Text and Button Color', 'digits'); ?>"
                                   data-type2="<?php _e('Text Color', 'digits'); ?>">Color</label></th>
            <td>
                <input type="text" name="fontcolor1" class="bg_color" value="<?php echo $fontcolor1; ?>"
                       data-alpha="true"/>
            </td>
        </tr>


        <tr class="dig_page_type_1">
            <th scope="row"><label><?php _e('Button Font Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="fontcolor2" class="bg_color" value="<?php echo $fontcolor2; ?>"
                       data-alpha="true"/>
            </td>
        </tr>
        <tr>
            <th scope="row"><label><?php _e('Back/Cancel Button Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="backcolor" class="bg_color" value="<?php echo $backcolor; ?>"
                       data-alpha="true"/>
            </td>
        </tr>


        <tr class="dig_page_type_2">
            <th scope="row"><label><?php _e('Input Background Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="input_bg_color" class="bg_color" value="<?php echo $input_bg_color; ?>"
                       data-alpha="true"/>
            </td>
        </tr>
        <tr class="dig_page_type_2">
            <th scope="row"><label><?php _e('Input Border Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="input_border_color" class="bg_color" value="<?php echo $input_border_color; ?>"
                       data-alpha="true"/>
            </td>
        </tr>
        <tr class="dig_page_type_2">
            <th scope="row"><label><?php _e('Input Text Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="input_text_color" class="bg_color" value="<?php echo $input_text_color; ?>"
                       data-alpha="true"/>
            </td>
        </tr>
        <tr class="dig_page_type_2">
            <th scope="row"><label><?php _e('Button Border Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="button_bg_color" class="bg_color" value="<?php echo $button_bg_color; ?>"
                       data-alpha="true"/>
            </td>
        </tr>

        <tr class="dig_page_type_2">
            <th scope="row"><label><?php _e('Button Text Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="button_text_color" class="bg_color" value="<?php echo $button_text_color; ?>"
                       data-alpha="true"/>
            </td>
        </tr>

        <tr class="dig_page_type_2">
            <th scope="row"><label><?php _e('Signup Button Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="signup_button_color" class="bg_color"
                       value="<?php echo $signup_button_color; ?>"
                       data-alpha="true"/>
            </td>
        </tr>
        <tr class="dig_page_type_2">
            <th scope="row"><label><?php _e('Signup Button Border Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="signup_button_border_color" class="bg_color"
                       value="<?php echo $signup_button_border_color; ?>"
                       data-alpha="true"/>
            </td>
        </tr>

        <tr class="dig_page_type_2">
            <th scope="row"><label><?php _e('Signup Button Text Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="signup_button_text_color" class="bg_color"
                       value="<?php echo $signup_button_text_color; ?>"
                       data-alpha="true"/>
            </td>
        </tr>


    </table>


    <?php
    $color = $color_modal;
    $bgcolor = "rgba(6, 6, 6, 0.8)";
    $fontcolor = 0;

    $loginboxcolor = "rgba(255,255,255,1)";
    $sx = 0;
    $sy = 0;
    $sspread = 0;
    $sblur = 20;
    $scolor = "rgba(0, 0, 0, 0.3)";

    $fontcolor1 = "rgba(20,20,20,1)";
    $fontcolor2 = "rgba(255,255,255,1)";


    $input_bg_color = "rgba(0,0,0,0)";
    $input_border_color = "rgba(0,0,0,0)";
    $input_text_color = "rgba(0,0,0,0)";
    $button_bg_color = "rgba(0,0,0,0)";
    $signup_button_color = "rgba(0,0,0,0)";
    $signup_button_border_color = "rgba(0,0,0,0)";
    $button_text_color = "rgba(0,0,0,0)";
    $signup_button_text_color = "rgba(0,0,0,0)";


    $left_bg_position = 'Center Center';
    $left_bg_size = 'auto';

    $leftcolor = 'rgba(0,0,0,1)';
    $sradius = 0;
    if ($color !== false) {
        $bgcolor = $color['bgcolor'];


        $col = get_option('digit_color');
        if (isset($col['fontcolor'])) {
            $fontcolor = $col['fontcolor'];
            if ($fontcolor == 1) {
                $fontcolor1 = "rgba(255,255,255,1)";
                $fontcolor2 = "rgba(20,20,20,1)";
            }
        }

        if (isset($color['sx'])) {
            $sx = $color['sx'];
            $sy = $color['sy'];
            $sspread = $color['sspread'];
            $sblur = $color['sblur'];
            $scolor = $color['scolor'];
            $fontcolor1 = $color['fontcolor1'];
            $fontcolor2 = $color['fontcolor2'];
            $loginboxcolor = $color['loginboxcolor'];
            $sradius = $color['sradius'];

            if (isset($color['type'])) {
                $page_type = $color['type'];
                if ($page_type == 2) {
                    $leftcolor = $color['left_color'];
                }
                $modal_type = $color_modal['type'];


                $input_bg_color = $color['input_bg_color'];
                $input_border_color = $color['input_border_color'];
                $input_text_color = $color['input_text_color'];
                $button_bg_color = $color['button_bg_color'];
                $signup_button_color = $color['signup_button_color'];
                $signup_button_border_color = $color['signup_button_border_color'];
                $button_text_color = $color['button_text_color'];
                $signup_button_text_color = $color['signup_button_text_color'];
                $left_bg_position = $color['left_bg_position'];
                $left_bg_size = $color['left_bg_size'];

            }

        }

    }
    ?>

    <div class="dig_admin_head"><span><?php _e('Modal', 'digits'); ?></span></div>
    <table class="form-table">
        <tr>
            <th scope="row"><label><?php _e('Modal Overlay Color', 'digits'); ?> </label></th>
            <td>
                <input name="bg_color_modal" type="text" class="bg_color" value="<?php echo $bgcolor; ?>"
                       autocomplete="off"
                       required data-alpha="true">

            </td>
        </tr>


        <tr>
            <th scope="row"><label><?php _e('Login Modal Background Image', 'digits'); ?> </label></th>
            <td>

                <?php
                $imgid = get_option('digits_bg_image_modal');
                $remstyle = "";
                if (empty($imgid)) {
                    $imagechoose = __("Select image", 'digits');
                    $remstyle = 'style="display:none;"';
                } else {
                    $imagechoose = __("Remove Image", 'digits');
                }

                $wid = "";
                if (is_numeric($imgid)) {
                    $wid = wp_get_attachment_url($imgid);
                }
                ?>
                <div class='image-preview-wrapper'>
                    <img id='bg_image-preview_modal' src='<?php if (is_numeric($imgid)) {
                        echo $wid;
                    } else {
                        echo $imgid;
                    } ?>'
                         style="max-height:100px;">
                </div>

                <input type="text" name="bg_image_attachment_id_modal" id='bg_image_attachment_id_modal'
                       value='<?php if (is_numeric($imgid)) {
                           if ($wid) {
                               echo $wid;
                           }
                       } else {
                           echo $imgid;
                       } ?>' placeholder="<?php _e("URL", "digits"); ?>" class="dig_url_img"/>

                <Button id="bg_upload_image_button_modal" type="button" class="button dig_img_chn_btn dig_imsr"
                ><?php echo $imagechoose; ?></Button>


            </td>
        </tr>

        <tr class="dig_modal_type_2">
            <th scope="row"><label for="bgcolor"><?php _e('Login Box Left Background Color', 'digits'); ?> </label>
            </th>
            <td>
                <input name="left_color_modal" type="text" class="bg_color" value="<?php echo $leftcolor; ?>"
                       autocomplete="off"
                       required data-alpha="true">
            </td>
        </tr>
        <tr class="dig_modal_type_2">
            <th scope="row"><label><?php _e('Login Box Left Image', 'digits'); ?> </label></th>
            <td>

                <?php
                $imgid = get_option('digits_left_bg_image_modal');
                $remstyle = "";
                if (empty($imgid)) {
                    $imagechoose = __("Select image", 'digits');
                    $remstyle = 'style="display:none;"';
                } else {
                    $imagechoose = __("Remove Image", 'digits');
                }
                $wid = "";
                if (is_numeric($imgid)) {
                    $wid = wp_get_attachment_url($imgid);
                }
                ?>
                <div class='image-preview-wrapper'>
                    <img id='bg_image-preview_left_modal' src='<?php if (is_numeric($imgid)) {
                        echo $wid;
                    } else {
                        echo $imgid;
                    } ?>'
                         style="max-height:100px;">
                </div>

                <input type="text" name="bg_image_attachment_id_left_modal" id='bg_image_attachment_id_left_modal'
                       value='<?php if (is_numeric($imgid)) {
                           if ($wid) {
                               echo $wid;
                           }
                       } else {
                           echo $imgid;
                       } ?>' placeholder="<?php _e("URL", "digits"); ?>" class="dig_url_img"/>

                <Button id="bg_upload_image_button_left_modal" type="button" class="button dig_img_chn_btn dig_imsr"
                ><?php echo $imagechoose; ?></Button>


            </td>
        </tr>


        <tr class="dig_page_type_2">
            <th scope="row"><label><?php _e('Login Page Left Background Size', 'digits'); ?></label></th>
            <td>
                <select name="left_bg_size_modal">
                    <?php
                    foreach ($size_bg as $size) {
                        $sel = '';
                        if ($left_bg_size == $size) {
                            $sel = 'selected';
                        }
                        echo '<option value="' . $size . '" ' . $sel . '>' . $size . '</option>';

                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr class="dig_page_type_2">
            <th scope="row"><label><?php _e('Login Page Left Background Position', 'digits'); ?></label></th>
            <td>
                <select name="left_bg_position_modal">
                    <?php
                    foreach ($positions_bg as $position) {
                        $sel = '';
                        if ($left_bg_position == $position) {
                            $sel = 'selected';
                        }
                        echo '<option value="' . $position . '" ' . $sel . '>' . $position . '</option>';

                    }
                    ?>
                </select>
            </td>
        </tr>


        <tr>
            <th scope="row"><label><?php _e('Login Modal Background Color', 'digits'); ?> </label></th>
            <td>
                <input name="lbxbg_color_modal" type="text" class="bg_color" value="<?php echo $loginboxcolor; ?>"
                       autocomplete="off"
                       required data-alpha="true">

            </td>
        </tr>
        <tr>
            <th scope="row"><label for="lb_x_modal"><?php _e('Login Modal Shadow', 'digits'); ?> </label></th>
            <td>

                <table class="digotlbr">
                    <tr class="dignochkbxra">
                        <td><input id="lb_x_modal" name="lb_x_modal" type="number" value="<?php echo $sx; ?>"
                                   autocomplete="off" required maxlength="2">
                            <div class="digno-tr_dt"><label for="lb_x_modal"><?php _e('X', 'digits'); ?></label></div>
                        </td>
                        <td><input id="lb_y_modal" name="lb_y_modal" type="number" value="<?php echo $sy; ?>"
                                   autocomplete="off" required maxlength="2">
                            <div class="digno-tr_dt"><label for="lb_y_modal"><?php _e('Y', 'digits'); ?></label></div>
                        </td>
                        <td><input id="lb_blur_modal" name="lb_blur_modal" type="number" value="<?php echo $sblur; ?>"
                                   autocomplete="off" required maxlength="2">
                            <div class="digno-tr_dt"><label for="lb_blur_modal"><?php _e('Blur', 'digits'); ?></label>
                            </div>
                        </td>
                        <td><input id="lb_spread_modal" name="lb_spread_modal" type="number"
                                   value="<?php echo $sspread; ?>" autocomplete="off" required maxlength="2">
                            <div class="digno-tr_dt"><label
                                        for="lb_spread_modal"><?php _e('Spread', 'digits'); ?></label></div>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>

        <tr>
            <th scope="row"><label><?php _e('Login Modal Shadow Color', 'digits'); ?> </label></th>
            <td>
                <input name="lb_color_modal" class="bg_color" type="text" value="<?php echo $scolor; ?>"
                       autocomplete="off"
                       required data-alpha="true">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="lb_radius_modal"><?php _e('Login Modal Radius', 'digits'); ?> </label></th>
            <td>
                <div class="dig_gs_nmb_ovr_spn">
                    <input class="dignochkbx" name="lb_radius_modal" id="lb_radius_modal" type="number"
                           value="<?php echo $sradius; ?>" autocomplete="off" dig-min="42" required maxlength="2"
                           placeholder="0">
                    <span style="left:42px;">px</span>
                </div>


            </td>
        </tr>


        <tr class="dig_modal_type_1_2">
            <th scope="row"><label data-type1="<?php _e('Text and Button Color', 'digits'); ?>"
                                   data-type2="<?php _e('Text Color', 'digits'); ?>">Color</label></th>
            <td>
                <input type="text" name="fontcolor1_modal" class="bg_color" value="<?php echo $fontcolor1; ?>"
                       data-alpha="true"/>
            </td>
        </tr>


        <tr class="dig_modal_type_1">
            <th scope="row"><label><?php _e('Button Font Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="fontcolor2_modal" class="bg_color" value="<?php echo $fontcolor2; ?>"
                       data-alpha="true"/>
            </td>
        </tr>


        <tr class="dig_modal_type_2">
            <th scope="row"><label><?php _e('Input Background Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="input_bg_color_modal" class="bg_color" value="<?php echo $input_bg_color; ?>"
                       data-alpha="true"/>
            </td>
        </tr>
        <tr class="dig_modal_type_2">
            <th scope="row"><label><?php _e('Input Border Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="input_border_color_modal" class="bg_color"
                       value="<?php echo $input_border_color; ?>"
                       data-alpha="true"/>
            </td>
        </tr>
        <tr class="dig_modal_type_2">
            <th scope="row"><label><?php _e('Input Text Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="input_text_color_modal" class="bg_color"
                       value="<?php echo $input_text_color; ?>"
                       data-alpha="true"/>
            </td>
        </tr>
        <tr class="dig_modal_type_2">
            <th scope="row"><label><?php _e('Button Border Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="button_bg_color_modal" class="bg_color" value="<?php echo $button_bg_color; ?>"
                       data-alpha="true"/>
            </td>
        </tr>

        <tr class="dig_modal_type_2">
            <th scope="row"><label><?php _e('Button Text Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="button_text_color_modal" class="bg_color"
                       value="<?php echo $button_text_color; ?>"
                       data-alpha="true"/>
            </td>
        </tr>

        <tr class="dig_modal_type_2">
            <th scope="row"><label><?php _e('Signup Button Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="signup_button_color_modal" class="bg_color"
                       value="<?php echo $signup_button_color; ?>"
                       data-alpha="true"/>
            </td>
        </tr>
        <tr class="dig_modal_type_2">
            <th scope="row"><label><?php _e('Signup Button Border Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="signup_button_border_color_modal" class="bg_color"
                       value="<?php echo $signup_button_border_color; ?>"
                       data-alpha="true"/>
            </td>
        </tr>

        <tr class="dig_modal_type_2">
            <th scope="row"><label><?php _e('Signup Button Text Color', 'digits'); ?> </label></th>
            <td>
                <input type="text" name="signup_button_text_color_modal" class="bg_color"
                       value="<?php echo $signup_button_text_color; ?>"
                       data-alpha="true"/>
            </td>
        </tr>


    </table>


    <div class="dig_admin_head"><span><?php _e('Advanced Options', 'digits'); ?></span></div>
    <?php
    $custom_css = get_option('digit_custom_css');
    $custom_css = stripslashes($custom_css);
    ?>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="dig_custom_css"><?php _e('Custom CSS', 'digits'); ?> </label></th>
            <td><textarea name="digit_custom_css" rows="6"
                          class="dig_inp_wid28" id="dig_custom_css"><?php echo $custom_css; ?></textarea></td>
        </tr>
    </table>

    <?php


    if ($isWiz) {
        ?>
        <p class="digits-setup-action step">
            <input type="submit" value="<?php _e("Continue", "digits"); ?>"
                   class="button-primary button button-large button-next"/>
            <a href="<?php echo admin_url('index.php?page=digits-setup&step=apisettings'); ?>"
               class="button"><?php _e("Back", "digits"); ?></a>
        </p>
        </form>
        <?php
    }

    ?>
    <?php


}

function digits_configure_settings()
{


    $enable_createcustomeronorder = get_option('enable_createcustomeronorder');

    $defaultuserrole = get_option('defaultuserrole', "customer");

    if (!get_role($defaultuserrole)) {
        $defaultuserrole = 'subscriber';
    }

    $digforgotpass = get_option('digforgotpass', 1);

    $dig_overwrite_forgotpass_link = get_option('dig_overwrite_forgotpass_link', 1);


    $dig_hide_countrycode = get_option('dig_hide_countrycode', 0);


    $mobInUname = get_option("dig_mobilein_uname", 0);

    $dig_mob_otp_resend_time = get_option('dig_mob_otp_resend_time', 30);
    $dig_use_strongpass = get_option('dig_use_strongpass', 0);
    $login_reg_success_msg = get_option('login_reg_success_msg', 1);
    $dig_otp_size = get_option("dig_otp_size", 6);


    $wp_login_inte = get_option("dig_wp_login_inte", 0);
    $dig_redirect_wc_to_dig = get_option('dig_redirect_wc_to_dig', 0);

    $dig_mobile_no_formatting = get_option('dig_mobile_no_formatting', 1);
    ?>
    <div class="dig_admin_head"><span><?php _e('Basic', 'digits'); ?></span></div>
    <table class="form-table">

        <tr>
            <th scope="row"><label><?php _e('Username Generation', 'digits'); ?> </label></th>
            <td>
                <select name="dig_mobilein_uname">
                    <option value="3" <?php if ($mobInUname == 3) {
                        echo 'selected="selected"';
                    } ?>><?php _e('From Email', 'digits'); ?></option>
                    <option value="2" <?php if ($mobInUname == 2) {
                        echo 'selected="selected"';
                    } ?>><?php _e('Random Numbers', 'digits'); ?></option>
                    <option value="1" <?php if ($mobInUname == 1) {
                        echo 'selected="selected"';
                    } ?>><?php _e('From Phone Number (with just country code)', 'digits'); ?></option>
                    <option value="4" <?php if ($mobInUname == 4) {
                        echo 'selected="selected"';
                    } ?>><?php _e('From Phone Number (with + and country code)', 'digits'); ?></option>
                    <option value="5" <?php if ($mobInUname == 5) {
                        echo 'selected="selected"';
                    } ?>><?php _e('From Phone Number (without country code)', 'digits'); ?></option>

                    <option value="6" <?php if ($mobInUname == 6) {
                        echo 'selected="selected"';
                    } ?>><?php _e('From Phone Number (with 0)', 'digits'); ?></option>

                    <option value="0" <?php if ($mobInUname == 0) {
                        echo 'selected="selected"';
                    } ?>><?php _e('From Name', 'digits'); ?></option>

                </select>
            </td>
        </tr>

        <tr>
            <th scope="row"><label><?php _e('Mobile Number Formatting', 'digits'); ?> </label></th>
            <td>
                <select name="dig_mobile_no_formatting">
                    <option value="2" <?php if ($dig_mobile_no_formatting == 2) {
                        echo 'selected="selected"';
                    } ?>><?php _e('Local', 'digits'); ?></option>
                    <option value="1" <?php if ($dig_mobile_no_formatting == 1) {
                        echo 'selected="selected"';
                    } ?>><?php _e('International', 'digits'); ?></option>
                    <option value="0" <?php if ($dig_mobile_no_formatting == 0) {
                        echo 'selected="selected"';
                    } ?>><?php _e('No', 'digits'); ?></option>
                </select>
                <p class="dig_ecr_desc dig_sel_erc_desc"><?php _e('This function only works on Digits Login/Signup Modal and Page', 'digits'); ?></p>

            </td>
        </tr>


        <tr>
            <th scope="row"><label class="top-10"><?php _e('Enable /wp-login.php Integeration', 'digits'); ?> </label>
            </th>
            <td>
                <?php digits_input_switch('dig_wp_login_inte', $wp_login_inte); ?>
            </td>
        </tr>

        <tr class="enabledisableforgotpasswordrow">
            <th scope="row"><label class="top-10"><?php _e('Enable Forgot Password', 'digits'); ?> </label></th>
            <td>
                <?php digits_input_switch('dig_enable_forgotpass', $digforgotpass); ?>

                <p class="dig_ecr_desc dig_sel_erc_desc"><?php _e('This function only works on Digits Login/Signup Modal and Page', 'digits'); ?></p>
            </td>
        </tr>

        <tr class="enabledisableforgotpasswordrow">
            <th scope="row"><label
                        class="top-10"><?php _e('Use Digits form as default Forgot Password form', 'digits'); ?> </label>
            </th>
            <td>
                <?php digits_input_switch('dig_overwrite_forgotpass_link', $dig_overwrite_forgotpass_link); ?>
            </td>
        </tr>


        <tr id="enabledisablestrongpasswordrow">
            <th scope="row"><label
                        class="top-10"><?php _e('Enable Strong Password for Registration', 'digits'); ?> </label></th>
            <td>
                <?php digits_input_switch('dig_enable_strongpass', $dig_use_strongpass); ?>
            </td>
        </tr>


        <tr>
            <th scope="row" style="vertical-align:top;"><label
                        for="defaultuserrole"><?php _e('Default User Role', 'digits'); ?></label></th>
            <td>
                <select name="defaultuserrole" id="defaultuserrole">
                    <?php

                    foreach (wp_roles()->roles as $rkey => $rvalue) {

                        if ($rkey == $defaultuserrole) {
                            $sel = 'selected=selected';
                        } else {
                            $sel = '';
                        }
                        echo '<option value="' . $rkey . '" ' . $sel . '>' . $rvalue['name'] . '</option>';
                    }

                    ?>
                </select>

                <p class="dig_ecr_desc dig_sel_erc_desc"><?php _e('The default role which will be assigned to new user created.', 'digits'); ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row" style="vertical-align:top;"><label
                        for="login_reg_success_msg"
                        class="top-10"><?php _e('Login/Registration Success Message', 'digits'); ?></label>
            </th>
            <td>
                <?php digits_input_switch('login_reg_success_msg', $login_reg_success_msg); ?>

                <p class="dig_ecr_desc dig_sel_erc_desc"><?php _e('This function only works on Digits Login/Signup Modal and Page', 'digits'); ?></p>
            </td>
        </tr>
    </table>
    <div class="dig_admin_head"><span><?php _e('OTP SMS', 'digits'); ?></span></div>


    <?php

    $countryList = getCountryList();

    $currentCountry = get_option("dig_default_ccode", 'United States');
    $whiteListCountryCodes = get_option("whitelistcountrycodes");
    $blacklistcountrycodes = get_option("dig_blacklistcountrycodes");
    ?>

    <table class="form-table" style="overflow: hidden">
        <tr>
            <th scope="row"><label><?php _e('Default Country Code', 'digits'); ?> </label></th>
            <td>
                <select name="default_ccode" class="dig_inp_wid3 dig_inp_wid_wil">
                    <option value="-1">Disabled</option>
                    <?php
                    $valCon = "";
                    foreach ($countryList as $key => $value) {
                        $ac = "";


                        if ($currentCountry == $key) {
                            $ac = "selected=selected";
                        }
                        echo '<option class="dig-cc-visible" ' . $ac . ' value="' . $key . '" country="' . digits_strtolower($key) . '">' . getTranslatedCountryName($key) . ' (+' . $value . ')</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr>
            <th scope="row" style="vertical-align:top;"><label
                        for="whitelistcountrycodes"><?php _e('Country Codes Allowlist', 'digits'); ?></label></th>
            <td>

                <select name="whitelistcountrycodes[]" class="whitelistcountrycodeslist dig_multiselect_enable"
                        multiple="multiple">
                    <?php


                    foreach ($countryList as $key => $value) {
                        $ac = "";
                        if ($whiteListCountryCodes) {
                            if (in_array($key, $whiteListCountryCodes)) {
                                $ac = "selected=selected";
                            }
                        }
                        echo '<option value="' . $key . '" ' . $ac . '>' . getTranslatedCountryName($key) . ' (+' . $value . ')</option>';
                    }


                    ?>
                </select><br/>
                <p class="dig_ecr_desc"><?php _e('Sign In/Sign Up will be allowed for phone numbers with these country codes. To allow Sign In/Sign Up for all country codes, leave this blank.', 'digits'); ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row" style="vertical-align:top;"><label
                        for="blacklistcountrycodes"><?php _e('Country Codes Denylist', 'digits'); ?></label></th>
            <td>

                <select name="blacklistcountrycodes[]" class="blacklistcountrycodes dig_multiselect_enable"
                        multiple="multiple">
                    <?php


                    foreach ($countryList as $key => $value) {
                        $ac = "";
                        if ($blacklistcountrycodes) {
                            if (in_array($key, $blacklistcountrycodes)) {
                                $ac = "selected=selected";
                            }
                        }
                        echo '<option value="' . $key . '" ' . $ac . '>' . getTranslatedCountryName($key) . ' (+' . $value . ')</option>';
                    }


                    ?>
                </select><br/>
                <p class="dig_ecr_desc"><?php _e('Sign In/Sign Up will be not allowed for phone numbers with these country codes. To allow Sign In/Sign Up for all country codes, leave this blank.', 'digits'); ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row" style="vertical-align:top;"><label
                        for="phonenumberdenylist"><?php _e('Phone numbers Denylist', 'digits'); ?></label></th>
            <td>

                <select name="phonenumberdenylist[]"
                        class="dig_ignore_select2 phonenumberdenylist dig_multiselect_phone_dynamic_enable"
                        multiple="multiple">
                    <?php
                    $dig_phonenumberdenylist = get_option("dig_phonenumberdenylist");

                    if (is_array($dig_phonenumberdenylist)) {
                        foreach ($dig_phonenumberdenylist as $value) {
                            echo '<option value="' . $value . '" selected=selected>' . $value . '</option>';
                        }
                    }

                    ?>
                </select><br/>
                <p class="dig_ecr_desc"><?php _e('Sign In/Sign Up will be not allowed for these phone numbers.', 'digits'); ?></p>
            </td>
        </tr>

        <?php
        $disp = "";
        $dispotp = '';

        ?>
    </table>
    <?php

    $digits_hidecountrycode_style = 'style="display:none;"';
    if (is_array($whiteListCountryCodes)) {
        if (count($whiteListCountryCodes) == 1) {
            $digits_hidecountrycode_style = 'style="display:block;"';
        }
    }
    ?>
    <div id="digits_hidecountrycode" <?php echo $digits_hidecountrycode_style; ?>>
        <table class="form-table">
            <tr>
                <th scope="row"><label class="top-10"><?php _e('Hide Country Code', 'digits'); ?> </label></th>
                <td>
                    <?php digits_input_switch('dig_hide_countrycode', $dig_hide_countrycode); ?>
                </td>
            </tr>
        </table>
    </div>
    <table class="form-table">
        <tr class="disotp" <?php echo $dispotp; ?>>
            <th scope="row" style="vertical-align:top;"><label for="dig_otp_size"><?php _e('OTP size', 'digits'); ?>
                </label></th>
            <td>
                <select name="dig_otp_size">
                    <option value="4" <?php if ($dig_otp_size == 4) {
                        echo "selected='selected'";
                    } ?>>4
                    </option>
                    <option value="5" <?php if ($dig_otp_size == 5) {
                        echo "selected='selected'";
                    } ?>>5
                    </option>
                    <option value="6" <?php if ($dig_otp_size == 6) {
                        echo "selected='selected'";
                    } ?>>6
                    </option>
                    <option value="7" <?php if ($dig_otp_size == 7) {
                        echo "selected='selected'";
                    } ?>>7
                    </option>
                    <option value="8" <?php if ($dig_otp_size == 8) {
                        echo "selected='selected'";
                    } ?>>8
                    </option>

                </select>
            </td>
        </tr>


        <tr>
            <th scope="row" style="vertical-align:top;"><label
                        for="dig_mob_otp_resend_time"><?php _e('OTP Resend Time', 'digits'); ?></label>
            </th>
            <td>
                <div class="dig_gs_nmb_ovr_spn">
                    <input dig-min="51" type="number" name="dig_mob_otp_resend_time"
                           value="<?php echo $dig_mob_otp_resend_time; ?>"
                           placeholder="<?php _e('0', 'digits'); ?>" class="dig_inp_wid3" min="20" required/>
                    <span style="left:51px;"><?php _e('Seconds', 'digits'); ?></span>
                </div>
            </td>
        </tr>


    </table>


    <?php
    $dig_reqfieldbilling = get_option("dig_reqfieldbilling", 0);

    $enable_wc_autofill = get_option('dig_autofill_wc_billing', 1);
    $showWC = '';
    if (!class_exists('WooCommerce')) {
        $showWC = 'style="display:none;"';
    }
    ?>

    <div <?php echo $showWC; ?> class="dig_admin_head"><span><?php _e('WooCommerce Settings', 'digits'); ?></span></div>

    <table <?php echo $showWC; ?> class="form-table">
        <tr>
            <th scope="row"><label
                        class="no-top"><?php _e('Redirect WooCommerce account page to Digits login page', 'digits'); ?> </label>
            </th>
            <td>
                <?php digits_input_switch('dig_redirect_wc_to_dig', $dig_redirect_wc_to_dig); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="enable_createcustomeronorder"
                                   class="top-10"><?php _e('Create Customer Button', 'digits'); ?>
                </label></th>
            <td>
                <?php digits_input_switch('enable_createcustomeronorder', $enable_createcustomeronorder); ?>
                <p class="dig_ecr_desc dig_sel_erc_desc"><?php _e('Add customer on Add Order Page on dashboard using Modal', 'digits'); ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row"><label for="dig_reqfieldbilling"><?php _e('Required field for billing info', 'digits'); ?>
                </label></th>
            <td>
                <select name="dig_reqfieldbilling" id="dig_reqfieldbilling" class="dig_inp_wid3">
                    <option value="0" <?php if ($dig_reqfieldbilling == 0) {
                        echo 'selected=selected';
                    } ?> ><?php _e('Mobile Number and Email', 'digits'); ?></option>
                    <option value="1" <?php if ($dig_reqfieldbilling == 1) {
                        echo 'selected=selected';
                    } ?> ><?php _e('Mobile Number', 'digits'); ?></option>
                    <option value="2" <?php if ($dig_reqfieldbilling == 2) {
                        echo 'selected=selected';
                    } ?> ><?php _e('Email', 'digits'); ?></option>
                </select>
            </td>
        </tr>

        <tr>
            <th scope="row"><label for="enable_autofillcustomerdetails"
                                   class="top-10"><?php _e('Autofill WooCommerce billing fields with user info', 'digits'); ?>
                </label></th>
            <td>
                <?php digits_input_switch('enable_autofillcustomerdetails', $enable_wc_autofill); ?></td>
        </tr>


        <?php
        $enable_guest_checkout_verification = get_option('enable_guest_checkout_verification', 1);
        $enable_billing_phone_verification = get_option('enable_billing_phone_verification', 1);
        ?>
        <tr style="display: none">
            <th scope="row"><label for="enable_guest_checkout_verification"
                                   class="top-10"><?php _e('Enable guest checkout verification', 'digits'); ?>
                </label></th>
            <td>
                <?php digits_input_switch('enable_guest_checkout_verification', $enable_guest_checkout_verification); ?></td>
        </tr>
        <tr style="display: none">
            <th scope="row"><label for="enable_billing_phone_verification"
                                   class="top-10"><?php _e('Enable billing phone verification', 'digits'); ?>
                </label></th>
            <td>
                <select id="enable_billing_phone_verification" name="enable_billing_phone_verification">
                    <option value="1" <?php if ($enable_billing_phone_verification == 1) echo 'selected'; ?>>
                        <?php _e('For Cash on Delivery', 'digits'); ?>
                    </option>
                    <option value="2" <?php if ($enable_billing_phone_verification == 2) echo 'selected'; ?>>
                        <?php _e('For all payment gateways', 'digits'); ?>
                    </option>
                </select>
        </tr>
    </table>


    <div class="dig_admin_head"><span><?php _e('Redirection', 'digits'); ?></span></div>

    <table class="form-table dig_cs_re">
        <tr>
            <th scope="row"><label
                        for="digits_myaccount_redirect"><?php _e('My Account Link', 'digits'); ?></label></th>
            <td>

                <input type="url" id="digits_myaccount_redirect" name="digits_myaccount_redirect"
                       value="<?php echo get_option("digits_myaccount_redirect"); ?>"
                       placeholder="<?php _e("URL", "digits"); ?>"/>
                <p class="dig_ecr_desc"><?php _e('Leave blank for auto redirect', 'digits'); ?> </p>
            </td>
        </tr>

        <tr>
            <th scope="row"><label for="digits_loginred"><?php _e('Login Redirect', 'digits'); ?></label></th>
            <td>

                <input type="url" id="digits_loginred" name="digits_loginred"
                       value="<?php echo get_option("digits_loginred"); ?>"
                       placeholder="<?php _e("URL", "digits"); ?>"/>
                <p class="dig_ecr_desc"><?php _e('Leave blank for auto redirect', 'digits'); ?> </p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="digits_regred"><?php _e('Signup Redirect', 'digits'); ?></label></th>
            <td>
                <input type="url" id="digits_regred" name="digits_regred"
                       value="<?php echo get_option("digits_regred"); ?>"
                       placeholder="<?php _e("URL", "digits"); ?>"/>
                <p class="dig_ecr_desc"><?php _e('Leave blank for auto redirect', 'digits'); ?> </p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="digits_forgotred"><?php _e('Forgot Password Redirect', 'digits'); ?></label>
            </th>
            <td>
                <input type="url" id="digits_forgotred" name="digits_forgotred"
                       value="<?php echo get_option("digits_forgotred"); ?>"
                       placeholder="<?php _e("URL", "digits"); ?>"/>
                <p class="dig_ecr_desc"><?php _e('Leave blank for auto redirect', 'digits'); ?> </p>
            </td>
        </tr>
        <tr class="dig_csmargn">
            <th scope="row"><label for="digits_logoutred"><?php _e('Logout Redirect', 'digits'); ?></label></th>
            <td>
                <input type="url" id="digits_logoutred" name="digits_logoutred"
                       value="<?php echo get_option("digits_logoutred"); ?>"
                       placeholder="<?php _e("URL", "digits"); ?>"/>
                <p class="dig_ecr_desc"><?php _e('Leave blank for auto redirect', 'digits'); ?>
                    <br/><b><?php _e('Note:', 'digits'); ?></b>&nbsp;<?php _e('Redirect settings only works on Woocommerce, Digits Login/Signup Modal and Page', 'digits'); ?>
                </p>
            </td>
        </tr>

    </table>


    <div class="dig_admin_head"><span><?php _e('Advanced', 'digits'); ?></span></div>

    <table class="form-table">
        <tr>
            <?php
            $sameorigin_protection = get_option('digits_sameorigin_protection', 0);
            ?>
            <th scope="row">
                <label class="top-10" for="digits_sameorigin_protection">
                    <?php _e('Allow Digits Login/Signup pages in iframe', 'digits'); ?>
                </label>
            </th>
            <td>

                <?php digits_input_switch('digits_sameorigin_protection', $sameorigin_protection); ?>

            </td>
        </tr>

        <tr>
            <th scope="row">
                <label>
                    <?php _e('Export/Import', 'digits'); ?>
                </label>
            </th>
            <td>

                <button id="digits_configuration_export" class="button"
                        type="button"><?php _e('Export', 'digits'); ?></button>
                <button id="digits_configuration_import" class="button"
                        type="button"><?php _e('Import', 'digits'); ?></button>
            </td>
        </tr>
    </table>

    <div class="dig_presets_modal dig_overlay_modal_content" id="dig_export_import_content">
        <div class="dig-flex_center">
            <div id="dig_presets_modal_box">
                <div id="dig_presets_modal_body" class="form-table">
                    <div class="modal_head"></div>
                    <textarea class="dig_export_import_values"
                              placeholder="<?php _e('Paste your import code here...', 'digits') ?>"></textarea>

                    <div class="dig_ex_imp_bottom">
                        <button class="imp_exp_button button imp_exp_btn_fn" type="button"
                                attr-export="<?php _e('Copy', 'digits'); ?>"></button>
                        <div class="imp_exp_button imp_exp_cancel dig_presets_modal_head_close"
                             id="dig_presets_modal_head_close"><?php _e('CLOSE', 'digits'); ?></div>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <?php


}


function digits_input_switch($name, $value)
{
    $sel = $value == 'on' || $value == 1 ? 'checked' : '';
    ?>
    <div class="input-switch <?php echo $sel; ?>">
        <input type="checkbox" class="<?php echo $name; ?>" name="<?php echo $name; ?>" id="<?php echo $name; ?>"
            <?php echo $sel; ?> value="1"/>
        <label for="<?php echo $name; ?>"></label>
        <span class="status_text yes">On</span>
        <span class="status_text no">Off</span>
    </div>
    <input type="checkbox" class="hide-input <?php echo $name; ?>_off" name="<?php echo $name; ?>"
        <?php if (empty($sel)) echo 'checked'; ?> value="0"/>

    <?php
}

function digits_pages_complete_list()
{
    return array_merge(digits_pages_list('modal'), digits_pages_list('page'));
}

/*
 * modal, page
 */
function digits_pages_list($type)
{
    $list = array(
        'default' => array(
            'label' => __('Digits Native', 'digits'),
            'value' => '-1',
        )
    );
    return apply_filters('digits_' . $type . '_list', $list);
}