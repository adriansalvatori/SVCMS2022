<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

include_once 'class-ph-setup-ajax-actions.php';
include_once 'class-ph-jetpack-install.php';

class PH_Setup_Wizard
{
    protected $jetpack;
    public function __construct()
    {
        add_action('admin_menu', [$this, 'page'], 10);
        //add_action('activated_plugin', [$this, 'redirect']);
        new PH_Setup_Ajax_Actions();
        $this->jetpack = new PH_Jetpack_Install();
    }

    public function redirect($plugin)
    {
        if (get_option('ph_setup_completed', false) || !current_user_can('manage_options')) {
            return;
        }
        if ($plugin == plugin_basename(PH_PLUGIN_FILE)) {
            exit(wp_redirect(admin_url('?page=ph-setup')));
        }
    }

    public function page()
    {
        $page = add_submenu_page(
            null,
            __('Setup', 'project-huddle'),
            __('Setup', 'project-huddle'),
            'manage_options',
            'ph-setup',
            function () {
                echo '<div id="app"><div class="ph-fixed ph-top-0 ph-left-0 ph-right-0 ph-bottom-0 ph-w-screen ph-h-screen ph-bg-white ph-flex ph-items-center ph-justify-center" style="z-index:9999999">
                <div class="spinner" style="visibility:visible"></div>
                </div></div>';
            }
        );

        add_action('admin_print_styles-' . $page, [$this, 'assets']);
    }

    public function assets()
    {
        $js_dir = PH_PLUGIN_URL . 'assets/js/dist/';
        $css_dir = PH_PLUGIN_URL . 'assets/css/dist/';

        // Image Upload
        wp_enqueue_media();

        wp_enqueue_script('project-huddle-setup', $js_dir . 'project-huddle-setup.js', ['underscore', 'ph.components', 'wp-color-picker'], PH_VERSION, true);
        wp_enqueue_style('project-huddle-setup', $css_dir . 'project-huddle-setup.css', ['wp-color-picker'], PH_VERSION);

        wp_localize_script('project-huddle-setup', 'phData', [
            'nonce' => wp_create_nonce('ph_setup_nonce'),
            'admin_url' => admin_url(),
            'rest_root'    => esc_url_raw(get_rest_url()),
            'jetpack_installed' => $this->jetpack->is_active(),
            'new_mockup_link' => admin_url('post-new.php?post_type=ph-project'),
            'new_website_link' => admin_url('post-new.php?post_type=ph-website'),
            'license_key' => get_option('ph_license_key'),
            'license_status' => get_option('ph_license_status'),
            'highlight_color' => get_option('ph_highlight_color', '#4353ff'),
            'dark_logo' => $this->get_attachment_data('ph_control_logo'),
            'dark_retina' => (bool) get_option('ph_control_logo_retina', false),
            'light_logo' => $this->get_attachment_data('ph_login_logo'),
            'light_retina' => (bool) get_option('ph_login_logo_retina', false),
            'sender_name' => wp_kses_post(get_option('ph_email_from_name', get_bloginfo('name'))),
            'sender_email' => sanitize_email(get_option('ph_email_from_address', get_option('admin_email'))),
            'email_throttle' => wp_kses_post(get_option('ph_email_throttle', 'immediate')),
            'daily_email' => filter_var(get_option('ph_daily_email', true), FILTER_VALIDATE_BOOLEAN),
            'weekly_email' => filter_var(get_option('ph_weekly_email', true), FILTER_VALIDATE_BOOLEAN)
        ]);
    }

    public function get_attachment_data($key)
    {
        if (!$post = get_post(get_option($key))) {
            return false;
        }
        $meta = wp_get_attachment_metadata($post->ID);
        return [
            'id' => $post->ID,
            'url' => $post->guid,
            'width' => $meta['width'],
            'height' => $meta['height']
        ];
    }
}
