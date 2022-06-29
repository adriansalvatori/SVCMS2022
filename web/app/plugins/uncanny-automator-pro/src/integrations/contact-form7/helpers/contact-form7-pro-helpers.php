<?php


namespace Uncanny_Automator_Pro;


use Uncanny_Automator\Contact_Form7_Helpers;
use WPCF7_ContactForm;

/**
 * Class Contact_Form7_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Contact_Form7_Pro_Helpers extends Contact_Form7_Helpers {
	/**
	 * @var
	 */
	private $tag_types;

	/**
	 * Contact_Form7_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Contact_Form7_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}

		add_action( 'wp_ajax_select_form_fields_ANONCF7FORMS', array( $this, 'select_form_fields_func' ) );
		add_action( 'wp_ajax_select_form_fields_CF7FORMS', array( $this, 'select_form_fields_func' ) );
	}

	/**
	 * @param Contact_Form7_Pro_Helpers $pro
	 */
	public function setPro( Contact_Form7_Pro_Helpers $pro ) {
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
			$form_id = absint( $_POST['value'] );
			$this->load_field_types();

			foreach ( $this->tag_types as $key => $tag ) {
				wpcf7_add_form_tag( $key, $tag['function'], $tag['features'] );
			}

			$wpcform = WPCF7_ContactForm::get_instance( $form_id );
			if ( ! empty( $wpcform ) ) {
				$form_tags = $wpcform->scan_form_tags();

				if ( ! empty( $form_tags ) ) {
					foreach ( $form_tags as $tag ) {
						if ( $tag->type !== 'submit' ) {
							$fields[] = [
								'value' => $tag->name,
								'text'  => $tag->name . ' [' . $tag->type . ']',
							];
						}
					}
				}
			}
		}

		echo wp_json_encode( $fields );
		die();
	}

	/**
	 * A list of available tags of contact form 7
	 */
	private function load_field_types() {
		$this->tag_types = array(
			'acceptance' => array(
				'function' => 'wpcf7_acceptance_form_tag_handler',
				'features' => array( 'name-attr' => 1 ),
			),
			'checkbox'   => array(
				'function' => 'wpcf7_checkbox_form_tag_handler',
				'features' => array(
					'name-attr'                   => 1,
					'selectable-values'           => 1,
					'multiple-controls-container' => 1,
				),
			),
			'checkbox*'  => array(
				'function' => 'wpcf7_checkbox_form_tag_handler',
				'features' => array(
					'name-attr'                   => 1,
					'selectable-values'           => 1,
					'multiple-controls-container' => 1,
				),
			),
			'radio'      => array(
				'function' => 'wpcf7_checkbox_form_tag_handler',
				'features' => array(
					'name-attr'                   => 1,
					'selectable-values'           => 1,
					'multiple-controls-container' => 1,
				),
			),
			'count'      => array(
				'function' => 'wpcf7_count_form_tag_handler',
				'features' => array(
					'name-attr'               => 1,
					'zero-controls-container' => 1,
					'not-for-mail'            => 1,
				),
			),
			'date'       => array(
				'function' => 'wpcf7_date_form_tag_handler',
				'features' => array( 'name-attr' => 1 ),
			),
			'date*'      => array(
				'function' => 'wpcf7_date_form_tag_handler',
				'features' => array( 'name-attr' => 1 ),
			),
			'file'       => array(
				'function' => 'wpcf7_file_form_tag_handler',
				'features' => array( 'name-attr' => 1 ),
			),
			'file*'      => array(
				'function' => 'wpcf7_file_form_tag_handler',
				'features' => array( 'name-attr' => 1 ),
			),
			'number'     => array(
				'function' => 'wpcf7_number_form_tag_handler',
				'features' => array( 'name-attr' => 1 ),
			),
			'number*'    => array(
				'function' => 'wpcf7_number_form_tag_handler',
				'features' => array( 'name-attr' => 1 ),
			),
			'range'      => array(
				'function' => 'wpcf7_number_form_tag_handler',
				'features' => array( 'name-attr' => 1 ),
			),
			'range*'     => array(
				'function' => 'wpcf7_number_form_tag_handler',
				'features' => array( 'name-attr' => 1 ),
			),
			'quiz'       => array(
				'function' => 'wpcf7_quiz_form_tag_handler',
				'features' => array(
					'name-attr'    => 1,
					'do-not-store' => 1,
					'not-for-mail' => 1,
				),
			),
			'captchac'   => array(
				'function' => 'wpcf7_captchac_form_tag_handler',
				'features' => array(
					'name-attr'               => 1,
					'zero-controls-container' => 1,
					'not-for-mail'            => 1,
				),
			),
			'captchar'   => array(
				'function' => 'wpcf7_captchar_form_tag_handler',
				'features' => array(
					'name-attr'    => 1,
					'do-not-store' => 1,
					'not-for-mail' => 1,
				),
			),
			'response'   => array(
				'function' => 'wpcf7_response_form_tag_handler',
				'features' => array(
					'display-block' => 1,
				),
			),
			'select'     => array(
				'function' => 'wpcf7_select_form_tag_handler',
				'features' => array(
					'name-attr'         => 1,
					'selectable-values' => 1,
				),
			),
			'select*'    => array(
				'function' => 'wpcf7_select_form_tag_handler',
				'features' => array( 'name-attr' => 1, 'selectable-values' => 1 )
			),
			'submit'     => array( 'function' => 'wpcf7_submit_form_tag_handler', 'features' => array() ),
			'text'       => array(
				'function' => 'wpcf7_text_form_tag_handler',
				'features' => array( 'name-attr' => 1 )
			),
			'text*'      => array(
				'function' => 'wpcf7_text_form_tag_handler',
				'features' => array( 'name-attr' => 1 )
			),
			'email'      => array(
				'function' => 'wpcf7_text_form_tag_handler',
				'features' => array( 'name-attr' => 1 )
			),
			'email*'     => array(
				'function' => 'wpcf7_text_form_tag_handler',
				'features' => array( 'name-attr' => 1 )
			),
			'url'        => array(
				'function' => 'wpcf7_text_form_tag_handler',
				'features' => array( 'name-attr' => 1 )
			),
			'url*'       => array(
				'function' => 'wpcf7_text_form_tag_handler',
				'features' => array( 'name-attr' => 1 )
			),
			'tel'        => array(
				'function' => 'wpcf7_text_form_tag_handler',
				'features' => array( 'name-attr' => 1 )
			),
			'tel*'       => array(
				'function' => 'wpcf7_text_form_tag_handler',
				'features' => array( 'name-attr' => 1 )
			),
			'textarea'   => array(
				'function' => 'wpcf7_textarea_form_tag_handler',
				'features' => array( 'name-attr' => 1 )
			),
			'textarea*'  => array(
				'function' => 'wpcf7_textarea_form_tag_handler',
				'features' => array( 'name-attr' => 1 )
			),
			'hidden'     => array(
				'function' => 'wpcf7_hidden_form_tag_handler',
				'features' => array( 'name-attr' => 1, 'display-hidden' => 1 )
			)
		);
	}
}