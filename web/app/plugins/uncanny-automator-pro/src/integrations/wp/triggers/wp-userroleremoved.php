<?php

namespace Uncanny_Automator_Pro;

/**
 * @package Uncanny_Automator_Pro
 * Class WP_USERROLEREMOVED
 */
class WP_USERROLEREMOVED {
	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WP';

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
		$this->trigger_code = 'USERROLEREMOVED';
		$this->trigger_meta = 'WPROLE';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/wordpress-core/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			'is_pro'              => true,
			/* translators: Logged-in trigger - WordPress Core */
			'sentence'            => sprintf( __( '{{A specific:%1$s}} role is removed from the user', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - WordPress Core */
			'select_option_name'  => __( '{{A specific}} role is removed from the user', 'uncanny-automator-pro' ),
			'action'              => array( 'remove_user_role', 'set_user_role' ),
			'priority'            => 90,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'wp_remove_user_role' ),
			'options_group'       => array(),
			'options_callback'    => array( $this, 'load_options' ),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * @return array[]
	 */
	public function load_options() {

		return Automator()->utilities->keep_order_of_options(
			array(
				'options' => array(
					Automator()->helpers->recipe->wp->options->wp_user_roles(),
				),
			)
		);
	}

	/**
	 * @param $user_id
	 * @param $role
	 * @param $old_role
	 */
	public function wp_remove_user_role( $user_id, $role, $old_roles = array() ) {
		$recipes            = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_user_role = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );

		if ( ! $recipes ) {
			return;
		}

		if ( ! $required_user_role ) {
			return;
		}

		$matched_recipe_ids = array();
		$user_obj           = get_user_by( 'ID', (int) $user_id );

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];

				if ( empty( $old_roles ) && (string) $role === (string) $required_user_role[ $recipe_id ][ $trigger_id ] ) {
					$matched_recipe_ids[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					);
				} elseif ( ! empty( $old_roles ) && in_array( $required_user_role[ $recipe_id ][ $trigger_id ], $old_roles ) ) {
					$matched_recipe_ids[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					);
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$pass_args = array(
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $user_obj->ID,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
				);

				$results = Automator()->maybe_add_trigger_entry( $pass_args, false );
				if ( $results ) {
					foreach ( $results as $result ) {
						if ( true === $result['result'] ) {

							$trigger_meta = array(
								'user_id'        => $user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							);
							$roles        = array();
							foreach ( wp_roles()->roles as $role_name => $role_info ) {
								$roles[ $role_name ] = $role_info['name'];
							}
							$role_label = isset( $roles[ $role ] ) ? $roles[ $role ] : '';
							// Post Title Token
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':WPROLE';
							$trigger_meta['meta_value'] = maybe_serialize( $role_label );
							Automator()->insert_trigger_meta( $trigger_meta );

							Automator()->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}
}
