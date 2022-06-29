<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BDB_USERUPDATEPROFILEFIELDS
 * @package Uncanny_Automator_Pro
 */
class BDB_USERUPDATEPROFILEFIELDS {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'BDB';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'BDBUSERUPDATEPROFILEFIELDS';
		$this->trigger_meta = 'BDBUSER';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/buddyboss/' ),
			'integration'         => self::$integration,
			'is_pro'              => true,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			/* translators: Logged-in trigger - BuddyBoss */
			'sentence'            => sprintf( __( 'A user updates their profile with {{a specific value:%1$s}} in {{a specific field:%2$s}}', 'uncanny-automator-pro' ), 'SUBVALUE:' . $this->trigger_meta, $this->trigger_meta ),
			/* translators: Logged-in trigger - BuddyBoss */
			'select_option_name'  => __( 'A user updates their profile with {{a specific value}} in {{a specific field}}', 'uncanny-automator-pro' ),
			'action'              => 'xprofile_updated_profile',
			'priority'            => 10,
			'accepted_args'       => 5,
			'validation_function' => array( $this, 'bp_user_updated_profile' ),
			'options_group'       => [
				$this->trigger_meta => [
					$uncanny_automator->helpers->recipe->buddyboss->options->pro->list_all_profile_fields( esc_attr__( 'Field', 'uncanny-automator-pro' ), $this->trigger_meta ),
					$uncanny_automator->helpers->recipe->field->text_field( 'SUBVALUE', __( 'Value', 'uncanny-automator-pro' ) ),
				],
			],
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 *  Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $posted_field_ids
	 * @param $errors
	 * @param $old_values
	 * @param $new_values
	 */
	public function bp_user_updated_profile( $user_id, $posted_field_ids, $errors, $old_values, $new_values ) {

		global $uncanny_automator;

		if ( $errors ) {
			return;
		}

		$recipes    = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$conditions = $this->match_condition( $user_id, $recipes, $this->trigger_meta, $this->trigger_code, 'SUBVALUE', $new_values );

		if ( empty( $conditions ) ) {
			return;
		}

		foreach ( $conditions['recipe_ids'] as $trigger_id => $recipe_id ) {
			if ( ! $uncanny_automator->is_recipe_completed( $recipe_id, $user_id ) ) {

				$args = [
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $user_id,
					'ignore_post_id'   => true,
					'is_signed_in'     => true,
					'recipe_to_match'  => $recipe_id,
					'trigger_to_match' => $trigger_id,
				];

				$user_data = get_userdata( $user_id );
				$args      = $uncanny_automator->maybe_add_trigger_entry( $args, false );

				// Save trigger meta
				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] && $result['args']['trigger_id'] && $result['args']['get_trigger_id'] ) {

							$run_number = $uncanny_automator->get->trigger_run_number( $result['args']['trigger_id'], $result['args']['get_trigger_id'], $user_id );
							$save_meta  = [
								'user_id'        => $user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'run_number'     => $run_number, //get run number
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'ignore_user_id' => true,
							];

							$save_meta['meta_key']   = 'first_name';
							$save_meta['meta_value'] = $user_data->first_name;
							$uncanny_automator->insert_trigger_meta( $save_meta );

							$save_meta['meta_key']   = 'last_name';
							$save_meta['meta_value'] = $user_data->last_name;
							$uncanny_automator->insert_trigger_meta( $save_meta );

							$save_meta['meta_key']   = 'useremail';
							$save_meta['meta_value'] = $user_data->user_email;
							$uncanny_automator->insert_trigger_meta( $save_meta );

							$save_meta['meta_key']   = 'username';
							$save_meta['meta_value'] = $user_data->user_login;
							$uncanny_automator->insert_trigger_meta( $save_meta );

							$save_meta['meta_key']   = 'user_id';
							$save_meta['meta_value'] = $user_data->ID;
							$uncanny_automator->insert_trigger_meta( $save_meta );

							$uncanny_automator->maybe_trigger_complete( $result['args'] );
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
	public function match_condition( $user_id, $recipes = null, $trigger_meta = null, $trigger_code = null, $trigger_second_code = null, $new_values = [] ) {

		if ( null === $recipes ) {
			return false;
		}

		if ( empty( $new_values ) ) {
			return false;
		}

		$matches    = [];
		$recipe_ids = [];

		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				if ( key_exists( $trigger_meta, $trigger['meta'] ) ) {
					$matches[ $trigger['ID'] ]    = array(
						'field' => $trigger['meta'][ $trigger_meta ],
						'value' => $trigger['meta'][ $trigger_second_code ],
					);
					$recipe_ids[ $trigger['ID'] ] = $recipe['ID'];
					break;
				}
			}
		}

		if ( ! empty( $matches ) ) {
			foreach ( $matches as $recipe_id => $match ) {
				if ( ! isset( $new_values[ $match['field'] ] ) ) {
					unset( $recipe_ids[ $recipe_id ] );
				} else {
					if ( is_array( $new_values[ $match['field'] ] ['value'] ) ) {
						// convert string to array.
						$user_submission = $new_values[ $match['field'] ] ['value'];
						$trigger_match   = explode( ',', $match['value'] );
						if ( ! empty( array_diff( $trigger_match, $user_submission ) ) ) {
							unset( $recipe_ids[ $recipe_id ] );
						}
					} else {
						if ( $new_values[ $match['field'] ] ['value'] !== $match['value'] ) {
							unset( $recipe_ids[ $recipe_id ] );
						}
					}
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
