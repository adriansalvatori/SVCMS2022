<?php

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('df-form', 'df_digits_form');
add_shortcode('df-form-login', 'df_digits_form_login');
add_shortcode('df-form-signup', 'df_digits_form_signup');
add_shortcode('df-form-forgot-password', 'df_digits_form_forgot_password');

function df_digits_form()
{
    $values = array('render_form' => 1);
    return df_digits_form_render($values);
}

function df_digits_form_login()
{
    $values = array('render_form' => 4);
    return df_digits_form_render($values);
}

function df_digits_form_signup()
{
    $values = array('render_form' => 2);
    return df_digits_form_render($values);
}

function df_digits_form_forgot_password()
{
    $values = array('render_form' => 3);
    return df_digits_form_render($values);
}

function df_digits_form_render($values)
{
    if (is_user_logged_in()) {
        return '';
    }

    ob_start();
    $values['login_redirect'] = '-1';
    $values['redirect_to'] = '-1';
    _df_digits_form_render($values);
    $data = ob_get_contents();
    ob_end_clean();

    return $data;
}

function _df_digits_form_render($values)
{

    $color = get_option('digit_color');
    $page_type = 1;

    if (isset($color['type'])) {
        $page_type = $color['type'];
    }
    ?>
    <div class="dig_lrf_box dig-elem dig_pgmdl_2 dig_show_label digits_form_shortcode_render">
        <div class="dig_form">
            <?php
            if ($page_type == 2) {
                dig_verify_otp_box();
            }
            $dig_cust_forms = apply_filters('dig_hide_forms', 0);
            if ($dig_cust_forms === 0) {
                digits_forms($values);
            } else {
                do_action('digits_custom_form');
            }
            ?>
        </div>
    </div>
    <?php
}