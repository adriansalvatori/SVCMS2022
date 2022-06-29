<?php


namespace Uncanny_Automator_Pro;


use Caldera_Forms_Forms;
use Uncanny_Automator\Caldera_Helpers;

/**
 * Class Caldera_Forms_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Caldera_Forms_Pro_Helpers extends Caldera_Helpers {
	/**
	 * Caldera_Forms_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Caldera_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}

		add_action( 'wp_ajax_select_form_fields_ANONCFFORMS', array( $this, 'select_form_fields_func' ) );
		add_action( 'wp_ajax_select_form_fields_CFFORMS', array( $this, 'select_form_fields_func' ) );
	}

	/**
	 * @param Caldera_Forms_Pro_Helpers $pro
	 */
	public function setPro( Caldera_Forms_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}


	/**
	 * Return all the specific fields of a form ID provided in ajax call
	 */
	public function select_form_fields_func() {

		global $uncanny_automator;

		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$fields = [];

		if ( isset( $_POST ) ) {
			$form_id = sanitize_text_field( $_POST['value'] );

			$form = Caldera_Forms_Forms::get_form( $form_id );

			if ( ! empty( $form['fields'] ) ) {
				foreach ( $form['fields'] as $field ) {
					if ( $field['type'] !== 'html'
					     && $field['type'] !== 'summary'
					     && $field['type'] !== 'section_break'
					     && $field['type'] !== 'button'
					) {
						$fields[] = [
							'value' => $field['ID'],
							'text'  => $field['label'],
						];
					}
				}
			}
		}

		echo wp_json_encode( $fields );
		die();
	}
}