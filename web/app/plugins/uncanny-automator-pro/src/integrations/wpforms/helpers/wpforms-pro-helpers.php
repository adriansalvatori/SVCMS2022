<?php


namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Wpforms_Helpers;
use WPForms_Form_Handler;

/**
 * Class Wpforms_Pro_Helpers
 *
 * @package Uncanny_Automator_Pro
 */
class Wpforms_Pro_Helpers extends Wpforms_Helpers {
	/**
	 * Wpforms_Pro_Helpers constructor.
	 */
	public function __construct() {

		$this->load_options = true;

		add_action( 'wp_ajax_select_form_fields_ANONWPFFORMS', array( $this, 'select_form_fields_func' ) );

		add_action( 'wp_ajax_select_form_fields_WPFFORMS', array( $this, 'select_form_fields_func' ) );

	}

	/**
	 * @param Wpforms_Pro_Helpers $pro
	 */
	public function setPro( Wpforms_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}

	/**
	 * Return all the specific fields of a form ID provided in ajax call
	 */
	public function select_form_fields_func() {

		global $uncanny_automator;

		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$fields                 = array();
		$form_id                = absint( $_POST['value'] );
		$wpforms                = new WPForms_Form_Handler();
		$form                   = $wpforms->get( $form_id );
		$meta                   = wpforms_decode( $form->post_content );
		$disallowed_field_types = apply_filters(
			'automator_wpforms_disallowed_fields',
			array(
				'pagebreak',
				'file-upload',
				'password',
				'divider',
				'entry-preview',
				'html',
				'stripe-credit-card',
				'authorize_net',
				'square',
			),
			array( $form_id )
		);
		if ( is_array( $meta['fields'] ) ) {
			foreach ( $meta['fields'] as $field ) {
				if ( in_array( (string) $field['type'], $disallowed_field_types, true ) ) {
					continue;
				}
				$input_id    = $field['id'];
				$input_title = $field['label'];
				$fields[]    = array(
					'value' => $input_id,
					'text'  => $input_title,
				);
			}
		}
		echo wp_json_encode( $fields );
		die();
	}

	/**
	 * Match condition for form field and value.
	 *
	 * @param array $entry .
	 * @param null|array $recipes .
	 * @param null|string $trigger_meta .
	 * @param null|string $trigger_code .
	 * @param null|string $trigger_second_code .
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
		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				if ( key_exists( $trigger_meta, $trigger['meta'] ) && absint( $trigger['meta'][ $trigger_meta ] ) === absint( $entry_to_match ) ) {
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

	/**
	 * @param $entry_id
	 *
	 * @return string
	 */
	public function get_entry_user_ip_address( $entry_id ) {
		if ( method_exists( '\Uncanny_Automator\Wpforms_Helpers', 'get_entry_user_ip_address' ) ) {
			return Automator()->helpers->recipe->wpforms->options->get_entry_user_ip_address( $entry_id );
		}
		$user_ip = 'N/A';
		if ( wpforms()->entry && method_exists( wpforms()->entry, 'get' ) ) {
			$entry_details = wpforms()->entry->get( $entry_id, array( 'cap' => false ) );
			$user_ip       = $entry_details->ip_address;
		}

		return $user_ip;
	}

	/**
	 * @param $entry_id
	 *
	 * @return false|int|string
	 */
	public function get_entry_entry_date( $entry_id ) {
		if ( method_exists( '\Uncanny_Automator\Wpforms_Helpers', 'get_entry_entry_date' ) ) {
			return Automator()->helpers->recipe->wpforms->options->get_entry_entry_date( $entry_id );
		}
		$entry_date = 'N/A';
		if ( wpforms()->entry && method_exists( wpforms()->entry, 'get' ) ) {
			$entry_details = wpforms()->entry->get( $entry_id, array( 'cap' => false ) );
			$entry_date    = strtotime( $entry_details->date );
		}

		return $entry_date;
	}

	/**
	 * @param $entry_id
	 *
	 * @return mixed
	 */
	public function get_entry_entry_id( $entry_id ) {
		if ( method_exists( '\Uncanny_Automator\Wpforms_Helpers', 'get_entry_entry_id' ) ) {
			return Automator()->helpers->recipe->wpforms->options->get_entry_entry_id( $entry_id );
		}
		if ( wpforms()->entry && method_exists( wpforms()->entry, 'get' ) ) {
			$entry_details = wpforms()->entry->get( $entry_id, array( 'cap' => false ) );
			$entry_id      = $entry_details->entry_id;
		}

		return $entry_id;
	}
}
