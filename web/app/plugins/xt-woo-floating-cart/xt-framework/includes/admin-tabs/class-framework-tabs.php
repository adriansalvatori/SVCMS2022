<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if(!class_exists('XT_Framework_Framework_Tabs')) {

    /**
     * The admin-specific functionality of the plugin.
     *
     * Defines the plugin name, version, and two examples hooks for how to
     * enqueue the admin-specific stylesheet and JavaScript.
     *
     * @package    XT_Framework_Framework_Tabs
     * @author     XplodedThemes
     */
    class XT_Framework_Framework_Tabs extends XT_Framework_Admin_Tabs {

        public static $_instance;

        public $network_registered_plugins = array();

        protected function init() {

            $this->capability = $this->core->plugin_dependencies()->depends_on('WooCommerce') ? 'manage_woocommerce' : 'manage_options';

            parent::init();

            // Move framework menu just above the Plugins menu
            add_filter( 'custom_menu_order', array($this, 'menu_order'), 10, 1 );
            add_filter( 'menu_order', array($this, 'menu_order'), 10, 1 );
        }

        protected function apply_filters() {

            $this->tabs = apply_filters(  'xtfw_admin_tabs' , $this->tabs, $this );
        }

        public function footer_version() {
            return '<span class="alignright"><strong>' . $this->core->framework_name() . '</strong> - v' . $this->core->framework_version() . '</strong></span>';
        }

        public function set_active_tab() {

            if ( ! empty( $_GET['page'] ) && $_GET['page'] !== $this->core->framework_slug() ) {
                $page = sanitize_text_field($_GET['page']);
                $tab_id = str_replace( $this->core->framework_slug() . '-', '', $page );
                if ( $this->tab_exists( $tab_id ) ) {
                    $this->active_tab = $tab_id;
                }
            }

            if ( ! empty( $_GET['page'] ) && $_GET['page'] === $this->core->framework_slug() ) {

                if ( ! empty( $_GET['tab'] ) ) {
                    $tab_id = sanitize_text_field( $_GET['tab'] );
                    if ( $this->tab_exists( $tab_id ) ) {
                        $this->active_tab = $tab_id;
                    }
                } else {
                    $this->active_tab = $this->default_tab;
                }
            }
        }

        public function add_default_tabs() {

            $this->tabs[] = array(
                'id'         => 'changelog',
                'title'      => esc_html__( 'Change Log', 'xt-framework' ),
                'show_menu'  => false,
                'hide_title' => true,
                'icon'       => 'dashicons-media-text',
                'order'      => 100,
                'content'    => array(
                    'title'        => esc_html__( 'Change Log', 'xt-framework' ),
                    'type'         => 'changelog',
                    'show_refresh' => true
                ),
                'secondary'  => true
            );

            $this->tabs[] = array(
                'id'         => 'support',
                'title'      => esc_html__( 'Get Support', 'xt-framework' ),
                'show_menu'  => true,
                'hide_title' => true,
                'icon'       => 'dashicons-sos',
                'order'      => 110,
                'external'   => $this->core->get_xt_url('admin-support-icon', 'https://xplodedthemes.com/support'),
                'secondary'  => true
            );


            $this->tabs[] = array(
                'id'         => 'home',
                'title'      => esc_html__( 'XplodedThemes.com', 'xt-framework' ),
                'show_menu'  => false,
                'hide_title' => true,
                'icon'       => 'dashicons-admin-home',
                'order'      => 120,
                'external'   => $this->core->get_xt_url('admin-home-icon'),
                'secondary'  => true
            );

        }

        public function is_admin_tabs_page() {
            return ! empty( $_GET['page'] ) && ( ( $_GET['page'] === $this->core->framework_slug() ) || $_GET['page'] === $this->core->framework_slug( $this->active_tab ) );
        }

        public function tabs_admin_menu() {

            // Add global menu
            $global_badge = $this->get_global_badge();

            add_menu_page( $this->core->framework_menu_name(), $this->core->framework_menu_name().$global_badge, $this->capability, $this->core->framework_slug(), array(
                $this,
                'tabs_admin_page'
            ), $this->core->framework_icon());

            // Add menu divider
            add_submenu_page($this->core->framework_slug(), '', '<span class="xtfw-admin-menu-divider"></span>', $this->capability, '#', null, 0);

            foreach ( $this->tabs as $tab ) {

                $id    = $tab['id'];
                $title = ! empty( $tab['menu_title'] ) ? $tab['menu_title'] : $tab['title'];
                $title = apply_filters(  'xtfw_admin_tabs_tab_title' , $title, $tab );

                if( !empty($tab['badges'])) {
                    $title .= $this->get_badges_html($tab['badges']);
                }

                $order     = ! empty( $tab['order'] ) ? $tab['order'] : 1;
                $redirect  = ! empty( $tab['external'] ) ? $tab['external'] : '';
                $redirect  = ! empty( $tab['redirect'] ) ? $tab['redirect'] : $redirect;
                $show_menu = ! empty( $tab['show_menu'] );

                $parent_menu = $show_menu ? $this->core->framework_slug() : null;

                $this->page_hooks[ $id ] = add_submenu_page( $parent_menu, $title, $title, $this->capability, $this->core->framework_slug( $id ), function () use ( $id, $redirect ) {
                    if ( ! $redirect ) {
                        $this->tabs_admin_page();
                    } else {
                        wp_redirect( $redirect );
                        exit;
                    }
                }, $order );

                remove_submenu_page( $this->core->framework_slug(), $this->core->framework_slug() );

                if ( ! empty( $tab['callback'] ) && $this->is_tab( $id ) ) {
                    $tab['callback']();
                }
            }
        }

        public function tabs_network_admin_menu() {

            $this->tabs_admin_menu();
        }

        public function tabs_admin_page() {

            $classes = array( 'wrap', 'xtfw-admin-tabs-wrap', $this->core->plugin_slug( "tabs-wrap" ) );

            ?>
            <div class="<?php echo esc_attr(implode( " ", $classes )); ?>">

                <div class="xtfw-admin-tabs-header">

                    <span class="xtfw-badges">
                        <span class="xtfw-badge xtfw-badge-version"><strong>V.<?php echo esc_html($this->core->framework_version()); ?></strong></span>
                    </span>

                    <h1><img alt="<?php echo esc_attr($this->core->framework_name()); ?>" src="<?php echo esc_url( $this->core->framework_logo() ); ?>" class="xtfw-logo image-50"/><?php echo esc_html($this->core->framework_name()); ?></h1>

                </div>

                <?php $this->show_nav(); ?>

                <div class="xtfw-admin-tabs-panel xtfw-<?php echo esc_attr($this->get_tab_id()); ?>-tab">

                    <?php $this->show_tab(); ?>

                </div>

                <script type="text/javascript">
                    XT_FOLLOW.init();
                </script>

            </div>

            <?php
        }

        public function get_tab_url($tab = '', $params = null, $network = false) {

            $network = ($network || is_network_admin());

            return esc_url($this->core->framework_admin_url($tab, $params, $network));
        }

        public function menu_order($menu_order) {

            if(!$menu_order || !is_array($menu_order)) {
                return $menu_order;
            }

            $plugins_menu_index       = array_search( 'plugins.php', $menu_order ) + 1;

            if(empty($plugins_menu_index)) {
                $plugins_menu_index = array_search( 'tools.php', $menu_order ) + 1;
            }

            if(empty($plugins_menu_index)) {
                return $menu_order;
            }

            $framework_menu_index     = array_search( $this->core->framework_slug(), $menu_order );
            $framework_menu_new_index = $plugins_menu_index;

            if ( $framework_menu_index ) {
                $this->move_menu_element( $menu_order, $framework_menu_index, $framework_menu_new_index );
            }

            return $menu_order;
        }

        // helper function to move an element inside an array
        public function move_menu_element( &$array, $a, $b ) {
            $out = array_splice( $array, $a, 1 );
            array_splice( $array, $b, 0, $out );
        }

        public function get_changelog() {

            $readme_file = $this->core->plugin_framework_path( '/', 'readme.txt', true );

            return xtfw_changelog_html($readme_file);
        }

        /**
         * Main XT_Framework_Framework_Tabs Instance
         *
         * Ensures only one instance of XT_Framework_Framework_Tabs is loaded or can be loaded.
         *
         * @return XT_Framework_Framework_Tabs instance
         * @see XT_Framework_Framework_Tabs()
         * @since 1.0.0
         * @static
         */
        public static function instance( $core ) {
            if ( empty( self::$_instance ) ) {
                self::$_instance = new self( $core );
            }

            return self::$_instance;
        } // End instance()

    }
}