<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WPUM_USERLEAVESGROUP
 * @package Uncanny_Automator_Pro
 */
class WPUM_USERLEAVESGROUP {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WPUSERMANAGER';

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
		$this->trigger_code = 'WPUMLEAVESGROUP';
		$this->trigger_meta = 'WPUMGLEAVED';
		if ( class_exists( 'WPUM_Groups' ) ) {
			$this->define_trigger();
		}
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;
		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/wp-user-manager/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'is_pro'              => true,
			/* translators: Logged-in trigger - WP User Manager */
			'sentence'            => sprintf( __( 'A user leaves {{a group:%1$s}}', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - WP User Manager */
			'select_option_name'  => __( 'A user leaves {{a group}}', 'uncanny-automator-pro' ),
			'action'              => 'wpumgp_after_member_leave',
			'priority'            => 99,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'wpum_user_leaves_group' ),
			'options'             => [
				$uncanny_automator->helpers->recipe->wp_user_manager->pro->get_all_groups( null, $this->trigger_meta, [
					'is_any' => true,
				] ),
			],
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @param $group_id
	 * @param $user_id
	 */
	public function wpum_user_leaves_group( $group_id, $user_id ) {
		global $uncanny_automator;

		if ( 0 === absint( $user_id ) ) {
			// Its a logged in recipe and
			// user ID is 0. Skip process
			return;
		}

		$recipes            = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_group     = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = [];

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( $group_id == $required_group[ $recipe_id ][ $trigger_id ] || $required_group[ $recipe_id ][ $trigger_id ] == '-1' ) {
					$matched_recipe_ids[] = [
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					];
					break;
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$pass_args = [
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $user_id,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
				];

				$args = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {
							$trigger_meta = [
								'user_id'        => $user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							];

							$trigger_meta['meta_key']   = $this->trigger_meta;
							$trigger_meta['meta_value'] = maybe_serialize( get_post_field( 'post_title', $group_id ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$uncanny_automator->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}

	}

}