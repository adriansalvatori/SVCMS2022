<?php

if (!defined('ABSPATH')) {
    exit;
}


add_action('wp_enqueue_scripts', 'digits_add_style');
add_action('admin_enqueue_scripts', 'digits_add_style');
add_action('login_enqueue_scripts', 'digits_add_style');
function digits_add_style()
{


    wp_register_style('digits-style', get_digits_asset_uri('/assets/css/main.min.css'), array(), digits_version(), 'all');


    wp_enqueue_style('digits-login-style', get_digits_asset_uri('/assets/css/login.min.css'), array(), digits_version(), 'all');
    wp_enqueue_style('digits-style');

    if (is_rtl()) {
        $rtl_wc = "
                #woocommerce-order-data .address p:nth-child(3) a,.woocommerce-customer-details--phone{
                    text-align:right;
                    }";
        wp_add_inline_style('digits-style', $rtl_wc);
    }


}


function digits_admin_add_scripts()
{
    digits_add_scripts();
}

add_action('admin_init', 'digits_admin_add_scripts');

add_action('wp_enqueue_scripts', 'digits_add_scripts', 9999);
add_action('login_enqueue_scripts', 'digits_add_scripts');


function digits_add_scripts($usercode = 0)
{

    $wp_login_inte = get_option("dig_wp_login_inte", 0);

    if ($GLOBALS['pagenow'] === 'wp-login.php' && $wp_login_inte == 0) {
        return;
    }

    /*    digits_select2();*/
    if ($usercode == 0 || empty($usercode)) {
        $usercode = getUserCountryCode();
    }

    wp_register_script('scrollTo', get_digits_asset_uri('/assets/js/scrollTo.js'), array('jquery'), digits_version(), true);

    wp_register_script('digits-main-script', get_digits_asset_uri('/assets/js/main.min.js'), dig_deps_scripts(), digits_version(), true);

    $current_url = "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $current_url = str_replace("login=true", "", $current_url);

    $t = get_option("digits_loginred");
    if (!empty($t)) {
        $current_url = $t;
    }


    $app = get_option('digit_api');
    $appid = "";
    if ($app !== false) {
        $appid = $app['appid'];
    }


    $dig_reg_details = digit_get_reg_fields();

    $dig_login_details = digit_get_login_fields();


    $nameaccep = $dig_reg_details['dig_reg_name'];
    $usernameaccep = $dig_reg_details['dig_reg_uname'];
    $emailaccep = $dig_reg_details['dig_reg_email'];
    $passaccep = $dig_reg_details['dig_reg_password'];
    $mobileaccp = $dig_reg_details['dig_reg_mobilenumber'];

    $emailormobile = __("Email/Mobile Number", "digits");


    $firebase = 0;
    if (dig_is_gatewayEnabled(13)) {
        $firebase = 1;
    }

    $verify_c = 0;

    if (!current_user_can('edit_user') && !current_user_can('administrator')) {
        $verify_c = 1;
    }

    $hide_countrycode = get_option("dig_hide_countrycode", 0);

    if (is_admin()) {
        $hide_countrycode = 0;
    }

    $jsData = array(
        'dig_hide_ccode' => $hide_countrycode,
        'loginwithotp' => __('Login With OTP', 'digits'),
        'dig_sortorder' => get_option("dig_sortorder"),
        'dig_dsb' => get_option('dig_dsb', -1),
        "Passwordsdonotmatch" => __("Passwords do not match!", "digits"),
        'fillAllDetails' => __('Please fill all the required details.', 'digits'),
        'accepttac' => __('Please accept terms & conditions.', 'digits'),
        'resendOtpTime' => dig_getOtpTime(),
        'useStrongPasswordString' => __('Please enter a stronger password.', 'digits'),
        'strong_pass' => dig_useStrongPass(),
        'firebase' => $firebase,
        'forgot_pass' => get_option('digforgotpass', 1),
        'mail_accept' => $dig_reg_details['dig_reg_email'],
        'pass_accept' => $dig_reg_details['dig_reg_password'],
        'mobile_accept' => $dig_reg_details['dig_reg_mobilenumber'],
        'login_uname_accept' => $dig_login_details['dig_login_username'],
        'login_mobile_accept' => $dig_login_details['dig_login_mobilenumber'],
        'login_mail_accept' => $dig_login_details['dig_login_email'],
        'login_otp_accept' => $dig_login_details['dig_login_otp'],
        'captcha_accept' => $dig_login_details['dig_login_captcha'],
        'ajax_url' => admin_url('admin-ajax.php'),
        'appId' => $appid,
        'uri' => $current_url,
        'state' => wp_create_nonce('crsf-otp'),
        'uccode' => $usercode,
        'nonce' => wp_create_nonce('dig_form'),
        'pleasesignupbeforelogginin' => __("Please signup before logging in.", 'digits'),
        'invalidapicredentials' => __("Invalid API credentials!", 'digits'),
        'invalidlogindetails' => __("Invalid login credentials!", 'digits'),
        'emailormobile' => $emailormobile,
        "RegisterWithPassword" => __("Register With Password", "digits"),
        "Invaliddetails" => __("Invalid details!", "digits"),
        'invalidpassword' => __("Invalid Password", "digits"),
        "InvalidMobileNumber" => __("Invalid Mobile Number!", "digits"),
        "InvalidEmail" => __("Invalid Email!", "digits"),
        'invalidcountrycode' => __("At the moment, we do not allow users from your country", "digits"),
        "Mobilenumbernotfound" => __("Mobile number not found!", "digits"),
        "MobileNumberalreadyinuse" => __("Mobile Number already in use!", "digits"),
        "MobileNumber" => __("Mobile Number", "digits"),
        "InvalidOTP" => __("Invalid OTP!", "digits"),
        "Pleasetryagain" => __("Please try again", "digits"),
        "ErrorPleasetryagainlater" => __("Error! Please try again later", "digits"),
        "UsernameMobileno" => __("Username/Mobile Number", "digits"),
        "OTP" => __("OTP", "digits"),
        "resendOTP" => __("Resend OTP", "digits"),
        "verify_mobile" => $verify_c,
        'otp_l' => get_option("dig_otp_size", 6),
        "Password" => __("Password", "digits"),
        "ConfirmPassword" => __("Confirm Password", "digits"),
        "pleaseentermobormail" => __("Please enter your Mobile Number/Email", "digits"),
        "eitherentermoborusepass" => __("Either enter your Mobile Number or use Password!", "digits"),
        "submit" => __("Submit", "digits"),
        "overwriteWcBillShipMob" => get_option('dig_bill_ship_fields', 0),
        "signupwithpassword" => __('SIGN UP WITH PASSWORD', 'digits'),
        "signupwithotp" => __('SIGN UP WITH OTP', 'digits'),
        "verifymobilenumber" => __('Verify Mobile Number', 'digits'),
        "signup" => __('SIGN UP', 'digits'),
        "or" => __('OR', 'digits'),
        "email" => __('Email', 'digits'),
        "optional" => __('Optional', 'digits'),
        "error" => __('Error', 'digits'),
        "mob_verify_checkout" => get_option('dig_mob_ver_chk_fields', 1),
        'SubmitOTP' => __('Submit OTP', 'digits'),
        'Registrationisdisabled' => __('Registration is disabled', 'digits'),
        'forgotPasswordisdisabled' => __('Forgot Password is disabled', 'digits'),
        'Thisfeaturesonlyworkswithmobilenumber' => __('This features only works with mobile number', 'digits'),
        'codevalidproceedcheckout' => __('Code is valid, please proceed with checkout', 'digits'),
        'guest_checkout_verification' => get_option('enable_guest_checkout_verification', 1),
        'billing_phone_verification' => get_option('enable_billing_phone_verification', 1),
    );
    wp_localize_script('digits-main-script', 'dig_mdet', $jsData);


    wp_register_script('digits-login-script', get_digits_asset_uri('/assets/js/login.min.js'), dig_deps_scripts(), digits_version(), true);


    $current_url = "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $current_url = str_replace("login=true", "", $current_url);

    $t = get_option("digits_loginred");
    if (!empty($t)) {
        $current_url = $t;
    }

    $dig_login_details = digit_get_login_fields();


    $jsData = array(
        "direction" => is_rtl() ? 'rtl' : 'ltr',
        'dig_mobile_no_formatting' => get_option("dig_mobile_no_formatting", 1),
        'dig_hide_ccode' => $hide_countrycode,
        'dig_sortorder' => get_option("dig_sortorder"),
        'dig_dsb' => get_option('dig_dsb', -1),
        'show_asterisk' => get_option('dig_show_asterisk', 0),
        'login_mobile_accept' => $dig_login_details['dig_login_mobilenumber'],
        'login_mail_accept' => $dig_login_details['dig_login_email'],
        'login_otp_accept' => $dig_login_details['dig_login_otp'],
        'captcha_accept' => $dig_login_details['dig_login_captcha'],
        "Passwordsdonotmatch" => __("Passwords do not match!", "digits"),
        'fillAllDetails' => __('Please fill all the required details.', 'digits'),
        'accepttac' => __('Please accept terms & conditions.', 'digits'),
        'resendOtpTime' => dig_getOtpTime(),
        'useStrongPasswordString' => __('Please enter a stronger password.', 'digits'),
        'strong_pass' => dig_useStrongPass(),
        'firebase' => $firebase,
        'mail_accept' => $dig_reg_details['dig_reg_email'],
        'pass_accept' => $dig_reg_details['dig_reg_password'],
        'mobile_accept' => $dig_reg_details['dig_reg_mobilenumber'],
        'username_accept' => $dig_reg_details['dig_reg_uname'],
        'ajax_url' => admin_url('admin-ajax.php'),
        'appId' => $appid,
        'uri' => $current_url,
        'state' => wp_create_nonce('crsf-otp'),
        'left' => 0,
        'verify_mobile' => 0,
        'Registrationisdisabled' => __('Registration is disabled', 'digits'),
        'forgotPasswordisdisabled' => __('Forgot Password is disabled', 'digits'),
        'invalidlogindetails' => __("Invalid login credentials!", 'digits'),
        'invalidapicredentials' => __("Invalid API credentials!", 'digits'),
        'pleasesignupbeforelogginin' => __("Please signup before logging in.", 'digits'),
        'pleasetryagain' => __("Please try again!", 'digits'),
        'invalidcountrycode' => __("At the moment, we do not allow users from your country", "digits"),
        "Mobilenumbernotfound" => __("Mobile number not found!", "digits"),
        "MobileNumberalreadyinuse" => __("Mobile Number already in use!", "digits"),
        "Error" => __("Error", "digits"),
        'Thisfeaturesonlyworkswithmobilenumber' => __('This features only works with mobile number', 'digits'),
        "InvalidOTP" => __("Invalid OTP!", "digits"),
        "ErrorPleasetryagainlater" => __("Error! Please try again later", "digits"),
        "Passworddoesnotmatchtheconfirmpassword" => __("Password does not match the confirm password!", "digits"),
        "Invaliddetails" => __("Invalid details!", "digits"),
        "InvalidEmail" => __("Invalid Email!", "digits"),
        "InvalidMobileNumber" => __("Invalid Mobile Number!", "digits"),
        "eitherenterpassormob" => __("Either enter your mobile number or click on sign up with password", "digits"),
        "login" => __("Log In", "digits"),
        "signup" => __("Sign Up", "digits"),
        "ForgotPassword" => __("Forgot Password", "digits"),
        "Email" => __("Email", "digits"),
        "Mobileno" => __("Mobile Number", "digits"),
        "ohsnap" => __("Oh Snap!", "digits"),
        "yay" => __("Yay!", "digits"),
        "notice" => __("Notice!", "digits"),
        "submit" => __("Submit", "digits"),
        'SubmitOTP' => __('Submit OTP', 'digits'),
        'required' => __('Required', 'digits'),
        'select' => __('(select)', 'digits'),
        'login_success' => __('Login Successful, Redirecting..', 'digits'),
        'login_reg_success_msg' => get_option('login_reg_success_msg', 1),
        'nonce' => wp_create_nonce('dig_form'),
    );
    wp_localize_script('digits-login-script', 'dig_log_obj', $jsData);


    wp_enqueue_script('jquery');
    if (dig_is_gatewayEnabled(1)) {
        wp_enqueue_script('account-kit');
        wp_add_inline_script('account-kit', iniAccInit());
    }

    wp_enqueue_script('scrollTo');
    wp_enqueue_script('digits-main-script');
    wp_enqueue_script('digits-login-script');
    wp_enqueue_style('google-roboto-regular', dig_fonts());

}


function digits_select2()
{
    wp_register_style('select2', get_digits_asset_uri('/assets/css/select2.min.css'), array(), null, false);
    wp_enqueue_style('select2');
    wp_register_script('select2-full', get_digits_asset_uri('/assets/js/select2.min.js'), array('jquery'), null, false);
    wp_enqueue_script('select2-full');

}


function dig_deps_scripts()
{


    wp_register_script('libphonenumber-mobile', 'https://unpkg.com/libphonenumber-js@1.7.16/bundle/libphonenumber-max.js', array('jquery'), null, true);
    wp_enqueue_script('libphonenumber-mobile');

    $re = array('jquery', 'scrollTo', 'libphonenumber-mobile');

    if (dig_useStrongPass()) {
        array_push($re, 'password-strength-meter');
    }
    global $pagenow;
    $profile_page = array('user-edit.php', 'profile.php');
    if (!is_admin() || in_array($pagenow, $profile_page)) {
        if (dig_is_gatewayEnabled(13)) {
            digits_reg_firebase_script();
            array_push($re, 'firebase-auth');
        }
        if (dig_is_gatewayEnabled(1)) {
            array_push($re, 'account-kit');
        }
    }

    return $re;
}


add_action('wp_footer', 'dig_wc_login_hide_pass_field');
function dig_wc_login_hide_pass_field()
{
    $dig_login_details = digit_get_login_fields();

    if (empty($dig_login_details['dig_login_password'])) {
        ?>
        <script>var password = document.querySelector("#password");
            if (password != null) {
                password.parentElement.remove();
            }</script>
        <?php
    }
}
