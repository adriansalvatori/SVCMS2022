<?php
/*
 *
 * Plugin Name: Support Board
 * Plugin URI: https://board.support/
 * Description: Smart chat for better support and marketing
 * Version: 3.5.1
 * Author: Schiocco
 * Author URI: https://schiocco.com/
 * © 2017-2022 board.support. All rights reserved.
 *
 */

define('SB_WP', true);
if (!file_exists(__DIR__ . '/supportboard/config.php')) {
    sb_on_activation();
} else {
    require_once('supportboard/include/functions.php');
}

function sb_boot_session() {
    require_once('supportboard/include/functions.php');
    sb_updates_validation();
}

/*
 * ----------------------------------------------------------
 * # ADMIN AREA
 * ----------------------------------------------------------
 *
 * Display the administration area and the nav menu
 *
 */

function sb_set_admin_menu() {
    $current_user = wp_get_current_user();
    $allowed_roles = ['editor', 'administrator', 'author'];
    $allowed_roles_custom = sb_get_setting('wp-roles');
    if ($allowed_roles_custom) $allowed_roles = array_merge(explode(',', $allowed_roles_custom), $allowed_roles);
    if (array_intersect($allowed_roles, $current_user->roles)) {
        add_menu_page('Support Board', 'Support Board', 'read', 'support-board', 'sb_admin_action', SB_URL . '/media/icon-18x18.svg');
    }
}

function sb_admin_action() {
    require_once('supportboard/include/functions.php');
    require_once('supportboard/include/components.php');
    require_once('supportboard/apps/wordpress/functions.php');
    if (sb_db_check_connection() !== true) {
        sb_installation(sb_installation_array());
    }
    if (strpos(SB_URL, site_url()) === false) {
        sb_installation(sb_installation_array(), true);
    }
    sb_set_external_active_admin(sb_wp_get_current_user());
    sb_js_global();
    sb_js_admin();
    sb_component_admin();
}

function sb_enqueue_admin() {
    if (sb_is_admin_page()) {
        wp_enqueue_style('sb-admin-css', SB_URL . '/css/min/admin.min.css', [], SB_VERSION, 'all');
        wp_enqueue_style('sb-wp-admin-css', SB_URL . '/apps/wordpress/admin.css', [], SB_VERSION, 'all');
        wp_enqueue_script('sb-js', SB_URL . '/js/main.js', ['jquery'], SB_VERSION);
        wp_enqueue_script('sb-admin-js', SB_URL . '/js/admin.js', ['jquery'], SB_VERSION);
        wp_add_inline_script('sb-admin-js', 'var SB_WP_AJAX_URL = "' . admin_url('admin-ajax.php'). '";');
        if (sb_get_setting('rtl-admin')) wp_enqueue_style('sb-admin-rtl-css', SB_URL . '/css/min/rtl-admin.min.css', [], SB_VERSION, 'all');
    }
    if (defined('SB_ARMEMBER') && sb_isset($_GET, 'page') == 'arm_manage_members' && !empty($_GET['member_id'])) {
        wp_add_inline_script('mce-view', '(function ($) { let armember_interval = setInterval(function () {
            let member = $(\'.arm_openpreview[data-id="'.$_GET['member_id']. '"]\');
            if (member.length) {
                member.click();
                clearInterval(armember_interval);
            }}, 500);  }(jQuery));');
    }
}

/*
 * ----------------------------------------------------------
 * # FRONT END AREA
 * ----------------------------------------------------------
 *
 * Front end area functions
 *
 */

function sb_enqueue() {
    require_once('supportboard/include/functions.php');
    if (sb_get_setting('wp-manual') !== true) {
        $ump = defined('SB_UMP');
        $armember = defined('SB_ARMEMBER');
        $page_id = get_the_ID();
        $current_user = wp_get_current_user();
        $current_user_id = empty($current_user->ID) ? false : $current_user->ID;
        $lang = '';
        $inline_code = '';
        $exclusions = [sb_get_multi_setting('wp-visibility', 'wp-visibility-ids'), sb_get_multi_setting('wp-visibility', 'wp-visibility-post-types'), sb_get_multi_setting('wp-visibility', 'wp-visibility-type')];
        $exclusions = [$exclusions[0] ? array_map('trim', explode(',', $exclusions[0])) : [], $exclusions[1] ? array_map('trim', explode(',', $exclusions[1])) : [], $exclusions[2]];
        $force_languge = sb_get_setting('wp-language');
        $js_main_url = SB_URL . (sb_get_multi_setting('performance', 'performance-minify') ? '/js/min/main.min.js' : '/js/main.js');
        $multisite = is_multisite();

        // Selective chat loading
        if ($exclusions[2] != false && (count($exclusions[0]) && (($exclusions[2] == 'show' && !in_array($page_id, $exclusions[0])) || ($exclusions[2] == 'hide' && in_array($page_id, $exclusions[0]))))) {
            return false;
        }
        if (count($exclusions[1])) {
            $post_type = get_post_type($page_id);
            if ((($exclusions[2] == 'show' && !in_array($post_type, $exclusions[1])) || ($exclusions[2] == 'hide' && in_array($post_type, $exclusions[1])))) {
                return false;
            }
        }
        if ($current_user_id && (($ump && !sb_ump_is_init($current_user_id)) || ($armember && !sb_armember_is_init($current_user_id)))) {
            return false;
        }

        // Multilingual
        if (!sb_get_setting('front-auto-translations') && $force_languge == false) {
            if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE != 'en') {
                $lang = '?lang=' . substr(ICL_LANGUAGE_CODE, 0, 2);
            } else {
                $locale = get_locale();
                if ($locale && $locale != 'en') {
                    $lang = '?lang=' . substr($locale, 0, 2);
                }
            }
        } else if ($force_languge) {
            $lang = '?lang=' . $force_languge;
        }

        // Miscellaneous
        if (defined('SB_WOOCOMMERCE')) {
            $inline_code .= sb_woocommerce_inline();
        }
        $inline_code .= 'var SB_INIT_URL = "' . SB_URL . '/js/main.js' . $lang . '"; var SB_WP_PAGE_ID = ' . ($page_id == '' ? -1 : $page_id) . '; var SB_WP_AJAX_URL = "' . admin_url('admin-ajax.php'). '";';
        if ($current_user_id) {
            $new_user = sb_get_active_user_ID() != $current_user_id;
            if (!$multisite || get_current_blog_id() == 1) {
                $inline_code .= 'var SB_WP_ACTIVE_USER = ' . $current_user_id . ';';
                if ($new_user) {
                    $inline_code .= 'var SB_WP_AVATAR = "' . get_avatar_url($current_user_id, ['size' => '500']) . '";';
                }
            } else if ($new_user) {
                $profile_image = get_avatar_url($current_user_id, ['size' => '500']);
                if (empty($profile_image) || !(strpos($profile_image, '.jpg') || strpos($profile_image, '.png'))) {
                    $profile_image = '';
                }
                $inline_code .= 'var SB_DEFAULT_USER = { first_name: "' . $current_user->user_firstname . '", last_name: "' . $current_user->user_lastname . '", email: "' . $current_user->user_email . '", profile_image: "' . $profile_image . '", password: "' . $current_user->user_pass . '", extra: { "wp-id": [' . $current_user_id . ', "WordPress ID"] }};';
            }
        }

        // WordPress Multisite
        if (sb_get_setting('wp-multisite') && $multisite) {
            global $blog_id;
            $blog_name = get_blog_details(['blog_id' => $blog_id])->blogname;
            if ($blog_name != '') {
                $departments = sb_get_departments();
                $department_id = -1;
                foreach ($departments as $key => $value) {
                    if ($value['name'] == $blog_name) {
                        $department_id = $key;
                        break;
                    }
                }
                if ($department_id == -1) {
                    $department_id = count($departments) + 1;
                    $settings = sb_get_settings();
                    $item = ['department-name' => $blog_name, 'department-color' => '', 'department-image' => '', 'department-id' => $department_id];
                    if (is_array($settings['departments'][0])) {
                        array_push($settings['departments'][0], $item);
                    } else {
                        $settings['departments'] = [[$item], 'repeater'];
                    }
                    sb_save_settings($settings);
                }
                $inline_code .= ' var SB_DEFAULT_DEPARTMENT = ' . $department_id . ';';
            }
        } else if ($current_user_id && ($ump || $armember)) {
            $department_id = $ump ? sb_ump_get_membership_department($current_user_id) : sb_armember_get_membership_department($current_user_id);
            if ($department_id) {
                $inline_code .= ' var SB_DEFAULT_DEPARTMENT = ' . $department_id . ';';
            }
        }

        // WhatsApp Shop
        if (isset($_GET['sbwa'])) {
            sb_whatsapp_shop_url($_GET['sbwa']);
        }

        // Load scripts
        wp_enqueue_script('sb-js', $js_main_url . $lang, ['jquery'], SB_VERSION);
        wp_add_inline_script('sb-js', $inline_code);
    }
}

/*
 * ----------------------------------------------------------
 * # SHORTCODES
 * ----------------------------------------------------------
 *
 */

function sb_tickets_shortcode() {
    wp_register_script('sb-tickets', '');
    wp_enqueue_script('sb-tickets');
    wp_add_inline_script('sb-tickets', 'var SB_TICKETS = true;');
    return '<div id="sb-tickets"></div>';
}

function sb_articles_shortcode() {
    return '<script>var SB_ARTICLES_PAGE = true</script><div id="sb-articles" class="sb-loading"></div>';
}

/*
 * ----------------------------------------------------------
 * # FUNCTIONS
 * ----------------------------------------------------------
 *
 */

function sb_is_admin_page() {
    return key_exists('page', $_GET) && $_GET['page'] == 'support-board';
}

function sb_on_activation() {
    global $SB_CONNECTION;
    $path = __DIR__ . '/supportboard/config.php';
    if (!file_exists($path)) {
        $raw = str_replace(['[url]', '[name]', '[user]', '[password]', '[host]', '[port]'], '', file_get_contents(__DIR__ . '/supportboard/resources/config-source.php'));
        $file = fopen($path, 'w');
        fwrite($file, $raw);
        fclose($file);
    }
    require_once('supportboard/include/functions.php');
    sb_installation(sb_installation_array());
}

function sb_installation_array() {
    return array_merge(['db-name' => [DB_NAME], 'db-user' => [DB_USER], 'db-password' => [DB_PASSWORD], 'db-host' => [DB_HOST], 'url' => plugins_url() . '/supportboard/supportboard'], sb_wp_user_array());
}

function sb_wp_user_array($user = false) {
    if ($user == false) {
        if (function_exists('wp_get_current_user')) {
            $user = wp_get_current_user();
        } else return [];
    }
    return ['first-name' => [$user->user_firstname == '' ? esc_html($user->user_login) : esc_html($user->user_firstname)], 'last-name' => [esc_html($user->user_lastname)], 'email' => [esc_html($user->user_email)], 'password' => [$user->user_pass]];
}

function sb_on_wp_user_update($user_id) {
    if (sb_get_setting('wp-users-system') == 'wp') {
        $wp_user = get_userdata($user_id);
        $settings = sb_wp_get_user($user_id);
        $settings['user_type'] = in_array('administrator', $wp_user->roles) ? 'admin' : 'user';
        $settings_extra = ['wp-id' => [$user_id, 'WordPress ID']];
        if (defined('SB_UMP') && isset($_POST['ihc_avatar'])) {
            $settings_extra = sb_ump_on_user_update();
            $settings['profile_image'] = sb_ump_get_profile_image($_POST['ihc_avatar']);
        } else if (defined('SB_ARMEMBER') && isset($_POST['arm_primary_status'])) {
            $settings_extra = sb_armember_on_user_update();
        }
        $active_user = sb_get_active_user();
        if ($active_user) {
            $result = sb_update_user($active_user['id'], $settings, $settings_extra, false);
        } else {
            $result = sb_add_user($settings, $settings_extra, false);
            if (sb_is_validation_error($result) && $result->code() == 'duplicate-email') {
                $user = sb_db_get('SELECT id, user_type FROM sb_users WHERE email = "' . $settings['email'] . '"');
                if ($user) {
                    $settings['user_type'] = $user['user_type'];
                    sb_update_user($user['id'], $settings, $settings_extra, false);
                }
            }
        }
    }
    return $user_id;
}

function sb_on_wp_user_logout($user_id) {
    if (sb_get_setting('wp-users-system') == 'wp') {
        sb_logout();
    }
    return $user_id;
}

function sb_wp_get_current_user() {
    $current_user = wp_get_current_user();
    if (!empty($current_user) && !empty($current_user->ID)) {
        return sb_wp_get_user($current_user->ID);
    }
    return false;
}

/*
 * ------------------------------
 * # ACTIONS
 * ------------------------------
 *
 */

add_shortcode('sb-tickets', 'sb_tickets_shortcode');
add_shortcode('sb-articles', 'sb_articles_shortcode');
add_action('wp_loaded', 'sb_boot_session');
add_action('admin_menu', 'sb_set_admin_menu');
add_action('network_admin_menu', 'sb_set_admin_menu');
add_action('admin_enqueue_scripts', 'sb_enqueue_admin');
add_action('wp_enqueue_scripts', 'sb_enqueue');
add_action('wp_ajax_sb_wp_ajax', 'sb_wp_ajax');
add_action('wp_ajax_nopriv_sb_wp_ajax', 'sb_wp_ajax');
add_action('user_register', 'sb_on_wp_user_update', 999);
add_action('profile_update', 'sb_on_wp_user_update', 999);
add_action('wp_logout', 'sb_on_wp_user_logout');
register_activation_hook(__FILE__, 'sb_on_activation');

if (defined('SB_WOOCOMMERCE')) {
    sb_woocommerce_actions();
}
?>