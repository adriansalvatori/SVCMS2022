<?php

/**
 * A class for making internal REST Requests to the ProjectHuddle API
 */

class PH_Rest_Request
{
    /**
     * Post type route base
     *
     * @var string
     */
    protected $route_base = '/projecthuddle/v2';

    protected $route = '';

    /**
     * Get things going
     *
     * @param string $post_type
     */
    public function __construct($base = '')
    {
        $this->route = $this->route_base . '/' . $base;
    }

    public function route()
    {
        return $this->route;
    }

    /**
     * Get object
     *
     * @param int    $id           ID of item.
     * @param array  $params       Request Parameters.
     * @param array  $query_params Request Query Parameters.
     * @param string $route        Overwrite route.
     *
     * @return mixed
     */
    public function get($id = 0, $params = [], $query_params = [], $route = '')
    {
        if (!$id) {
            global $post;
            $id = $post->ID;
        }

        $route    = !$route ? $this->route . '/' . $id : $route;
        $request  = new \WP_REST_Request('GET', $route, $params);
        $response = $this->makeRequest($request, $params, $query_params);
        wp_reset_postdata();
        return $response;
    }

    /**
     * Get object items
     *
     * @param array $params       Request Parameters.
     * @param array $query_params Request Query Parameters.
     *
     * @return mixed
     */
    public function fetch($params = [], $query_params = [])
    {
        $request  = new \WP_REST_Request('GET', $this->route, $params);
        $response = $this->makeRequest($request, $params, $query_params);
        wp_reset_postdata();
        return $response;
    }

    /**
     * Create item
     *
     * @param array  $params       Request Parameters.
     * @param array  $query_params Request Query Parameters.
     * @param string $route        Overwrite route.
     *
     * @return mixed
     */
    public function create($params = [], $query_params = [], $route = '')
    {
        $route   = !$route ? $this->route : $route;
        $request = new \WP_REST_Request('POST', $route, $params);
        return $this->makeRequest($request, $params, $query_params);
    }

    /**
     * Update item
     *
     * @param int    $id           Post id.
     * @param array  $params       Request Parameters.
     * @param array  $query_params Request Query Parameters.
     * @param string $route        Overwrite route.
     *
     * @return mixed
     */
    public function update($id = 0, $params = [], $query_params = [], $route = '')
    {
        if (!$id) {
            global $post;
            $id = $post->ID;
        }

        $route    = !$route ? $this->route . '/' . $id : $route;
        $request  = new \WP_REST_Request('PATCH', $route, $params);
        $response = $this->makeRequest($request, $params, $query_params);
        wp_reset_postdata();
        return $response;
    }

    public function delete($id = 0, $params = [], $query_params = [], $route = '')
    {
        if (!$id) {
            global $post;
            $id = $post->ID;
        }
        $route    = !$route ? $this->route . '/' . $id : $route;
        $request  = new \WP_REST_Request('DELETE', $route, $params);
        $response = $this->makeRequest($request, $params, $query_params);
        wp_reset_postdata();
        return $response;
    }

    /**
     * Make an internal API request
     *
     * @param WP_Rest_Request $request      Request.
     * @param array           $params       Params.
     * @param array           $query_params Query Params.
     *
     * @return mixed
     */
    public function makeRequest($request, $params = array(), $query_params = array())
    {
        global $wp_rest_server;

        // Add specified request parameters into the request.
        if (!empty($params)) {
            foreach ($params as $param_name => $param_value) {
                $request->set_param($param_name, $param_value);
            }
        }

        // set query params (i.e. per_page, context, etc.).
        if (!empty($query_params)) {
            $request->set_query_params($query_params);
        }

        $response = rest_do_request($request);
        $data     = $wp_rest_server->response_to_data($response, true);

        return $data;
    }
}
