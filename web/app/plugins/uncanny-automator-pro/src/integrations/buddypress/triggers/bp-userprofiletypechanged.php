<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BP_USERPROFILETYPECHANGED
 * @package Uncanny_Automator_Pro
 */
class BP_USERPROFILETYPECHANGED {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'BP';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'BPUSERPROFILETYPECHANGED';
		$this->trigger_meta = 'BPPROFILETYPE';
		$this->define_trigger();

	}

	/**
	 *
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name(),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/buddypress/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - BuddyBoss */
			'sentence'            => sprintf( __( "A user's member type is set to {{a specific type:%1\$s}}", 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - BuddyBoss */
			'select_option_name'  => __( "A user's member type is set to {{a specific type}}", 'uncanny-automator-pro' ),
			'action'              => 'bp_set_member_type',
			'priority'            => 10,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'bp_set_member_type_updated' ),
			'options'             => [
				$uncanny_automator->helpers->recipe->buddypress->pro->get_profile_types(
					__( 'Member type', 'uncanny-automator' ),
					$this->trigger_meta
				),
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * @param $user_id
	 * @param $member_type
	 * @param $append
	 */
	public function bp_set_member_type_updated( $user_id, $member_type, $append ) {
		global $uncanny_automator;

		if ( empty( $member_type ) ) {
			return;
		}

		// match profile type.
		$recipes    = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$conditions = $this->match_condition( $member_type, $recipes, $this->trigger_meta, $this->trigger_code, '' );

		if ( empty( $conditions ) ) {
			return;
		}

		foreach ( $conditions['recipe_ids'] as $trigger_id => $recipe_id ) {
			if ( ! $uncanny_automator->is_recipe_completed( $recipe_id, $user_id ) ) {
				$trigger_args = [
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'recipe_to_match'  => $recipe_id,
					'trigger_to_match' => $trigger_id,
					'ignore_post_id'   => true,
					'user_id'          => $user_id,
				];
				$uncanny_automator->maybe_add_trigger_entry( $trigger_args );
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
	public function match_condition( $member_type, $recipes = null, $trigger_meta = null, $trigger_code = null, $trigger_second_code = null ) {

		if ( null === $recipes ) {
			return false;
		}

		$recipe_ids = array();

		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				if ( key_exists( $trigger_meta, $trigger['meta'] ) && (string) $trigger['meta'][ $trigger_meta ] === (string) $member_type ) {
					$recipe_ids[ $trigger['ID'] ] = $recipe['ID'];
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
