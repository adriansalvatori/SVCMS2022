<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_USERROLECHANGEDFRMTO
 * @package Uncanny_Automator_Pro
 */
class WP_USERROLECHANGEDFRMTO {

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
	 * @var string
	 */
	private $trigger_meta_new;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code     = 'USERROLECHANGEDFRMTO';
		$this->trigger_meta     = 'WPROLEOLD';
		$this->trigger_meta_new = 'WPROLENEW';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/wordpress-core/' ),
			'integration'         => self::$integration,
			'is_pro'              => true,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			/* translators: Logged-in trigger - WordPress Core */
			'sentence'            => sprintf( __( "A user's role changed from {{a specific role:%1\$s}} to {{a specific role:%2\$s}}", 'uncanny-automator-pro' ), $this->trigger_meta, $this->trigger_meta_new ),
			/* translators: Logged-in trigger - WordPress Core */
			'select_option_name'  => __( "A user's role changed from {{a specific role}} to {{a specific role}}", 'uncanny-automator-pro' ),
			'action'              => 'set_user_role',
			'priority'            => 100,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'set_user_role' ),
			'options_group'       => array(),
			'options_callback'    => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->trigger( $trigger );
	}
	
	/**
	 * load_options
	 *
	 * @return array
	 */
	public function load_options() {

		return Automator()->utilities->keep_order_of_options(
			array(
				'options' => array(
					Automator()->helpers->recipe->wp->options->wp_user_roles( __( 'Old user role', 'uncanny-automator-pro' ), $this->trigger_meta ),
					Automator()->helpers->recipe->wp->options->wp_user_roles( __( 'New user role', 'uncanny-automator-pro' ), $this->trigger_meta_new ),
				),
			)
		);
	}

	/**
	 * @param $user_id
	 * @param $role
	 * @param $old_roles
	 */
	public function set_user_role( $user_id, $role, $old_roles ) {
		global $uncanny_automator;

		$recipes           = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_old_role = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$required_new_role = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta_new );

		if ( ! $recipes ) {
			return;
		}

		if ( ! $required_old_role ) {
			return;
		}

		if ( ! $required_new_role ) {
			return;
		}

		$matched_roles = array();

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				//Add where option is set to Any post type
				if ( empty( $old_roles ) || in_array( $required_old_role[ $recipe_id ][ $trigger_id ], $old_roles, false ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.FoundNonStrictFalse
					$matched_roles[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
						'role'       => $required_old_role[ $recipe_id ][ $trigger_id ],
					);
				}
			}
		}

		if ( empty( $matched_roles ) ) {
			return;
		}

		$matched_recipe_ids = array();

		$user_obj = get_user_by( 'ID', (int) $user_id );

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				//Add where option is set to Any post type
				if ( intval( '-1' ) === intval( $required_new_role[ $recipe_id ][ $trigger_id ] ) ) {
					$matched_recipe_ids[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					);
				}

				if ( (string) $role === (string) $required_new_role[ $recipe_id ][ $trigger_id ] ) {
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

				$results = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );
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
							//Existing role
							foreach ( $matched_roles as $o_role ) {
								$role_label                 = isset( $roles[ $o_role['role'] ] ) ? $roles[ $o_role['role'] ] : '';
								$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta;
								$trigger_meta['meta_value'] = maybe_serialize( $role_label );
							}
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							//New Role
							$role_label                 = isset( $roles[ $role ] ) ? $roles[ $role ] : '';
							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta_new;
							$trigger_meta['meta_value'] = maybe_serialize( $role_label );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$uncanny_automator->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}

}
