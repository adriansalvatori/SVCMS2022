<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BP_POSTGROUPACTIVITY
 * @package Uncanny_Automator_Pro
 */
class BP_POSTGROUPACTIVITY {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'BP';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'BPPOSTGROUPACTIVITY';
		$this->trigger_meta = 'BPGROUPS';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$bp_group_args = array(
			'uo_include_any' => true,
			'uo_any_label'   => __( 'Any group', 'uncanny-automator-pro' ),
			'status'         => [ 'public', 'private', 'hidden' ],
		);

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name(),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/buddypress/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - BuddyBoss */
			'sentence'            => sprintf( esc_attr__( 'A user makes a post to the activity stream of {{a group:%1$s}}', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - BuddyBoss */
			'select_option_name'  => esc_attr__( 'A user makes a post to the activity stream of {{a group}}', 'uncanny-automator-pro' ),
			'action'              => 'bp_groups_posted_update',
			'priority'            => 10,
			'accepted_args'       => 4,
			'validation_function' => array( $this, 'bp_activity_posted_update' ),
			'options'             => [
				$uncanny_automator->helpers->recipe->buddypress->options->all_buddypress_groups(
					__( 'Group', 'uncanny-automator-pro' ),
					'BPGROUPS',
					$bp_group_args
				),
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $content
	 * @param $user_id
	 * @param $activity_id
	 */

	public function bp_activity_posted_update( $content, $user_id, $group_id, $activity_id ) {

		global $uncanny_automator;

		$recipes            = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_users     = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = [];


		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( intval( '-1' ) === intval( $required_users[ $recipe_id ][ $trigger_id ] ) || intval( $group_id ) === intval( $required_users[ $recipe_id ][ $trigger_id ] ) ) {
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

				$returns = $uncanny_automator->maybe_add_trigger_entry( $args, false );

				if ( $returns ) {
					foreach ( $returns as $result ) {
						if ( true === $result['result'] ) {

							$trigger_meta = [
								'user_id'        => $user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							];

							// ACTIVITY_ID Token
							$trigger_meta['meta_key']   = 'ACTIVITY_ID';
							$trigger_meta['meta_value'] = $activity_id;
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$group = groups_get_group( $group_id );
							// ACTIVITY_URL Token
							$trigger_meta['meta_key']   = 'ACTIVITY_URL';
							$trigger_meta['meta_value'] = bp_get_group_permalink( $group ) . 'activity';
							Automator()->insert_trigger_meta( $trigger_meta );

							// ACTIVITY_STREAM_URL Token
							$trigger_meta['meta_key']   = 'ACTIVITY_STREAM_URL';
							$trigger_meta['meta_value'] = bp_core_get_user_domain( $user_id ) . 'activity/' . $activity_id;
							Automator()->insert_trigger_meta( $trigger_meta );

							// ACTIVITY_CONTENT Token
							$trigger_meta['meta_key']   = 'ACTIVITY_CONTENT';
							$trigger_meta['meta_value'] = $content;
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							// GROUP ID Token
							$trigger_meta['meta_key']   = 'BPGROUPS';
							$trigger_meta['meta_value'] = $group_id;
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$uncanny_automator->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}
}
