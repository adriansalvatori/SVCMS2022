<?php

/*
 * Plugin Name: DIGITS: Wordpress Mobile Number Signup and Login
 * Description: Expand your website dimensions by providing signup and login using mobile number. User can register themself with just a mobile number.
 * Version: 7.9.2.10
 * Plugin URI: https://digits.unitedover.com
 * Author URI: https://www.unitedover.com/
 * Author: UnitedOver
 * Text Domain: digits
 * Requires PHP: 5.5
 * Domain Path: /languages
 */


if (!defined('ABSPATH')) {
    exit;
}

function digits_version()
{
    return '7.9.2.10';
}
update_option('dig_purchasecode', '8699958a-77f3-4db8-9422-126b0836e1c5');

global $dig_logingpage, $dig_save_details;
$dig_logingpage = 0;
$dig_save_details = 0;

require_once plugin_dir_path(__FILE__) . 'admin/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/core/functions.php';

require_once plugin_dir_path(__FILE__) . 'includes/update.php';

require_once plugin_dir_path(__FILE__) . 'includes/edit_number.php';
require_once plugin_dir_path(__FILE__) . 'includes/forms_shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/process_user.php';
require_once plugin_dir_path(__FILE__) . 'includes/woocommerce-registration.php';
require_once plugin_dir_path(__FILE__) . 'includes/userdata.php';
require_once plugin_dir_path(__FILE__) . 'includes/login.php';
require_once plugin_dir_path(__FILE__) . 'includes/register.php';
require_once plugin_dir_path(__FILE__) . 'includes/wp.php';
require_once plugin_dir_path(__FILE__) . 'includes/wcs.php';
require_once plugin_dir_path(__FILE__) . 'includes/forms.php';
require_once plugin_dir_path(__FILE__) . 'includes/sessions/class-session-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/plugins/init.php';


require_once('update/plugin-update-checker.php');

add_filter('plugin_row_meta', 'digits_update_plugin_meta', 10, 4);

function digits_update_plugin_meta($plugin_meta, $plugin_file, $plugin_data, $status)
{

    $list = apply_filters('digits_addon', array());

    if (!isset($plugin_data['slug'])) {
        return $plugin_meta;
    }

    $slug = $plugin_data['slug'];
    if ($slug == 'digits') {
        $plugin_meta['2'] = '<a href="https://digits.unitedover.com/changelog" target="_blank">' . __('View changelog', 'digits') . '</a>';
    }

    return $plugin_meta;

}

function get_digits_basename()
{
    return plugin_basename(__FILE__);
}

function get_digits_asset_uri($path)
{
    return plugins_url($path, __FILE__);
}

add_action('init', function () {

    $session = new Digits_Session_Handler();


    if (!$session->get('digits_countrycode')) {
        $session->set('digits_countrycode', getCountry());
    }


}, 1);


function digits_load_plugin_textdomain()
{
    load_plugin_textdomain('digits', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

add_action('plugins_loaded', 'digits_load_plugin_textdomain');


function dig_create_user_menu($admin_bar)
{
    if (!user_can(get_current_user_id(), "create_users")) {
        return;
    }

    $enable_createcustomeronorder = get_option('enable_createcustomeronorder');
    if ($enable_createcustomeronorder == 0) {
        return;
    }

    $args = array(
        'id' => 'dig-create-user',
        'title' => __('+ Add User', 'digits'),
        'href' => '#',
        'meta' => array(
            'target' => '_self',
            'title' => __('Add new user', 'digits'),
            'class' => 'DigCreateCustomer noaction',
        ),
    );
    $admin_bar->add_menu($args);

    createCustomerOnOrderPage(true);
}

add_action('admin_bar_menu', 'dig_create_user_menu', 100); // 10 = Position on the admin bar


function getCountry()
{


    $countrycode_default = get_option("dig_default_ccode");

    if ($countrycode_default != -1) {
        $countrycode = getCountryCode($countrycode_default);
        if (!empty($countrycode)) {
            return $countrycode;
        }
    }
    $ip = isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER["REMOTE_ADDR"];

    $countryname = '';
    if (class_exists('WC_Geolocation')) {
        $location = WC_Geolocation::geolocate_ip('', true, false);
        $countrycode = $location['country'];
        $countryname = dig_countrycodetocountry($countrycode);
    }

    if(empty($countryname)){


        if (!empty($_SERVER['HTTP_CF_IPCOUNTRY'])) { // WPCS: input var ok, CSRF ok.
            return strtoupper(sanitize_text_field(wp_unslash($_SERVER['HTTP_CF_IPCOUNTRY']))); // WPCS: input var ok, CSRF ok.
        } elseif (!empty($_SERVER['GEOIP_COUNTRY_CODE'])) { // WPCS: input var ok, CSRF ok.
            // WP.com VIP has a variable available.
            return strtoupper(sanitize_text_field(wp_unslash($_SERVER['GEOIP_COUNTRY_CODE']))); // WPCS: input var ok, CSRF ok.
        } elseif (!empty($_SERVER['HTTP_X_COUNTRY_CODE'])) { // WPCS: input var ok, CSRF ok.
            // VIP Go has a variable available also.
            return strtoupper(sanitize_text_field(wp_unslash($_SERVER['HTTP_X_COUNTRY_CODE']))); // WPCS: input var ok, CSRF ok.
        } else {
            $ch = curl_init();


            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_setopt($ch, CURLOPT_URL, 'http://ip-api.com/json/' . $ip);
            $result = curl_exec($ch);


            $uinfo = json_decode($result);

            if (!empty($uinfo->country)) {
                $countryname = $uinfo->country;
            }


        }
    }
    if (empty($countryname)) {
        $countrycode = getCountryCode(get_option("dig_default_ccode"));
    } else {
        $countrycode = getCountryCode($countryname);

    }

    if(dig_checkblacklist($countrycode)){
        $countrycode = '';
    }

    if (empty($countrycode)) {

        $whiteListCountryCodes = get_option("whitelistcountrycodes", array());

        $size = sizeof($whiteListCountryCodes);
        $countryarray = getCountryList();

        if ($size > 0 && is_array($whiteListCountryCodes)) {
            $countrycode = $countryarray[$whiteListCountryCodes[0]];
        } else {
            $countrycode = reset($countryarray);
        }
    }
    return $countrycode;
}

function getUserCountryCodeFunction()
{


    $countrycode_default = get_option("dig_default_ccode", -1);

    if ($countrycode_default != -1) {
        $countrycode = getCountryCode($countrycode_default);

        if (!empty($countrycode) && checkwhitelistcode($countrycode)) {
            return $countrycode;
        }
    }


    $session = new Digits_Session_Handler();

    if (!$session->get('digits_countrycode')) {
        $country_code = getCountry();
        $session->set('digits_countrycode', $country_code);

        return $country_code;
    } else {
        $ccode = $session->get('digits_countrycode');
        if (checkwhitelistcode($ccode)) {
            return $ccode;
        } else {
            return getCountry();
        }
    }
}

function getUserCountryCode()
{
    $code = getUserCountryCodeFunction();

    if ($code == null) {
        $code = getCountryCode(get_option("dig_default_ccode"));
        if($code==null){
            $code = '1';
        }
    }

    return '+' . $code;

}


add_action('woocommerce_admin_order_data_after_order_details', 'dig_ccreateCustomerOnOrderPage');

function dig_ccreateCustomerOnOrderPage()
{
    createCustomerOnOrderPage(false);

}

function createCustomerOnOrderPage($noui = false)
{

    $enable_createcustomeronorder = get_option('enable_createcustomeronorder');
    $defaultuserrole = get_option('defaultuserrole');

    if ($enable_createcustomeronorder == 0) {
        return;
    }


    if (!$noui) {
        ?>

        <div class="digit-crncw button" id="DigCreateCustomer">
            <?php _e('Create New Customer', 'digits'); ?>
        </div>
    <?php }

    $dir = 'ltr';
    if (is_rtl()) {
        $dir = 'rtl';
    }
    ?>
    <div id="dig-ucr-container" class="dig-box" style="display: none;">
        <div class="dig-content">
            <?php _e('Create Customer', 'digits'); ?>

            <span class="dig-cont-close">&times;</span>
            <p>
                <input type="text" id="dig-cru-firstname" name="firstname"
                       placeholder="<?php _e('First Name', 'digits'); ?>" autocomplete="name"
                       style="direction: <?php echo $dir ?>;"/>
                <input type="text" id="dig-cru-lastname" name="lastname"
                       placeholder="<?php _e('Last Name', 'digits'); ?>" autocomplete="name"
                       style="direction: <?php echo $dir ?>;"/>
                <input type="text" id="username" class="dig-cru-mailormob" name="emailormobilenumber"
                       placeholder="<?php _e('Email/Mobile Number', 'digits'); ?>"
                       autocomplete="off"/><br/>
            <div class="cancelccb button"><?php _e('Cancel', 'digits'); ?></div>
            <div class="createcustomer dig_createcustomer button button-primary"><?php _e('Create Customer', 'digits'); ?></div>
            <br/>
            </p>

        </div>

    </div>

    <?php

    wp_register_script('digits-cco', plugins_url('/admin/assets/js/dig_cco.min.js', __FILE__), array('jquery'), digits_version(), true);


    $jsData = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'csrf' => wp_create_nonce('dig-create-user-order'),
        'enterallfields' => __('Enter all fields!', 'digits'),
        'invalidmailormobile' => __('Invalid Email or mobile number!', 'digits'),
        'error' => __('Error', 'digits'),
        'EmailMobileNumberAlreadyRegistered' => __('Email/Mobile number has already registered', 'digits'),
        'userregisteredsuccessfully' => __("User registered successfully", 'digits')
    );
    wp_localize_script('digits-cco', 'dig_cco_obj', $jsData);


    wp_enqueue_script('digits-cco');


    wp_enqueue_style('digits-cco-style', plugins_url('/admin/assets/css/dig_cco.css', __FILE__), array(), digits_version());


}

function createUserOnOrder()
{
    if (!current_user_can('edit_user') && !current_user_can('administrator')) {
        die('0');
    }


    check_ajax_referer('dig-create-user-order', 'csrf', true);
    $enable_createcustomeronorder = get_option('enable_createcustomeronorder');
    $defaultuserrole = get_option('defaultuserrole');


    $firstname = sanitize_text_field($_REQUEST['firstname']);
    $lastname = sanitize_text_field($_REQUEST['lastname']);
    $phone = sanitize_text_field($_REQUEST['mailormob']);

    $countrycode = sanitize_text_field($_REQUEST['countrycode']);

    if (isValidMobile($phone)) {
        $mailormob = $countrycode . $phone;
    } else {
        $mailormob = $phone;
    }


    if ($firstname == "" || $lastname == "" || $mailormob == "") {
        die("0");
    }
    if (!isValidMobile($phone) && !isValidEmail($mailormob)) {
        die("0");
    }


    if (empty($pass)) {
        $pass = wp_generate_password();
    }


    $useMobAsUname = get_option('dig_mobilein_uname', 0);

    if ($useMobAsUname == 1 && isValidMobile($phone)) {
        $mobu = str_replace("+", "", $mailormob);
        $check = username_exists($mobu);
        if (!empty($check)) {
            die("0");
        } else {
            $ulogin = $mobu;
        }
    } else {
        $check = username_exists($firstname);
        if (!empty($check)) {
            $suffix = 2;
            while (!empty($check)) {
                $alt_ulogin = $firstname . $suffix;
                $check = username_exists($alt_ulogin);
                $suffix++;
            }
            $ulogin = $alt_ulogin;
        } else {
            $ulogin = $firstname;
        }
    }


    if (isValidMobile($phone)) {
        $user1 = getUserFromPhone($mailormob);
        if ($user1) {
            die("-1");
        }
        $ulogin = sanitize_user($ulogin, true);
        $new_customer = wp_create_user($ulogin, $pass);


        update_user_meta($new_customer, 'digits_phone', $mailormob);
        update_user_meta($new_customer, 'digt_countrycode', $countrycode);
        update_user_meta($new_customer, 'digits_phone_no', $phone);

        update_user_meta($new_customer, "billing_phone", $phone);


    } else {
        if (email_exists($mailormob)) {
            die("-1");
        }

        $validation_error = new WP_Error();
        $validation_error = apply_filters('digits_validate_email', $validation_error, $mailormob);

        if ($validation_error->get_error_code()) {
            die('0');
        }

        $ulogin = sanitize_user($ulogin, true);
        $new_customer = wp_create_user($ulogin, $pass, $mailormob);
        update_user_meta($new_customer, "billing_email", $mailormob);

    }

    if (is_wp_error($new_customer)) {
        die("0");
    }
    update_user_meta($new_customer, 'last_name', $lastname);
    update_user_meta($new_customer, 'first_name', $firstname);

    wp_update_user(array(
        'ID' => $new_customer,
        'role' => $defaultuserrole,
        'first_name' => $firstname,
        'last_name' => $lastname,
        'display_name' => $firstname
    ));

    do_action('register_new_user', $new_customer);
    $newuser = new stdClass();
    $newuser->success = "1";
    $newuser->ID = $new_customer;
    $newuser->url = get_edit_user_link($new_customer);
    echo json_encode($newuser);

    die();


}

add_action("wp_ajax_digits_create_user_order", "createUserOnOrder");

if (!function_exists('isValidMobile')) {
    function isValidMobile($mobile)
    {
        return preg_match('/^[0-9]+$/', $mobile);
    }
}
if (!function_exists('isValidEmail')) {
    function isValidEmail($email)
    {
        return is_email($email);
    }
}

/**
 * Add a settings to plugin_action_links
 */
function dig_add_plugin_action_links($links, $file)
{
    static $this_plugin;

    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) {
        $uri = admin_url("admin.php?page=digits_settings");
        $wsl_links = '<a href="' . $uri . '">' . __("Settings") . '</a>';

        array_unshift($links, $wsl_links);
    }

    return $links;
}

add_filter('plugin_action_links', 'dig_add_plugin_action_links', 10, 2);


add_action('wp_footer', function () {


    if (function_exists('dig_custom_modal_temp')) {
        return;
    }


    $registerContent = '';
    $dig_style = 'style="display: none; opacity: 0; left: 31px; z-index: 2;top:0;"';
    $dig_main_re = "dig-modal-con-reno";


    $color = get_option('digit_color');


    $theme = "dark";


    $color = get_option('digit_color_modal');

    $page_type = 1;


    if ($color === false) {
        $color = get_option('digit_color');

    }


    if (isset($color['type'])) {
        $page_type = $color['type'];
    }


    $left = 9;


    $bg = get_option('digits_bg_image_modal');
    $url = "";
    if (!empty($bg)) {
        if (is_numeric($bg)) {
            $bg = wp_get_attachment_url($bg);
        }
        $url = ", url('" . $bg . "')";
    }


    digits_form_inline_css($color, false, $url);
    ?>

    <div class="dig_load_overlay">
        <div class="dig_load_content">
            <div class="dig_spinner">
                <div class="dig_double-bounce1"></div>
                <div class="dig_double-bounce2"></div>
            </div>
            <?php


            ?>
        </div>
    </div>
    <div class="digits_login_form">
    <?php


     $load_digits_modal = apply_filters('load_digits_modal', true);

     if (!is_user_logged_in() && $load_digits_modal) {


        $digits_modal_class = implode(" ", apply_filters('digits_modal_class_digits_native', array()));
        ?>


        <div id="dig-ucr-container" class="<?php if (is_rtl()) {
            echo 'dig_rtl';
        } ?> dig_lrf_box dig_ma-box dig-box <?php echo $digits_modal_class . ' ';
        echo $dig_main_re.'';
        if ($page_type == 2) {
            echo ' dig_pgmdl_2';
        } else {
            echo ' dig_pgmdl_1';
        } ?>" <?php if($page_type==2) echo 'data-placeholder="yes"';?> data-asterisk="<?php echo get_option( 'dig_show_asterisk', 0 ); ?>"  style="display:none;">


            <div class="dig-content dig-modal-con <?php if ($page_type == 2) {
                echo 'dig_ul_divd';
            }
            echo ' ' . $theme; ?>">
                <?php if ($page_type == 2) {
                    $bg_left = get_option('digits_left_bg_image_modal');

                    if (!empty($bg_left)) {
                        if (is_numeric($bg_left)) {
                            $bg_left = wp_get_attachment_url($bg_left);
                        }
                    }
                    ?>
                    <div class="dig_ul_left_side" style="background-image: url('<?php echo $bg_left; ?>');">
                    </div>

                <?php } ?>


                <div class="digits_bx_cred_frm_container">
                    <div class="digits_bx_head">
                        <span class="dig-box-login-title"><?php _e('Log In', 'digits'); ?></span>
                        <span class="dig-cont-close"><span>&times;</span></span>
                    </div>
                    <div class="digits_bx_cred_frm">
                        <div class="dig_bx_cnt_mdl">


                            <?php


                            if ($page_type == 2) {
                                dig_verify_otp_box();
                            }


                            $dig_cust_forms = apply_filters('dig_hide_forms', 0);
                            if ($dig_cust_forms === 0) {
                                echo '<div class="dig-log-par">';
                                digits_forms();
                                echo '</div>';
                            } else {
                                do_action('digits_custom_form');
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
                if ($page_type == 2) {
                    /*<div class="dig_login_cancel">
						<a href="#"
						   class="dig_page_cancel_color"><span><?php _e( "Cancel", "digits" );<!--</span></a>
					</div> */
                }
                ?>
            </div>
        </div>

        <?php
    }
echo '</div>';
    digCountry();
}
, 9);


function dig_login_contents($modal, $type = 1, $page = false, $args = null)
{

    $left = 9;
    $element = '';
    $registerButton = '';

    $modalBox = '';

    $dtype = 1;
    if (!$modal) {
        $dtype = 10;
    }
    $class = array('digits-login-modal');

    $href = '';
    $data_attr = array();
    if($args!=null){
        $url = $args['url'];
        if(isset($args['class'])){
            $class = array_merge($class, $args['class']);
        }
        if(isset($args['data'])){
            $data_attr[] = $args['data'];
        }
    }

    $class = implode(" ", $class);
    $data_attr = implode(" ", $data_attr);

    $element = 'onclick="jQuery(\'this\').digits_login_modal(jQuery(this));return false;" attr-disclick="1" class="'.$class.'" '.$data_attr;

    wp_enqueue_style('digits-login-style');
    wp_enqueue_script('digits-login-script');

    $diglogintrans = get_option("diglogintrans", "Login / Register");
    $digregistertrans = get_option("digregistertrans", "Register");
    $digforgottrans = get_option("digforgottrans", "Forgot your Password?");
    $digmyaccounttrans = get_option("digmyaccounttrans", "My Account");

    $digonlylogintrans = get_option("digonlylogintrans", __("Login", "digits"));

    $opatt = "";
    if ($page) {
        $opatt = "data-fal='1'";
    }
    if (!is_user_logged_in()) {


        if(empty($url)){
            $current_url = '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            $page_type = '';


            if($type==1){
                $form_type = 'login_register';
            }else if ($type == 2) {
                $form_type = 'register';
            } else if ($type == 3) {
                $form_type = 'forgot';
            } else if ($type == 4) {
                $form_type = 'login';
            }

            if($type!=1){
                if ($type == 2) {
                    $page_type = 'register';
                } else if ($type == 3) {
                    $page_type = 'forgot-password';
                } else if ($type == 4) {
                    $page_type = 'login';
                }
                $page_type = '&type='.$page_type;
            }

            $url = '?login=true' .$page_type . dig_url_language();

            $url = apply_filters('digits_page_url', $url, $current_url, $form_type);

        }

        if ($type == 1) {
            return '<span href="'.$url.'" ' . $element . ' ' . $opatt . ' type="' . $dtype . '"><span>' . $diglogintrans . '</span></span>' . $modalBox;
        } else if ($type == 2) {
            return '<span href="'.$url.'" ' . $element . ' ' . $opatt . ' type="2"><span>' . $digregistertrans . '</span></span>' . $modalBox;
        } else if ($type == 3) {
            return '<span href="'.$url.'" ' . $element . ' ' . $opatt . ' type="3"><span>' . $digforgottrans . '</span></span>' . $modalBox;
        } else if ($type == 4) {
            return '<span href="'.$url.'" ' . $element . ' ' . $opatt . ' type="4"><span>' . $digonlylogintrans . '</span></span>' . $modalBox;
        }

    } else if ($type == 1) {
        $custom_redirect = get_option('digits_myaccount_redirect');
        if (!empty($custom_redirect)) {
            $url = $custom_redirect;
        } else if (class_exists('WooCommerce')) {
            $url = get_permalink(get_option('woocommerce_myaccount_page_id'));
        } else if (function_exists('bp_is_active')) {
            $url = bp_core_get_user_domain(get_current_user_id()) . 'profile/';
        } else {
            $url = get_author_posts_url(get_current_user_id());
        }

        return '<span href=' . $url . ' ' . $element . ' type="10"><span>' . $digmyaccounttrans . '</span></span>';
    }
}


add_filter('wp_nav_menu_items', 'do_shortcode');

function digits_login_button()
{
    return dig_login_contents(false);
}

add_shortcode('digits-login', 'digits_login_button');


add_shortcode('dm-page', 'digits_login_button');


function digits_logout()
{
    if (is_user_logged_in()) {
        $url = "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $query = parse_url($url, PHP_URL_QUERY);

// Returns a string if the URL has parameters or NULL if not
        if ($query) {
            $url .= '&logout=true&lnounce=' . wp_create_nonce("lnounce");
        } else {
            $url .= '?logout=true&lnounce=' . wp_create_nonce("lnounce");
        }

        $logouttrans = get_option('diglogouttrans', 'Logout');

        return "<span href='" . $url . dig_url_language() . "' type='10' class=\"digits-login-modal\"><span>" . __($logouttrans, "digits") . "</span></span>";
    }
}

add_shortcode('digits-logout', 'digits_logout');
add_shortcode('dm-logout', 'digits_logout');

function dig_addmobile()
{
    ?>
    <div id="dig_ihc_mobcon">
        <input type="hidden" name="dig_nounce" class="dig_nounce" value="<?php echo wp_create_nonce('dig_form') ?>">
        <input type="hidden" name="code" id="dig_ihc_ea_code"/>
        <input type="hidden" name="csrf" id="dig_ihc_ea_csrf"/>

        <input type="hidden" name="dig_ihc_current_mob" id="dig_ihc_current_mob"
               value="<?php echo esc_attr(get_the_author_meta('digits_phone_no', get_current_user_id())); ?>"/>
        <div class="iump-form-line-register iump-form-text">
            <label style="display:none;"><?php _e("Mobile Number", "digits"); ?></label>
            <input type="text" id="username" name="dig_ihc_mobileno"
                   placeholder="<?php _e("Mobile Number", "digits"); ?>" mob="1"
                   countryCode="<?php echo esc_attr(get_the_author_meta('digt_countrycode', get_current_user_id())); ?>"
                   value="<?php echo esc_attr(get_the_author_meta('digits_phone_no', get_current_user_id())); ?>"/>
        </div>

        <input type="hidden" name="current_mob" id="dig_bp_current_mob"
               value="<?php echo esc_attr(get_the_author_meta('digits_phone_no', get_current_user_id())); ?>"/>

        <div id="dig_ihc_mobotp" class="iump-form-line-register iump-form-text" style="display:none;">
            <input type="text" id="dig_ihc_otp" name="dig_ihc_otp" placeholder="<?php _e("OTP", "digits"); ?>"/>
        </div>

    </div>
    <?php
}


function digits_modal_login()
{
    return dig_login_contents(true);
}

add_shortcode('digits-modal-login', 'digits_modal_login');


add_shortcode('dm-modal', 'digits_modal_login');


function digits_modal_registration()
{
    return dig_login_contents(true, 2);

}

function digits_modal_forgotpass()
{
    return dig_login_contents(true, 3);
}

add_shortcode('digits-registration', 'digits_modal_registration');
add_shortcode('digits-forgot-password', 'digits_modal_forgotpass');

add_shortcode('dm-signup-modal', 'digits_modal_registration');
add_shortcode('dm-registration-modal', 'digits_modal_registration');
add_shortcode('dm-forgot-password-modal', 'digits_modal_forgotpass');


function digits_page_registration()
{
    return dig_login_contents(true, 2, true);

}

function digits_page_forgotpass()
{
    return dig_login_contents(true, 3, true);
}

add_shortcode('digits-page-registration', 'digits_page_registration');
add_shortcode('digits-page-forgot-password', 'digits_page_forgotpass');


add_shortcode('dm-signup-page', 'digits_page_registration');
add_shortcode('dm-registration-page', 'digits_page_registration');
add_shortcode('dm-forgot-password-page', 'digits_page_forgotpass');


function digits_modal_onlylogin()
{
    return dig_login_contents(true, 4);
}

function digits_page_onlylogin()
{
    return dig_login_contents(false, 4, true);
}

add_shortcode('dm-login-modal', 'digits_modal_onlylogin');
add_shortcode('dm-login-page', 'digits_page_onlylogin');



function digits_activate()
{

    do_action('digits_activation_hooks');

    if (version_compare(PHP_VERSION, '5.5', '<') && is_admin()) {
        $version_required = sprintf('<div><p>You are currently using outdated version of PHP %1$s. Please update your PHP to newer version, Digits requires PHP v5.5 or higher to work. </p></div>', PHP_VERSION);
        wp_die($version_required);
    }


    if (!function_exists('curl_version')) {
        wp_die(__('<div><p><b>Fatal Error</b>: Digits requires curl to work correctly. </p></div>', 'digits'));
    }


    dig_pcd_act();
    add_option('digits_do_activation_redirect', true);
}


add_action('admin_init', 'digits_admin_redirect');

register_activation_hook(__FILE__, 'digits_activate');

function digits_admin_redirect()
{

    if(get_option('digits_activation_time', -1)==-1){
        update_option('digits_activation_time',time());
    }
    return;

    $digits_version = digits_version();

    $current_time = time();
    $addon_redirect = get_option('digits_addon_update_page_redirect',array('time'=>0,'version'=>'1'));
    if($current_time - $addon_redirect['time'] > 604800 && $addon_redirect['version']!=$digits_version){
        $addon_redirect['time'] = $current_time;
        $addon_redirect['version'] = $digits_version;

        update_option('digits_addon_update_page_redirect', $addon_redirect);

        wp_redirect(esc_url_raw(admin_url("/admin.php?page=digits_settings&tab=addons")));
        die();
    }

}

add_action('admin_init','digits_survery');
function digits_survery(){
    if (get_option('digits_survery_pop_redirect', false) == false) {
        $act_time = get_option('digits_activation_time', -1);
        if($act_time==-1){
            update_option('digits_activation_time',time());
            return;
        }

        $current_time = time();
        if($current_time - $act_time > 30 * 24 * 3600 ){
            update_option('digits_survery_pop_redirect', true);
            wp_redirect(esc_url_raw(admin_url("/admin.php?page=digits_settings&tab=addons&show_survey=1")));
        }
        return;
    }
}


function dig_fonts()
{

    $fonts = array(
        "Roboto:700,500,500i,400,200,300"
    );

    $fonts_collection = add_query_arg(array(

        "family" => urlencode(implode("|", $fonts)),

    ), 'https://fonts.googleapis.com/css');

    return $fonts_collection;
}


$DigitsUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://bridge.unitedover.com/updates/changelog/digits.json',
    __FILE__,
    'digits'
);

$DigitsUpdateChecker->addQueryArgFilter('dig_filter_update_checks');
function dig_filter_update_checks($queryArgs)
{

    $digpc = dig_get_option('dig_purchasecode');
    if (!empty($digpc)) {
        $queryArgs['license_key'] = dig_get_option('dig_purchasecode');
    }

    $queryArgs['request_site'] = dig_network_home_url();

    $queryArgs['license_type'] = dig_get_option('dig_license_type', 1);

    $plugin_version = digits_version();

    $queryArgs['version'] = $plugin_version;


    return $queryArgs;
}


function dig_get_locale($locale, $supportedLocales)
{
    foreach ($supportedLocales as $v) {
        if (stripos(strtolower($v), strtolower($locale)) !== false) {
            return $v;
        }
    }

    return false;
}


/**
 * Show the signin/signup page.
 */


function dig_removeParam($url, $param)
{
    $url = preg_replace('/(&|\?)' . preg_quote($param) . '=[^&]*$/', '', $url);
    $url = preg_replace('/(&|\?)' . preg_quote($param) . '=[^&]*&/', '$1', $url);

    return $url;
}

add_action('digits_page_ini', 'digits_send_frame_options_header');
function digits_send_frame_options_header()
{
    $sameorigin_protection = get_option('digits_sameorigin_protection', 1);
    if($sameorigin_protection==0){
        send_frame_options_header();
    }
}


function digits_login()
{


    if (isset($_GET['logout']) && isset($_GET['lnounce'])) {
        if (!empty($_GET['logout']) || 'true' == $_GET['logout']) {
            $nounce = wp_verify_nonce($_GET['lnounce'], 'lnounce');
            if (is_user_logged_in() && $nounce) {


                $current_url = get_option("digits_logoutred");
                if (empty($current_url)) {
                    $current_url = site_url();
                }


                wp_logout();
                wp_safe_redirect($current_url);
                exit();
            }
        }
    }
    if (!isset($_GET['login'])) {
        return;
    }
    if (empty($_GET['login']) || 'true' !== $_GET['login'] || is_user_logged_in()) {
        return;
    }


    // Redirect to https login if forced to use SSL
    if (force_ssl_admin() && !is_ssl()) {
        if (0 === strpos($_SERVER['REQUEST_URI'], 'http')) {
            wp_redirect(set_url_scheme($_SERVER['REQUEST_URI'], 'https'));
            exit();
        } else {
            wp_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            exit();
        }
    }


    $digforgotpass = get_option('digforgotpass', 1);
    $users_can_register = get_option('dig_enable_registration', 1);

    $page = !empty($_GET['type']) ? sanitize_text_field($_GET['type']) : '';

    if(!empty($_GET['type']) && empty($page)){
        $page = $_GET['type'];
    }

    if ($page == 'register') {
        $page = 2;
    } else if ($page == 'forgot-password') {
        $page = 3;
    }else if ($page == 'login') {
        $page = 4;
    } else if (empty($page)) {
        $page = 1;
    }


    if ($page == 4) {
        $users_can_register = 0;
    }

    if (empty($page) || ($users_can_register == 0 && $page == 2) || ($digforgotpass == 0 && $page == 3)) {
        $page = 1;
    }
    if ($page > 1 && $page > 4) {
        $page = 1;
    }

    digits_process_login();
    digits_process_forgotpassword();

    // Don't index any of these forms


    $separator = is_rtl() ? ' &rsaquo; ' : ' &lsaquo; ';

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

    $left_color = 'rgba(255,255,255,1)';
    $page_type = 1;
    $sradius = 4;
    $left_bg_position = 'Center Center';
    $left_bg_size = 'auto';
    if ($color !== false) {
        $bgcolor = $color['bgcolor'];


        if (isset($color['fontcolor'])) {
            $fontcolor = $color['fontcolor'];
            $loginboxcolor = $bgcolor;
            $scolor = "rgba(0,0,0,0)";
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

        }
        if (isset($color['type'])) {
            $page_type = $color['type'];
            if ($page_type == 2) {
                $left_color = $color['left_color'];
            }

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


    wp_register_style('digits-main-login-style', plugins_url('/assets/css/login_body.css', __FILE__), array(), digits_version(), 'all');

    do_action('digits_page_ini');

    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name='robots' content='noindex,noarchive'/>
        <meta name='referrer' content='strict-origin-when-cross-origin'/>
        <title><?php echo get_bloginfo('name', 'display') . $separator; ?><?php _e("Log In", "digits"); ?></title>
        <?php
        /**
         * Enqueue scripts and styles for the login page.
         *
         * @since 3.1.0
         */

        do_action('login_enqueue_scripts');
        wp_print_styles('digits-main-login-style');
        do_action('login_head');


        if (isset($_GET['back'])) {
            $current_url = "//" . $_SERVER['HTTP_HOST'];
        } else {
            $current_url = "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $current_url = dig_removeParam($current_url, "login");
            $current_url = dig_removeParam($current_url, "page");
        }

        $left = 9;

        $users_can_register = get_option('dig_enable_registration', 1);


        $theme = "dark";
        $themevar = "light";
        $themee = "lighte";
        $bgtype = "bgdark";
        $bgtransbordertype = "bgtransborderdark";

        $bg = get_option('digits_bg_image');
        $url = "";
        if (!empty($bg)) {
            if (is_numeric($bg)) {
                $bg = wp_get_attachment_url($bg);
            }
            $url = ", url(" . $bg . ")";
        }


        digits_form_inline_css($color, true, $url);
        ?>


    </head>
    <body class="digits_login_form">
    <div class="dig_bdy_container <?php if ($page_type == 2) {
        echo 'dig_ul_divd';
    } ?>">
        <?php if ($page_type == 2) {
            $bg_left = get_option('digits_left_bg_image');

            if (!empty($bg_left)) {
                if (is_numeric($bg_left)) {
                    $bg_left = wp_get_attachment_url($bg_left);
                }
            }
            ?>
            <div class="dig_ul_left_side" style="background-image: url('<?php echo $bg_left; ?>');">
                <?php

                $footer = trim(get_option('login_page_footer'));

                if (!empty($footer)) {
                    echo '<div class="dig_lp_footer">' . stripslashes(base64_decode($footer)) . '</div>';
                }
                ?>
            </div>

            <div class="dig-bgleft-arrow-right"></div>

        <?php } ?>

        <div class="dig_lrf_box dig_ma-box <?php if ($page_type == 2) {
            echo 'dig_pgmdl_2';
        } else {
            echo 'dig_pgmdl_1';
        } ?>" <?php if($page_type==2) echo 'data-placeholder="yes"';?> data-asterisk="<?php echo get_option( 'dig_show_asterisk', 0 ); ?>">
            <div class="header <?php echo $theme; ?>">
                <?php if ($page_type == 1) { ?>
                    <a href="<?php echo $current_url; ?>" <?php if (!empty($backcolor)) {
                        echo 'style="color:' . $backcolor . ';"';
                    } ?>><span><?php _e("BACK", "digits"); ?></span></a>
                <?php } ?>
            </div>
            <?php
            $logo = get_option('digits_logo_image');
            $top = 0;

            if (!empty($logo)) {
                $top = 0;
                ?>
                <div class="logocontainer"><a href="<?php echo get_home_url(); ?>"><img class="logo" src="<?php
                        $imgid = $logo;
                        if (is_numeric($imgid)) {
                            echo wp_get_attachment_url($imgid);
                        } else {
                            echo $imgid;
                        }
                        ?>" alt="Logo" draggable="false"/></a>
                </div>
            <?php } ?>
            <div class="dig_clg_bx" style="opacity: 0;">
                <div class="<?php if (is_rtl()) {
                    echo 'dig_rtl';
                } ?> dig-container dig_ma-box <?php echo $theme; ?> <?php if ($page == 2) {
                    echo 'dig-min-het';
                } ?>">

                    <?php
                    $dig_login_details = digit_get_login_fields();


                    $usernameaccep = $dig_login_details['dig_login_username'];
                    $emailaccep = $dig_login_details['dig_login_email'];
                    $passaccep = $dig_login_details['dig_login_password'];
                    $mobileaccp = $dig_login_details['dig_login_mobilenumber'];
                    $otpaccp = $dig_login_details['dig_login_otp'];

                    $captcha = $dig_login_details['dig_login_captcha'];

                    if ($emailaccep == 1 && $mobileaccp == 1) {
                        $emailaccep = 2;
                    }

                    if ($emailaccep == 2) {
                        $emailmob = __("Email/Mobile Number", "digits");
                    } else if ($mobileaccp == 1) {
                        $emailmob = __("Mobile Number", "digits");
                    } else if ($emailaccep > 0) {
                        $emailmob = __("Email", "digits");
                    } else {
                        $emailmob = __("Username", "digits");
                    }

                    $data_accept = 1;
                    if ($emailaccep == 0 && $usernameaccep == 0 && $mobileaccp != 0) {
                        $data_accept = 2;
                    }


                    $redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : '';


                    if ($page_type == 2) {
                        dig_verify_otp_box();
                    }


                    $dig_cust_forms = apply_filters('dig_hide_forms', 0);

                    if ($dig_cust_forms === 0) {
                        digits_forms();

                    } else {
                        do_action('digits_custom_form');
                    }

                    ?>


                </div>
                <?php if ($page_type == 2) { ?>
                    <div class="dig_login_cancel">
                        <a href="<?php echo $current_url; ?>"
                           class="dig_page_cancel_color" <?php if (!empty($backcolor)) {
                            echo 'style="color:' . $backcolor . ';"';
                        } ?>><span><?php _e("Cancel", "digits"); ?></span></a>
                    </div>
                <?php } ?>
            </div>


            <div class="dig_load_overlay">
                <div class="dig_load_content">
                    <div class="dig_spinner">
                        <div class="dig_double-bounce1"></div>
                        <div class="dig_double-bounce2"></div>
                    </div>
                    <?php
                    ?>

                </div>
            </div>
        </div>
    </div>

    <?php
    digCountry();
    do_action('login_footer');
    ?>
    </body>
    </html>
    <?php
    die();
}


function cust_dig_filter_string($string)
{
    $string = str_replace(array('[t]', '[/t]', '[p]', '[/p]'), '', preg_replace('/\s+/', '', $string));

    return digits_strtolower(dig_filter_string($string));
}

function dig_filter_string($string)
{
    if (empty($string)) {
        return $string;
    }

    $string = preg_replace('/[^\p{L}\p{N}\s]/u', '', $string);

    return esc_attr(trim($string));
}


function dig_update_wpwc_custom_fields($user_id, $meta_key, $value)
{
    if ($meta_key == 'display_name' || $meta_key == 'last_name' || $meta_key == 'first_name') {
        wp_update_user(array('ID' => $user_id, $meta_key => $value));

        return true;
    } else if ($meta_key == 'user_role') {
        $user = get_user_by('ID', $user_id);
        $user->set_role($value);

        return true;
    }

    return false;
}

function update_digp_reg_fields($reg_custom_fields, $user_id)
{

    $digits_data = get_user_meta($user_id,'digits_form_data', true);
    if(empty($digits_data) || !is_array($digits_data)){
        $digits_data = array();
    }
    foreach ($reg_custom_fields as $key => $values) {
        $type = strtolower($values['type']);
        $field_key = cust_dig_filter_string($values['meta_key']);

        $e_value = '';
        if ($type == "captcha") {
            continue;
        }
        $label = cust_dig_filter_string($values['label']);


        if (!isset($_POST['digits_reg_' . $field_key])) {
            continue;
        }
        $e_value = $_POST['digits_reg_' . $field_key];


        $e_value = apply_filters("dig_update_reg_field", $e_value, $type, $values, $user_id);

        $meta_key = sanitize_text_field($values['meta_key']);

        if (dig_update_wpwc_custom_fields($user_id, $meta_key, $e_value)) {
            continue;
        }

        if ($type == "textarea") {
            $e_value = sanitize_textarea_field($e_value);
        } else if ($type == "checkbox") {

            $vals = array();

            foreach ($e_value as $val) {
                $vals[] = sanitize_text_field($val);
            }
            $e_value = $vals;

        } else {
            $e_value = sanitize_text_field($e_value);
        }


        if(!in_array($meta_key,$digits_data) && !empty($e_value))
            $digits_data[$meta_key] = array('label'=>$label,'meta_key'=>$meta_key);

        dig_update_custom_field_data($user_id, $meta_key, $e_value);
    }

    update_user_meta($user_id, 'digits_form_data', $digits_data);

}

function dig_update_custom_field_data($user_id, $meta_key, $value)
{
    update_user_meta($user_id, $meta_key, $value);
}

/*
 * todo: remove checking & retrieving value from label after version 8
 * */
function dig_get_custom_field_data($user_id, $meta_key, $label = null, $single = true)
{


    $value = get_user_meta($user_id, $meta_key, true);

    if ($value == null && $label != null) {
        $value = get_user_meta($user_id, $label, true);
        update_user_meta($user_id, $meta_key, $value);
    }

    return $value;
}


function dig_field_add_date_script()
{

    wp_enqueue_style('datepicker', plugins_url('/assets/css/datepicker.min.css', __FILE__), array(), null, 'all');
    wp_enqueue_script('datepicker', plugins_url('/assets/js/datepicker.min.js', __FILE__), array('jquery'), null);
    wp_enqueue_script('datepicker-lang', plugins_url('/assets/js/i18n/datepicker.en.min.js', __FILE__), array('jquery'), null);

}

add_action('dig_field_type_date', 'dig_field_add_date_script');

function validate_digp_reg_fields($reg_custom_fields, $error, $captcha = true)
{
    if (!session_id() || session_status() == PHP_SESSION_NONE) {
        session_start();
    }


    if (session_id() == '') {
        session_start();
    }


    if(empty($_POST['digits_reg_name']) && !empty($_POST['billing_first_name'])){
        $_POST['digits_reg_name'] = $_POST['billing_first_name'];
    }

    if(empty($_POST['digits_reg_lastname']) && !empty($_POST['billing_last_name'])){
        $_POST['digits_reg_lastname'] = $_POST['billing_last_name'];
    }


    if (current_user_can('edit_user') || current_user_can('administrator')) {
        return $error;
    }

    if (empty($reg_custom_fields)) {
        return $error;
    }

    $digits_fields = digits_get_all_custom_fields();

    foreach ($reg_custom_fields as $label => $values) {


        $type = strtolower($values['type']);

        if (!array_key_exists($type, $digits_fields)) {
            continue;
        }


        $e_value = null;

        $custom_class = null;
        $lb_class = null;
        $label = cust_dig_filter_string($label);

        $required = $values['required'];
        $meta_key = cust_dig_filter_string($values['meta_key']);

        $post_index = 'digits_reg_' . $meta_key;

        if (dig_custom_hide_to_loggedin($type, $values['meta_key'])) {
            continue;
        }


        $values['required'] = dig_check_required($values);


        $e_value = apply_filters("dig_validate_reg_field", $e_value, $type, $values, $post_index);
        if (is_wp_error($e_value)) {

            return $e_value;
        }
        if (!$e_value) {
            $e_value = $_POST[$post_index];
        }

        if($type=='dropdown' && $e_value==-1){
            $e_value = '';
        }

        if (!is_array($e_value)) {
            $e_value = trim($e_value);
        }
        if ($required == 1 && empty($e_value)) {

            if ($type == "captcha" && !$captcha) {
                continue;
            }

            $continue = apply_filters("dig_require_field", false, $values);
            if ($continue) {
                continue;
            }

            $error->add("incompletedetails", __('Please fill all the required details!', 'digits'));
            break;
        } else {


            $options = dig_sanitize_options($values['options']);
            if ($type == "captcha") {
                $ses = filter_var($_POST['dig_captcha_ses'], FILTER_SANITIZE_NUMBER_FLOAT);
                if ($e_value != $_SESSION['dig_captcha' . $ses] && $captcha) {
                    $error->add("captcha", __('Please enter a valid Captcha!', 'digits'));
                } else if (isset($_SESSION['dig_captcha' . $ses])) {
                    unset($_SESSION['dig_captcha' . $ses]);
                }
            } else if ($type == "tac") {
                if ($e_value != 1 && !$e_value) {
                    $error->add("tac", __('Please accept terms and condition!', 'digits'));
                }
            } else {

                if ($type == 'user_role') {
                    $type = 'dropdown';
                }
                if ($type == "dropdown" || $type == "radio") {


                    if ($required == 0 && empty($e_value)) {
                        continue;
                    } else if (!in_array($e_value, $options) && !in_array($e_value, $values['options'])) {
                        $error->add("invalidValue", __('Please select a valid option!', 'digits'));
                    }


                } else if ($type == "checkbox") {

                    if ($required == 0 && empty($e_value)) {
                        continue;
                    }
                    if (!is_array($e_value)) {
                        $error->add("invalidValue", __('Please select a valid option!', 'digits'));
                    }

                    foreach ($e_value as $ev) {
                        if (!in_array($ev, $options) && !in_array($ev, $values['options'])) {
                            $error->add("invalidValue", __('Please select a valid option!', 'digits'));
                        }
                    }
                }
            }
        }
    }

    return $error;
}


function dig_custom_show_label($type)
{

    if ($type == 'tac') {
        return false;
    }

    return true;
}

function dig_custom_hide_to_loggedin($type, $meta_key)
{

    if (!is_user_logged_in()) {
        return false;
    }

    $hidden_types = array('captcha', 'tac', 'user_role');
    if (in_array($type, $hidden_types)) {
        $hide = true;
    } else {
        $hide = false;
    }
    $hide = apply_filters('dig_show_field_to_loggedin_user', $hide, $type, $meta_key);

    return $hide;
}


function dig_show_login_captcha($login_page = 1, $bgtype = null, $user_id = 0)
{
    if (isset($_POST['digits_reg_logincaptcha'])) {
        unset($_POST['digits_reg_logincaptcha']);
    }
    dig_show_fields(array(
        'Captcha' => array(
            'label' => __('Captcha', 'digits'),
            'type' => 'captcha',
            'required' => '1',
            'meta_key' => 'login_captcha',
            'custom_class' => 'login_captcha'
        )
    ), 0, $login_page, $bgtype, $user_id);
}

/*
 * 1-> digits
 * 2-> WC
 * 3-> WP
 */
function show_digp_reg_fields($login_page = 1, $bgtype = null, $user_id = 0, $reg_custom_fields = null)
{
    if($reg_custom_fields==null){
        $reg_custom_fields = stripslashes(base64_decode(get_option("dig_reg_custom_field_data", "e30=")));
        $reg_custom_fields = json_decode($reg_custom_fields, true);
    }
    $show_asterisk = get_option('dig_show_asterisk', 0);
    dig_show_fields($reg_custom_fields, $show_asterisk, $login_page, $bgtype, $user_id);
}


function dig_check_required($values)
{
    if (function_exists('is_checkout')) {
        if (is_checkout()) {
            if (($values['meta_key'] == 'last_name' && !empty($_REQUEST['billing_first_name'])) ||
            ($values['meta_key'] == 'first_name' && !empty($_REQUEST['billing_last_name']))
            ) {
                return 0;
            }
        }
    }

    return $values['required'];

}

function dig_hide_custom_field($values,$page_type)
{
    if($page_type==1){
        return false;
    }

    if (function_exists('is_checkout')) {
        if (is_checkout()) {
            $meta_key = $values['meta_key'];
            $hidden_types = array('first_name', 'last_name');
            if (in_array($meta_key, $hidden_types)) {
                return true;
            }
        }
    }

    return false;
}

function dig_show_fields($reg_custom_fields, $show_asterisk, $login_page = 1, $bgtype = null, $user_id = 0)
{

    if (empty($reg_custom_fields)) {
        return;
    }

    $digits_fields = digits_get_all_custom_fields();

    foreach ($reg_custom_fields as $key => $values) {
        $type = strtolower($values['type']);

        if (!array_key_exists($type, $digits_fields)) {
            continue;
        }


        $values['required'] = dig_check_required($values);
        $asterisk = ($show_asterisk == 1 && $values['required'] == 1) ? ' *' : '';

        $custom_class = null;
        $lb_class = null;
        $label = cust_dig_filter_string($values['label']);

        $meta_key = cust_dig_filter_string($values['meta_key']);


        do_action('dig_field_type_' . $type);


        if (dig_hide_custom_field($values,$login_page)) {
            continue;
        }
        if (is_user_logged_in()) {
            if (dig_custom_hide_to_loggedin($type, $values['meta_key'])) {
                continue;
            }
        }


        $wcClass = '';

        if ($login_page == 2) {
            $wcClass = 'woocommerce-Input woocommerce-Input--text input-text';
        }else if($login_page == 3){
            $wcClass = 'regular-text';
        }
        if (!empty($values['custom_class'])) {
            $custom_class = 'class="' . dig_filter_string($values['custom_class']) . ' ' . $wcClass . '"';
        } else {
            $custom_class = 'class="' . $wcClass . '"';
        }


        $e_value = false;

        if (isset($_POST['digits_reg_' . $meta_key])) {
            $e_value = cust_dig_filter_string($_POST['digits_reg_' . $meta_key]);
        }

        $extra_style = '';
        $user_role = 0;
        if ($type == 'user_role') {
            $type = 'dropdown';
            $user_role = 1;
        }
        if ($type == "dropdown") {
            $extra_style = '';
        }


        $rand = rand();
        $values['label'] = apply_filters('wpml_translate_single_string', $values['label'], 'digits', $values['label']);
        if ($login_page == 1) {
            $dg = 'dg_min_capt';
            if ($type != "captcha") {
                $dg = '';
            }
            echo '<div id="dig_cs_' . cust_dig_filter_string($meta_key) . '" class="minput ' . $dg . ' dig-custom-field dig-custom-field-type-' . $type . '" ' . $extra_style . '><div class="minput_inner">';


        } else if ($login_page == 2) {
            echo '<div id="dig_cs_' . cust_dig_filter_string($meta_key) . '" class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide dig-custom-field dig-custom-field-type-' . $type . '" ' . $extra_style . '>';
            echo '<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">';
            if (dig_custom_show_label($type)) {
                ?>
                <label for="digits_reg_<?php echo $meta_key . $rand; ?>"><?php _e($values['label'], "digits");
                    if ($values['required'] == 1) {
                        echo '<span class="required">*</span>';
                    } ?></label>
                <?php
            }
        } else if ($login_page == 3) {
            echo '<tr id="dig_cs_' . cust_dig_filter_string($meta_key) . '" class="dig-custom-field dig-custom-field-type-' . $type . '">';
            ?>
            <th>
                <?php
                if (dig_custom_show_label($type)) {
                    ?>
                    <label for="digits_reg_<?php echo $meta_key . $rand; ?>"><?php _e($values['label'], "digits"); ?></label>
                <?php } ?>
            </th>
            <?php
            echo '<td>';
            $e_value = dig_get_custom_field_data($user_id, sanitize_text_field($values['meta_key']), sanitize_text_field($label), true);

        }

        if ($type == "captcha") {
            if ($login_page == 3) {
                continue;
            }
            show_digcaptcha();
        }


        $return = apply_filters("before_dig_show_fields", false, $custom_class, $values, $type, $e_value, $meta_key, $user_id, $login_page);
        if (!$return) {

            if ($type == "textarea") {

                ?>
                <div class="digits-input-wrapper">
                <textarea type="<?php echo $type; ?>" name="digits_reg_<?php echo $meta_key; ?>"
                          id="digits_reg_<?php echo $meta_key . $rand; ?>" <?php echo $custom_class; ?> <?php if ($values['required'] == 1) {
                    echo "required";
                } ?>
                      rows="2"><?php if ($e_value) {
                        echo $e_value;
                    } ?></textarea>
                    </div>
                <?php
            } else if ($type == "dropdown" || $type == "checkbox" || $type == "radio") {
                if ($type == "dropdown"){
                    digits_select2();
                }
                if ($type == "dropdown" && $user_role == 1) {
                    global $wp_roles;
                    $roles = $wp_roles->roles;
                    $dropdown_display_label = __($values['label'], 'digits');

                    ?>
                    <select name="digits_reg_<?php echo $meta_key; ?>" <?php echo $custom_class; ?> <?php if ($values['required'] == 1) {
                        echo "data-req='1'";
                    } ?>>
                        <?php

                        if ($values['required'] == 1) $dropdown_display_label = $dropdown_display_label.' *';

                        if (empty($e_value)) {
                            $selected = "selected";
                        }

                        $drop_required = '';
                        if ($values['required'] === 1) {
                            $drop_required = 'disabled';
                        }


                        if($login_page!=1){
                            $dropdown_display_label = esc_attr__('(select)','digits');
                        }
                        echo '<option value="-1" ' . $drop_required . ' ' . $selected . ' data-display="'.esc_attr($dropdown_display_label).'">' . __('Nothing','digits') . '</option>';

                        foreach ($values['options'] as $option) {
                            $selected = "";
                            $san_option = dig_filter_string($option);
                            if ($e_value == $san_option) {
                                $selected = "selected";
                            }


                            echo "<option " . $selected . " value='" . esc_html($option) . "'>" . __($roles[$option]['name'], 'digits') . "</option>";
                        }
                        ?>
                    </select>

                    <?php

                } else if ($type == "dropdown") {

                    $drop_required = '';
                    if ($values['required'] === 1) {
                        $drop_required = 'disabled';
                    }

                    $dropdown_display_label = __($values['label'], 'digits');

                    ?>
                    <select name="digits_reg_<?php echo $meta_key; ?>" <?php echo $custom_class; ?> <?php if ($values['required'] == 1) {
                        echo "data-req='1'";
                    } ?>>
                        <?php

                        if ($values['required'] == 1) $dropdown_display_label = $dropdown_display_label.' *';

                        $selected = empty($e_value) ? 'selected' : '';

                        if($login_page!=1){
                            $dropdown_display_label = esc_attr__('(select)','digits');
                        }

                        echo '<option value="-1" ' . $drop_required . ' ' . $selected . ' data-display="'.esc_attr($dropdown_display_label).'">' . __('Nothing','digits') . '</option>';


                        foreach ($values['options'] as $option_display) {
                            $selected = "";
                            $option = dig_filter_string($option_display);
                            if ($e_value == $option) {
                                $selected = "selected";
                            }

                            $option_display = apply_filters('wpml_translate_single_string', $option_display, 'digits', $option_display);

                            echo "<option " . $selected . " value='" . $option . "'>" . __($option_display, 'digits') . "</option>";
                        }
                        ?>
                    </select>

                    <?php
                } else {

                    $re = '';
                    if ($values['required'] == 1) {
                        $re = "data-req=1";
                    }

                    echo "<div class='dig_opt_mult_con'>";

                    $ar = "";
                    if ($type == 'checkbox') {
                        $ar = "[]";

                    }

                    foreach ($values['options'] as $raw_option) {
                        $lb_class = "dig_opt_mult_lab";
                        $option = dig_filter_string($raw_option);

                        $selected = "";
                        $selected_class = "";
                        if ($e_value == $option ||
                        ($type == 'checkbox' &&
                        is_array($e_value) && (in_array($option, $e_value) || in_array($raw_option, $e_value))
                        )) {
                            $selected = "checked";
                            $selected_class = "class='selected'";
                        }


                        $option_display = apply_filters('wpml_translate_single_string', $raw_option, 'digits', $raw_option);

                        $option_value = $raw_option;

                        echo '<div class="dig_opt_mult" >
                            <label ' . $selected_class . ' for="digits_reg_for_' . $meta_key . '_' . $option . $rand . '">
                            <div class="dig_input_wrapper">
                            <input ' . $re . ' name="digits_reg_' . $meta_key . $ar . '" ' . $custom_class . ' id="digits_reg_for_' . $meta_key . '_' . $option . $rand . '" type="' . $type . '" value="' . esc_attr($option_value) . '" ' . $selected . '>
                            <div>' . $option_display . '</div>
                            </div></label></div>';

                    }
                    echo "</div>";


                }
            } else if ($type == 'tac') {
                $re = '';
                if ($values['required'] == 1) {
                    $re = "data-req=1";
                }
                echo "<div class='dig_opt_mult_con dig_opt_mult_con_tac'><div class=\"dig_opt_mult\" >";

                $option = $values['label'];
                $tac = $option;

                $defaultValues = array('[t]', '[/t]', '[p]', '[/p]');

                $links = array(
                    '<a href="' . $values['tac_link'] . '" target="_blank">',
                    '</a>',
                    '<a href="' . $values['tac_privacy_link'] . '" target="_blank">',
                    '</a>'
                );

                $tac = str_replace($defaultValues, $links, $tac);


                echo '<label for="digits_reg_for_' . $option . $rand . '"><div class="dig_input_wrapper">
                <input ' . $re . ' name="digits_reg_' . $meta_key . '" ' . $custom_class . ' id="digits_reg_for_' . $option . $rand . '" type="checkbox" value="1"><div>' . $tac.'</div>';
                echo '</div></label>';


                echo "</div></div>";
            } else {

                $inp_type = $type;
                if (in_array($inp_type, array('captcha', 'date'))) {
                    $inp_type = 'text';
                }

                ?>
                <div class="digits-input-wrapper">
                <input type="<?php echo $inp_type; ?>"
                       name="digits_reg_<?php echo $meta_key; ?>"
                       id="digits_reg_<?php echo $meta_key . $rand; ?>" <?php echo $custom_class; ?> <?php if ($values['required'] == 1) {
                    echo "required";
                } ?>
                       value="<?php if ($e_value) {
                           echo $e_value;
                       } ?>"
                />
                </div>
                <?php

            }
        }
        ?>

        <?php
        if ($login_page == 1) {
            if (dig_custom_show_label($type)) {
                $str = apply_filters("before_dig_custom_show_label", $type);
                ?>
                <label class="field_label" <?php echo $str; ?> <?php if (!empty($lb_class)) {
                    echo 'class="' . $lb_class . '"';
                } ?>><?php _e($values['label'], "digits");
                    echo $asterisk; ?></label>
                <?php
            }
            if ($type != "dropdown") {
                $str = apply_filters("before_dig_custom_show_line", $type);
                echo '<span ' . $str . ' class="' . $bgtype . '"></span>';
            }
            echo '</div>';
            echo '</p></div>';
        } else if ($login_page == 2) {
            echo '</div>';
        } else if ($login_page == 3) {
            echo '</td></tr>';
        }
    }
}

function show_digcaptcha()
{
    $r = mt_rand();
    $cap = plugins_url('/captcha/captcha.php', __FILE__);
    ?>
    <input type="hidden" class="dig_captcha_ses" name="dig_captcha_ses" value="<?php echo $r; ?>"/>
    <img src="<?php echo $cap . '?r=' . $r; ?>" cap_src="<?php echo $cap; ?>" class="dig_captcha"
         draggable="false"/>
    <?php
}


add_action('init', 'digits_login', 100);


function dig_pcd_act()
{
    if (!wp_next_scheduled('dig_pcd_act_chk')) {
        wp_schedule_event(time(), 'daily', 'dig_pcd_act_chk');
    }
}

add_action('init', 'dig_init_pcver');
function dig_init_pcver()
{
    if (!wp_next_scheduled('dig_pcd_act_chk')) {
        wp_schedule_event(time(), 'daily', 'dig_pcd_act_chk');
    }
    //digits_show_reg_check_disabled(false);
}


add_action('dig_pcd_act_chk', 'dig_pcd_act_chk_req');


function dig_pcd_act_chk_req()
{
    dig_pcd_act_chk_req_cd(false);

}

function dig_pcd_act_chk_req_cd($dec = false)
{

    if (!function_exists('get_plugin_data')) {
        /** @noinspection PhpIncludeInspection */
        require_once(ABSPATH . '/wp-admin/includes/plugin.php');
    }
    $dec = false;// remove it
    $dpc = 'dig_purchasecode';
    $dicp = dig_get_option($dpc);


    $plugin_version = digits_version();

    $type = 'dig_license_type';

    $list = apply_filters('digits_addon', array());
    $params = array(
        'json' => 1,
        'code' => $dicp,
        'request_site' => dig_network_home_url(),
        'slug' => 'digits',
        $type => dig_get_option('dig_license_type', 1),
        'version' => $plugin_version,
        'schedule' => 1,
        'addons' => implode(",", $list)
    );

    if ($dec) {
        $params['unregister'] = 1;
    }
    $u = 'https://bridge.unitedover.com/updates/verify.php';
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $u);
    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, $params);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($c);


    $http_status = curl_getinfo($c, CURLINFO_HTTP_CODE);
    $un = 'dig_unr';
    $ds = 'dig_dsb';

    if (!curl_errno($c)) {

        $pcf = 'dig_purchasefail';

        if ($http_status == 200) {

            if ($dec) {
                return;
            }

            $response = json_decode($result);
            $result = $response->code;

            if ($result == -99) {
                update_site_option($ds, 1);
            } else if ($result != 1) {
                $check = dig_get_option($pcf, 2);
                if ($check == 2) {
                    delete_site_option($dpc);
                    delete_site_option($pcf);
                    delete_site_option($type);
                } else {
                    update_site_option($pcf, 2);
                }

                $t = dig_get_option($un, -1);

                if ($t == -1) {
                    update_site_option($un, time());
                }

            } else if ($result == 1) {
                delete_site_option($pcf);
                delete_site_option($un);
                delete_site_option($ds);

                if (isset($response->type)) {

                    if ($response->type != -1) {
                        update_site_option($type, $response->type);
                    }
                }
            }
        }
    }


    curl_close($c);

    if (empty($dicp)) {
        $time = get_option($un, -1);
        if ($time == -1) {
            $time = time();
            update_site_option($un, $time);
        }

        if (!empty($time)) {
            $c = 360 * 3600;
            $time = $time + $c;
            $current_time = time();
            $t = $time - $current_time;
            if ($t < 0 || $t > $c) {
                update_site_option($ds, 1);
            }
        }
    }

}


register_deactivation_hook(__FILE__, 'dig_pcd_decact');

function dig_pcd_decact()
{
    wp_clear_scheduled_hook('dig_pcd_act_chk');
    dig_pcd_act_chk_req_cd(true);
}


function dig_getOtpTime()
{
    return min(max(get_option('dig_mob_otp_resend_time', 30), 20), 3600);
}

function dig_useStrongPass()
{
    return get_option('dig_use_strongpass', 1);
}


function digits_get_all_custom_fields()
{
    $fields = digits_customfieldsTypeList();
    $fields = apply_filters('dig_all_custom_fields', $fields);

    return $fields;
}

/*
 * 0-> Disabled
 * 1-> Optional
 * 2-> Required
 */
function digit_default_login_fields()
{
    return
        array(
            'dig_login_username' => array('name' => __('Username', 'digits')),
            'dig_login_email' => array('name' => __('Email', 'digits')),
            'dig_login_mobilenumber' => array('name' => __('Mobile Number', 'digits')),
            'dig_login_otp' => array('name' => __('OTP', 'digits'), 'opt' => 1),
            'dig_login_password' => array(
                'name' => __('Password', 'digits'),
                'ondis_disable' => 'dig_login_email',
                'opt' => 1
            ),
            'dig_login_captcha' => array('name' => __('Captcha', 'digits'), 'opt' => 1),
        );

}


function digit_get_login_fields()
{
    $dig_login_fields = get_option('dig_login_fields', false);
    $dig_remember_me = get_option('dig_login_rememberme', 1);
    if ($dig_login_fields) {
        if (!isset($dig_login_fields['dig_login_captcha'])) {
            $dig_login_fields['dig_login_captcha'] = 0;
        }
        if (!isset($dig_login_fields['dig_login_username'])) {
            $dig_login_fields['dig_login_username'] = 1;
        }

        $dig_login_fields['dig_login_rememberme'] = $dig_remember_me;


    } else {
        $dig_login_fields = array(
            'dig_login_email' => get_option("digemailaccep", 1),
            'dig_login_username' => 1,
            'dig_login_mobilenumber' => 1,
            'dig_login_otp' => 1,
            'dig_login_password' => get_option("digpassaccep", 1),
            'dig_login_captcha' => 0,
            'dig_login_rememberme' => $dig_remember_me,
        );
    }
    $dig_login_fields = apply_filters('digits_login_fields', $dig_login_fields, null);
    return $dig_login_fields;
}


function digit_default_reg_fields()
{
    return array(
        'dig_reg_name' => array('name' => __('Name', 'digits'), 'id' => 'name'),
        'dig_reg_uname' => array('name' => __('Username', 'digits'), 'id' => 'username'),
        'dig_reg_email' => array('name' => __('Email', 'digits'), 'id' => 'email'),
        'dig_reg_mobilenumber' => array('name' => __('Mobile Number', 'digits'), 'id' => 'mobilenumber'),
        'dig_reg_password' => array('name' => __('Password', 'digits'), 'id' => 'password'),

    );
}

function digit_get_reg_fields($default = false)
{
    $dig_reg_fields = get_option('dig_reg_fields', false);
    if ($dig_reg_fields && !$default) {
        return $dig_reg_fields;
    } else {
        return array(
            'dig_reg_name' => 1,
            'dig_reg_uname' => 0,
            'dig_reg_email' => get_option("digemailaccep", 1),
            'dig_reg_mobilenumber' => 1,
            'dig_reg_password' => get_option("digpassaccep", 1)
        );
    }
}

function dig_requireCustomToString($value)
{
    switch ($value) {
        case 0:
            return __("Optional", "digits");
        case 1:
            return __("Required", "digits");
        default:
            return null;
    }
}
