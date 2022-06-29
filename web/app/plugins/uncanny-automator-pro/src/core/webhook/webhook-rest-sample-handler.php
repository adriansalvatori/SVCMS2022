<?php

namespace Uncanny_Automator_Pro;

use WP_REST_Request;
use WP_REST_Response;

/**
 * Webhook_Rest_Sample_Handler
 */
class Webhook_Rest_Sample_Handler {

	/**
	 * Webhook_Rest_Handler construct
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( __CLASS__, 'automator_webhook_init_rest_api_samples' ) );
	}

	/**
	 * Catch "Get Samples" button
	 *
	 * @return void
	 */
	public static function automator_webhook_init_rest_api_samples() {
		global $wpdb;
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT `option_name` AS `name`, `option_value` AS `value`
FROM  $wpdb->options
WHERE `option_name` LIKE %s
ORDER BY `option_name`",
				'%%transient_uap-%%'
			)
		);
		if ( empty( $results ) ) {
			return;
		}

		foreach ( $results as $result ) {
			if ( empty( $result->value ) ) {
				continue;
			}
			$expiry_option = str_replace( 'transient_', 'expiry_', $result->value );
			$data_type     = str_replace( 'transient_', 'data_type_', $result->value );
			$created_at    = get_option( $expiry_option, current_time( 'U' ) ); //phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
			$difference    = round( abs( current_time( 'U' ) - $created_at ) / 60, 2 ); //phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested

			if ( $difference < 1 ) {
				$route_value = str_replace( 'transient_', '', $result->value );
				register_rest_route(
					AUTOMATOR_REST_API_END_POINT,
					'/' . $route_value,
					array(
						'methods'             => array( 'POST', 'GET', 'PUT' ),
						'callback'            => array( __CLASS__, 'automator_webhook_catch_sample_data' ),
						'args'                => array(),
						'permission_callback' => function () {
							return true;
						},
					)
				);

				return;
			}
			delete_option( $result->value . '_fields' );
			delete_option( $result->value );
			delete_option( $expiry_option );
			delete_option( $data_type );
		}
	}

	/**
	 * Catch Rest API Sample data
	 *
	 * @param WP_REST_Request $data
	 *
	 * @return WP_REST_Response
	 */
	public static function automator_webhook_catch_sample_data( WP_REST_Request $data ) {
		$route       = $data->get_route();
		$params      = $data->get_params();
		$body        = $data->get_body();
		$route_parts = explode( '/', $route );
		$route_parts = end( $route_parts );
		if ( empty( $route_parts ) ) {
			return new WP_REST_Response(
				array(
					'status'      => 'success',
					'this'        => __CLASS__,
					'route_parts' => $route_parts,
				),
				200
			);

		}
		if ( false === get_option( 'transient_' . $route_parts ) ) {
			return new WP_REST_Response(
				array(
					'status'     => 'success',
					'this'       => __CLASS__,
					'transients' => false,
				),
				200
			);
		}
		if ( empty( $params ) && empty( $body ) ) {
			return new WP_REST_Response(
				array(
					'status'    => 'success',
					'this'      => __CLASS__,
					'params'    => $params,
					'is_sample' => 'yes',
				),
				200
			);
		}
		$data_type = get_option( 'data_type_' . $route_parts, 'json' );
		if ( ! empty( $body ) && empty( $params ) ) {
			$params = Webhook_Rest_Handler::handle_non_json_type_format( $body, $data_type );
		}
		$field = Webhook_Rest_Handler::handle_params( $params, true );
		update_option( 'transient_' . $route_parts . '_fields', $field );

		$trigger_id_raw = explode( '-', $route_parts );
		if ( ! empty( $trigger_id_raw ) && isset( $trigger_id_raw[2] ) ) {
			update_post_meta( $trigger_id_raw[2], 'WEBHOOK_SAMPLE', $body );
		}

		return new WP_REST_Response(
			array(
				'status'     => 'success',
				'this'       => __CLASS__,
				'route_path' => $route_parts,
				'is_sample'  => 'yes',
			),
			200
		);
	}
}
