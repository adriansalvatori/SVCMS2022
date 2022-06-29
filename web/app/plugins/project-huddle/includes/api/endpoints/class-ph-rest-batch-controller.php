<?php

/**
 * REST API: Controller for batch requests
 *
 * @since 2.7.0
 */

/**
 * Controller for batch requests
 *
 * @since 2.7.0
 *
 * @see   PH_REST_Controller
 */
class PH_REST_Batch_Controller extends WP_REST_Controller
{
	/**
	 * Constructor.
	 *
	 * @since  2.7.0
	 * @access public
	 */
	public function __construct()
	{
		// namespace
		$this->namespace = 'projecthuddle/v2';
	}

	/**
	 * Register the batch route
	 */
	public function register_routes()
	{
		register_rest_route($this->namespace, '/batch', array(
			// Supported methods for this endpoint.
			'methods'  => WP_REST_Server::EDITABLE,
			// Register the callback for the endpoint.
			'callback' => array($this, 'do_batch_request'),
			// user must be logged in
			'permission_callback' => 'is_user_logged_in',
			// Register args for the batch endpoint.
			'args'     => array(
				'requests' => array(
					'description'       => esc_html__('An array of request objects arguments that can be built into WP_REST_Request instances.', 'project-huddle'),
					'type'              => 'array',
					'required'          => true,
					'validate_callback' => array($this, 'validate_batch_requests'),
					'items'             => array(
						array(
							'type'       => 'object',
							'properties' => array(
								'method' => array(
									'description' => esc_html__('HTTP Method of the desired request.', 'project-huddle'),
									'type'        => 'string',
									'required'    => true,
									'enum'        => array(
										'GET',
										'POST',
										'PATCH',
										'PUT',
										'DELETE',
										'OPTIONS',
									),
								),
								'route'  => array(
									'description' => esc_html__('Desired route for the request.', 'project-huddle'),
									'required'    => true,
									'type'        => 'string',
									'format'      => 'uri',
								),
								'params' => array(
									'description' => esc_html__('Key value pairs of desired request parameters.', 'project-huddle'),
									'type'        => 'object',
								),
							),
						),
					),
				)
			)
		));
	}

	/**
	 * Our registered endpoint callback. Notice how we are passing in $request as an argument.
	 * By default, the WP_REST_Server will pass in the matched request object to our callback.
	 *
	 * @param WP_REST_Request $request The current matched request object.
	 *
	 * @return WP_REST_Response
	 */
	public function do_batch_request($request)
	{
		return $this->handle_batch_requests($request['requests']);
	}

	/**
	 * This handles the building of the response for the batch requests we make.
	 *
	 * @param array $requests An array of data to build WP_REST_Request objects from.
	 * @return WP_REST_Response|WP_Error A collection of response data for batch endpoints.
	 */
	public function handle_batch_requests($requests)
	{
		$data = array();
		global $is_ph_batch;
		$is_ph_batch = true;

		// Foreach request specified in the requests param run the endpoint.
		foreach ($requests as $request_params) {

			$response = $this->handle_request($request_params);

			// handle error and short circuit
			if ($response->is_error()) {
				// Convert to a WP_Error object and bail
				return $response->as_error();
			}

			// prepare for collection
			$key = $request_params['method'] . ' ' . $request_params['route'];
			$data[$key] = $this->prepare_for_collection($response);


			$data_response = new WP_REST_Response($data);
		}

		// if we're not throttling, trigger manual activity email for specific ids
		if (!PH()->activity_emails->is_throttled()) {
			$post_ids = [];
			if (!empty($data)) {
				foreach ($data as $item) {
					if (isset($item['id'])) {
						$post_ids[] = $item['id'];
					}
				}
			}

			do_action('ph_batch_activity_email', $post_ids);
		}

		return rest_ensure_response($data_response);
	}

	/**
	 * Prepare a response for inserting into a collection of responses.
	 *
	 * This is lifted from WP_REST_Controller class in the WP REST API v2 plugin.
	 *
	 * @param WP_REST_Response $response Response object.
	 * @return WP_REST_Response|array Response data, ready for insertion into collection data.
	 */
	public function prepare_for_collection($response)
	{
		if (!($response instanceof WP_REST_Response)) {
			return $response;
		}

		$data = (array) $response->get_data();
		$server = rest_get_server();

		if (method_exists($server, 'get_compact_response_links')) {
			$links = call_user_func(array($server, 'get_compact_response_links'), $response);
		} else {
			$links = call_user_func(array($server, 'get_response_links'), $response);
		}

		if (!empty($links)) {
			$data['_links'] = $links;
		}

		return $data;
	}

	/**
	 * This handles the building of the response for the batch requests we make.
	 *
	 * @param array $request_params Data to build a WP_REST_Request object from.
	 * @return WP_REST_Response Response data for the request.
	 */
	public function handle_request($request_params)
	{
		if (filter_var($request_params['route'], FILTER_VALIDATE_URL)) {
			$request = WP_REST_Request::from_url($request_params['route']);
			$request->set_method($request_params['method']);
		} else {
			$request = new WP_REST_Request($request_params['method'], $request_params['route']);
		}

		// Add specified request parameters into the request.
		if (!empty($request_params['params'])) {
			foreach ($request_params['params'] as $param_name => $param_value) {
				$request->set_param($param_name, $param_value);
			}
		}

		if (!empty($request_params['query'])) {
			$request->set_query_params((array) $request_params['query']);
		}

		$response = rest_do_request($request);
		wp_reset_postdata();
		return $response;
	}

	/**
	 * Validate batch requests
	 *
	 * @param $requests
	 * @param $request
	 * @param $param_key
	 *
	 * @return bool|WP_Error
	 */
	public function validate_batch_requests($requests, $request, $param_key)
	{
		// If requests isn't an array of requests then we don't process the batch.
		if (!is_array($requests)) {
			return new WP_Error('rest_invald_param', esc_html__('The requests parameter must be an array of requests.'), array('status' => 400));
		}

		foreach ($requests as $request) {
			// If the method or route is not set then we do not run the requests.
			if (!isset($request['method']) || !isset($request['route'])) {
				return new WP_Error('rest_invald_param', esc_html__('You must specify the method and route for each request.'), array('status' => 400));
			}

			if (isset($request['params']) && !is_array($request['params'])) {
				return new WP_Error('rest_invald_param', esc_html__('You must specify the params for each request as an array of named key value pairs.'), array('status' => 400));
			}
		}

		// This is a black listing approach to data validation.
		return true;
	}
}

// Function to register our new routes from the controller.
function ph_register_batch_routes()
{
	$controller = new PH_REST_Batch_Controller();
	$controller->register_routes();
}

add_action('rest_api_init', 'ph_register_batch_routes');
