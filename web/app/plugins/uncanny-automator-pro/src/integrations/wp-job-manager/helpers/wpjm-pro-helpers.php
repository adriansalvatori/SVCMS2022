<?php

namespace Uncanny_Automator_Pro;


/**
 * Class Wpjm_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Wpjm_Pro_Helpers extends \Uncanny_Automator\Wpjm_Helpers {

	/**
	 * Wpjm_Helpers constructor.
	 */
	public function __construct() {
		global $uncanny_automator;
		$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		add_action( 'wp_ajax_select_specific_job_type', array( $this, 'select_specific_job_type' ) );
	}

	/**
	 * @param Wpjm_Pro_Helpers $pro
	 */
	public function setPro( \Uncanny_Automator_Pro\Wpjm_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed
	 */

	public function list_wpjm_job_application_statuses( $label = null, $option_code = 'WPJMAPPSTATUS', $args = [] ) {
		if ( ! $this->load_options ) {
			global $uncanny_automator;

			return $uncanny_automator->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		global $uncanny_automator;
		if ( ! $label ) {
			$label = esc_attr__( 'Application status', 'uncanny-automator' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';
		$options      = [];

		$options['-1'] = __( 'Any status', 'uncanny-automator' );

		if ( $uncanny_automator->helpers->recipe->load_helpers ) {
			// WP Job Manager is hidding terms on non job template
			if ( function_exists( 'get_job_application_statuses' ) ) {
				foreach ( get_job_application_statuses() as $name => $status_label ) {
					$options[ esc_attr( $name ) ] = esc_html( $status_label );
				}
			}
		}
		$type = 'select';

		$option = [
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => $type,
			'required'        => true,
			'supports_tokens' => $token,
			'is_ajax'         => $is_ajax,
			'fill_values_in'  => $target_field,
			'endpoint'        => $end_point,
			'options'         => $options,
		];

		return apply_filters( 'uap_option_list_wpjm_job_application_statuses', $option );
	}

	public function select_specific_job_type() {

		global $uncanny_automator;

		$uncanny_automator->utilities->ajax_auth_check( $_POST );
		$fields = [];
		if ( isset( $_POST ) && key_exists( 'value', $_POST ) && ! empty( $_POST['value'] ) ) {
			$job_id = sanitize_text_field( $_POST['value'] );

			if ( $job_id == '-1' ) {
				$terms = get_terms( [ 'taxonomy' => 'job_listing_type', 'hide_empty' => false ] );
			} else {
				$terms = wpjm_get_the_job_types( $job_id );
			}

			$fields[] = [
				'value' => - 1,
				'text'  => __( 'Any type', 'uncanny-automator-pro' ),
			];


			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					// Check if the post title is defined
					$term_name = ! empty( $term->name ) ? esc_html( $term->name ) : sprintf( __( 'ID: %1$s (no title)', 'uncanny-automator-pro' ), $term->term_id );

					$fields[] = array(
						'value' => $term->term_id,
						'text'  => $term_name,
					);
				}
			}
		}
		echo wp_json_encode( $fields );
		die();

	}

	/**
	 * Returns an array collection of categories in Job.
	 * 
	 * @return array $terms The collection of terms.
	 */
	public function get_job_categories( $job_id = 0 ) {

		if ( empty( $job_id ) ) {
			return array();
		}

		$categories = [];

		$terms = wp_get_object_terms( $job_id, 'job_listing_category' ); 
		
		if ( ! is_wp_error( $terms ) ) {
			if ( ! empty( $terms ) ) {
				foreach( $terms as $term ){
					$categories[] = $term->name;
				}
				// Sort alphabetically.
				sort( $categories );
			}
		}

		return $categories;
		
	}
}