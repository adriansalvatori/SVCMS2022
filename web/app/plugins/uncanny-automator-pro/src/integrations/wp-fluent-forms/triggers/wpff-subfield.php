<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WPFF_SUBFORM
 * @package Uncanny_Automator
 */
class WPFF_SUBFIELD {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WPFF';

	/**
	 * @var string
	 */
	private $trigger_code;
	/**
	 * @var string
	 */
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'WPFFSUBFIELD';
		$this->trigger_meta = 'WPFFFORMS';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/wp-fluent-forms/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - Fluent Forms */
			'sentence'            => sprintf(
				__( 'A user submits {{a form:%1$s}} with {{a specific value:%2$s}} in {{a specific field:%3$s}}', 'uncanny-automator' ),
				$this->trigger_meta, 'FORMFIELDVALUE:' . $this->trigger_meta, 'FORMFIELD:' . $this->trigger_meta ),
			/* translators: Logged-in trigger - Fluent Forms */
			'select_option_name'  => __( 'A user submits {{a form}} with {{a specific value}} in {{a specific field}}', 'uncanny-automator' ),
			'action'              => 'fluentform_before_insert_submission',
			'priority'            => 20,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'wpffform_submit' ),
			'options_group'       => [
				$this->trigger_meta => [
					$uncanny_automator->helpers->recipe->wp_fluent_forms->options->list_wp_fluent_forms( null, 'WPFFFORMS', [
						'token'        => false,
						'is_ajax'      => true,
						'target_field' => 'FORMFIELD',
						'endpoint'     => 'SELECT_WPFF_FORM_FIELDS',
					] ),
					$uncanny_automator->helpers->recipe->field->select_field( 'FORMFIELD', __( 'Field', 'uncanny-automator' ) ),
					$uncanny_automator->helpers->recipe->field->text_field( 'FORMFIELDVALUE', __( 'Value', 'uncanny-automator' ) ),
				],
			],
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $insert_data
	 * @param $data
	 * @param $form
	 */
	public function wpffform_submit( $insert_data, $submitted_data, $form_data ) {

		$user_id = get_current_user_id();

		// Logged in users only
		if ( empty( $user_id ) ) {
			return;
		}

		// Current user ID should match the logged in user ... Sanity check
		if ( $insert_data['user_id'] !== $user_id ) {
			return;
		}


		global $uncanny_automator;

		$recipes = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$matches = $this->match_condition( $form_data, $submitted_data, $recipes );
		if ( ! $matches ) {
			return;
		}

		foreach ( $matches as $trigger_id => $match ) {
			if ( $uncanny_automator->is_recipe_completed( $match['recipe_id'], $user_id ) ) {
				continue;
			}
			$args = array(
				'code'             => $this->trigger_code,
				'meta'             => $this->trigger_meta,
				'meta_key'         => $this->trigger_meta,
				'recipe_to_match'  => $match['recipe_id'],
				'trigger_to_match' => $trigger_id,
				'ignore_post_id'   => true,
				'user_id'          => $user_id,
			);

			$submitted_data['match_data'] = $match;

			$result = $uncanny_automator->maybe_add_trigger_entry( $args, false );

			if ( $result ) {
				foreach ( $result as $r ) {
					if ( true === $r['result'] ) {
						if ( isset( $r['args'] ) && isset( $r['args']['get_trigger_id'] ) ) {
							//Saving form values in trigger log meta for token parsing!
							$wp_ff_args = [
								'code'           => $this->trigger_code,
								'meta'           => $this->trigger_meta,
								'trigger_id'     => (int) $r['args']['trigger_id'],
								'meta_key'       => $this->trigger_meta,
								'user_id'        => $user_id,
								'trigger_log_id' => $r['args']['get_trigger_id'],
								'run_number'     => $r['args']['run_number'],
							];

							$uncanny_automator->helpers->recipe->wp_fluent_forms->extract_save_wp_fluent_form_fields( $submitted_data, $form_data, $wp_ff_args );
							$uncanny_automator->maybe_add_trigger_entry( $r['args'] );
						}
					}
				}
			}
		}
	}

	/**
	 * @param      $form_data
	 * @param      $submitted_data
	 * @param null $recipes
	 * @param null $trigger_meta
	 * @param null $trigger_code
	 * @param null $trigger_second_code
	 *
	 * @return array|bool
	 */
	public function match_condition( $form_data, $submitted_data, $recipes = null ) {

		if ( null === $recipes ) {
			return false;
		}

		$matches = array();

		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				//  Validate that all needed fields and value are set
				if (
					isset( $trigger['meta'] ) && ! empty( $trigger['meta'] )
					&& isset( $trigger['meta']['WPFFFORMS'] ) && ! empty( $trigger['meta']['WPFFFORMS'] ) // FORM ID
					&& isset( $trigger['meta']['FORMFIELD'] ) && ! empty( $trigger['meta']['FORMFIELD'] )
					&& isset( $trigger['meta']['FORMFIELDVALUE'] ) && ! empty( $trigger['meta']['FORMFIELDVALUE'] )
				) {

					// Validate if the form id of the submitted form matches the form id of the created trigger
					if ( absint( $form_data->id ) !== absint( $trigger['meta']['WPFFFORMS'] ) ) {
						continue;
					}

					foreach ( $submitted_data as $key => $field ) {

						// $field maybe either an array of user inputs and the actual user in put
						// $key may be either the key of the user input or the name of a group of user inputs

						if ( is_array( $field ) ) {
							// checks for multi checkbox, etc.
							if ( $trigger['meta']['FORMFIELD'] === $key ) {
								if ( in_array( $trigger['meta']['FORMFIELDVALUE'], $field, true ) ) {
									$matches[ $trigger['ID'] ] = array(
										'recipe_id' => $recipe['ID'],
										'form_id'   => $trigger['meta'][ $this->trigger_meta ],
										'field_key' => $trigger['meta']['FORMFIELD'],
										'value'     => $trigger['meta']['FORMFIELDVALUE'],
									);
								}
							} else {
								// check a field that has multiple user inputs
								foreach ( $field as $_key => $user_input ) {
									if ( $_key === $trigger['meta']['FORMFIELD'] ) {
										if ( $user_input === $trigger['meta']['FORMFIELDVALUE'] ) {
											$matches[ $trigger['ID'] ] = array(
												'recipe_id' => $recipe['ID'],
												'form_id' => $trigger['meta'][ $this->trigger_meta ],
												'field_key' => $trigger['meta']['FORMFIELD'],
												'value'   => $trigger['meta']['FORMFIELDVALUE'],
											);
										}
									}
								}
							}
						} else {
							// check a field that has only one input
							if ( $key === $trigger['meta']['FORMFIELD'] ) {
								if ( $field === $trigger['meta']['FORMFIELDVALUE'] ) {
									$matches[ $trigger['ID'] ] = [
										'recipe_id' => $recipe['ID'],
										'form_id'   => $trigger['meta']['WPFFFORMS'],
										'field_key' => $trigger['meta']['FORMFIELD'],
										'value'     => $trigger['meta']['FORMFIELDVALUE'],
									];
								}
							}
						}

					}
				}
			}
		}

		if ( ! empty( $matches ) ) {
			return $matches;
		}

		return false;
	}
}
