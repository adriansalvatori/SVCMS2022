<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Ultimate_Member_Helpers;

/**
 * Class Ultimate_Member_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Ultimate_Member_Pro_Helpers extends Ultimate_Member_Helpers {
	/**
	 * @param Ultimate_Member_Pro_Helpers $pro
	 */
	public function setPro( Ultimate_Member_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}

	/**
	 * Ultimate_Member_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Ultimate_Member_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}

		//select_form_fields_UMFORM
		add_action( 'wp_ajax_select_form_fields_UMFORM', array( $this, 'select_form_fields_func' ) );
	}


	/**
	 * Return all the specific fields of a form ID provided in ajax call
	 */
	public function select_form_fields_func() {

		global $uncanny_automator;

		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$fields = [];
		if ( isset( $_POST ) ) {
			$form_id   = absint( $_POST['value'] );
			$um_fields = UM()->query()->get_attr( 'custom_fields', $form_id );

			if ( $um_fields ) {
				foreach ( $um_fields as $field ) {
					if ( isset( $field['public'] ) && 1 === absint( $field['public'] ) ) {
						$fields[] = array(
							'value' => $field['metakey'],
							'text'  => $field['title'],
						);
					}
				}
			}
		}
		echo wp_json_encode( $fields );
		die();
	}
}