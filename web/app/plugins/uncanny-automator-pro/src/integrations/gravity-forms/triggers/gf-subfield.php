<?php

namespace Uncanny_Automator_Pro;

use GFAPI;

/**
 * Class GF_MATCH_FIELD_VALUE
 *
 * @package Uncanny_Automator_Pro
 */
class GF_SUBFIELD {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'GF';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'SUBFIELD';
		$this->trigger_meta = 'GFFORMS';
		$this->define_trigger();

	}

	/**
	 *
	 */
	public function define_trigger() {
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/gravity-forms/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'sentence'            => sprintf(
			/* translators: Logged-in trigger - Gravity Forms */
				esc_attr__( 'A user submits {{a form:%1$s}} with {{a specific value:%2$s}} in {{a specific field:%3$s}}', 'uncanny-automator-pro' ),
				$this->trigger_meta,
				'SUBVALUE' . ':' . $this->trigger_meta,
				$this->trigger_code . ':' . $this->trigger_meta
			),
			/* translators: Logged-in trigger - Gravity Forms */
			'select_option_name'  => esc_attr__( 'A user submits {{a form}} with {{a specific value}} in {{a specific field}}', 'uncanny-automator-pro' ),
			'action'              => 'gform_after_submission',
			'priority'            => 20,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'gform_submit' ),
			'options'             => array(),
			'options_group'       => array(
				$this->trigger_meta => array(
					Automator()->helpers->recipe->gravity_forms->options->list_gravity_forms( null, $this->trigger_meta, array(
						'token'        => false,
						'is_ajax'      => true,
						'target_field' => $this->trigger_code,
						'endpoint'     => 'select_form_fields_GFFORMS',
					) ),
					Automator()->helpers->recipe->field->select_field( $this->trigger_code, esc_attr__( 'Field', 'uncanny-automator-pro' ) ),
					Automator()->helpers->recipe->field->text_field( 'SUBVALUE', esc_attr__( 'Value', 'uncanny-automator-pro' ) ),
				),
			),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * @param $entry
	 * @param $form
	 */
	public function gform_submit( $entry, $form ) {
		$recipes = Automator()->get->recipes_from_trigger_code( $this->trigger_code );

		if ( empty( $entry ) ) {
			return;
		}

		$conditions = $this->match_condition( $entry, $recipes, $this->trigger_meta, $this->trigger_code, 'SUBVALUE', $form );

		if ( ! $conditions ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( ! empty( $conditions ) ) {
			foreach ( $conditions['recipe_ids'] as $trigger_id => $recipe_id ) {
				if ( ! Automator()->is_recipe_completed( $recipe_id, $user_id ) ) {
					$pass_args = array(
						'code'             => $this->trigger_code,
						'meta'             => $this->trigger_meta,
						'recipe_to_match'  => $recipe_id,
						'trigger_to_match' => $trigger_id,
						'ignore_post_id'   => true,
						'user_id'          => $user_id,
					);

					$args = Automator()->maybe_add_trigger_entry( $pass_args, false );

					if ( ! empty( $args ) ) {
						foreach ( $args as $result ) {
							if ( true === $result['result'] ) {
								$trigger_meta = array(
									'user_id'        => $user_id,
									'trigger_id'     => $result['args']['trigger_id'],
									'trigger_log_id' => $result['args']['get_trigger_id'],
									'run_number'     => $result['args']['run_number'],
								);

								$trigger_meta['meta_key']   = 'GFENTRYID';
								$trigger_meta['meta_value'] = $entry['id'];
								Automator()->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = 'GFUSERIP';
								$trigger_meta['meta_value'] = maybe_serialize( $entry['ip'] );
								Automator()->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = 'GFENTRYDATE';
								$trigger_meta['meta_value'] = maybe_serialize( \GFCommon::format_date( $entry['date_created'], false, 'Y/m/d' ) );
								Automator()->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = 'GFENTRYSOURCEURL';
								$trigger_meta['meta_value'] = maybe_serialize( $entry['source_url'] );
								Automator()->insert_trigger_meta( $trigger_meta );

								Automator()->maybe_trigger_complete( $result['args'] );
							}
						}
					}
				}
			}
		}
	}


	/**
	 *
	 *
	 *
	 * @param $entry
	 * @param null $recipes
	 * @param null $trigger_meta
	 * @param null $trigger_code
	 * @param null $trigger_second_code
	 * @param null $form
	 *
	 * @return array|bool
	 */
	public function match_condition( $entry, $recipes = null, $trigger_meta = null, $trigger_code = null, $trigger_second_code = null, $form = null ) {
		if ( null === $recipes ) {
			return false;
		}

		$matches        = array();
		$recipe_ids     = array();
		$entry_to_match = $entry['form_id'];
		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				if ( key_exists( $trigger_meta, $trigger['meta'] ) && $trigger['meta'][ $trigger_meta ] === $entry_to_match ) {
					$matches[ $trigger['ID'] ]    = array(
						'field' => $trigger['meta'][ $trigger_code ],
						'value' => $trigger['meta'][ $trigger_second_code ],
					);
					$recipe_ids[ $trigger['ID'] ] = $recipe['ID'];
				}
			}
		}

		if ( ! empty( $matches ) ) {
			foreach ( $matches as $trigger_id => $match ) {
				// check form field type
				// Passing the form object with the field id.
				$gf_field = GFAPI::get_field( $form, $match['field'] );
				if ( 'multiselect' === $gf_field->type ) {
					// convert string to array.
					$user_submission = json_decode( $entry[ $match['field'] ], true );
					$trigger_match   = explode( ',', $match['value'] );
					if ( count( $trigger_match ) !== count( $user_submission ) ) {
						unset( $recipe_ids[ $trigger_id ] );
					} elseif ( ! empty( array_diff( $trigger_match, $user_submission ) ) ) {
						unset( $recipe_ids[ $trigger_id ] );
					}
				} else {
					if ( $entry[ $match['field'] ] !== $match['value'] ) {
						unset( $recipe_ids[ $trigger_id ] );
					}
				}
			}
		}

		if ( ! empty( $recipe_ids ) ) {
			return array( 'recipe_ids' => $recipe_ids, 'result' => true );
		}

		return false;
	}
}
