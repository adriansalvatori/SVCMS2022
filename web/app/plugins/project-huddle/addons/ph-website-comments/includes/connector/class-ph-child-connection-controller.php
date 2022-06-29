<?php

/**
 * Controller class for child website connections
 */

// required classes
require_once PH_WEBSITE_PLUGIN_DIR . 'includes/connector/class-ph-xml-request.php';
require_once PH_WEBSITE_PLUGIN_DIR . 'includes/connector/class-ph-child-connection-data.php';
require_once PH_WEBSITE_PLUGIN_DIR . 'includes/connector/class-ph-child-connection-status.php';
require_once PH_WEBSITE_PLUGIN_DIR . 'includes/connector/class-ph-child-connection-validator.php';

/**
 * Controller class for setting data on the remote site via XMLRPC
 */
class PH_Child_Connection_Controller
{
	public $data;
	protected $username;
	protected $password;
	protected $url;

	/**
	 * Values to get from $_POST
	 *
	 * @var array
	 */
	protected $post_values = array(
		'id',
		'url',
		'username',
		'password',
		'type',
	);

	/**
	 * Run on actions
	 */
	public function __construct()
	{
		// get setup data
		add_filter('ph_website_admin_js_data', array($this, 'setup_data'), 10, 2);
		// connect child site action
		add_action('wp_ajax_ph_connect_child_site', array($this, 'connect_child_site'));
		// check child site connection
		add_action('wp_ajax_ph_check_child_site_connection', array($this, 'check_child_site_connection'));
		// verify script installation with virtual visit
		add_action('wp_ajax_ph_verify_installation', array($this, 'validate_script_installation'));
		// save website type option
		add_action('wp_ajax_ph_website_type', array($this, 'save_website_type'));
	}

	/**
	 * Lets us get $_POSTed values if not yet set on class.
	 * Makes it easy to get these at runtime.
	 *
	 * @param string $method
	 * @param string $args
	 * @return void
	 */
	public function __call($method, $args)
	{
		if (in_array($method, $this->post_values)) {
			if (!isset($this->{$method})) {
				$this->{$method} = isset($_POST[$method]) ? $_POST[$method] : $this->{$method};
			}

			// set status and data automatically when id is set
			if ('id' === $method && $this->id) {
				if (!isset($this->status->id)) {
					$this->status = new PH_Child_Connection_Status($this->id);
				}
				if (!$this->data) {
					$this->data = new PH_Child_Connection_Data($this->id);
				}
			}

			// return value
			return $this->{$method};
		}

		return false;
	}

	/**
	 * Loads existing site data
	 * For use in website admin javascript
	 *
	 * @param array $data
	 * @param integer $id
	 * @return array
	 */
	public function setup_data($data = array(), $id = '')
	{
		$this->data     = new PH_Child_Connection_Data($id);
		$this->status   = new PH_Child_Connection_Status($id);
		$merged         = array_merge((array) $this->status, (array) $this->data);
		$merged['type'] = get_post_meta($id, 'website_type', true);
		return array_merge($data, $merged);
	}

	/**
	 * Checks what's currently stored on the child site
	 *
	 * @return void
	 */
	public function check_child_site_connection()
	{
		if (wp_doing_ajax()) {
			check_ajax_referer('wp_rest', 'nonce');
		}

		// check request variables
		$this->check_vars();

		// make request to set options
		$this->site = new PH_XML_Request($this->endpoint(), $this->username(), $this->password());
		$response   = $this->site->request(
			'wp.getOptions',
			array_keys($this->data->to_prefixed_array())
		);

		// validate connection
		$this->validate_connection($response);

		// send success
		$this->send_success();
	}

	/**
	 * Connect a child site via ajax
	 *
	 * @return void
	 */
	public function connect_child_site()
	{
		if (wp_doing_ajax()) {
			check_ajax_referer('wp_rest', 'nonce');
		}

		// check request variables
		$this->check_vars();

		// set options on remote site
		$this->set_remote_options();
	}

	/**
	 * Sets the options on the remote site
	 *
	 * @return void
	 */
	public function set_remote_options()
	{
		// reset installation each time we try
		$this->status->reset();

		// make request to set options
		$site     = new PH_XML_Request($this->endpoint(), $this->username(), $this->password());
		$response = $site->request('wp.setOptions', $this->data->to_prefixed_array());

		$this->validate_connection($response);

		// send success
		$this->send_success($response);
	}

	/**
	 * Validate install script is on target site
	 *
	 * @return boolean
	 */
	public function validate_script_installation()
	{
		if (wp_doing_ajax()) {
			check_ajax_referer('wp_rest', 'nonce');
		}

		// make sure our vars are set for this request
		$this->check_vars();

		// check if the remote script is installed
		$validator = new PH_Child_Connection_Validator($this->endpoint(), $this->data, $this->username(), $this->password());
		$installed = $validator->is_installed();

		// update status in db
		$this->status->save_installed(!(is_wp_error($installed) || !$installed));

		// respond
		if (is_wp_error($installed)) {
			$this->send_error($installed->get_error_code(), $installed->get_error_message());
		} elseif (!$installed) {
			$this->send_error('not_installed', __('The script could not be verified. Please try visiting the site directly.', 'project-huddle'));
		} else {
			$this->send_success($installed);
		}
	}

	/**
	 * Validates connection data
	 * Ensures script is installed
	 * Ensures data is valid
	 *
	 * @param array $response
	 * @return void
	 */
	public function validate_connection($response)
	{
//		$response = (array) $response;
		// check for errors
		if ( is_wp_error($response) ) {
			$this->send_error($response->get_error_code(), $response->get_error_message());
		} else if ( !is_array($response) && $response->isFault() ) {
			$fault = $response->getFault();
			$this->send_error($fault->getCode(), $fault->getMessage());
		} else if ( is_array($response) && isset($response['errors']) && !empty($response['errors'])) {
			foreach ($response['errors'] as $key => $error) {
				$error = (array) $error;
				$this->send_error($key, $error[0]);
			}
		}

		$this->status = new PH_Child_connection_Status($this->id());

		// we've been connected, update in database for future requests
		$this->status->save_connected(true);

		// now we must validate what was sent
		$validator = new PH_Child_Connection_Validator($this->endpoint(), $this->data, $this->username(), $this->password());

		// is the plugin installed
		$this->status->save_activated($validator->is_activated());

		// is the data stored there valid
		$this->status->save_valid($validator->check_options($this->data, $response));
	}

	/**
	 * Saves which option was selected for "Type of site"
	 * As of right now, it's either "wp" or "custom", unless more get added
	 *
	 * @return void
	 */
	public function save_website_type()
	{
		// check nonce
		check_ajax_referer('wp_rest', 'nonce');

		// save it
		update_post_meta($this->id(), 'website_type', $this->type());

		// success
		$this->send_success();
	}

	/**
	 * Get the child site endpoint for the requests
	 *
	 * @return string
	 */
	public function endpoint()
	{
		return apply_filters('ph_child_site_rpc', esc_url(trailingslashit($this->url()) . 'xmlrpc.php'), $this->id(), $this->url());
	}

	/**
	 * Set variables and validate for requests
	 *
	 * @return void
	 */
	protected function check_vars()
	{
		if (!$this->id()) {
			$this->send_error('invalid_param', __('You must specify a post url.', 'project-huddle'));
		}
		if (!$this->url()) {
			$this->send_error('url_not_set', __('You must enter a URL for your child site.', 'project-huddle'));
		}
	}

	/**
	 * Send an error back to the requester
	 *
	 * @param string $code
	 * @param string $message
	 * @return void
	 */
	protected function send_error($code, $message)
	{
		if (wp_doing_ajax()) {
			wp_send_json_error(
				array(
					'code'    => $code,
					'message' => $message,
					'details' => (array) $this->data,
					'status'  => $this->status,
				)
			);
		} else {
			return new WP_Error($code, $message);
		}
	}

	/**
	 * Send success back to the requester
	 *
	 * @param string $code
	 * @param string $message
	 * @return void
	 */
	protected function send_success($success = true)
	{
		if (wp_doing_ajax()) {
			wp_send_json_success(
				array(
					'details' => $this->data,
					'status'  => $this->status,
				)
			);
		} else {
			return $success;
		}
	}
}

// run on admin init only
function ph_child_connector_init()
{
	new PH_Child_Connection_Controller();
}
add_action('admin_init', 'ph_child_connector_init');
