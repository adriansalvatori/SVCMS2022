<?php

/**
 * Validates data for a child site connection
 * Makes sure the information on the child site matches the remote project
 */

class PH_Child_Connection_Validator
{
	public $data;
	public $endpoint = '';
	public $username = '';
	public $password = '';
	protected $request;

	public function __construct($endpoint, PH_Child_Connection_Data $data, $username = '', $password = '')
	{
		$this->endpoint = $endpoint;
		$this->data     = $data;
		$this->username = $username;
		$this->password = $password;
	}

	/**
	 * Verifies if a script is installed via a virtual visit
	 *
	 * @return WP_Error|true
	 */
	public function is_installed()
	{
		// simulate a visit to verify installation
		$response = wp_remote_get(
			$this->request_url(),
			array(
				'user-agent' => 'ProjectHuddle/' . PH_VERSION . '; ' . esc_url_raw($this->data->parent_url),
				'sslverify'  => false,
				'headers'    => array(
					'Referer' => $this->data->child_url, // get referred from ourself
				),
			)
		);

		if (is_wp_error($response)) {
			return $response;
		}

		$body = '';
		if (isset($response['body'])) {
			$body = strval($response['body']);
		}

		// in case it gets nested on some servers
		if (isset($response['response'])) {
			$response = $response['response'];
		}

		if (is_wp_error($response)) {
			return new WP_Error($response->get_error_code(), $response->get_error_message());
		}

		if (isset($response['code']) && isset($response['message']) && $response['code'] !== 200) {
			return new WP_Error($response['code'], $response['message']);
		}

		// find access token in script loader
		return strpos($body, 'ph_access_token=' . $this->data->access_token);
	}

	/**
	 * Check if the plugin is activated
	 *
	 * @return boolean
	 */
	public function is_activated()
	{
		// make request to set options
		$this->site = new PH_XML_Request($this->endpoint, $this->username, $this->password);

		// check to see if the plugin is installed
		$response = $this->site->request(
			'wp.getOptions',
			array(
				'ph_child_installed',
			)
		);

		// check for errors
		if ( !is_array($response) && $response->isFault() ) {
			$fault = $response->getFault();
			return new WP_Error($fault->getCode(), $fault->getMessage());
		}

		return is_array($response) && isset($response['ph_child_installed']);
	}

	/**
	 * Checks data against remote data to make sure it matches
	 *
	 * @return boolean
	 */
	public function is_valid()
	{
		// make request to set options
		$this->site = new PH_XML_Request($this->endpoint, $this->username, $this->password);

		// check to see if we have our options
		$response = $this->site->request('wp.getOptions', array_keys($this->data->to_prefixed_array()));

		// check for errors
		if ( !is_array($response) && $response->isFault() ) {
			$fault = $response->getFault();
			return new WP_Error($fault->getCode(), $fault->getMessage());
		}

		// check the options to make sure they match
		return $this->check_options($this->data, $response);
	}

	/**
	 * Check to make sure child options match what's in our database
	 *
	 * @param array $response
	 * @return void
	 */
	public function check_options(PH_Child_Connection_Data $data, $response = array())
	{
		$stored = $data->to_prefixed_array();

		if (empty($response)) {
			return false;
		}

		foreach ($response as $key => $option) {
			// bail if option does not exist on child or here
			if (!isset($stored[$key])) {
				return false;
			}

			// values must match
			if ($option['value'] != $stored[$key]) {
				return false;
			}
		}

		// success
		return true;
	}

	/**
	 * Build the request url for validating the script installation
	 *
	 * @return void
	 */
	public function request_url()
	{
		return add_query_arg(
			array(
				'ph_access_token' => $this->data->access_token,
			),
			$this->data->child_url
		);
	}
}
