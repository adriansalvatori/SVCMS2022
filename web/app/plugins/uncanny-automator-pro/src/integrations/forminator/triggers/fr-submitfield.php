<?php

namespace Uncanny_Automator_Pro;

/**
 * Class FR_SUBMITFIELD
 * @package Uncanny_Automator_Pro
 */
class FR_SUBMITFIELD {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'FR';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'FRSUBMITFIELD';
		$this->trigger_meta = 'FRFORM';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name(),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/forminator/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'sentence'            => sprintf(
			/* translators: Logged-in trigger - Forminator */
				esc_attr__( 'A user submits {{a form:%1$s}} with {{a specific value:%2$s}} in {{a specific field:%3$s}}', 'uncanny-automator-pro' ),
				$this->trigger_meta,
				'SUBVALUE' . ':' . $this->trigger_meta,
				$this->trigger_code . ':' . $this->trigger_meta
			),
			/* translators: Logged-in trigger - Forminator */
			'select_option_name'  => esc_attr__( 'A user submits {{a form}} with {{a specific value}} in {{a specific field}}', 'uncanny-automator-pro' ),
			'action'              => 'forminator_custom_form_submit_before_set_fields',
			'priority'            => 10,
			'accepted_args'       => 4,
			'validation_function' => array( $this, 'fr_submit_form' ),
			'options'             => [],
			'options_group'       => [
				$this->trigger_meta => [
					$uncanny_automator->helpers->recipe->forminator->options->all_forminator_forms( null, $this->trigger_meta, [
						'token'        => false,
						'is_ajax'      => true,
						'target_field' => $this->trigger_code,
						'endpoint'     => 'select_form_fields_FRFORMS',
					] ),
					$uncanny_automator->helpers->recipe->field->select_field( $this->trigger_code, __( 'Field', 'uncanny-automator-pro' ) ),
					$uncanny_automator->helpers->recipe->field->text_field( 'SUBVALUE', __( 'Value', 'uncanny-automator-pro' ) ),
				],
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param int $form_id submitted form id.
	 * @param array $response response array.
	 * @param string $args form type.
	 */
	public function fr_submit_form( $entry, $form_id, $field_data_array ) {
		global $uncanny_automator;
		$recipes    = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$form_entry = forminator_get_latest_entry_by_form_id( $form_id );
		$user_id    = get_current_user_id();
		if ( empty( $user_id ) ) {
			return;
		}

		if ( empty( $form_entry ) ) {
			return;
		}

		$conditions = $this->match_condition( $field_data_array, $form_id, $recipes, $this->trigger_meta, $this->trigger_code, 'SUBVALUE' );

		if ( ! $conditions ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( ! empty( $conditions ) ) {
			foreach ( $conditions['recipe_ids'] as $trigger_id => $recipe_id ) {
				if ( ! $uncanny_automator->is_recipe_completed( $recipe_id, $user_id ) ) {
					$args = [
						'code'             => $this->trigger_code,
						'meta'             => $this->trigger_meta,
						'recipe_to_match'  => $recipe_id,
						'trigger_to_match' => $trigger_id,
						'ignore_post_id'   => true,
						'user_id'          => $user_id,
					];

					$args = $uncanny_automator->maybe_add_trigger_entry( $args, false );

					$recipe_to_match = $uncanny_automator->get_recipes_data( true, $recipe_id );
					do_action( 'automator_save_forminator_form_entry', $form_id, $recipe_to_match, $args );

					if ( $args ) {
						foreach ( $args as $result ) {
							if ( true === $result['result'] ) {
								if ( ! empty( $field_data_array ) ) {
									$trigger_id     = (int) $result['args']['trigger_id'];
									$user_id        = (int) $user_id;
									$trigger_log_id = (int) $result['args']['get_trigger_id'];
									$run_number     = (int) $result['args']['run_number'];
									$meta_key       = (string) $this->trigger_meta;
									foreach ( $field_data_array as $entry_field ) {
										$field_meta = "{$trigger_id}:{$meta_key}:{$form_id}|" . $entry_field['name'];
										$insert     = [
											'user_id'        => $user_id,
											'trigger_id'     => $trigger_id,
											'trigger_log_id' => $trigger_log_id,
											'meta_key'       => $field_meta,
											'meta_value'     => maybe_serialize( $entry_field['value'] ),
											'run_number'     => $run_number,
										];
										Automator()->insert_trigger_meta( $insert );
									}
								}
								$uncanny_automator->maybe_trigger_complete( $result['args'] );
								break;
							}
						}
					}
				}
			}
		}

	}

	/**
	 * Match condition for form field and value.
	 *
	 * @param object $entry .
	 * @param null|array $recipes .
	 * @param null|string $trigger_meta .
	 * @param null|string $trigger_code .
	 * @param null|string $trigger_second_code .
	 *
	 * @return array|bool
	 */
	public function match_condition( $entry, $form_id, $recipes = null, $trigger_meta = null, $trigger_code = null, $trigger_second_code = null ) {
		if ( null === $recipes ) {
			return false;
		}

		if ( empty( $entry ) ) {
			return false;
		}

		$matches        = [];
		$recipe_ids     = [];
		$entry_to_match = $form_id;
		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				if ( key_exists( $trigger_meta, $trigger['meta'] ) && $trigger['meta'][ $trigger_meta ] === $entry_to_match ) {
					$matches[ $trigger['ID'] ]    = [
						'field' => $trigger['meta'][ $trigger_code ],
						'value' => $trigger['meta'][ $trigger_second_code ],
					];
					$recipe_ids[ $trigger['ID'] ] = $recipe['ID'];
				}
			}
		}

		$entry_array = [];
		foreach ( $entry as $index ) {
			$entry_array[ $index['name'] ] = $index['value'];
		}

		if ( ! empty( $matches ) ) {
			foreach ( $matches as $trigger_id => $match ) {
				if ( $entry_array[ $match['field'] ] !== $match['value'] ) {
					if ( ! is_array( maybe_unserialize( $entry_array[ $match['field'] ] ) ) ) {
						unset( $recipe_ids[ $trigger_id ] );
					}
					if ( is_array( maybe_unserialize( $entry_array[ $match['field'] ] ) ) && ! in_array( $match['value'], maybe_unserialize( $entry_array[ $match['field'] ] ), false ) ) {
						unset( $recipe_ids[ $trigger_id ] );
					}
				}
			}
		}

		if ( ! empty( $recipe_ids ) ) {
			return [ 'recipe_ids' => $recipe_ids, 'result' => true ];
		}

		return false;
	}
}
