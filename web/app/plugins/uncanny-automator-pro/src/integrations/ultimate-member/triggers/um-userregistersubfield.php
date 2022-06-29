<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Automator_Recipe_Process_User;

/**
 * Class UM_USERREGISTERSUBFIELD
 *
 * @package Uncanny_Automator_Pro
 */
class UM_USERREGISTERSUBFIELD {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'UM';

	/**
	 * Trigger code
	 *
	 * @var string
	 */
	private $trigger_code;

	/**
	 * Trigger meta
	 *
	 * @var string
	 */
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'UMUSERREGISTERSUBFIELD';
		$this->trigger_meta = 'UMFORM';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		global $uncanny_automator;
		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/ultimate-member/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'sentence'            => sprintf(
				/* translators: Logged-in trigger - Ultimate Member */
				__( 'A user registers with {{a form:%1$s}} with {{a specific value:%2$s}} in {{a specific field:%3$s}}', 'uncanny-automator-pro' ),
				$this->trigger_meta,
				'SUBVALUE:' . $this->trigger_meta,
				$this->trigger_code . ':' . $this->trigger_meta
			),
			/* translators: Logged-in trigger - Ultimate Member */
			'select_option_name'  => __( 'A user registers with {{a form}} with {{a specific value}} in {{a specific field}}', 'uncanny-automator-pro' ),
			'action'              => 'um_registration_complete',
			'priority'            => 99,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'um_user_register_with_form' ),
			'options'             => array(),
			'options_group'       => array(
				$this->trigger_meta => array(
					$uncanny_automator->helpers->recipe->ultimate_member->options->get_um_forms(
						null,
						$this->trigger_meta,
						'register',
						array(
							'any'          => false,
							'token'        => false,
							'is_ajax'      => true,
							'target_field' => $this->trigger_code,
							'endpoint'     => 'select_form_fields_UMFORM',
						)
					),
					$uncanny_automator->helpers->recipe->field->select_field( $this->trigger_code, __( 'Field', 'uncanny-automator-pro' ) ),
					$uncanny_automator->helpers->recipe->field->text_field( 'SUBVALUE', __('Value', 'uncanny-automator-pro' ) ),
				),
			),
		);
		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param int   $user_id User ID.
	 * @param array $um_args Ultimate member form args.
	 */
	public function um_user_register_with_form( $user_id, $um_args ) {
		if ( ! isset( $um_args['form_id'] ) ) {
			return;
		}
		global $uncanny_automator;
		$recipes = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$entry   = isset( $um_args['submitted'] ) ? $um_args['submitted'] : array();

		// adjust for role selector.
		$role = $entry['role'];
		unset( $entry['role'] );
		$entry['role_select'] = $role;
		$entry['role_radio']  = $role;

		$conditions = $this->match_condition( $entry, $recipes, $this->trigger_meta, $this->trigger_code, 'SUBVALUE', $um_args );
		if ( ! empty( $conditions ) ) {
			foreach ( $conditions['recipe_ids'] as $trigger_id => $recipe_id ) {
				$args = array(
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'recipe_to_match'  => $recipe_id,
					'trigger_to_match' => $trigger_id,
					'ignore_post_id'   => true,
					'user_id'          => $user_id,
					'is_signed_in'     => true,
				);
				if ( isset( $uncanny_automator->process ) && isset( $uncanny_automator->process->user ) && $uncanny_automator->process->user instanceof Automator_Recipe_Process_User ) {
					$uncanny_automator->process->user->maybe_add_trigger_entry( $args );
				} else {
					$uncanny_automator->maybe_add_trigger_entry( $args );
				}
			}
		}

	}

	/**
	 * Match Field Conditions.
	 *
	 * @param array  $entry Form entry.
	 * @param array  $recipes Recipes.
	 * @param string $trigger_meta Trigger meta.
	 * @param string $trigger_code Trigger code.
	 * @param string $trigger_second_code Second trigger code.
	 * @param array  $um_args Ultimate Member Form args.
	 *
	 * @return array
	 */
	public function match_condition( $entry, $recipes = null, $trigger_meta = null, $trigger_code = null, $trigger_second_code = null, $um_args = array() ) {
		if ( is_null( $recipes ) || empty( $recipes ) ) {
			return array();
		}
		$matches        = array();
		$recipe_ids     = array();
		$entry_to_match = $um_args['form_id'];
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
		// Figure if field is available and data matches!!
		if ( ! empty( $matches ) ) {
			$fields = $entry;
			foreach ( $matches as $trigger_id => $match ) {
				$to_match = (string) $match['value'];
				$matched = false;
				if ( $fields ) {
					foreach ( $fields as $field_id => $value ) {
						if ( (string) $match['field'] !== (string) $field_id ) {
							continue;
						}
						if ( is_array( $value ) && in_array( $to_match, $value ) ) {
							$matched = true;
							break;
						}
						if ( (string) $value !== $to_match ) {
							continue;
						}
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

		return array();
	}
}
