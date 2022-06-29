<?php

namespace Objectiv\Plugins\Checkout\API;

use Objectiv\Plugins\Checkout\Managers\SettingsManager;
use WP_Error;
use WP_REST_Server;

class SettingsAPI {
	protected $settings = array();

	public function __construct() {
	}

	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_route' ) );
	}

	public function register_route() {
		register_rest_route(
			'checkoutwc/v1',
			'/setting/(?P<setting_key>[a-z|_]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'readable_callback' ),
					'permission_callback' => array( $this, 'permission_callback' ),
					'args'                => array(
						'setting_key' => array(
							'validate_callback' => array( $this, 'validate_key_callback' ),
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'editable_callback' ),
					'permission_callback' => array( $this, 'permission_callback' ),
					'args'                => array(
						'setting_key'   => array(
							'validate_callback' => array( $this, 'validate_key_callback' ),
						),
						'setting_value' => array(
							'validate_callback' => array( $this, 'validate_value_callback' ),
						),
					),
				),
			)
		);
	}

	public function readable_callback( \WP_REST_Request $request ) {
		$key     = $request->get_param( 'setting_key' );
		$setting = SettingsManager::instance()->get_setting( $key );

		return rest_ensure_response( $setting );
	}

	public function editable_callback( \WP_REST_Request $request ) {
		$manager       = SettingsManager::instance();
		$key           = $request->get_param( 'setting_key' );
		$value         = $request->get_param( 'setting_value' );
		$success       = $manager->update_setting( $key, $value );
		$new_value     = $manager->get_setting( $key );
		$response_data = array(
			'currentValue' => $new_value,
		);

		if ( $success ) {
			return rest_ensure_response( $response_data );
		}

		$response_data['detailedError'] = "Unable to update setting_key:$key to value:$value";

		$response = new WP_Error( '500', 'Unable to update setting. If this error persists contact your site administrator.', $response_data );

		return rest_ensure_response( $response );
	}

	public function permission_callback( \WP_REST_Request $request ): bool {
		return current_user_can( 'manage_options' );
	}

	public function validate_key_callback( $value, \WP_REST_Request $request, string $param ): bool {
		return 'enable' === $value;
	}

	public function validate_value_callback( $value, \WP_REST_Request $request, string $param ): bool {
		return 'yes' === $value || 'no' === $value;
	}
}
