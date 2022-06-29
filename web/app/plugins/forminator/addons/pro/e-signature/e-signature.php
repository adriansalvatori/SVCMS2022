<?php

/**
 * Addon Name: E-Signature
 * Version: 1.0
 * Plugin URI:  https://wpmudev.com/
 * Description: E-Signature field for Forminator
 * Author: WPMU DEV
 * Author URI: http://wpmudev.com
 */

class Forminator_E_Signature {
	/**
	 * @var self|null
	 */
	private static $_instance = null;

	/**
	 * Get Instance
	 *
	 * @since 1.0 Signature Addon
	 * @return self|null
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
		if ( ! FORMINATOR_PRO ) {
			add_filter( 'forminator_pro_fields', [ 'Forminator_E_Signature', 'add_pro_field' ] );
			return;
		}
		add_filter( 'forminator_fields', [ 'Forminator_E_Signature', 'add_signature_field' ] );
		add_filter( 'forminator_entry_meta_value_to_string', [ 'Forminator_E_Signature', 'meta_value_to_string' ], 10, 5 );
		add_filter( 'forminator_handle_specific_field_types', [ 'Forminator_E_Signature', 'handle_signature' ], 10, 3 );
	}

	/**
	 * Add signature field
	 *
	 * @param Forminator_Signature_Field $fields
	 * @return array Fields
	 */
	public static function add_signature_field( $fields ) {
		require_once 'library/signature_field.php';
		$fields[] = new Forminator_Signature_Field();

		return $fields;
	}

	/**
	 * Get pro fields for showing them as promo for pro version
	 *
	 * @param array $pro_fields
	 * @return array
	 */
	public static function add_pro_field( $pro_fields ) {
		require_once 'library/signature_field.php';
		$signature = new Forminator_Signature_Field();
		$pro_fields[] = $signature->get_pro_field();

		return $pro_fields;
	}

	/**
	 * Convert signature to string
	 *
	 * @param type $string_value
	 * @param string $field_type
	 * @param array $meta_value
	 * @param bool $allow_html
	 * @param int $truncate
	 * @return string
	 */
	public static function meta_value_to_string( $string_value, $field_type, $meta_value, $allow_html, $truncate ) {
		if ( 'signature' === $field_type ) {
			$file = '';
			if ( isset( $meta_value['file'] ) ) {
				$file = $meta_value['file'];
			}
			if ( ! empty( $file ) && is_array( $file ) && isset( $file['file_url'] ) ) {
				$string_value = $file['file_url'];
				if ( $allow_html ) {
					// make image.
					$url = $string_value;
					$file_name = basename( $url );
					$file_name = ! empty( $file_name ) ? $file_name : __( '(no filename)', 'forminator' );
					//truncate
					if ( strlen( $file_name ) > $truncate ) {
						$file_name = substr( $file_name, 0, $truncate ) . '...';
					}
					$string_value = '<a href="' . esc_url( $url )  . '" target="_blank"><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $file_name ) . '" width="100" /></a>';
				} else {
					//truncate url
					if ( strlen( $string_value ) > $truncate ) {
						$string_value = substr( $string_value, 0, $truncate ) . '...';
					}
				}

			} else {
				$string_value = '';
			}
		}

		return $string_value;
	}

	/**
	 * Handle signature
	 *
	 * @param array $field_data
	 * @param object $form_field_obj
	 * @param array $field_array
	 * @param string $submission_behav
	 * @return array|string
	 */
	public static function handle_signature( $field_data, $form_field_obj, $field_array ) {
		if ( 'signature' === $field_array["type"] ) {
			$upload_data = $form_field_obj->handle_sign_upload( $field_array );
			if ( !empty( $upload_data['success'] ) && $upload_data['success'] ) {
				$field_data['file'] = $upload_data;
			} elseif ( isset( $upload_data['success'] ) && false === $upload_data['success'] ) {
				$response = array(
					'return' => true,
					'message' => $upload_data['message'],
					'errors'  => array(),
					'success' => false,
				);

				return $response;
			} else {
				// no sign uploaded for this field_id.
				$field_data = '';
			}
		}

		return $field_data;
	}
}

Forminator_E_Signature::get_instance();