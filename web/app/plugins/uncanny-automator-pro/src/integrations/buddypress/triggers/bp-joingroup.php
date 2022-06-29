<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BP_JOINGROUP
 * @package Uncanny_Automator_Pro
 */
class BP_JOINGROUP {

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
	function __construct() {
		$this->trigger_code = 'BPJOINGROUP';
		$this->trigger_meta = 'BPGROUPS';
		$this->define_trigger();

	}

	/**
	 *
	 */
	function define_trigger() {

		global $uncanny_automator;

		$bp_group_args = array(
			'uo_include_any' => true,
			'uo_any_label'   => __( 'Any public group', 'uncanny-automator-pro' ),
			'status'         => array( 'public' ),
		);

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name(),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/buddypress/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - BuddyPress */
			'sentence'            => sprintf( __( 'A user joins {{a public group:%1$s}}', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - BuddyPress */
			'select_option_name'  => __( 'A user joins {{a public group}}', 'uncanny-automator-pro' ),
			'action'              => 'groups_join_group',
			'priority'            => 60,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'groups_join_group' ),
			'options'             => [
				$uncanny_automator->helpers->recipe->buddypress->options->all_buddypress_groups(
					__( 'Public group', 'uncanny-automator-pro' ),
					'BPGROUPS',
					$bp_group_args
				),
			]
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * @param $group_id
	 * @param $user_id
	 */
	function groups_join_group( $group_id, $user_id ) {
		global $uncanny_automator;

		$recipes = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$group   = $uncanny_automator->get->meta_from_recipes( $recipes, 'BPGROUPS' );

		$matched_recipe_ids = [];

		foreach ( $recipes as $recipe_id => $recipe ) {
			// Match recipe if trigger for Any group '-1', or matching Group ID.
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if (
					intval( '-1' ) === intval( $group[ $recipe_id ][ $trigger_id ] )
					|| intval( $group_id ) === intval( $group[ $recipe_id ][ $trigger_id ] )

				) {
					$matched_recipe_ids[] = [
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					];
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$args = [
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $user_id,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
				];

				$args = $uncanny_automator->maybe_add_trigger_entry( $args, false );
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
								'meta_key'       => 'BPGROUPS',
								'meta_value'     => $group_id,
							];

							$uncanny_automator->insert_trigger_meta( $save_meta );

							$uncanny_automator->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}
}