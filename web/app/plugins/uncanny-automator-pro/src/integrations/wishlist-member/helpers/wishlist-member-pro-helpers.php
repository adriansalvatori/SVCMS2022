<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Wishlist_Member_Helpers;

/**
 * Class Wishlist_Member_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Wishlist_Member_Pro_Helpers extends Wishlist_Member_Helpers {

	/**
	 * @var bool
	 */
	public $load_options;

	/**
	 * Wishlist_Member_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Wishlist_Member_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		} else {
			$this->load_options = true;
		}

		add_action( 'wp_ajax_select_form_fields_WLMFORM', array( $this, 'select_form_fields_func' ) );
	}

	/**
	 * @param \Uncanny_Automator_Pro\Wishlist_Member_Pro_Helpers $pro
	 */
	public function setPro( \Uncanny_Automator_Pro\Wishlist_Member_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}

	/**
	 * @param null $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return array|mixed|void
	 */
	public function wm_get_all_forms( $label = null, $option_code = 'WMFORMS', $args = [] ) {

		global $uncanny_automator, $wpdb;
		if ( ! $this->load_options ) {
			global $uncanny_automator;

			return $uncanny_automator->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		if ( ! $label ) {
			$label = esc_attr__( 'Form', 'uncanny-automator-pro' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';
		$any          = key_exists( 'any', $args ) ? $args['any'] : false;

		$options = [];
		if ( $any ) {
			$options['-1'] = esc_attr__( 'Any form', 'uncanny-automator-pro' );
		}

		$options['default'] = esc_attr__( 'Default registration form', 'uncanny-automator-pro' );
		$forms              = $wpdb->get_results( "SELECT option_name,option_value FROM `{$wpdb->prefix}wlm_options` WHERE `option_name` LIKE 'CUSTOMREGFORM-%' ORDER BY `option_name` ASC", ARRAY_A );

		foreach ( $forms as $k => $form ) {
			$form_value                        = maybe_unserialize( wlm_serialize_corrector( $form['option_value'] ) );
			$all_forms[ $form['option_name'] ] = $form_value['form_name'];
		}

		if ( ! empty( $all_forms ) ) {
			foreach ( $all_forms as $key => $form ) {
				$options[ $key ] = $form;
			}
		}

		$option = [
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'supports_tokens' => $token,
			'is_ajax'         => $is_ajax,
			'fill_values_in'  => $target_field,
			'endpoint'        => $end_point,
			'options'         => $options,
			'relevant_tokens' => [
				$option_code => esc_attr__( 'Form', 'uncanny-automator' ),
			],
		];

		return apply_filters( 'uap_option_wm_get_all_forms', $option );
	}

	/**
	 * Return all the specific fields of a form ID provided in ajax call
	 */
	public function select_form_fields_func() {

		global $uncanny_automator, $wpdb;

		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$fields = [];
		if ( isset( $_POST['value'] ) ) {

			if ( $_POST['value'] != 'default' ) {
				$form        = $wpdb->get_var( "SELECT option_value FROM `{$wpdb->prefix}wlm_options` WHERE `option_name` LIKE '%{$_POST['value']}%' ORDER BY `option_name` ASC" );
				$form_value  = maybe_unserialize( wlm_serialize_corrector( $form ) );
				$form_fields = $form_value['form_dissected']['fields'];
				if ( is_array( $form_fields ) ) {
					foreach ( $form_fields as $field ) {
						if ( $field['attributes']['type'] != 'password' ) {
							$fields[] = array(
								'value' => $field['attributes']['name'],
								'text'  => str_replace( ':', '', $field['label'] ),
							);
						}
					}
				}

			} elseif ( $_POST['value'] == 'default' ) {
				$form_fields = $this->get_form_fields();
				if ( is_array( $form_fields ) ) {
					foreach ( $form_fields as $key => $field ) {
						$fields[] = array(
							'value' => $key,
							'text'  => $field,
						);
					}
				}
			}

		}
		echo wp_json_encode( $fields );
		die();
	}

	/**
	 * @return mixed|void
	 */
	public function get_form_fields() {

		$fields = [
			'firstname' => __( 'First name', 'uncanny-automator' ),
			'lastname'  => __( 'Last name', 'uncanny-automator' ),
			'email'     => __( 'Email', 'uncanny-automator' ),
			'username'  => __( 'Username', 'uncanny-automator' ),
		];

		return apply_filters( 'automator_wm_default_form_field', $fields );
	}

}