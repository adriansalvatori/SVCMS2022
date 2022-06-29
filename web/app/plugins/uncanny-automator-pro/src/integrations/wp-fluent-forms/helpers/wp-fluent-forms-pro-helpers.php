<?php /** @noinspection ALL */

namespace Uncanny_Automator_Pro;

/**
 * Class Wp_Fluent_Forms_Helpers
 * @package Uncanny_Automator
 */
class Wp_Fluent_Forms_Pro_Helpers extends \Uncanny_Automator\Wp_Fluent_Forms_Helpers {

	/**
	 * @param \Uncanny_Automator_Pro\Wp_Fluent_Forms_Pro_Helpers $pro
	 */
	public function setPro( \Uncanny_Automator_Pro\Wp_Fluent_Forms_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}

	/**
	 * Wp_Fluent_Forms_Pro_Helpers constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_SELECT_WPFF_FORM_FIELDS', array( $this, 'select_field_func' ) );
	}

	/**
	 * Return all the specific fields of post type in ajax call
	 */
	public function select_field_func() {
		global $uncanny_automator;

		$uncanny_automator->utilities->ajax_auth_check( $_POST );
		$select_values = [];
		if ( isset( $_POST ) && key_exists( 'value', $_POST ) && ! empty( $_POST['value'] ) ) {
			$form_id       = sanitize_text_field( $_POST['value'] );
			$select_values = $this->form_fields_assoc_array( $form_id );
		}

		echo wp_json_encode( $select_values );
		die();
	}


	/**
	 * @param null $form_id
	 * @param null $form
	 * @param bool $children
	 *
	 * @return array
	 */
	public function form_fields_assoc_array( $form_id = null, $form = null, $children = false ) {

		$select_values = array();
		$form          = ( null !== $form ) ? $form : wpFluent()->table( 'fluentform_forms' )->find( $form_id );
		$field_data    = json_decode( $form->form_fields, true );
		if ( ! empty( $field_data ) && isset( $field_data['fields'] ) ) {
			foreach ( $field_data['fields'] as $field ) {
				// check if the field has multiple inputs ...
				if ( isset( $field['fields'] ) ) {
					foreach ( $field['fields'] as $field_key => $sub_field ) {
						if (
							isset( $sub_field['settings'] )
							&& isset( $sub_field['settings']['label'] )
							&& isset( $sub_field['settings']['visible'] )
							&& true === $sub_field['settings']['visible']
						) {
							if ( $children ) {
								$select_values[] = array(
									'value'  => $field_key,
									'text'   => esc_html( $sub_field['settings']['label'] ),
									'parent' => $field['attributes']['name'],
								);
							} else {
								$select_values[] = array(
									'value' => $field_key,
									'text'  => esc_html( $sub_field['settings']['label'] ),
								);
							}
						}
					}
				} elseif ( isset( $field['element'] ) && 'container' === (string) $field['element'] && isset( $field['columns'] ) && is_array( $field['columns'] ) ) {
					$container_fields = $field['columns'];
					foreach ( $container_fields as $c_fields ) {
						foreach ( $c_fields['fields'] as $field_key => $sub_field ) {
							if ( isset( $sub_field['settings'] ) && isset( $sub_field['settings']['label'] ) ) {
								$select_values[] = array(
									'value' => isset( $sub_field['attributes']['name'] ) ? $sub_field['attributes']['name'] : strtolower( $sub_field['settings']['label'] ),
									'text'  => esc_html( $sub_field['settings']['label'] ),
								);
								
							}
						}
					}
				} elseif ( isset( $field['attributes'] ) && isset( $field['attributes']['name'] ) ) {
					if ( isset( $field['attributes']['placeholder'] ) && ! empty( $field['attributes']['placeholder'] ) ) {
						$select_values[] = array(
							'value' => $field['attributes']['name'],
							'text'  => esc_html( $field['attributes']['placeholder'] ),
						);
					} elseif ( isset( $field['settings'] ) && isset( $field['settings']['label'] ) && ! empty( $field['settings']['label'] ) ) {
						$select_values[] = array(
							'value' => $field['attributes']['name'],
							'text'  => esc_html( $field['settings']['label'] ),
						);
					}
				}
			}
		}

		return $select_values;
	}

	/**
	 * @param $form_id
	 * @param $trigger_meta
	 *
	 * @return array
	 */
	public function create_tokens( $form_id, $trigger_meta ) {

		$form_fields = $this->form_fields_assoc_array( $form_id, null, true );
		$new_tokens  = array();

		foreach ( $form_fields as $field ) {
			$input_id    = $field['value'];
			$input_title = $field['text'];

			if ( isset( $field['parent'] ) ) {
				$parent   = $field['parent'];
				$token_id = "$form_id|$parent|$input_id";
			} else {
				$token_id = "$form_id|$input_id";
			}
			$type = 'text';
			switch ( $input_id ) {
				case 'email':
					$type = 'email';
					break;
				case 'url':
					$type = 'url';
					break;
				case 'numeric-field':
					$type = 'float';
					break;
			}
			$new_tokens[] = array(
				'tokenId'         => $token_id,
				'tokenName'       => $input_title,
				'tokenType'       => $type,
				'tokenIdentifier' => $trigger_meta,
			);
		}

		return $new_tokens;

	}
}
