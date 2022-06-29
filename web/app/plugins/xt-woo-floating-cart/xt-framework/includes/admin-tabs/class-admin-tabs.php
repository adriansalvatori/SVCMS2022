<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if(!class_exists('XT_Framework_Admin_Tabs')) {

    /**
     * The admin-specific functionality of the plugin.
     *
     * Defines the plugin name, version, and two examples hooks for how to
     * enqueue the admin-specific stylesheet and JavaScript.
     *
     * @package    XT_Framework_Admin_Tabs
     * @author     XplodedThemes
     */
    abstract class XT_Framework_Admin_Tabs {

        /**
         * Core class reference.
         *
         * @since    1.0.0
         * @access   private
         * @var      XT_Framework    core    Core Class
         */
        public $core;

        public $tabs = array();
        public $default_tab;
        public $active_tab;
        public $capability = 'manage_options';
        public $page_hooks = array();

        /**
         *
         * /**
         * Initialize the class and set its properties.
         *
         * @since    1.0.0
         * @var      Object    core    Core Class
         */
        public function __construct( $core ) {

            $this->core = $core;

            add_action('xtfw_plugins_loaded', function() {
                $this->init();
            });

        }

        protected function init() {

            $this->add_default_tabs();
            $this->apply_filters();

            usort( $this->tabs, array( $this, 'sort_tab' ) );
            add_action( 'admin_menu', array( $this, 'tabs_admin_menu' ));
            add_action( 'network_admin_menu', array( $this, 'tabs_network_admin_menu' ));

            $this->set_default_tab();
            $this->set_active_tab();

            if ( $this->is_admin_tabs_page() ) {

                add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles'), 999 );
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts'), 999 );
                add_action( 'admin_body_class', array( $this, 'admin_body_class'), 999 );
                add_filter( 'admin_footer_text', array( $this, 'footer_text'), 999 );
                add_filter( 'update_footer', array( $this, 'footer_version'), 999 );
            }
        }

        abstract protected function apply_filters();

        abstract protected function add_default_tabs();

        abstract protected function set_active_tab();

        abstract protected function is_admin_tabs_page();

        abstract protected function tabs_admin_menu();

        abstract protected function tabs_network_admin_menu();

        abstract protected function tabs_admin_page();

        abstract protected function get_tab_url();

        abstract public function footer_version();

        public function set_default_tab() {

            if ( ! empty( $this->tabs ) ) {
                $this->default_tab = current( $this->tabs )['id'];
            }
        }

        public function tab_exists( $id ) {

            foreach ( $this->tabs as $tab ) {

                if ( $tab['id'] === $id || $tab['id'] === '_'.$id) {
                    return true;
                }
            }

            return false;
        }

        public function sort_tab( $a, $b ) {

            if ( ! isset( $a['order'] ) ) {
                $a['order'] = 0;
            }

            if ( ! isset( $b['order'] ) ) {
                $b['order'] = 0;
            }

            return $a['order'] - $b['order'];
        }


        public function footer_text() {

            return '<span id="footer-thankyou">Developed by <a href="'.$this->core->get_xt_url('admin-footer').'" target="_blank"><strong>XplodedThemes</strong></a></span>';
        }

        public function admin_body_class( $classes ) {

            if ( $this->is_admin_tabs_page() ) {
                $classes .= ' xtfw-admin-tabs-page';
                $classes .= ' ' . $this->core->plugin_slug( 'admin' );
            }

            return $classes;
        }

        /**
         * Register the stylesheets for the tabs area.
         *
         * @since    1.0.0
         */
        public function enqueue_styles() {

            wp_enqueue_style(  'xtfw_admin-tabs' , xtfw_dir_url( XTFW_DIR_ADMIN_TABS_ASSETS ) . '/css/admin-tabs.css', array(), XTFW_VERSION, 'all' );

        }

        /**
         * Register the JavaScript for the tabs area.
         *
         * @since    1.0.0
         */
        public function enqueue_scripts() {

            wp_enqueue_style( 'xt-follow' );
            wp_enqueue_script( 'xt-follow' );

            wp_add_inline_script('xtfw-inline', '
            
                (function( $ ) {
                
                    // Admin Tabs Mobile Menu Toggle
                    $(document).on("click", ".nav-tab-toggle", function(evt) {
                
                        evt.preventDefault();
                        $(this).toggleClass("expanded");
                   
                    });
             
                })( jQuery );
            ');

        }

        public function show_nav() {

            echo '<div class="nav-tab-toggle">';
            $this->show_nav_tab($this->get_tab());
            echo '</div>';

            echo '<div class="nav-tab-wrapper">';

            $main_keys      = array();
            $secondary_keys = array();

            foreach ( $this->tabs as $key => $tab ) {

                if ( ! empty( $tab['hide_tab'] ) ) {
                    continue;
                }

                if ( ! empty( $tab['secondary'] ) ) {
                    $secondary_keys[] = $key;
                } else {
                    $main_keys[] = $key;
                }
            }

            foreach ( $main_keys as $key ) {

                $this->show_nav_tab( $this->tabs[ $key ] );
            }

            if ( ! empty( $secondary_keys ) ) {
                echo '<span class="nav-secondary-tabs">';

                foreach ( $secondary_keys as $key ) {

                    $this->show_nav_tab( $this->tabs[ $key ] );
                }

                echo '</span>';
            }

            echo '</div>';
        }

        public function show_nav_tab( $tab ) {

            $id       = $tab['id'];
            $url      = $this->get_tab_url( $id );
            $featured = ! empty( $tab['featured'] );

            $target = '_self';

            if ( ! empty( $tab['external'] ) ) {
                $url    = $tab['external'];
                $target = '_blank';
            }

            if ( ! empty( $tab['redirect'] ) ) {
                $url    = $tab['redirect'];
                $target = '_self';
            }

            if ( ! empty( $tab['target'] ) ) {
                $target = $tab['target'];
            }

            $classes = array('nav-tab');

            if ( $this->is_tab( $id ) ) {
                $classes[] = 'nav-tab-active';
            }

            if ( $featured ) {
                $classes[] = 'nav-tab-featured';
            }

            if ( empty( $tab['hide_title'] ) ) {
                $classes[] = 'nav-tab-has-title';
            } else {
                $classes[] = 'nav-tab-hide-title';
            }

            if ( ! empty( $tab['icon'] ) ) {
                $classes[] = 'nav-tab-has-icon';
            }

            if ( ! empty( $tab['secondary'] ) ) {
                $classes[] = 'secondary';
            }

            if ( ! empty( $tab['classes'] ) ) {
                $classes = array_merge( $classes, $tab['classes'] );
            }

            echo '<a title="' . wp_kses_post( $tab['title'] ) . '" href="' . esc_url( $url ) . '" class="' . esc_attr(implode( " ", $classes )) . '" target="' . esc_attr( $target ) . '">';

            if ( ! empty( $tab['icon'] ) ) {
                echo '<span class="dashicons ' . esc_attr( $tab['icon'] ) . '"></span>';
            }

            echo '<span class="nav-tab-title">';

            echo esc_html( $tab['title'] );

            if( !empty($tab['badges'])) {

                $badges = is_callable($tab['badges']) ? $tab['badges']() : $tab['badges'];

                foreach($badges as $badge) {

                    $badge['title'] = !empty($badge['title']) ? $badge['title'] : '';
                    echo '<span title="'.esc_attr($badge['title']).'" class="update-plugins">'.wp_kses_post($badge['content']).'</span>';
                }
            }

            echo '</span>';


            if (!empty($tab['flashing_badge']) && $this->show_flashing_badge($tab['flashing_badge'])) {
                echo '<span class="nav-tab-flashing-badge"></span>';
            }

            echo '</a>';
        }

        public function show_tab( $page_content = null ) {

            $tab = $this->get_tab();

            if ( ! empty( $page_content ) ) {
                $tab['content']['type'] = 'html';
                $tab['content']['html'] = $page_content;
            }

            $id  = ! empty( $tab['id'] ) ? $tab['id'] : null;
            $url = $this->get_tab_url( $id, array( 'nocache' => '1' ) );

            if ( ! empty( $tab['content'] ) ) {

                $content      = $tab['content'];
                $content_type = ! empty( $content['type'] ) ? $content['type'] : null;

                if ( ! empty( $content['show_refresh'] ) ) {

                    echo '
                    <a class="xtfw-refresh-link" href="' . esc_url( $url ) . '">
                        <span class="dashicons dashicons-image-rotate"></span> 
                        '.esc_html__('Refresh', 'xt-framework').'
                    </a>';
                }

                if ( ! empty( $content['title'] ) ) {

                    echo '<h3 class="xtfw-admin-tabs-panel-title">' . wp_kses_post($content['title']) . '</h3>';
                }

                if ( $content_type === 'template' ) {

                    if ( file_exists( $content['template'] ) ) {
                        include( $content['template'] );
                    }

                } else if ( $content_type === 'function' ) {

                    $args = ! empty( $content['args'] ) ? $content['args'] : array();
                    if ( ! empty( $content['function'] ) ) {
                        echo call_user_func_array( $content['function'], $args );
                    }

                } else if ( $content_type === 'html' ) {

                    echo ($content['html']);

                } else if ( $content_type === 'url' ) {

                    $json_decode = ! empty( $content['json'] );
                    echo ($this->remote_get( $content['url'], $json_decode ));

                } else if ( $content_type === 'changelog' && method_exists( $this, 'get_changelog' ) ) {

                    echo ($this->get_changelog());
                }

            }

            return $tab;

        }

        public function is_tab( $tab ) {

            return $this->active_tab === $tab || '_' . $this->active_tab === $tab;
        }

        public function is_default_tab( $tab ) {

            return $tab == $this->default_tab;
        }

        public function get_tab( $id = null ) {

            $id = ! empty( $id ) ? $id : $this->get_tab_id();

            $tab_key = array_search( $id, array_column( $this->tabs, 'id' ) );

            if($tab_key === false) {
                $id = '_'.$id;
                $tab_key = array_search( $id, array_column( $this->tabs, 'id' ) );
            }

            $tab = ( $tab_key !== false ) ? $this->tabs[ $tab_key ] : null;

            return $tab;
        }

        public function get_tab_id() {

            return $this->active_tab;
        }

        public function get_tab_content_type() {

            $tab = $this->get_tab();

            if ( ! empty( $tab['settings'] ) ) {
                return 'settings';
            }

            return ! empty( $tab['content']['type'] ) ? $tab['content']['type'] : 'default';
        }

        public function remote_get( $url, $json_decode = false ) {

            $cache_key = md5( $url );

            $content = get_site_transient( $cache_key );

            if ( $content === false || ! empty( $_GET['nocache'] ) !== null ) {

                if ( ! empty( $_GET['nocache'] ) ) {
                    $url = add_query_arg( 'nocache', intval( $_GET['nocache'] ), $url );
                }

                $response = wp_remote_get( $url, array( 'sslverify' => false ) );

                // Stop here if the response is an error.
                if ( is_wp_error( $response ) ) {

                    $content = '';

                    // Set temporary transient.
                    set_site_transient( $cache_key, $content, MINUTE_IN_SECONDS );

                } else {

                    // Retrieve data from the body and decode json format.
                    $content = wp_remote_retrieve_body( $response );

                    set_site_transient( $cache_key, $content, DAY_IN_SECONDS );
                }

            }

            if ( $json_decode ) {
                $content = json_decode( $content, true );
            }

            return $content;
        }

        public function get_global_badge() {

            $notifications = 0;

            foreach ( $this->tabs as $tab ) {
                if( !empty($tab['badges'])) {

                    $badges = is_callable($tab['badges']) ? $tab['badges']() : $tab['badges'];

                    foreach($badges as $badge) {

                        if(isset($badge['hide_global'])) {
                            continue;
                        }

                        $notifications += is_numeric($badge['content']) ? intval($badge['content']) : 1;
                    }
                }
            }

            if($notifications > 0) {
                return ' <span class="update-plugins">'.$notifications.'</span>';
            }

            return '';
        }

        public function get_badges_html(&$badges) {

            $badges_html = '';
            if( !empty($badges)) {

                $badges = is_callable($badges) ? $badges() : $badges;

                foreach($badges as $badge) {

                    $badge['title'] = !empty($badge['title']) ? $badge['title'] : '';
                    $badges_html .= ' <span title="'.esc_attr($badge['title']).'" class="update-plugins">'.sanitize_text_field($badge['content']).'</span>';
                }
            }

            return $badges_html;
        }

        public function show_flashing_badge($callable) {

            if( !empty($callable)) {

                return (bool)(is_callable($callable) ? $callable() : $callable);
            }

            return false;
        }

    }
}