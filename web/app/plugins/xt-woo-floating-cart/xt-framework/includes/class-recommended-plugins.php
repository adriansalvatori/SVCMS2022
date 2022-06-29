<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'XT_Framework_Recommended_Plugins' ) ) {

    /**
     * Class that takes care of adding XplodedThemes plugins within wordpress popular plugins sections.
     *
     * @package    XT_Framework
     * @subpackage XT_Framework/includes
     * @author     XplodedThemes
     */

    class XT_Framework_Recommended_Plugins {

        public static $_instance;

        /**
         * Core class reference.
         *
         * @since    1.0.0
         * @access   protected
         * @var      XT_Framework   $core    Core Class
         */
        protected $core;

        /**
         * @var string the page slug
         */
        protected $slug = 'plugins';

        /**
         * @var string wp plugin install page custom tab slug
         */
        protected $tab = 'xt_plugins';

        /**
         * Author name to base plugin search on
         *
         * @since    1.0.0
         * @access   protected
         * @var      string $author
         */
        protected $author = 'XplodedThemes';

        /**
         * Construct.
         *
         * @since    1.0.0
         * @access   public
         * @var      XT_Framework $core Core Class
         */
        public function __construct( $core ) {

            $this->core = $core;

            add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'), 99);
            add_filter('xtfw_admin_tabs', array( $this, 'add_plugins_tab'), 10, 1 );
            add_filter('install_plugins_tabs', array( $this, 'add_native_plugins_tab'), 10, 1);
            add_filter('install_plugins_table_api_args_'.$this->tab, array( $this, 'native_plugins_args'), 10, 1);
            add_action('install_plugins_'.$this->tab,  array( $this, 'browse_plugins') );
            add_action('network_install_plugins_'.$this->tab,  array( $this, 'browse_plugins') );
            add_filter('plugins_api_result', array( $this, 'plugin_results' ), 1, 3 );

        }

        public function is_plugin_install_page() {

            $screen = get_current_screen();

            return (!empty($screen) && ($screen->id === 'plugin-install' || $screen->id === 'plugin-install-network'));

        }

        public function is_plugin_install_page_xt_tabs($_tab = false) {

            $isPluginInstallPage = $this->is_plugin_install_page();

            $tab = !empty(filter_input(INPUT_POST, 'tab')) ? filter_input(INPUT_POST, 'tab') : filter_input(INPUT_GET, 'tab');

            if($_tab) {

                return $isPluginInstallPage && ($tab === $_tab);
            }

            return $isPluginInstallPage && in_array($tab, array('recommended', 'popular', 'search'));
        }

        public function is_framework_plugins_page() {

            return $this->core->framework_tabs()->is_tab($this->slug);
        }

        public function enqueue_assets() {

            if(!$this->is_framework_plugins_page() && !$this->is_plugin_install_page_xt_tabs()) {
                return;
            }

            wp_add_inline_script('xtfw-inline', '
                (function( $ ) {
                   
                    function alter_frame(frame) {
                    
                        var doc = $(frame).contents().get(0);
                        var filter = 5;
                    
                        $(".counter-container", doc).each(function() {
                    
                            var has_reviews = parseInt($(this, doc).find(".counter-count").text().trim()) > 0;
                            var link = $(this, doc).find(".counter-label a");
                            var plugin_link = $("#plugin-information-content > .fyi > ul:first-child", doc).find("li").last().find("a");
                    
                            if(!has_reviews) {
                    
                                link.removeAttr("href");
                    
                            }else if(plugin_link.length){
                    
                                link.attr("href", plugin_link.attr("href")+"?filter="+filter+"#ratings");
                            }
                    
                            filter--;
                        });
                    }
                    
                    var observer = new MutationObserver(function (mutations) {
                        mutations.forEach(function (mutation) {
                            [].filter.call(mutation.addedNodes, function (node) {
                                return node.nodeName == "IFRAME" && node.id === "TB_iframeContent";
                            }).forEach(function (frame) {
                                frame.addEventListener("load", function (e) {
                                    alter_frame(frame);
                                });
                            });
                        });
                    });
                    $(document).ready(function() {
                        observer.observe(window.document.body, { childList: true, subtree: true });
                    });
                })( jQuery );
            ');
        }

        public function add_plugins_tab($tabs) {

            $tabs[] = array(
                'id'        => $this->slug,
                'title'     => esc_html__( 'Browse Plugins', 'xt-framework' ),
                'badges'    => array($this, 'get_badges'),
                'show_menu' => true,
                'order'     => 20,
                'content'   => array(
                    'type'     => 'function',
                    'function' => array( $this, 'browse_plugins' )
                ),
                'callback'  => array( $this, 'browse_plugins_assets' )
            );

            return $tabs;
        }


        public function add_native_plugins_tab($tabs) {

            $tabs[$this->tab] = esc_html__( 'XT Plugins', 'xt-framework' );

            return $tabs;
        }

        public function native_plugins_args($args) {

            return array(
                'page'     => $this->get_pagenum(),
                'per_page' => 36,
                'browse' => $this->tab,
                // Send the locale to the API so it can provide context-sensitive results.
                'locale'   => get_user_locale(),
            );
        }

        public function get_badges() {

            $total = $this->core->transient()->result('get_total_plugins', function() {

                $table = $this->get_plugins_table();
                $items = !empty($table) ? $table->items : array();
                return !empty($items) && is_array($items) ? count($items) : 0;

            }, WEEK_IN_SECONDS);

            return array(
                array(
                    'content' => $total,
                    'hide_global' => true
                )
            );
        }

        /**
         * Gets the current plugin page number.
         *
         * @return int
         */
        public function get_pagenum() {

            $pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;

            return max( 1, $pagenum );
        }

        public function get_plugins() {

            return $this->core->cache()->result('get_plugins', function() {

                $result = plugins_api('query_plugins', array(
                    'page'     => $this->get_pagenum(),
                    'per_page' => 36,
                    'author' => $this->author,
                    'xt_plugins_query' => true
                ));

                $result->plugins = $this->alter_plugin_results($result->plugins);

                return $result;

            });
        }

        public function get_plugins_table() {

            return $this->core->cache()->result('plugins_table', function() {

                if(is_multisite() && is_admin()) {

                    add_filter('self_admin_url', function($url, $path, $scheme ) {

                        if(strpos($url, 'plugin-install.php') !== false) {
                            $url = network_admin_url($path, $scheme);
                        }

                        return $url;

                    }, 10, 3);
                }

                // Avoid weird error trigger: Undefined property: stdClass::$plugin in wp-includes/class-wp-list-util.php on line 166.
                add_filter("site_transient_update_plugins", '__return_false', 10, 2);

                $wp_list_table = _get_list_table('WP_Plugin_Install_List_Table');
                $wp_list_table->prepare_items();

                remove_filter("site_transient_update_plugins", '__return_false', 10);

                return $wp_list_table;

            });

        }

        public function browse_plugins() {

            $table = $this->get_plugins_table();

            if(!empty($table)) {

                echo '<form id="plugin-filter" method="post">';
                $table->display();
                echo '</form>';
            }
        }

        public function browse_plugins_assets() {

            wp_enqueue_script( 'plugin-install' );
            wp_enqueue_script( 'updates' );
            wp_add_inline_script( 'updates', 'window.pagenow = "plugin-install";', 'before');
            add_thickbox();
        }

        public function plugin_results( $res, $action, $args ) {

            if (is_wp_error( $res ) ) {
                return $res;
            }

            if ( $action === 'query_plugins' ) {

                $new_result = $this->alter_query_plugins($res, $action, $args);

                if (is_wp_error( $new_result ) ) {
                    return $res;
                }

                if($this->is_plugin_install_page_xt_tabs()) {

                    $position = empty( $args->search ) && empty( $args->tag ) ? 8 : 4;

                    $top_plugins   = array_splice( $res->plugins, 0, $position );
                    $below_plugins = array_splice( $res->plugins, $position );

                    $top_plugins = array_merge( $new_result->plugins, $top_plugins );

                    shuffle($top_plugins);

                    $res->plugins = array_merge( $top_plugins, $below_plugins );

                }else if($this->is_plugin_install_page_xt_tabs($this->tab) || $this->is_framework_plugins_page()) {

                    $res->plugins = $new_result->plugins;
                    $res->info['results'] = $new_result->info['results'];
                }

            }else if($action === 'plugin_information') {

                if(!empty($res->author) && strpos($res->author, $this->author)) {

                    $res = json_decode(json_encode($res), true);

                    return (object) $this->alter_plugin_info($res);
                }
            }

            return $res;
        }

        function search_contains($needles, $haystack) {
            return count(array_intersect($needles, explode(" ", preg_replace("/[^A-Za-z0-9' -]/", "", $haystack))));
        }

        function search_terms() {
            return array('xplodedthemes', 'woo', 'woocommerce', 'cart', 'quick view', 'quickview', 'points', 'rewards', 'swatches', 'attributes', 'variation', 'variations');
        }

        public function alter_query_plugins( $res, $action, $args ) {

            $args = (array) $args;

            if (
                !empty( $args['xt_plugins_query'] ) ||
                !empty( $args['author'] ) ||
                (!empty( $args['search'] ) && !$this->search_contains($this->search_terms(), $args['search'])) ||
                (!empty( $args['tag'] ) && !$this->search_contains($this->search_terms(), $args['tag']))
            ) {
                return $res;
            }

            return $this->get_plugins();
        }

        function is_plugin_active($slug) {

            $slug = str_replace('-lite', '', $slug);

            $active_plugins = wp_cache_get('active_plugins');
            if($active_plugins === false) {
                $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
                wp_cache_set('active_plugins', $active_plugins);
            }

            foreach ( $active_plugins as $plugin ) {

                if ( strpos( $plugin, $slug ) !== false ) {
                    return true;
                }
            }
            return false;
        }

        public function alter_plugin_results( $plugins ) {

            $slugs = array();

            $plugins = array_map(function($plugin) use(&$slugs) {

                if(is_object($plugin)) {
                    return $plugin;
                }

                if(strpos($plugin['author'], $this->author)) {

                    if(in_array($plugin['slug'], $slugs)) {
                        $plugin['duplicated'] = true;
                    }

                    $slugs[] = $plugin['slug'];

                    return $this->alter_plugin_info($plugin);
                }

                return $plugin;

            }, $plugins);

            $plugins = array_filter($plugins, function($plugin) {

                if(is_object($plugin)) {
                    return $plugin;
                }

                if(!empty($plugin['duplicated'])) {
                    return false;
                }

                if(strpos($plugin['slug'], 'woo-') === false) {
                    return false;
                }

                if($this->is_plugin_install_page_xt_tabs() && $this->is_plugin_active($plugin['slug'])) {
                    return false;
                }

                return true;
            });

            if($this->is_plugin_install_page_xt_tabs()) {
                shuffle($plugins);
                $count = $this->is_plugin_install_page_xt_tabs('search') ? 5 : 3;
                $plugins = array_slice($plugins, 0 , $count);
            }

            return $plugins;
        }

        public function alter_plugin_info($plugin) {

            if(!empty($plugin['ratings'])) {

                $total_rating_value = 0;
                $ratings = array();

                foreach($plugin['ratings'] as $rating => $total) {

                    $total =  absint($total);

                    if(absint($rating) < 4 && $total > 0) {
                        $total = 0;
                    }

                    $ratings[$rating] = $total;
                    $total_rating_value += ($rating * $total);
                }

                $plugin['ratings'] = $ratings;
                if(!empty($plugin['num_ratings'])) {
                    $plugin['num_ratings'] = $plugin['ratings']['5'] + $plugin['ratings']['4'];
                    $plugin['rating'] = ((($total_rating_value / $plugin['num_ratings']) * 100) / 5);
                }
            }

            if(!empty($plugin['sections']) && !empty($plugin['sections']['reviews'])) {

                $reviews = str_replace('<div class="review">', '|<div class="review">', $plugin['sections']['reviews']);
                $reviews = substr($reviews, 1);
                $reviews = explode('|', $reviews);

                $reviews = array_filter($reviews, function($review) {

                    preg_match('/data-rating\=\"(4|5)\"/', $review, $match);
                    return !empty($match[1]);
                });

                $plugin['sections']['reviews'] = implode('', $reviews);
            }

            $plugin['external'] = true;
            $plugin['homepage'] = $this->core->get_xt_url('plugin-info', $plugin['homepage']);
            $plugin['author_profile'] = $this->core->get_xt_url('plugin-info');
            $plugin['author'] = sprintf('<a href="%s" target="_blank">%s</a>', $plugin['author_profile'], $this->author);

            if(!empty($plugin['contributors'])) {
                foreach ($plugin['contributors'] as $key => $contributor) {

                    if($key === strtolower($this->author)) {

                        $contributor['profile'] = $plugin['author_profile'];
                        $contributor['display_name'] = $this->author;
                        $plugin['contributors'][$key] = $contributor;
                    }
                }
            }

            return $plugin;
        }

        /**
         * Main XT_Framework_Recommended_Plugins Instance
         *
         * Ensures only one instance of XT_Framework_Recommended_Plugins is loaded or can be loaded.
         *
         * @var XT_Framework   $core    Core Class
         *
         * @return XT_Framework_Recommended_Plugins instance
         * @since 1.0.0
         * @static
         */
        public static function instance($core) {
            if ( empty( self::$_instance ) ) {
                self::$_instance = new self($core);
            }

            return self::$_instance;
        } // End instance()

    }
}