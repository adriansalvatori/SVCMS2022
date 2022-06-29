<?php
class Guaven_Woos_Init {
    public function __construct(){   

        if (function_exists('is_multisite') and is_multisite()){
            $gws_home_url=home_url();
            if (!empty($_SERVER["HTTP_CF_CONNECTING_IP"]) or (isset($_SERVER["HTTPS"]) and $_SERVER["HTTPS"]=='on') ){
                $gws_home_url=str_replace("http:","https:",$gws_home_url);
            }
            define('GUAVEN_WOO_SEARCH_CACHE_ENDFIX', md5($gws_home_url));
        } 
        else {
            define('GUAVEN_WOO_SEARCH_CACHE_ENDFIX', '');
        }

        $guaven_woo_search_front     = new Guaven_woo_search_front();
        $guaven_woo_search_backend   = new Guaven_woo_search_backend();
        $guaven_woo_search_admin     = new Guaven_woo_search_admin();
        $guaven_woo_search_analytics = new Guaven_woo_search_analytics();

        $guaven_woos_active_plugins  = apply_filters('active_plugins', get_option('active_plugins'));
        $guaven_woo_search_admin->woo_activeness = in_array('woocommerce/woocommerce.php', $guaven_woos_active_plugins) ? 1 : 0;

        if ($guaven_woo_search_admin->woo_activeness == 0) {
            $guaven_woos_active_plugins_ms           = get_site_option('active_sitewide_plugins');
            $guaven_woo_search_admin->woo_activeness = (is_array($guaven_woos_active_plugins_ms) and !empty($guaven_woos_active_plugins_ms['woocommerce/woocommerce.php'])) ? 1 : 0;
        }
        global $argv;
        $guaven_woo_search_admin->argv = (isset($argv[1])&&strlen($argv[1])>=23) ? $argv : array();

        if (!empty($guaven_woo_search_admin->argv[1])) {
        add_action('init', array($guaven_woo_search_admin,'cache_rebuild_cron_mode'));
        }

        add_action('guaven_woos_custom_hook_rebuilding', function () {
            $guaven_woo_search_admin     = new Guaven_woo_search_admin();
            $guaven_woo_search_admin->argv[1] = $guaven_woo_search_admin->cron_token();
            $guaven_woo_search_admin->cache_rebuild_ajax_callback();
        });
        
        add_action('admin_menu', array($guaven_woo_search_admin,'admin_menu'));
        add_action('admin_menu', array($guaven_woo_search_analytics,'admin_menu'));
        add_action('admin_enqueue_scripts', array($guaven_woo_search_admin,'enqueue'), 100);
        add_action('admin_notices', array($guaven_woo_search_admin, 'check_for_support_expired'));
        add_action('edit_post', array($guaven_woo_search_admin,'edit_hook_rebuilder'));

        if (empty($guaven_woo_search_admin->guaven_woos_firstrun) and $guaven_woo_search_admin->woo_activeness == 1) {
            add_action('admin_footer', array($guaven_woo_search_admin,'do_rebuilder_at_footer'));
            add_shortcode('woo_search_standalone', array($guaven_woo_search_front,'standalone'));
            add_action('admin_bar_menu', array($guaven_woo_search_admin,'woos_rebuild_top_button'), 999);
            add_action('wp_enqueue_scripts', array($guaven_woo_search_front,'enqueue'), 100);
            add_action('wp_footer', array($guaven_woo_search_front,'inline_js'), 100);
            add_action('wp_ajax_cache_rebuild_ajax', array($guaven_woo_search_admin,'cache_rebuild_ajax_callback'));
            add_action('wp', array($guaven_woo_search_front,'personal_interest_collector'));
            add_action('woocommerce_order_status_completed', array($guaven_woo_search_front,'add_purchase_score_when_new_order'), 10, 1);
            add_action('posts_where', array($guaven_woo_search_backend,'backend_search_filter'), 10001);
            add_action('posts_where_request', array($guaven_woo_search_backend,'backend_search_filter'), 10001);
            add_action('pre_get_posts', array($guaven_woo_search_backend,'standalone_search_resetter'), 100);
            add_action('wp_ajax_guaven_woos_tracker', array($guaven_woo_search_front,'guaven_woos_tracker_callback'));
            add_action('wp_ajax_nopriv_guaven_woos_tracker', array($guaven_woo_search_front,'guaven_woos_tracker_callback'));
            add_action('wp_ajax_guaven_woos_trend', array($guaven_woo_search_front,'guaven_woos_trend_data'));
            add_action('wp_ajax_nopriv_guaven_woos_trend', array($guaven_woo_search_front,'guaven_woos_trend_data'));
            add_action('wp_ajax_guaven_woos_pass_to_backend', array($guaven_woo_search_backend,'guaven_woos_pass_to_backend'));
            add_action('wp_ajax_nopriv_guaven_woos_pass_to_backend', array($guaven_woo_search_backend,'guaven_woos_pass_to_backend'));
            add_action('wp_ajax_guaven_notice_dismissed', array($guaven_woo_search_admin, 'notice_dismissed'));
            add_action('wp_ajax_guaven_get_data_version', array($guaven_woo_search_front , 'get_jscss_version'));
            add_action('wp_ajax_nopriv_guaven_get_data_version', array($guaven_woo_search_front , 'get_jscss_version'));
            add_action('wp_ajax_guaven_purengine_search', array($guaven_woo_search_backend, 'purengine_ajax_callback'));
            add_action('wp_ajax_nopriv_guaven_purengine_search', array($guaven_woo_search_backend, 'purengine_ajax_callback'));
            add_action('wp_head', array($guaven_woo_search_backend,'force_search_reload'));
            add_action('save_post', array($guaven_woo_search_backend, 'purengine_post_saved'), 10, 3);
        }

        add_filter('posts_orderby', array($guaven_woo_search_backend,'backend_search_orderby'));
        add_filter('posts_search',array($guaven_woo_search_backend,'backend_search_replacer'),10001);

        add_action('init', function(){
        if(isset($_POST["s"]) and isset($_POST["guaven_woos_ids"]) and isset($_POST["post_type"]) and $_POST["post_type"]=='product' ){
            $_POST["s"]=trim($_POST["s"]);
            $_GET["s"]=trim($_GET["s"]);
        }
        });


        //by default both RESP API endpoints are disabled
        add_filter('gws_site_search',[$guaven_woo_search_backend ,'purengine_check_api_enabled']);

        add_action('woocommerce_loaded' , function (){
        add_action( 'rest_api_init',  [
                new Guaven_woo_search_WC_REST_Controller,
                'register_routes'
            ]);
        });
    }
}