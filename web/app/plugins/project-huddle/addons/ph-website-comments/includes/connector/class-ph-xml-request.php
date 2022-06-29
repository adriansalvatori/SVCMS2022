<?php

use ProjectHuddle\Vendor\Laminas\XmlRpc\Client as XmlRpcClient;

/**
 * A lightweight class for making xml-rpc requests to a remote
 */

class PH_XML_Request {
	/**
	 * The username to send for basic authentication
	 *
	 * @var string
	 */
	private $username = '';

	/**
	 * The password to send for basic authentication
	 *
	 * @var string
	 */
	private $password = '';

	/**
	 * The endpoint to make the request
	 *
	 * @var string
	 */
	private $endpoint = '';

	/**
	 * Requres a username and password for the request on init
	 *
	 * @param string $username
	 * @param string $password
	 */
	public function __construct( $endpoint, $username, $password ) {
		$this->username = $username;
		$this->password = $password;
		$this->endpoint = $endpoint;
	}

	/**
	 * Run xmlrpc CURL request
	 *
	 * @param string $request
	 * @param array  $params
	 *
	 * @return void
	 */
	public function request( $request, $params = array() ) {

		if ( version_compare(phpversion(), '7.4', '<') ) {
			return new WP_Error( 'required_php_version_missing', __( 'Your server cannot make connections to remote sites. Try manually connecting instead.', 'project-huddle' ) );
		}

		$client = new XmlRpcClient( $this->endpoint );

		try {
			$response = $client->call( $request, array( 0, $this->username, $this->password, $params ) );
		} catch ( \Throwable $th ) {
			return array(
				'errors' => array(
					$th->getCode() => $th->getMessage(),
				),
			);
		}

		$last_response = $client->getLastResponse();
		if ( $last_response->isFault() ) {
			return $last_response;
		}

		return $response;
	}

	/**
	 * Process and send CURL errors
	 *
	 * @param CURL $ch
	 * @return void
	 */
	public function curl_errors( $ch ) {
		if ( curl_errno( $ch ) ) {
			curl_close( $ch );
			return new WP_Error( curl_errno( $ch ), print_r( $ch, 1 ) );
		}
	}
}
