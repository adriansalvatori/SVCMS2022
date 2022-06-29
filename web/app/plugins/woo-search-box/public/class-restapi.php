<?php 

if (!defined('ABSPATH')) {
    die;
}

add_action('woocommerce_loaded' , function () {
    class Guaven_woo_search_WC_REST_Controller extends WC_REST_Products_Controller {
        protected $namespace = 'wc/v3';
        protected $rest_base = 'guaven-search/products';
    
        public function perform_search($request) {
            if(!isset($_GET["search"]))
                return new WP_REST_Response(['error' => 'Empty \'search\' query parameter'], 400);
            $search_engine = new Guaven_woo_search_backend();
            $extra_where_query = $search_engine->purengine_price_and_tax_query();
            $results = $search_engine->purengine_find_posts($_GET['search'], $extra_where_query);
            $product_ids = (array) explode(',', $results[1]);
            if(count($product_ids) <= 1 && empty($product_ids[1]))
                return [];

            $response = $this->process_results($request, $product_ids);
            return $response;
        }
    
        public function process_results($request, $product_ids) {
            unset($request['search']);
            $request['include'] = isset($request['include']) ? (array) $request['include']:[];
            $request['include'] = array_merge($product_ids, $request['include']);
            return $this->get_items($request);
        }
    
        public function register_routes() {
            //WC compatible API endpoint - response is raw wc json
            register_rest_route(
                $this->namespace,
                '/' . $this->rest_base,
                [
                    'methods' => 'GET',
                    'callback' => [$this, 'perform_search'],
                    'permission_callback' => function($request) {
                        $ret = apply_filters('gws_site_search', false);
                        if(!$ret)
                            return new WP_Error(400, 'Guaven Search Engine REST API feature is disabled.', []);

                        return call_user_func([$this, 'get_items_permissions_check'], $request);
                    }
                ]
            );

            $guaven_woo_search_backend   = new Guaven_woo_search_backend();
            //API endpoint for internal use - response is json_encoded html output
            register_rest_route( 'app/v2', '/gws_site_search/', array(
                'methods' => 'GET',
                'callback' => [$guaven_woo_search_backend ,'purengine_api_callback'],
                'permission_callback' => function($request) {
                    $ret = apply_filters('gws_site_search', false);
                    if(!$ret)
                        return new WP_Error(400, 'Guaven Search Engine REST API feature is disabled.', []);
                    return $ret;
                }
            ) );

            $guaven_woo_search_backend   = new Guaven_woo_search_backend();
            //add sync cache results to cache DB
            register_rest_route( 'app/v2', '/gws_site_search_cache_insert/', array(
                'methods' => 'POST',
                'callback' => [$guaven_woo_search_backend ,'purengine_async_do_cache_insert'],
                'permission_callback' => function($request) {
                    $ret = apply_filters('gws_site_search', false);
                    if(!$ret)
                        return new WP_Error(400, 'Guaven Search Engine REST API feature is disabled.', []);
                    return $ret;
                }
            ) );
        }
    } 
});