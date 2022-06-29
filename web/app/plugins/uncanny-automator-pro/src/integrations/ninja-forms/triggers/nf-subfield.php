<?php

namespace Uncanny_Automator_Pro;

/**
 * Class NF_SUBFIELD
 *
 * @package Uncanny_Automator_Pro
 */
class NF_SUBFIELD {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'NF';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'NFSUBFIELD';
		$this->trigger_meta = 'NFFORMS';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/ninja-forms/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'sentence'            => sprintf(
			/* translators: Logged-in trigger - Ninja Forms */
				__( 'A user submits {{a form:%1$s}} with {{a specific value:%2$s}} in {{a specific field:%3$s}}', 'uncanny-automator-pro' ),
				$this->trigger_meta,
				'SUBVALUE' . ':' . $this->trigger_meta,
				$this->trigger_code . ':' . $this->trigger_meta
			),
			/* translators: Logged-in trigger - Ninja Forms */
			'select_option_name'  => __( 'A user submits {{a form}} with {{a specific value}} in {{a specific field}}', 'uncanny-automator-pro' ),
			'action'              => 'ninja_forms_after_submission',
			'priority'            => 20,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'nform_submit' ),
			'options'             => [],
			'options_group'       => [
				$this->trigger_meta => [
					$uncanny_automator->helpers->recipe->ninja_forms->options->list_ninja_forms(
						null,
						$this->trigger_meta,
						[
							'token'        => false,
							'is_ajax'      => true,
							'target_field' => $this->trigger_code,
							'endpoint'     => 'select_form_fields_NFFORMS',
						]
					),
					$uncanny_automator->helpers->recipe->field->select_field( $this->trigger_code, __( 'Field', 'uncanny-automator-pro' ) ),
					$uncanny_automator->helpers->recipe->field->text_field( 'SUBVALUE', __( 'Value', 'uncanny-automator-pro' ) ),
				],
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return true;
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param array $entry form submission.
	 */
	public function nform_submit( $entry ) {

		global $uncanny_automator;
		$recipes = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );

		if ( empty( $entry ) ) {
			return;
		}

		$conditions = $uncanny_automator->helpers->recipe->ninja_forms->pro->match_condition( $entry, $recipes, $this->trigger_meta, $this->trigger_code, 'SUBVALUE' );

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

					$result = $uncanny_automator->maybe_add_trigger_entry( $args, false );

					if ( $result ) {
						foreach ( $result as $r ) {
							if ( true === $r['result'] ) {
								if ( isset( $r['args'] ) && isset( $r['args']['get_trigger_id'] ) ) {
									//Saving form values in trigger log meta for token parsing!
									$ninja_args = [
										'trigger_id'     => (int) $r['args']['trigger_id'],
										'meta_key'       => $this->trigger_meta,
										'user_id'        => $user_id,
										'trigger_log_id' => $r['args']['get_trigger_id'],
										'run_number'     => $r['args']['run_number'],
									];

									$uncanny_automator->helpers->recipe->ninja_forms->pro->extract_save_ninja_fields( $entry, $ninja_args );
								}

								$uncanny_automator->maybe_trigger_complete( $r['args'] );
							}
						}
					}
				}
			}
		}
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

		$matches        = [];
		$recipe_ids     = [];
		$entry_to_match = $entry['form_id'];
		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				if ( key_exists( $trigger_meta, $trigger['meta'] ) && $trigger['meta'][ $trigger_meta ] === $entry_to_match ) {
					$matches[ $recipe['ID'] ]    = [
						'field' => $trigger['meta'][ $trigger_code ],
						'value' => $trigger['meta'][ $trigger_second_code ],
					];
					$recipe_ids[ $recipe['ID'] ] = $recipe['ID'];
					break;
				}
			}
		}

		if ( ! empty( $matches ) ) {
			foreach ( $matches as $recipe_id => $match ) {
				if ( $entry['fields'][ $match['field'] ]['value'] !== $match['value'] ) {
					unset( $recipe_ids[ $recipe_id ] );
				}
			}
		}

		if ( ! empty( $recipe_ids ) ) {
			return [
				'recipe_ids' => $recipe_ids,
				'result'     => true,
			];
		}

		return false;
	}

}
