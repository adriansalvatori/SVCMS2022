<?php

namespace Uncanny_Automator_Pro;

/**
 * Webhook_Ajax_Handler
 */
class Webhook_Ajax_Handler {
	/**
	 * Webhook_Ajax_Handler Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_webhook_url_get_webhook_url', array( __CLASS__, 'webhook_url_ajax' ), 15 );
		add_action( 'wp_ajax_get_samples_get_webhook_url', array( __CLASS__, 'get_samples_ajax' ), 15 );
	}

	/**
	 * Get Webhook URL for the trigger
	 *
	 * @return void
	 */
	public static function webhook_url_ajax() {
		// Nonce and post object validation
		Automator()->utilities->ajax_auth_check();

		// Get recipe id
		$recipe_id = automator_filter_input( 'recipe_id', INPUT_POST );
		// Get item id
		$item_id = automator_filter_input( 'item_id', INPUT_POST );

		// Get webhook url
		$webhook_url = self::get_webhook_url( $recipe_id, $item_id );

		// Output webhook url
		echo wp_json_encode( $webhook_url );

		die();
	}

	/**
	 * Get sample button ajax handler
	 *
	 * @return void
	 */
	public static function get_samples_ajax() {
		// Nonce and post object validation
		Automator()->utilities->ajax_auth_check();

		$recipe_id      = automator_filter_input( 'recipe_id', INPUT_POST );
		$item_id        = automator_filter_input( 'item_id', INPUT_POST );
		$data_type      = automator_filter_input( 'data_format', INPUT_POST );
		$data_type_name = "data_type_uap-$recipe_id-$item_id";
		$option_name    = "transient_uap-$recipe_id-$item_id";
		$option_expiry  = "expiry_uap-$recipe_id-$item_id";
		update_option( $data_type_name, $data_type );

		$response = (object) array(
			'success' => false,
			'samples' => array(),
		);
		// Check if transit exists.
		$saved_hook = get_option( $option_name );
		if ( ! empty( $saved_hook ) ) {
			$fields = get_option( $option_name . '_fields', array() );
			if ( ! empty( $fields ) ) {
				$response = (object) array(
					'success' => true,
					'samples' => array( $fields ),
				);
				delete_option( $option_name . '_fields' );
				delete_option( $option_name );
				delete_option( $option_expiry );
				delete_option( $data_type_name );
			}

			// Output response
			echo wp_json_encode( $response );

			die();
		}

		update_option( $option_name, $option_name );
		update_option( $option_expiry, current_time( 'U' ) ); //phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
		update_option( $data_type_name, $data_type );

		// Output response
		echo wp_json_encode( $response );

		die();
	}

	/**
	 * Generate a webhook URL
	 *
	 * @param string $recipe_id
	 * @param string $item_id
	 *
	 * @return string
	 */
	private static function get_webhook_url( $recipe_id = '', $item_id = '' ) {
		// Get webhook url
		return sprintf( '%s%s/uap-%d-%d', get_rest_url(), AUTOMATOR_REST_API_END_POINT, $recipe_id, $item_id );
	}
}
