<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('XT_Framework_Plugin_Tabs')) {

    /**
     * The admin-specific functionality of the plugin.
     *
     * Defines the plugin name, version, and two examples hooks for how to
     * enqueue the admin-specific stylesheet and JavaScript.
     *
     * @package    XT_Framework_Plugin_Tabs
     * @author     XplodedThemes
     */
    class XT_Framework_Plugin_Tabs extends XT_Framework_Admin_Tabs
    {

        public $logo = '';
        public $description = '';

        public $is_network_active = false;

        protected function init()
        {

            $this->is_network_active = $this->core->is_network_active();
            $this->capability = $this->core->plugin_dependencies()->depends_on('WooCommerce') ? 'manage_woocommerce' : 'manage_options';

            parent::init();

            if (!defined('DOING_AJAX')) {
                add_filter('plugin_action_links_' . plugin_basename($this->core->plugin_file()), array($this, 'action_links'), 99);
            }

            if ($this->core->access_manager() && $this->is_admin_tabs_page() && $this->core->is_freemius()) {

                if ($this->core->access_manager()->is_activation_mode() && !$this->core->access_manager()->is_activation_page()) {

                    wp_redirect($this->get_tab_url());
                    exit;

                } else {

                    if (!$this->core->access_manager()->is_registered() && $this->is_network_active && is_network_admin() && !$this->core->access_manager()->is_activation_mode()) {
                        wp_redirect(network_admin_url());
                        exit;
                    }

                    $this->core->access_manager()->add_filter('templates/account.php', array($this, 'wrap_freemius_admin_page'), 10);
                    $this->core->access_manager()->add_filter('templates/pricing.php', array($this, 'wrap_freemius_minimal_admin_page'), 10);
                    $this->core->access_manager()->add_filter('templates/checkout.php', array($this, 'wrap_freemius_minimal_admin_page'), 10);
                    $this->core->access_manager()->add_filter('templates/add-ons.php', array($this, 'wrap_freemius_admin_page'), 10);
                    $this->core->access_manager()->add_filter('templates/contact.php', array($this, 'wrap_freemius_admin_page'), 10);
                    $this->core->access_manager()->add_filter('/forms/affiliation.php', array($this, 'wrap_freemius_admin_page'), 10);
                }
            }
        }

        public function is_admin_tabs_page()
        {

            return !empty($_GET['page']) && (strpos($_GET['page'], $this->core->plugin_slug()) !== false);
        }

        public function wrap_freemius_admin_page($template)
        {

            ob_start();
            if (strpos($template, 'fs-secure-notice') !== false) {
                echo '<p>&nbsp;</p>';
            }
            $this->tabs_admin_page($template);

            return ob_get_clean();
        }

        public function wrap_freemius_minimal_admin_page($template)
        {

            ob_start();

            $hide_title = false;

            if (strpos($template, 'fs-secure-notice') !== false) {
                echo '<p>&nbsp;</p>';
            }

            if (strpos($template, 'fs_pricing') !== false) {
                $hide_title = true;
            }

            $this->tabs_admin_page($template, true, $hide_title);

            return ob_get_clean();
        }

        protected function apply_filters()
        {

            $this->tabs = apply_filters($this->core->plugin_prefix('admin_tabs'), $this->tabs, $this);
            $this->logo = apply_filters($this->core->plugin_prefix('admin_tabs_logo'), $this->logo, $this);
            $this->description = apply_filters($this->core->plugin_prefix('admin_tabs_description'), $this->description, $this);

            if ($this->core->is_freemius() && $this->is_network_active && is_network_admin()) {

                if ($this->core->access_manager()->is_registered()) {

                    $this->tabs = array_filter($this->tabs, function ($tab) {
                        return !empty($tab['network']);
                    });

                } else {

                    $this->tabs = array();
                }
            }
        }

        public function footer_version()
        {
            return '<span class="alignright"><a href="' . esc_url($this->core->get_xt_url('admin-footer', $this->core->plugin()->url)). '"><strong>' . $this->core->plugin_name() . '</strong></a> - v' . $this->core->plugin_version() . '</strong></span>';
        }

        public function set_active_tab()
        {

            if (!$this->is_admin_tabs_page()) {
                return;
            }

            if (!empty($_GET['page']) && $_GET['page'] !== $this->core->plugin_slug()) {
                $page = sanitize_text_field($_GET['page']);
                $tab_id = str_replace($this->core->plugin_slug() . '-', '', $page);

                if ($this->tab_exists($tab_id)) {
                    $this->active_tab = $tab_id;
                }
            }

            if (!empty($_GET['page']) && $_GET['page'] === $this->core->plugin_slug()) {

                if (!empty($_GET['tab'])) {

                    $tab_id = sanitize_text_field($_GET['tab']);
                    if ($this->tab_exists($tab_id)) {
                        $this->active_tab = $tab_id;
                    }
                } else {
                    $this->active_tab = $this->default_tab;
                }
            }
        }

        public function add_default_tabs()
        {

            if ($this->core->is_freemius()) {

                if ($this->core->access_manager()->is_pricing_page_visible() && $this->core->access_manager()->is_submenu_item_visible('pricing')) {

                    $this->tabs[] = array(
                        'id' => '_pricing',
                        'title' => esc_html__('Upgrade&nbsp;&nbsp;➤', 'xt-framework'),
                        'show_menu' => false,
                        'redirect' => $this->core->access_manager()->get_upgrade_url(),
                        'featured' => true,
                        'order' => 100,
                        'secondary' => true,
                        'network' => true
                    );
                }


                if ($this->core->access_manager()->is_registered()) {

                    $this->tabs[] = array(
                        'id' => '_account',
                        'title' => $this->is_network_active && is_network_admin() ? esc_html__('Network Account', 'xt-framework') : esc_html__('Account', 'xt-framework'),
                        'show_menu' => false,
                        'redirect' => $this->core->access_manager()->get_account_url(),
                        'order' => 110,
                        'secondary' => !($this->is_network_active && is_network_admin()),
                        'network' => true
                    );

                } else if ($this->core->access_manager()->is_tracking_prohibited()) {

                    $this->tabs[] = array(
                        'id' => '_optin',
                        'title' => esc_html__('Opt In', 'xt-framework'),
                        'show_menu' => false,
                        'redirect' => $this->core->access_manager()->get_reconnect_url(),
                        'order' => 110,
                        'secondary' => true,
                        'network' => true
                    );
                }

                if ($this->core->access_manager()->has_affiliate_program()) {

                    $this->tabs[] = array(
                        'id' => '_affiliation',
                        'title' => esc_html__('Make $$$', 'xt-framework'),
                        'show_menu' => false,
                        'redirect' => $this->core->access_manager()->_get_admin_page_url('affiliation'),
                        'content' => array(
                            'title' => esc_html__('Affiliate Program', 'xt-framework')
                        ),
                        'order' => 120,
                        'secondary' => true,
                        'network' => true
                    );
                }


                $this->tabs[] = array(
                    'id' => '_contact',
                    'title' => esc_html__('Support', 'xt-framework'),
                    'show_menu' => false,
                    'hide_title' => true,
                    'icon' => 'dashicons-sos',
                    'redirect' => $this->core->access_manager()->contact_url(),
                    'order' => 140,
                    'secondary' => true,
                    'network' => true
                );

            } else {

                $this->tabs[] = array(
                    'id' => 'contact',
                    'title' => esc_html__('Support', 'xt-framework'),
                    'show_menu' => false,
                    'hide_title' => true,
                    'icon' => 'dashicons-sos',
                    'external' => $this->core->get_xt_url('admin-support-tab', 'https://xplodedthemes.com/support'),
                    'order' => 140,
                    'secondary' => true,
                    'network' => true
                );

            }

            $this->tabs[] = array(
                'id' => 'changelog',
                'title' => esc_html__('Change Log', 'xt-framework'),
                'show_menu' => false,
                'hide_title' => true,
                'icon' => 'dashicons-media-text',
                'order' => 130,
                'content' => array(
                    'title' => esc_html__('Change Log', 'xt-framework'),
                    'type' => 'changelog',
                    'show_refresh' => true
                ),
                'secondary' => true,
                'flashing_badge' => array($this->core->plugin_migrations(), 'has_unviewed_changelog'),
                'callback' => array($this->core->plugin_migrations(), 'set_changelog_viewed')
            );

            $this->tabs[] = array(
                'id' => 'home',
                'title' => esc_html__('XplodedThemes.com', 'xt-framework'),
                'show_menu' => false,
                'hide_title' => true,
                'icon' => 'dashicons-admin-home',
                'order' => 150,
                'external' => $this->core->get_xt_url('admin-home-icon'),
                'secondary' => true,
                'network' => true
            );

        }

        public function action_links($links)
        {

            foreach ($this->tabs as $i => $tab) {

                if (empty($tab['action_link'])) {
                    continue;
                }

                $id = $i > 0 ? $tab['id'] : '';
                $url = $this->get_tab_url($id);

                $action_link = $tab['action_link'];

                if (is_array($action_link)) {

                    $url = !empty($action_link['url']) ? $action_link['url'] : $url;
                    $title = !empty($action_link['title']) ? $action_link['title'] : $tab['title'];
                    $color = !empty($action_link['color']) ? $action_link['color'] : '';

                } else {

                    $title = $tab['title'];
                    $color = '';
                }

                $links[] = '<a style="color: ' . esc_attr($color) . '" href="' . esc_url($url) . '">' . sanitize_text_field($title) . '</a>';

            }

            return $links;
        }

        public function tabs_admin_menu()
        {

            if ($this->core->plugin()->top_menu()) {

                add_menu_page($this->core->plugin_menu_name(), $this->core->plugin_menu_name(), $this->capability, $this->core->plugin_slug(), array(
                    $this,
                    'tabs_admin_page'
                ), $this->core->plugin_icon());

            } else {

                if ($this->core->has_modules()) {

                    foreach ($this->core->modules()->all() as $module) {

                        if (did_action($module->prefix('menu_loaded'))) {
                            continue;
                        }

                        $title = $module->menu_name();
                        $parent_slug = $module->show_in_menu() ? $this->core->framework_slug() : null;

                        add_submenu_page($parent_slug, $title, $title, $this->capability, $module->id(), function () use ($module) {

                            wp_redirect($module->admin_url());
                            exit;
                        }, 0);

                        do_action($module->prefix('menu_loaded'));

                    }
                }

                if ($this->core->is_freemius() && $this->core->access_manager()->is_activation_mode() && $this->is_network_active && !is_network_admin()) {

                    add_submenu_page($this->core->framework_slug(), $this->core->plugin_menu_name(), $this->core->plugin_menu_name(), $this->capability, $this->core->plugin_slug(), function () {

                        wp_redirect($this->get_tab_url('', null, $this->is_network_active));
                        exit;
                    }, 0);

                } else {

                    add_submenu_page($this->core->framework_slug(), $this->core->plugin_menu_name(), $this->core->plugin_menu_name(), $this->capability, $this->core->plugin_slug(), array(
                        $this,
                        'tabs_admin_page'
                    ), 0);

                }

            }

            foreach ($this->tabs as $tab) {

                $id = $tab['id'];
                $title = !empty($tab['menu_title']) ? $tab['menu_title'] : (!empty($tab['title']) ? $tab['title'] : '');
                $title = apply_filters($this->core->plugin_prefix('admin_tabs_tab_title'), $title, $tab);

                if (!empty($tab['badges'])) {
                    $title .= $this->get_badges_html($tab['badges']);
                }

                $order = !empty($tab['order']) ? $tab['order'] : 1;
                $redirect = !empty($tab['external']) ? $tab['external'] : '';
                $redirect = !empty($tab['redirect']) ? $tab['redirect'] : $redirect;
                $show_menu = !empty($tab['show_menu']);

                $parent_menu = ($show_menu && $this->core->plugin()->top_menu) ? $this->core->plugin_slug() : null;

                if ($this->is_default_tab($id)) {
                    $this->page_hooks[$id] = add_submenu_page($parent_menu, $title, $title, $this->capability, $this->core->plugin_slug($id), array(
                        $this,
                        'tabs_admin_page'
                    ));
                } else {
                    $this->page_hooks[$id] = add_submenu_page($parent_menu, $title, $title, $this->capability, $this->core->plugin_slug($id), function () use ($id, $redirect) {
                        if (!$redirect) {
                            $this->tabs_admin_page();
                        } else {
                            wp_redirect($redirect);
                            exit;
                        }
                    }, $order);
                }

                if ($this->core->plugin()->top_menu()) {
                    remove_submenu_page($this->core->plugin_slug(), $this->core->plugin_slug());
                }

                if ($this->is_tab($id)) {

                    if (isset($tab['callback']) && is_callable($tab['callback'])) {

                        call_user_func($tab['callback']);
                    }

                    if (isset($tab['callbacks']) && is_array($tab['callbacks'])) {

                        foreach ($tab['callbacks'] as $callback) {
                            if (is_callable($callback)) {
                                call_user_func($callback);
                            }
                        }
                    }
                }
            }
        }

        public function tabs_network_admin_menu()
        {

            if (!$this->is_network_active || !$this->core->is_freemius()) {
                return;
            }

            if ($this->core->access_manager()->is_activation_mode()) {

                add_submenu_page($this->core->framework_slug(), $this->core->plugin_menu_name(), $this->core->plugin_menu_name(), $this->capability, $this->core->plugin_slug(), array(
                    $this,
                    'tabs_admin_page'
                ), 0);

            } else if ($this->core->access_manager()->is_registered()) {

                $this->core->framework_tabs()->network_registered_plugins[] = $this->core;

                add_submenu_page($this->core->framework_slug(), $this->core->plugin_menu_name(), $this->core->plugin_menu_name(), $this->capability, $this->core->plugin_slug(), function () {

                    wp_redirect($this->core->access_manager()->get_account_url());
                    exit;

                }, 0);

            }
        }

        public function tabs_admin_page($page_content = null, $minimal = false, $hide_title = false)
        {

            $classes = array('wrap', 'xtfw-admin-tabs-wrap', $this->core->plugin_slug("tabs-wrap"));

            if ($minimal) {
                $classes[] = 'xtfw-admin-tabs-minimal';
            }

            ?>
            <div class="<?php echo esc_attr(implode(" ", $classes)); ?>">

                <div class="xtfw-admin-tabs-header">

                    <?php
                    if (!$minimal) {

                        echo '<span class="xtfw-badges">';
                        $this->render_header_badges();
                        echo '</span>';
                    }
                    ?>

                    <?php if (!$hide_title): ?>
                        <?php if (!empty($this->logo)): ?>
                            <div class="xtfw-admin-tabs-logo">
                                <img alt="<?php echo esc_attr($this->core->plugin_name()); ?>" src="<?php echo esc_url($this->logo); ?>" class="image-50"/>
                            </div>
                        <?php else: ?>
                            <h1>
	                            <img alt="<?php echo esc_attr($this->core->plugin_name()); ?>" src="<?php echo esc_url($this->core->framework_logo()); ?>" class="xtfw-logo image-50"/><?php echo esc_html($this->core->plugin_name()); ?>
	                        </h1>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($this->description)): ?>
                        <div class="xtfw-admin-tabs-description">
                            <?php echo esc_html($this->description); ?>
                        </div>
                    <?php endif; ?>

                </div>

                <?php
                if (!$minimal) {
                    $this->show_nav();
                } else {

                    $this->show_tab($page_content);

                    return;
                }
                ?>

                <div class="xtfw-admin-tabs-panel xtfw-<?php echo esc_attr($this->get_tab_id()); ?>-tab xtfw-panel-<?php echo esc_attr($this->get_tab_content_type()); ?>-type">

                    <?php $this->show_tab($page_content); ?>

                </div>

                <script type="text/javascript">
                    XT_FOLLOW.init();
                </script>

            </div>

            <?php

        }

        public function render_header_badges($include_version = true)
        {
	        
	        if($this->core->access_manager()) {
			 
	            if ($this->core->access_manager()->is__premium_only()) {
	
	                if ($this->core->access_manager()->can_use_premium_code()) {
	                    echo ($this->core->get_license_activation_link__premium_only(esc_html__('Active License', 'xt-framework'), array('xtfw-badge', 'xtfw-badge-green')));
	                } else {
	                    echo ($this->core->get_license_activation_link__premium_only(esc_html__('Inactive License', 'xt-framework'), array('xtfw-badge', 'xtfw-badge-grey')));
	                }
	
	                echo ($this->core->get_account_link__premium_only(esc_html__('Premium Version', 'xt-framework'), array('xtfw-badge ', 'xtfw-badge-blue')));
	
	            } else if($this->core->has_premium_version()) {

	                echo '<span class="xtfw-badge xtfw-badge-grey">' . esc_html__('Free Version', 'xt-framework') . '</span>';

	            }else{

                    echo '<span class="xtfw-badge xtfw-badge-grey">' . esc_html__('Free', 'xt-framework') . '</span>';
                }
	            
            }else{

                echo '<span class="xtfw-badge xtfw-badge-grey">' . esc_html__('Free', 'xt-framework') . '</span>';
            }

            if ($include_version) {
                $this->render_version_badge();
            }
        }

        public function render_version_badge()
        {
            ?>
            <span class="xtfw-badge xtfw-badge-version"><strong>V.<?php echo esc_html($this->core->plugin_version()); ?></strong></span>
            <?php
        }

        public function show_tab($page_content = null)
        {

            $tab = parent::show_tab($page_content);

            do_action($this->core->plugin_prefix('admin_tabs_show_tab'), $tab, $this);

            return $tab;
        }

        public function get_active_tab_url($params = null)
        {

            return $this->get_tab_url($this->active_tab, $params);
        }

        public function get_tab_url($tab = '', $params = null, $network = false)
        {

            return esc_url($this->core->plugin_admin_url($tab, $params, $network));
        }

        public function get_changelog()
        {

            $readme_file = $this->core->plugin_path('/', 'readme.txt');

            return xtfw_changelog_html($readme_file);
        }

    }
}