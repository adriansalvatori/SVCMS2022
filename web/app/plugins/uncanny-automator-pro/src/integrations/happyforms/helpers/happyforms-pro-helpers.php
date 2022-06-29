<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Happyforms_Helpers;

/**
 * Class Happyforms_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Happyforms_Pro_Helpers extends Happyforms_Helpers {

	/**
	 * @var Happyforms_Pro_Helpers
	 */
	public $options;
	/**
	 * @var Happyforms_Pro_Helpers
	 */
	public $pro;
	/**
	 * @var bool
	 */
	public $load_options;

	/**
	 * Happyforms_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Happyforms_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}

		add_action( 'wp_ajax_select_form_fields_HFFORMS', array( $this, 'select_form_fields_func' ) );
	}

	/**
	 * @param Happyforms_Pro_Helpers $pro
	 */
	public function setPro( Happyforms_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}
	
	/**
	 * Return all the specific fields of a form ID provided in ajax call
	 */
	public function select_form_fields_func() {

		global $uncanny_automator;

		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$fields = [];
		if ( isset( $_POST ) ) {
			$form_id         = absint( $_POST['value'] );
			$form_controller = happyforms_get_form_controller();

			$form = $form_controller->get( $form_id );

			if ( is_array( $form ) && ! empty( $form['parts'] ) ) {
				foreach ( $form['parts'] as $field ) {
					$input_id    = $field['id'];
					$input_title = $field['label'] . ( $field['type'] !== '' ? ' (' . $field['type'] . ') ' : '' );
					$fields[]    = [
						'value' => $input_id,
						'text'  => $input_title,
					];
				}
			}
		}
		echo wp_json_encode( $fields );
		die();
	}


	/**
	 * Match condition for form field and value.
	 *
	 * @param array $metas .
	 * @param $form_id
	 * @param null|array $recipes .
	 * @param null|string $trigger_meta .
	 * @param null|string $trigger_code .
	 * @param null|string $trigger_second_code .
	 *
	 * @return array|bool
	 */
	public function match_condition( $metas, $form_id, $recipes = null, $trigger_meta = null, $trigger_code = null, $trigger_second_code = null ) {
		if ( null === $recipes ) {
			return false;
		}

		$matches    = [];
		$recipe_ids = [];

		//Limiting to specific recipe IDs
		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				if ( key_exists( $trigger_meta, $trigger['meta'] ) && absint( $trigger['meta'][ $trigger_meta ] ) === absint( $form_id ) ) {
					$matches[ $trigger['ID'] ]    = [
						'field' => $trigger['meta'][ $trigger_code ],
						'value' => $trigger['meta'][ $trigger_second_code ],
					];
					$recipe_ids[ $trigger['ID'] ] = $recipe['ID'];
				}
			}
		}

		//Try to match value with submitted to isolate recipe ids matched
		if ( ! empty( $matches ) ) {
			foreach ( $matches as $trigger_id => $match ) {
				$matched = false;
				if ( $metas ) {
					foreach ( $metas as $meta_key => $meta ) {
						if ( $match['field'] !== $meta_key ) {
							continue;
						}

						if ( ( $match['field'] === $meta_key ) && ( $match['value'] === $meta ) ) {
							$matched = true;
							break;
						}
					}
				}

				if ( ! $matched ) {
					unset( $recipe_ids[ $trigger_id ] );
				}
			}
		}

		if ( ! empty( $recipe_ids ) ) {
			return [ 'recipe_ids' => $recipe_ids, 'result' => true ];
		}

		return false;
	}
}