<?php

namespace Uncanny_Automator_Pro;

/**
 * Class ANON_CF7_SUBFIELD
 * @package Uncanny_Automator_Pro
 */
class ANON_CF7_SUBFIELD {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'CF7';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'ANONCF7SUBFIELD';
		$this->trigger_meta = 'ANONCF7FORMS';
		//add_filter( 'wpcf7_verify_nonce', '__return_true' );
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/contact-form-7/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			'sentence'            => sprintf(
			/* translators: Anonymous trigger - Contact Form 7 */
				__( '{{A form:%1$s}} is submitted with {{a specific value:%2$s}} in {{a specific field:%3$s}}', 'uncanny-automator-pro' ),
				$this->trigger_meta,
				'SUBVALUE' . ':' . $this->trigger_meta,
				$this->trigger_code . ':' . $this->trigger_meta
			),
			/* translators: Anonymous trigger - Contact Form 7 */
			'select_option_name'  => __( '{{A form}} is submitted with {{a specific value}} in {{a specific field}}', 'uncanny-automator-pro' ),
			'action'              => 'wpcf7_submit',
			'type'                => 'anonymous',
			'priority'            => 99,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'wpcf7_submit' ),
			'options_group'       => [
				$this->trigger_meta => [
					$uncanny_automator->helpers->recipe->contact_form7->options->list_contact_form7_forms( null, $this->trigger_meta, [
						'token'        => false,
						'is_ajax'      => true,
						'target_field' => $this->trigger_code,
						'endpoint'     => 'select_form_fields_ANONCF7FORMS',
					] ),
					$uncanny_automator->helpers->recipe->field->select_field( $this->trigger_code, __( 'Field', 'uncanny-automator-pro' ) ),
					$uncanny_automator->helpers->recipe->field->text_field( 'SUBVALUE', __( 'Value', 'uncanny-automator-pro' ) ),
				],
			],
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $form
	 * @param $result
	 */
	public function wpcf7_submit( $form, $result ) {

		if ( 'validation_failed' !== $result['status'] ) {

			global $uncanny_automator;

			$user_id = get_current_user_id();

			if ( empty( $form ) ) {
				return;
			}

			$recipes    = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
			$conditions = $this->match_condition( $form, $recipes, $this->trigger_meta, $this->trigger_code, 'SUBVALUE' );

			if ( ! $conditions ) {
				return;
			}
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

						$args            = $uncanny_automator->maybe_add_trigger_entry( $args, false );
						$recipe_to_match = $uncanny_automator->get_recipes_data( true, $recipe_id );
						do_action( 'automator_save_anon_cf7_form', $form, $recipe_to_match, $args );
						if ( $args ) {
							foreach ( $args as $result ) {
								if ( true === $result['result'] ) {
									$uncanny_automator->maybe_trigger_complete( $result['args'] );
									break;
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @param      $form
	 * @param null $recipes
	 * @param null $trigger_meta
	 * @param null $trigger_code
	 * @param null $trigger_second_code
	 *
	 * @return array|bool
	 */
	public function match_condition( $form, $recipes = null, $trigger_meta = null, $trigger_code = null, $trigger_second_code = null ) {

		if ( null === $recipes ) {
			return false;
		}

		$matches        = [];
		$recipe_ids     = [];
		$entry_to_match = $form->id();

		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				if ( key_exists( $trigger_meta, $trigger['meta'] ) && (string) $trigger['meta'][ $trigger_meta ] === (string) $entry_to_match ) {
					$matches[ $trigger['ID'] ]    = [
						'field' => $trigger['meta'][ $trigger_code ],
						'value' => $trigger['meta'][ $trigger_second_code ],
					];
					$recipe_ids[ $trigger['ID'] ] = $recipe['ID'];
				}
			}
		}

		if ( ! empty( $matches ) ) {
			foreach ( $matches as $trigger_id => $match ) {
				$post_input = isset( $_POST[ $match['field'] ] ) ? $_POST[ $match['field'] ] : '';
				// Check if input is an array or string
				if ( is_array( $post_input ) ) {
					$trigger_match = explode( ',', $match['value'] );
					// if input count is less then match then it does not match
					if ( count( $trigger_match ) > count( $post_input ) ) {
						unset( $recipe_ids[ $trigger_id ] );
					} elseif ( ! empty( array_diff( $trigger_match, $post_input ) ) ) {
						unset( $recipe_ids[ $trigger_id ] );
					}
				} else {
					if ( $post_input !== $match['value'] ) {
						unset( $recipe_ids[ $trigger_id ] );
					}
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
