<?php

namespace Uncanny_Automator_Pro;

/**
 * Class LD_USERCOMPLETESGROUPSCOURSE
 *
 * @package Uncanny_Automator_Pro
 */
class LD_USERCOMPLETESGROUPSCOURSE {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'LD';

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
		$this->trigger_code = 'LD_USERCOMPLETESGROUPSCOURSE';
		$this->trigger_meta = 'LDGROUPCOURSES';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/learndash/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - LearnDash */
			'sentence'            => sprintf( esc_attr__( "A user completes {{a group's:%1\$s}} courses {{a number of:%2\$s}} time(s)", 'uncanny-automator-pro' ), $this->trigger_meta, 'NUMTIMES' ),
			/* translators: Logged-in trigger - LearnDash */
			'select_option_name'  => esc_attr__( "A user completes {{a group's}} courses", 'uncanny-automator-pro' ),
			'action'              => 'learndash_group_completed',
			'priority'            => 99,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'group_courses_completed' ),
			'options'             => array(
				Automator()->helpers->recipe->learndash->options->all_ld_groups( null, $this->trigger_meta ),
				Automator()->helpers->recipe->options->number_of_times(),
			),
		);
		Automator()->register->trigger( $trigger );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $group_progress
	 */
	public function group_courses_completed( $group_progress ) {

		if ( empty( $group_progress ) ) {
			return;
		}

		$user = $group_progress['user'];
		if ( ! $user instanceof \WP_User ) {
			return;
		}

		$progress        = $group_progress['progress'];
		$group_completed = false;
		if ( ( ! empty( $progress['total'] ) ) && ( absint( $progress['total'] ) === absint( $progress['completed'] ) ) ) {
			$group_completed = true;
		}
		if ( false === $group_completed ) {
			return;
		}

		$user_id  = absint( $user->ID );
		$group    = $group_progress['group'];
		$group_id = absint( $group->ID );
		$recipes  = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		if ( empty( $recipes ) ) {
			return;
		}
		$required_group_id = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		if ( empty( $required_group_id ) ) {
			return;
		}

		$matched_recipe_ids = array();

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$recipe_id  = absint( $recipe_id );
				$trigger_id = absint( $trigger['ID'] );
				if ( ! isset( $required_group_id[ $recipe_id ] ) ) {
					continue;
				}
				if ( ! isset( $required_group_id[ $recipe_id ][ $trigger_id ] ) ) {
					continue;
				}
				if (
					intval( '-1' ) === intval( $required_group_id[ $recipe_id ][ $trigger_id ] ) ||
					absint( $group_id ) === absint( $required_group_id[ $recipe_id ][ $trigger_id ] )
				) {
					$matched_recipe_ids[ $recipe_id ] = array(
						'recipe_id'        => $recipe_id,
						'trigger_id'       => $trigger_id,
						'matched_group_id' => $group_id,
					);
				}
			}
		}

		if ( empty( $matched_recipe_ids ) ) {
			return;
		}
		foreach ( $matched_recipe_ids as $matched_recipe_id ) {
			$pass_args = array(
				'code'             => $this->trigger_code,
				'meta'             => $this->trigger_meta,
				'user_id'          => $user_id,
				'recipe_to_match'  => $matched_recipe_id['recipe_id'],
				'trigger_to_match' => $matched_recipe_id['trigger_id'],
				'ignore_post_id'   => true,
				'is_signed_in'     => true,
			);

			$args = Automator()->maybe_add_trigger_entry( $pass_args, false );
			if ( empty( $args ) ) {
				continue;
			}
			foreach ( $args as $result ) {
				if ( true !== $result['result'] ) {
					continue;
				}
				Automator()->insert_trigger_meta(
					array(
						'user_id'        => $user_id,
						'trigger_id'     => $result['args']['trigger_id'],
						'meta_key'       => 'COURSEINGROUP',
						'meta_value'     => $matched_recipe_id['matched_group_id'],
						'trigger_log_id' => $result['args']['get_trigger_id'],
						'run_number'     => $result['args']['run_number'],
					)
				);
				Automator()->maybe_trigger_complete( $result['args'] );
			}
		}
	}
}
