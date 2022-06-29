<?php


namespace Uncanny_Automator_Pro;

use GFCommon;
use RGFormsModel;
use Uncanny_Automator\Gravity_Forms_Helpers;

/**
 * Class Gravity_Forms_Pro_Helpers
 *
 * @package Uncanny_Automator_Pro
 */
class Gravity_Forms_Pro_Helpers extends Gravity_Forms_Helpers {
	/**
	 * Gravity_Forms_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Gravity_Forms_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}

		add_action( 'wp_ajax_select_form_fields_ANONGFFORMS', array( $this, 'select_form_fields_func' ) );
		add_action( 'wp_ajax_select_form_fields_GFFORMS', array( $this, 'select_form_fields_func' ) );
		add_action( 'wp_ajax_get_form_fields_GFFORMS', array( $this, 'get_fields_rows_gfforms' ) );
	}

	/**
	 * @param Gravity_Forms_Pro_Helpers $pro
	 */
	public function setPro( Gravity_Forms_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}

	/**
	 * Return all the specific fields of a form ID provided in ajax call
	 */
	public function select_form_fields_func() {

		global $uncanny_automator;

		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$fields = array();
		if ( isset( $_POST ) ) {
			$form_id = absint( $_POST['value'] );

			$form = RGFormsModel::get_form_meta( $form_id );

			if ( is_array( $form['fields'] ) ) {
				foreach ( $form['fields'] as $field ) {
					if ( isset( $field['inputs'] ) && is_array( $field['inputs'] ) ) {
						foreach ( $field['inputs'] as $input ) {
							$fields[] = array(
								'value' => $input['id'],
								'text'  => GFCommon::get_label( $field, $input['id'] ),
							);
						}
					} elseif ( ! rgar( $field, 'displayOnly' ) ) {
						$fields[] = array(
							'value' => $field['id'],
							'text'  => GFCommon::get_label( $field ),
						);
					}
				}
			}
		}
		echo wp_json_encode( $fields );
		die();
	}


	/**
	 * @return void
	 */
	public function get_fields_rows_gfforms() {
		global $uncanny_automator;

		// Nonce and post object validation
		$uncanny_automator->utilities->ajax_auth_check();

		$response = (object) array(
			'success' => false,
			'fields'  => array(),
		);

		$fields = array();

		if ( isset( $_POST ) ) {
			$form_id = absint( $_POST['form_id'] );

			$form = RGFormsModel::get_form_meta( $form_id );

			if ( is_array( $form['fields'] ) ) {
				foreach ( $form['fields'] as $field ) {

					if ( isset( $field['inputs'] ) && is_array( $field['inputs'] ) ) {
						foreach ( $field['inputs'] as $input ) {
							$fields[] = array(
								'key'  => $input['id'] . ' - ' . GFCommon::get_label( $field, $input['id'] ),
								'type' => 'text',
								'data' => $input['id'] . ' - ' . GFCommon::get_label( $field, $input['id'] ),
							);
						}
					} elseif ( ! rgar( $field, 'displayOnly' ) ) {
						$fields[] = array(
							'value' => $field['id'],
							'text'  => $field['id'] . ' - ' . GFCommon::get_label( $field ),
						);
					}
				}

				$response = (object) array(
					'success' => true,
					'fields'  => array( $fields ),
				);

				return wp_send_json_success( $response );
			}
		}

		$response = (object) array(
			'success' => false,
			'error'   => "Couldn't fetch fields",
		);

		return wp_send_json_success( $response );
	}


}
