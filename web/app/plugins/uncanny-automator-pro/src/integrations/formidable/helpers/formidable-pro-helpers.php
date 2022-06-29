<?php


namespace Uncanny_Automator_Pro;


use FrmEntryMeta;
use FrmField;
use Uncanny_Automator\Formidable_Helpers;

/**
 * Class Formidable_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Formidable_Pro_Helpers extends Formidable_Helpers {
	/**
	 * Formidable_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Formidable_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}

		add_action( 'wp_ajax_select_form_fields_ANONFIFORMS', array( $this, 'select_form_fields_func' ) );
		add_action( 'wp_ajax_select_form_fields_FIFORMS', array( $this, 'select_form_fields_func' ) );
	}

	/**
	 * @param Formidable_Pro_Helpers $pro
	 */
	public function setPro( Formidable_Pro_Helpers $pro ) {
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

			$form = FrmField::get_all_for_form( $form_id );
			if ( is_array( $form ) ) {
				foreach ( $form as $field ) {
					$input_id    = $field->id;
					$input_title = $field->name . ( $field->description !== '' ? ' (' . $field->description . ') ' : '' );
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
	 * @param $entry_id
	 * @param $form_id
	 * @param $args
	 *
	 * @return array
	 */
	public function extract_save_fi_fields( $entry_id, $form_id, $args ) {
		$data = [];
		if ( $entry_id && class_exists( '\FrmEntryMeta' ) ) {
			$metas          = FrmEntryMeta::get_entry_meta_info( $entry_id );
			$trigger_id     = (int) $args['trigger_id'];
			$user_id        = (int) $args['user_id'];
			$trigger_log_id = (int) $args['trigger_log_id'];
			$run_number     = (int) $args['run_number'];
			$meta_key       = (string) $args['meta_key'];

			foreach ( $metas as $meta ) {
				$field_id     = $meta->field_id;
				$key          = "{$trigger_id}:{$meta_key}:{$form_id}|{$field_id}";
				$data[ $key ] = $meta->meta_value;
			}

			if ( $data ) {
				global $uncanny_automator;
				$insert = [
					'user_id'        => $user_id,
					'trigger_id'     => $trigger_id,
					'trigger_log_id' => $trigger_log_id,
					'meta_key'       => $meta_key,
					'meta_value'     => maybe_serialize( $data ),
					'run_number'     => $run_number,
				];

				$uncanny_automator->insert_trigger_meta( $insert );
			}
		}

		return $data;
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
					if( isset($trigger['meta'][ $trigger_code ]) && isset($trigger['meta'][ $trigger_second_code ])){
						$matches[ $trigger['ID'] ]    = [
							'field' => $trigger['meta'][ $trigger_code ],
							'value' => $trigger['meta'][ $trigger_second_code ],
						];
						$recipe_ids[ $trigger['ID'] ] = $recipe['ID'];
					}
				}
			}
		}

		//Try to match value with submitted to isolate recipe ids matched
		if ( ! empty( $matches ) ) {
			foreach ( $matches as $trigger_id => $match ) {
				$matched = false;
				if ( $metas ) {
					foreach ( $metas as $meta ) {
						if ( absint( $match['field'] ) !== absint( $meta->field_id ) ) {
							continue;
						}

						if ( ( absint( $match['field'] ) === absint( $meta->field_id ) ) && ( $match['value'] == $meta->meta_value ) ) {
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