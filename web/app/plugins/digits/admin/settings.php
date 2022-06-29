<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once 'includes/functions.php';
require_once 'setup_wizard.php';

function add_digits_setting_page()
{
    $m = add_menu_page(
        'Digits',
        'Digits',
        'manage_options',
        'digits_settings',
        'digits_plugin_settings',
        '',
        68
    );
    add_submenu_page(
        'digits_settings',
        'Digits',
        'Settings',
        'manage_options',
        'digits_settings'
    );

    do_action('digits_register_menu');

    add_action('admin_print_styles-' . $m, 'dig_add_gs_css');
    add_action('admin_enqueue_scripts', 'dig_add_menu_css');

}

add_action("admin_menu", "add_digits_setting_page");
function dig_add_menu_css()
{
    wp_enqueue_style('digits-settings', get_digits_asset_uri('/admin/assets/css/settings.min.css'), array(), digits_version(), 'all');

}


function digit_admin_header_logo($show_update = true)
{
    $plugin_updates = get_plugin_updates();
    $text = esc_html(digits_version());
    $slug = 'digits';
    $base_name = get_digits_basename();

    if (isset($plugin_updates[$base_name]) && $show_update) {
        $link = wp_nonce_url(
            add_query_arg(
                array(
                    'puc_check_for_updates' => 1,
                    'puc_slug' => $slug,
                ),
                self_admin_url('plugins.php')
            ),
            'puc_check_for_updates'
        );

        $text .= ' <a href="' . $link . '" class="untdover_plugin_update" data-slug="' . $slug . '">' . __('(Update Available)', 'digits') . '</a>';
    }
    ?>
    <span class="dig-display_inline">
        <a href="https://digits.unitedover.com/" target="_blank">
            <img src="<?php echo get_digits_asset_uri('/assets/images/Digits_logo.png'); ?>"/></a></h1>
        <span class="digits_plugin_version"><?php echo $text; ?></span>
        </a>
    </span>
    <?php
}

add_action('admin_footer', 'digits_loader');
function digits_loader()
{
    ?>
    <div class="dig_load_overlay">
        <div class="dig_load_content">
            <div class="dig_spinner">
                <div class="dig_double-bounce1"></div>
                <div class="dig_double-bounce2"></div>
            </div>
        </div>
    </div>
    <?php
}

function digits_plugin_settings()
{

    dig_add_gs_css();

    wp_print_request_filesystem_credentials_modal();

    $code = get_site_option('dig_purchasecode');
    if (empty(get_site_option($code))) {
        $code = get_option('dig_purchasecode');
        if (!empty($code)) {
            update_site_option('dig_purchasecode', $code);
        }
    }

    ?>


    <?php
    if (isset($_GET['show_survey'])) {
        $link = 'https://forms.office.com/Pages/ResponsePage.aspx?id=DQSIkWdsW0yxEjajBLZtrQAAAAAAAAAAAAMAAASH_sdUNEZaSEFJN0c2NDlQNjVLT0JNQTJWQlhPVi4u';
        ?>
        <style>body {
                overflow: hidden;
            }</style>

        <div class="dig-addon-box dig-modal-center_align dig_ma-box dig-box  dig-modal-con-reno">
            <div class="dig-modal-center dig_addons_pop">
                <a href="<?php echo $link; ?>" target="_blank">
                    <img src="<?php echo get_digits_asset_uri('/assets/images/survey-popup.png'); ?>"/>
                </a>
            </div>
            <div class="dig_hide_modal">
            </div>
        </div>

        <?php
    }
    $digpc = dig_get_option('dig_purchasecode');
    $request_link = admin_url('admin.php?page=digits_settings&tab=activate');

    $purchase_link = 'https://1.envato.market/0zxKP';

    if (isset($_POST['dig_hid_addon_domain_notice'])) {

        update_site_option('dig_hid_addon_domain_notice', 1);
    }
    $dig_hid_addon_domain_notice = get_site_option('dig_hid_addon_domain_notice', -1);

  
        ?>

    
    <form method="post" autocomplete="off" id="digits_setting_update" class="dig_activation_form"
          enctype="multipart/form-data">

        <div class="digits_admim_conf">


            <?php
            if (isset($_GET['tab'])) {
                $active_tab = sanitize_text_field($_GET['tab']);
            } else {
                $active_tab = 'apisettings';
            } // end if

            digits_update_data(0);


            if (empty($digpc)) {
                if ($active_tab == "customize") {
                    $active_tab = 'activate';
                }
            }
            ?>

            <div class="dig_big_preset_show">
                <div class="dig-flex_center">
                    <img src="" draggable="false"/>
                </div>
            </div>

            <div class="dig_load_overlay_gs">
                <div class="dig_load_content">

                    <div class="circle-loader">
                        <div class="checkmark draw"></div>
                    </div>

                </div>
            </div>

            <div class="dig_log_setge">
                <div class="dig_admin_left_side">
                    <div class="dig_admin_left_side_content">


                        <div class="dig_sts_logo">
                            <?php
                            digit_admin_header_logo();
                            ?>
                            <ul class="dig_gs_log_men">
                                <li><a class="dig_ngmc"
                                       href="https://help.unitedover.com/?utm_source=digits-wp-settings&utm_medium=kb-button"
                                       target="_blank"><?php _e('KNOWLEDGEBASE', 'digits'); ?></a></li>
                                <li><a id="dig_addonstab" href="?page=digits_settings&tab=addons"
                                       class="dig_ngmc updatetabview <?php echo $active_tab == 'addons' ? 'dig-nav-tab-active' : ''; ?>"
                                       tab="addonstab"><?php _e('Addons', 'digits'); ?></a></li>
                                <li><a class="dig_ngmc" href="https://digits.unitedover.com/changelog/"
                                       target="_blank"><?php _e('CHANGELOG', 'digits'); ?></a></li>

                                <li><a id="dig_activatetab" href="?page=digits_settings&tab=activate"
                                       class="dig_ngmc updatetabview <?php echo $active_tab == 'activate' ? 'dig-nav-tab-active' : ''; ?>"
                                       tab="activatetab"><?php _e('Register', 'digits'); ?></a></li>
                            </ul>
                        </div>

                        <?php
                        if (!empty($digpc)) {
                            echo '<input type="hidden" id="dig_activated" value="1" />';
                        } ?>


                        <div class="digits-settings_body">

                            <div class="dig-tab-wrapper">

                                <ul class="dig-tab-ul">
                                    <li><a href="?page=digits_settings&tab=apisettings"
                                           class="updatetabview dig-nav-tab <?php echo $active_tab == 'apisettings' ? 'dig-nav-tab-active' : ''; ?>"
                                           tab="apisettingstab"><?php _e('Gateway', 'digits'); ?></a></li>

                                    <li><a href="?page=digits_settings&tab=configure"
                                           class="updatetabview dig-nav-tab <?php echo $active_tab == 'configure' ? 'dig-nav-tab-active' : ''; ?>"
                                           tab="configuretab"><?php _e('General', 'digits'); ?></a></li>

                                    <li><a href="?page=digits_settings&tab=customfields"
                                           class="customfieldsNavTab updatetabview dig-nav-tab <?php echo $active_tab == 'customfields' ? 'dig-nav-tab-active' : ''; ?>"
                                           tab="customfieldstab"><?php _e('Form', 'digits'); ?></a></li>

                                    <li><a href="?page=digits_settings&tab=customize"
                                           class="updatetabview dig-nav-tab <?php echo $active_tab == 'customize' ? 'dig-nav-tab-active' : ''; ?>"
                                           tab="customizetab" acr="1"><?php _e('Style', 'digits'); ?></a></li>

                                    <li><a href="?page=digits_settings&tab=translations"
                                           class="updatetabview dig-nav-tab <?php echo $active_tab == 'translations' ? 'dig-nav-tab-active' : ''; ?>"
                                           tab="translationstab"><?php _e('Translations', 'digits'); ?></a></li>

                                    <li><a href="?page=digits_settings&tab=shortcodes"
                                           class="updatetabview dig-nav-tab <?php echo $active_tab == 'shortcodes' ? 'dig-nav-tab-active' : ''; ?>"
                                           tab="shortcodestab"><?php _e('Shortcodes', 'digits'); ?></a></li>


                                </ul>

                            </div>


                            <div id="digits_setting_form_div" class="dig_settings_Form">
                                <div data-tab="apisettingstab"
                                     class="dig_admin_in_pt apisettingstab digtabview <?php echo $active_tab == 'apisettings' ? 'digcurrentactive' : '" style="display:none;'; ?>">
                                    <?php digits_api_settings();
                                    ?>

                                </div>
                                <div data-tab="configuretab"
                                     class="dig_admin_in_pt configuretab digtabview <?php echo $active_tab == 'configure' ? 'digcurrentactive' : '" style="display:none;'; ?>">
                                    <?php
                                    digits_configure_settings();
                                    ?>
                                </div>

                                <div data-tab="customizetab"
                                     class="dig_admin_in_pt customizetab digtabview <?php echo $active_tab == 'customize' ? 'digcurrentactive' : '" style="display:none;'; ?>">
                                    <?php

                                    digit_customize(false);


                                    ?>

                                </div>

                                <div data-tab="translationstab"
                                     class="dig_admin_in_pt translationstab digtabview <?php echo $active_tab == 'translations' ? 'digcurrentactive' : '" style="display:none;'; ?>">
                                    <?php digit_shortcodes_translations(); ?>
                                </div>
                                <div data-tab="shortcodestab"
                                     class="dig_admin_in_pt shortcodestab digtabview <?php echo $active_tab == 'shortcodes' ? 'digcurrentactive' : '" style="display:none;'; ?>">
                                    <?php digit_shortcodes(false); ?>

                                </div>
                                <div data-tab="activatetab"
                                     class="dig_admin_in_pt activatetab digtabview <?php echo $active_tab == 'activate' ? 'digcurrentactive' : '" style="display:none;'; ?>">
                                    <?php digit_activation(false); ?>
                                </div>


                                <div data-tab="customfieldstab"
                                     data-attach="customfieldsNavTab"
                                     class="dig_admin_in_pt customfieldstab digtabview <?php echo $active_tab == 'customfields' ? 'digcurrentactive' : '" style="display:none;'; ?>">
                                    <?php digit_customfields(); ?>
                                </div>


                                <div data-tab="addonstab"
                                     class="dig_admin_in_pt addonstab digtabview <?php echo $active_tab == 'addons' ? 'digcurrentactive' : '" style="display:none;'; ?>">
                                    <?php digit_addons($active_tab); ?>
                                </div>


                                <?php do_action('digits_settings_page', $active_tab); ?>
                                <Button type="submit" class="dig_admin_submit"
                                        disabled><?php _e('Save Changes', 'digits'); ?></Button>
                            </div>

                        </div>

                    </div>
                </div>


                <div class="dig_admin_side">
                    <?php
                    $plugin_version = digits_version();
                    $allowed_tags = [
                        'a' => [
                            'class' => [],
                            'href' => [],
                            'rel' => [],
                            'title' => [],
                        ],
                        'abbr' => [
                            'title' => [],
                        ],
                        'b' => [],
                        'blockquote' => [
                            'cite' => [],
                        ],
                        'cite' => [
                            'title' => [],
                        ],
                        'code' => [],
                        'del' => [
                            'datetime' => [],
                            'title' => [],
                        ],
                        'dd' => [],
                        'div' => [
                            'class' => [],
                            'title' => [],
                            'style' => [],
                        ],
                        'dl' => [],
                        'dt' => [],
                        'em' => [],
                        'h1' => [],
                        'h2' => [],
                        'h3' => [],
                        'h4' => [],
                        'h5' => [],
                        'h6' => [],
                        'i' => [],
                        'img' => [
                            'alt' => [],
                            'class' => [],
                            'height' => [],
                            'src' => [],
                            'width' => [],
                        ],
                        'li' => [
                            'class' => [],
                        ],
                        'ol' => [
                            'class' => [],
                        ],
                        'p' => [
                            'class' => [],
                        ],
                        'q' => [
                            'cite' => [],
                            'title' => [],
                        ],
                        'span' => [
                            'class' => [],
                            'title' => [],
                            'style' => [],
                        ],
                        'strike' => [],
                        'strong' => [],
                        'ul' => [
                            'class' => [],
                        ],
                    ];

                    $data = dig_curl('https://www.unitedover.com/images/digits-wpsettings/sidebar.php?version=' . $plugin_version);
                    echo $data;


                    ?>
                </div>

            </div>
            <?php
            if (is_rtl()) {
                echo '<input type="hidden" id="is_rtl" value="1"/>';
            }
            ?>
            <style type="text/css">
                #wpbody-content {
                    padding-bottom: 10px;
                }

                #wpfooter {
                    display: none;
                }
            </style>
        </div><!-- /.wrap -->

    </form>

    <?php

    wp_register_script('digits-upload-script', get_digits_asset_uri('/admin/assets/js/upload.min.js'), array('jquery'), digits_version(), true);

    $jsData = array(
        'logo' => get_option('digits_logo_image'),
        'selectalogo' => __('Select a logo', 'digits'),
        'usethislogo' => __('Use this logo', 'digits'),
        'changeimage' => __('Change Image', 'digits'),
        'selectimage' => __('Select Image', 'digits'),
        'removeimage' => __('Remove Image', 'digits'),
    );
    wp_localize_script('digits-upload-script', 'dig', $jsData);

    wp_enqueue_script('digits-upload-script');
    wp_enqueue_media();

    dig_config_scripts();

    ?>
    <?php

} // end

add_action('admin_head', 'digits_add_admin_settings_scripts');

add_action('admin_enqueue_scripts', 'digits_add_admin_settings_scripts');
function digits_add_admin_settings_scripts($hook)
{

    if (is_admin()) {

        if ($hook != -1) {
            if ($hook == 'edit.php') {
                if (!isset($_GET['post_type'])) return;

                if (strpos($_GET['post_type'], 'digits') === false) {
                    return;
                }
            } else if ($hook != 'plugins.php') {
                if (!isset($_GET['page'])) {
                    return;
                }
                if ($_GET['page'] != 'digits_settings') {
                    return;
                }
            }
        }


        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('google-roboto-regular', dig_fonts());
        digits_select2();

        wp_enqueue_script('rubaxa-sortable', get_digits_asset_uri('/assets/js/sortable.min.js'), null);


        wp_enqueue_script('slick', get_digits_asset_uri('/admin/assets/js/slick.min.js'), null);


        wp_register_script('digits-script', get_digits_asset_uri('/admin/assets/js/settings.min.js'), array(
            'jquery',
            'select2-full',
            'updates',
            'wp-color-picker',
            'rubaxa-sortable',
            'slick',
            'digits-login-script',
        ), digits_version(), true);

        $gateway_help = 'https://help.unitedover.com/';
        $settings_array = array(
            'plsActMessage' => __('Please activate your plugin to change the look and feel of your Login page and Popup', 'digits'),
            'cannotUseEmailWithoutPass' => __('Oops! You cannot enable email without password for login', 'digits'),
            'bothPassAndOTPCannotBeDisabled' => __('Both Password and OTP cannot be disabled', 'digits'),
            'selectatype' => __('Select a type', 'digits'),
            "Invalidmsg91senderid" => __("Invalid msg91 sender id!", 'digits'),
            "invalidpurchasecode" => __("Invalid Purchase Code", 'digits'),
            "Error" => __("Error! Please try again later", "digits"),
            "PleasecompleteyourSettings" => __("Please complete your settings", 'digits'),
            "PleasecompleteyourAPISettings" => sprintf(__("Please complete your SMS Gateway settings by clicking here, without those plugin will not work. For documentation, click %s here %s", 'digits'), '<a href="' . $gateway_help . '" target="_blank">', '</a>'),
            "PleasecompleteyourCustomFieldSettings" => __("Please complete your custom field settings", 'digits'),
            "Copiedtoclipboard" => __("Copied to clipboard", "digits"),
            'ajax_url' => admin_url('admin-ajax.php'),
            'fieldAlreadyExist' => __('Field Already exist', 'digits'),
            'duplicateValue' => __('Duplicate Value', 'digits'),
            "string_no" => __("No", "digits"),
            "string_optional" => __("Optional", "digits"),
            "string_required" => __("Required", "digits"),
            "validnumber" => __("Please enter a valid mobile number", "digits"),
            "invalidimportcode" => __("Please enter a valid import code", "digits"),
            "direction" => is_rtl() ? 'rtl' : 'ltr',

        );
        wp_localize_script('digits-script', 'digsetobj', $settings_array);

        wp_enqueue_script('digits-script');

        wp_register_script('igorescobar-jquery-mask', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js', array('jquery'), null, false);
        wp_print_scripts('igorescobar-jquery-mask');


        digits_add_style();
        digits_add_scripts();
    }
}


function dig_config_scripts()
{

    wp_register_script('digits-upload-script', get_digits_asset_uri('/admin/assets/js/upload.min.js'), array('jquery'), digits_version(), true);


    $jsData = array(
        'logo' => get_option('digits_logo_image'),
        'selectalogo' => __('Select a Image', 'digits'),
        'usethislogo' => __('Use this Image', 'digits'),
        'changeimage' => __('Change Image', 'digits'),
        'selectimage' => __('Select Image', 'digits'),
        'removeimage' => __('Remove Image', 'digits'),
    );
    wp_localize_script('digits-upload-script', 'dig', $jsData);


    wp_enqueue_script('wp-color-picker-alpha', get_digits_asset_uri('/admin/assets/js/wp-color-picker-alpha.min.js'),
        array('jquery', 'wp-color-picker'), '1.2.2', false);


    wp_enqueue_script('digits-upload-script');

    @do_action('admin_footer');
    do_action('admin_print_footer_scripts');
}


function digits_add_admin_scripts()
{
    digits_add_scripts();

    wp_print_scripts('scrollTo');
    wp_print_scripts('digits-main-script');
    wp_print_scripts('digits-login-script');
    wp_print_styles('google-roboto-regular');
    ?>
    <style>
        .woocommerce-input-wrapper .dig_wc_countrycodecontainer {
            position: absolute;
        }
    </style>
    <?php
}

add_action('admin_print_footer_scripts', 'digits_add_admin_scripts');

function dig_add_gs_css()
{
    wp_enqueue_style('google-roboto-regular', dig_fonts());
    digits_select2();
    wp_enqueue_style('digits-gs-style', get_digits_asset_uri('/admin/assets/css/gs.min.css'), array(
        'google-roboto-regular',
        'select2'
    ), digits_version(), 'all');

    if (is_rtl()) {
        wp_enqueue_style('digits-gs-rtl-style', get_digits_asset_uri('/admin/assets/css/gs-rtl.min.css'), array('digits-gs-style'), null, 'all');

    }

    digits_add_style();
}


function dig_network_home_url($path = '', $scheme = null ) {
    if ( ! is_multisite() ) {
        return dig_get_home_url( null, $path, $scheme );
    }

    $current_network = get_network();
    $orig_scheme     = $scheme;

    if ( ! in_array( $scheme, array( 'http', 'https', 'relative' ) ) ) {
        $scheme = is_ssl() && ! is_admin() ? 'https' : 'http';
    }

    if ( 'relative' == $scheme ) {
        $url = $current_network->path;
    } else {
        $url = set_url_scheme( 'http://' . $current_network->domain . $current_network->path, $scheme );
    }

    if ( $path && is_string( $path ) ) {
        $url .= ltrim( $path, '/' );
    }


    return $url;
}


function dig_get_home_url( $blog_id = null, $path = '', $scheme = null ) {
    global $pagenow;

    $orig_scheme = $scheme;

    if ( empty( $blog_id ) || ! is_multisite() ) {
        $url = get_option( 'home' );
    } else {
        switch_to_blog( $blog_id );
        $url = get_option( 'home' );
        restore_current_blog();
    }

    if ( ! in_array( $scheme, array( 'http', 'https', 'relative' ) ) ) {
        if ( is_ssl() && ! is_admin() && 'wp-login.php' !== $pagenow ) {
            $scheme = 'https';
        } else {
            $scheme = parse_url( $url, PHP_URL_SCHEME );
        }
    }

    $url = set_url_scheme( $url, $scheme );

    if ( $path && is_string( $path ) ) {
        $url .= '/' . ltrim( $path, '/' );
    }

    return $url;
}
