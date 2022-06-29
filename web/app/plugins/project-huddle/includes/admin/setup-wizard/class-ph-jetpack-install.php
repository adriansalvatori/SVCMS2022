<?php

class PH_Jetpack_Install
{
    protected $slug = 'jetpack/jetpack.php';
    protected $zip = 'https://downloads.wordpress.org/plugin/jetpack.latest-stable.zip';
    protected $installed = false;

    public function __construct()
    {
        add_action('admin_notices', [$this, 'monitor_notice']);
    }

    public function monitor_notice()
    {
        // installed, but monitoring is not enabled
        if ($this->is_installed() && $this->is_connected() && !$this->monitoring_enabled()) {
            // dismissed notice
            if (get_site_option('dismissed-ph-jetpack-monitor-notice', false)) {
                return;
            }
            echo '<div class="notice notice-info is-dismissible ph-notice" data-notice="ph-jetpack-monitor-notice">
				<p style="font-size: 16px"><strong>' . esc_html(__('ProjectHuddle: Almost done!', 'project-huddle')) . '</strong></p>
				<p>' . esc_html(__("Please enable Jetpack's monitoring feature to ensure emails send on time!", 'project-huddle')) . ' <a href="https://help.projecthuddle.com/article/113-why-do-we-recommend-jetpacks-monitor" data-beacon-article-modal="5dcdc48e2c7d3a7e9ae3f9b7">' . __('Why?', 'project-huddle') . '</a></p>' .
                '<p><a href="' . esc_url(admin_url('admin.php?page=jetpack#/settings')) . '" class="button button-primary">Go To Settings</a></p>
			</div>';
            ph_dismiss_js();
        }
    }

    public function is_installed()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $all_plugins = get_plugins();

        if (!empty($all_plugins[$this->slug])) {
            return true;
        } else {
            return false;
        }
    }

    public function is_active()
    {
        return class_exists('Jetpack');
    }

    /**
     * Is Jetpack Connected
     *
     * @return boolean
     */
    public function is_connected()
    {
        if (class_exists('Jetpack') && method_exists('Jetpack', 'is_active')) {
            return Jetpack::is_active();
        }
        return false;
    }

    /**
     * Install the plugin file
     *
     * @return void
     */
    function install_plugin()
    {
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        wp_cache_flush();

        $upgrader = new Plugin_Upgrader();
        $installed = $upgrader->install($this->zip);

        return $installed;
    }

    /**
     * Upgrade the plugin
     *
     * @return void
     */
    function upgrade_plugin()
    {
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        wp_cache_flush();

        $upgrader = new Plugin_Upgrader();
        $upgraded = $upgrader->upgrade($this->slug);

        return $upgraded;
    }

    public function install()
    {
        $this->installed = $this->is_installed();

        if (!$this->installed) {
            ph_log('Setup Wizard: Jetpack installed, upgrading.');
            $this->installed = $this->install_plugin();
        } else {
            ph_log('Setup Wizard: Jetpack already installed.');
        }

        if (!is_wp_error($this->installed) && $this->installed) {
            $activate = activate_plugin($this->slug);
            if (is_wp_error($activate)) {
                return $activate;
            }
            ph_log('Setup Wizard: Jetpack activated.');
        } else {
            ph_log('Setup Wizard: Could not install jetpack.');
        }

        return $this->installed;
    }

    public function enable_monitoring()
    {
        if (class_exists('Jetpack') && method_exists('Jetpack', 'activate_module')) {
            Jetpack::activate_module('monitor', false, false);
        }
    }

    public function monitoring_enabled()
    {
        if (class_exists('Jetpack') && method_exists('Jetpack', 'get_active_modules')) {
            // If it's already active, then don't do it again
            $active = Jetpack::get_active_modules();
            foreach ($active as $act) {
                if ($act == 'monitor') {
                    return true;
                }
            }
            return false;
        }
    }
}
