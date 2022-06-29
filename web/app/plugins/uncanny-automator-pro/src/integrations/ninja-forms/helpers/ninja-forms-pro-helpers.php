<?php


namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Ninja_Forms_Helpers;
use function Ninja_Forms;

/**
 * Class Ninja_Forms_Pro_Helpers
 *
 * @package Uncanny_Automator_Pro
 */
class Ninja_Forms_Pro_Helpers extends Ninja_Forms_Helpers {
	/**
	 * Ninja_Forms_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Ninja_Forms_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}

		add_action( 'wp_ajax_select_form_fields_ANONNFFORMS', array( $this, 'select_form_fields_func' ) );
		add_action( 'wp_ajax_select_form_fields_NFFORMS', array( $this, 'select_form_fields_func' ) );
	}

	/**
	 * @param Ninja_Forms_Pro_Helpers $pro
	 */
	public function setPro( Ninja_Forms_Pro_Helpers $pro ) {
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
			$meta    = Ninja_Forms()->form( $form_id )->get_fields();
			if ( is_array( $meta ) ) {
				foreach ( $meta as $field ) {
					if ( $field->get_setting( 'type' ) !== 'submit' ) {
						$fields[] = array(
							'value' => $field->get_id(),
							'text'  => $field->get_setting( 'label' ),
						);
					}
				}
			}
		}
		echo wp_json_encode( $fields );
		die();
	}

	/**
	 * @param $entry
	 * @param $args
	 *
	 * @return array
	 */
	public function extract_save_ninja_fields( $entry, $args ) {
		$data = array();
		if ( $entry && class_exists( '\Ninja_Forms' ) ) {
			$fields  = $entry['fields'];
			$form_id = (int) $entry['form_id'];

			$trigger_id     = (int) $args['trigger_id'];
			$user_id        = (int) $args['user_id'];
			$trigger_log_id = (int) $args['trigger_log_id'];
			$run_number     = (int) $args['run_number'];
			$meta_key       = (string) $args['meta_key'];
			if ( $fields ) {
				foreach ( $fields as $field ) {
					$field_id     = $field['id'];
					$key          = "{$trigger_id}:{$meta_key}:{$form_id}|{$field_id}";
					$data[ $key ] = $field['value'];
				}
			}

			if ( $data ) {
				global $uncanny_automator;
				$insert = array(
					'user_id'        => $user_id,
					'trigger_id'     => $trigger_id,
					'trigger_log_id' => $trigger_log_id,
					'meta_key'       => $meta_key,
					'meta_value'     => maybe_serialize( $data ),
					'run_number'     => $run_number,
				);

				$uncanny_automator->insert_trigger_meta( $insert );
			}
		}

		return $data;
	}

	/**
	 * Matching form fields values.
	 *
	 * @param array $entry form data.
	 * @param array|null $recipes recipe data.
	 * @param string|null $trigger_meta trigger meta key.
	 * @param string|null $trigger_code trigger code key.
	 * @param string|null $trigger_second_code trigger second code key.
	 *
	 * @return array|bool
	 */
	public function match_condition( $entry, $recipes = null, $trigger_meta = null, $trigger_code = null, $trigger_second_code = null ) {
		if ( null === $recipes ) {
			return false;
		}

		$matches        = array();
		$recipe_ids     = array();
		$entry_to_match = $entry['form_id'];
		//Matching recipe ids that has trigger meta
		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				if ( key_exists( $trigger_meta, $trigger['meta'] ) && (int) $trigger['meta'][ $trigger_meta ] === (int) $entry_to_match ) {
					$matches[ $trigger['ID'] ]    = array(
						'field' => $trigger['meta'][ $trigger_code ],
						'value' => $trigger['meta'][ $trigger_second_code ],
					);
					$recipe_ids[ $trigger['ID'] ] = $recipe['ID'];
				}
			}
		}

		//Figure if field is available and data matches!!
		if ( ! empty( $matches ) ) {
			$fields = $entry['fields'];
			foreach ( $matches as $trigger_id => $match ) {
				$matched = false;
				foreach ( $fields as $field ) {
					$field_id = $field['id'];
					if ( absint( $match['field'] ) !== absint( $field_id ) ) {
						continue;
					}

					$value = $field['value'];
					if ( is_array( $value ) ) {
						$value_slug = sanitize_title( strtolower( $match['value'] ) );
						if ( ( (int) $field_id === (int) $match['field'] ) && ( in_array( $match['value'], $value, true ) || in_array( $value_slug, $value, true ) ) ) {
							$matched = true;
							break;
						}
					}

					if ( ( (int) $field_id === (int) $match['field'] ) && ( $value == $match['value'] ) ) {
						$matched = true;
						break;
					}
				}

				if ( ! $matched ) {
					unset( $recipe_ids[ $trigger_id ] );
				}
			}
		}

		if ( ! empty( $recipe_ids ) ) {
			return array(
				'recipe_ids' => $recipe_ids,
				'result'     => true,
			);
		}

		return false;
	}
}
