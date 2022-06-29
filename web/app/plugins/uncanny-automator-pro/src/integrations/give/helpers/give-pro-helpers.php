<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Give_Helpers;

/**
 * Class Give_Pro_Helpers
 *
 * @package Uncanny_Automator_Pro
 */
class Give_Pro_Helpers extends Give_Helpers {

	/**
	 * Give_Pro_Helpers constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_select_form_fields_DONATIONFORMS', array( $this, 'select_form_fields_func' ) );
	}

	/**
	 * @param \Uncanny_Automator_Pro\Give_Pro_Helpers $pro
	 */
	public function setPro( \Uncanny_Automator_Pro\Give_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}

	/**
	 * Return all the specific fields of a form ID provided in ajax call
	 */
	public function select_form_fields_func() {

		global $uncanny_automator;

		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$fields = array();
		if ( isset( $_POST ) ) {
			$form_id     = $_POST['value'];
			$form_fields = Automator()->helpers->recipe->give->get_form_fields_and_ffm( $form_id );
			if ( is_array( $form_fields ) ) {
				foreach ( $form_fields as $key => $field ) {
					$fields[] = array(
						'value' => $key,
						'text'  => $field['label'],
					);
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
		$fields = array(
			'give_title'  => array(
				'type'     => 'text',
				'required' => true,
				'label'    => __( 'Name title prefix', 'uncanny-automator-pro' ),
				'key'      => 'title',
			),
			'give_first'  => array(
				'type'     => 'text',
				'required' => true,
				'label'    => __( 'First name', 'uncanny-automator-pro' ),
				'key'      => 'first_name',
			),
			'give_last'   => array(
				'type'     => 'text',
				'required' => false,
				'label'    => __( 'Last name', 'uncanny-automator-pro' ),
				'key'      => 'last_name',
			),
			'give_email'  => array(
				'type'     => 'email',
				'required' => true,
				'label'    => __( 'Email', 'uncanny-automator-pro' ),
				'key'      => 'user_email',
			),
			'give-amount' => array(
				'type'     => 'tel',
				'required' => true,
				'label'    => __( 'Donation amount', 'uncanny-automator-pro' ),
				'key'      => 'price',
			),
			'address1'    => array(
				'type'     => 'text',
				'required' => true,
				'label'    => __( 'Address line 1', 'uncanny-automator-pro' ),
				'key'      => 'address1',
			),
			'address2'    => array(
				'type'     => 'text',
				'required' => true,
				'label'    => __( 'Address line 2', 'uncanny-automator-pro' ),
				'key'      => 'address2',
			),
			'city'        => array(
				'type'     => 'text',
				'required' => true,
				'label'    => __( 'City', 'uncanny-automator-pro' ),
				'key'      => 'city',
			),
			'state'       => array(
				'type'     => 'text',
				'required' => true,
				'label'    => __( 'State', 'uncanny-automator-pro' ),
				'key'      => 'state',
			),
			'zip'         => array(
				'type'     => 'text',
				'required' => true,
				'label'    => __( 'Zip', 'uncanny-automator-pro' ),
				'key'      => 'zip',
			),
			'country'     => array(
				'type'     => 'text',
				'required' => true,
				'label'    => __( 'Country', 'uncanny-automator-pro' ),
				'key'      => 'country',
			),
		);

		return apply_filters( 'automator_give_wp_form_field', $fields );
	}

	/**
	 * @param $label
	 * @param $option_code
	 * @param $args
	 *
	 * @return array|mixed|void
	 */
	public function list_all_give_recurring_forms( $label = null, $option_code = 'MAKEDONATION', $args = array() ) {

		global $uncanny_automator;

		if ( ! $label ) {
			$label = __( 'Form', 'uncanny-automator' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$is_any       = key_exists( 'is_any', $args ) ? $args['is_any'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';
		$options      = array();

		if ( $is_any ) {
			$options['-1'] = __( 'Any form', 'uncanny-automator-pro' );
		}

		$query_args = array(
			'post_type'      => 'give_forms',
			'posts_per_page' => 9999,
			'post_status'    => 'publish',
		);

		$all_forms = $uncanny_automator->helpers->recipe->wp_query( $query_args );
		$type      = 'select';

		global $wpdb;
		foreach ( $all_forms as $opt => $val ) {
			$query         = "SELECT meta_value FROM {$wpdb->prefix}give_formmeta WHERE form_id = {$opt} AND meta_key LIKE '_give_recurring'";
			$not_recurring = $wpdb->get_var( $query );
			if ( ! empty( $not_recurring ) && $not_recurring != 'no' ) {
				$options[ $opt ] = $val;
			}
		}

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => $type,
			'required'        => true,
			'supports_tokens' => $token,
			'is_ajax'         => $is_ajax,
			'fill_values_in'  => $target_field,
			'endpoint'        => $end_point,
			'options'         => $options,
			'relevant_tokens' => array(
				$option_code                 => esc_attr__( 'Form title', 'uncanny-automator-pro' ),
				$option_code . '_ID'         => esc_attr__( 'Form ID', 'uncanny-automator-pro' ),
				$option_code . '_AMOUNT'     => esc_attr__( 'Recurring amount', 'uncanny-automator-pro' ),
				$option_code . '_DONOR'      => esc_attr__( 'Donor name', 'uncanny-automator-pro' ),
				$option_code . '_DONOREMAIL' => esc_attr__( 'Donor email', 'uncanny-automator-pro' ),
			),
		);

		return apply_filters( 'uap_option_list_all_give_recurring_forms', $option );
	}

}
