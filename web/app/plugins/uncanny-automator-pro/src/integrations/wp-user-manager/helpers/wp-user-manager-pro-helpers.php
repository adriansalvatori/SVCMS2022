<?php

namespace Uncanny_Automator_Pro;

use WPUM_Field;
use WPUM_Registration_Form;

/**
 * Class Wp_User_Manager_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Wp_User_Manager_Pro_Helpers {

	/**
	 * @var Wp_User_Manager_Pro_Helpers
	 */
	public $options;
	/**
	 * @var Wp_User_Manager_Pro_Helpers
	 */
	public $pro;

	/**
	 * @var bool
	 */
	public $load_options;

	/**
	 * Wp_User_Manager_Pro_Helpers constructor.
	 */
	public function __construct() {
		if ( method_exists( '\Uncanny_Automator\Automator_Helpers_Recipe', 'maybe_load_trigger_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		} else {
			$this->load_options = true;
		}
		add_action( 'wp_ajax_select_form_fields_WPUMRF', array( $this, 'select_form_fields_func' ) );
	}

	/**
	 * @param Wp_User_Manager_Pro_Helpers $options
	 */
	public function setOptions( Wp_User_Manager_Pro_Helpers $options ) {
		$this->options = $options;
	}

	/**
	 * @param Wp_User_Manager_Pro_Helpers $pro
	 */
	public function setPro( Wp_User_Manager_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}

	/**
	 * @param null   $label
	 * @param string $option_code
	 * @param array  $args
	 *
	 * @return array|mixed|void
	 */
	public function get_all_forms( $label = null, $option_code = 'WPUMFORMS', $args = [] ) {
		if ( ! $this->load_options ) {
			global $uncanny_automator;

			return $uncanny_automator->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		if ( ! $label ) {
			$label = esc_attr__( 'Form', 'uncanny-automator' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$is_any       = key_exists( 'is_any', $args ) ? $args['is_any'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';
		$options      = [];

		if ( $is_any ) {
			$options['-1'] = __( 'Any form', 'uncanny-automator-pro' );
		}

		$forms = wpumrf_registration_forms();

		if ( is_array( $forms ) && ! empty( $forms ) ) {
			foreach ( $forms as $key => $form ) {
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
		];

		return apply_filters( 'uap_option_get_all_forms', $option );
	}

	/**
	 * select form fields
	 */
	public function select_form_fields_func() {

		global $uncanny_automator;

		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$fields = [];
		if ( isset( $_POST ) && isset( $_POST['value'] ) ) {

			$form = new WPUM_Registration_Form( $_POST['value'] );

			if ( $form->exists() ) {

				$stored_fields = $form->get_fields();

				if ( is_array( $stored_fields ) && ! empty( $stored_fields ) ) {
					foreach ( $stored_fields as $field ) {

						$stored_field = new WPUM_Field( $field );

						if ( $stored_field->exists() ) {
							$fields[] = [
								'value' => $stored_field->get_primary_id(),
								'text'  => $stored_field->get_name(),
							];
						}

					}
				}
			}
			echo wp_json_encode( $fields );
			die();
		}
	}

	/**
	 * @param null   $label
	 * @param string $option_code
	 * @param array  $args
	 *
	 * @return array|mixed|void
	 */
	public function get_all_groups( $label = null, $option_code = 'WPUMGROUPS', $args = [] ) {
		if ( ! $this->load_options ) {
			global $uncanny_automator;

			return $uncanny_automator->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		if ( ! $label ) {
			$label = esc_attr__( 'Group', 'uncanny-automator' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$is_any       = key_exists( 'is_any', $args ) ? $args['is_any'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';

		$args = [
			'post_type'      => 'wpum_group',
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		];

		global $uncanny_automator;
		$options = $uncanny_automator->helpers->recipe->options->wp_query( $args, $is_any,
			esc_attr__( 'Any group', 'uncanny-automator' ) );

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
		];

		return apply_filters( 'uap_option_get_all_groups', $option );

	}

	/**
	 * @param null   $label
	 * @param string $option_code
	 * @param array  $args
	 *
	 * @return array|mixed|void
	 */
	public function get_all_private_groups( $label = null, $option_code = 'WPUMPRIVATEGROUPS', $args = [] ) {
		if ( ! $this->load_options ) {
			global $uncanny_automator;

			return $uncanny_automator->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		if ( ! $label ) {
			$label = esc_attr__( 'Private group', 'uncanny-automator-pro' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$is_any       = key_exists( 'is_any', $args ) ? $args['is_any'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';

		$args = [
			'post_type'      => 'wpum_group',
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
			'meta_query'     => [
				[
					'key'     => '_group_privacy_method',
					'value'   => 'private',
					'compare' => '=',
				],
			],
		];

		global $uncanny_automator;
		$options = $uncanny_automator->helpers->recipe->options->wp_query( $args, $is_any,
			esc_attr__( 'Any private group', 'uncanny-automator-pro' ) );

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
		];

		return apply_filters( 'uap_option_get_all_private_groups', $option );

	}
}