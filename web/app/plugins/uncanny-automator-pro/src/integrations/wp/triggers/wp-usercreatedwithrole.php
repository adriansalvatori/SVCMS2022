<?php

namespace Uncanny_Automator_Pro;

/**
 *
 */
class WP_USERCREATEDWITHROLE {

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
		$this->trigger_code = 'WPUSERCREATEDWITHROLE';
		$this->trigger_meta = 'USERCREATEDWITHROLE';
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
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			/* translators: Logged-in trigger - WordPress Core */
			'sentence'            => sprintf( __( 'A user is created with {{a specific:%1$s}} role', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - WordPress Core */
			'select_option_name'  => __( 'A user is created with {{a specific}} role', 'uncanny-automator-pro' ),
			'action'              => 'user_register',
			'priority'            => 10,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'a_user_is_created' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @return array[]
	 */
	public function load_options() {

		return Automator()->utilities->keep_order_of_options(
			array(
				'options' => array(
					Automator()->helpers->recipe->wp->options->wp_user_roles( null, $this->trigger_meta ),
				),
			)
		);
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 */
	public function a_user_is_created( $user_id ) {

		$user = get_user_by( 'ID', $user_id );

		if ( ! $user instanceof \WP_User ) {
			return;
		}
		global $uncanny_automator;

		$recipes       = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_role = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		if ( ! $recipes ) {
			return;
		}

		if ( ! $required_role ) {
			return;
		}

		$matched_recipe_ids = array();

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				//Add where option is set to Any post type
				if ( ( intval( '-1' ) === intval( $required_role[ $recipe_id ][ $trigger_id ] ) ) || ( user_can( $user, $required_role[ $recipe_id ][ $trigger_id ] ) ) || ( in_array( $required_role, $user->roles, true ) ) ) {
					$matched_recipe_ids[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
						'role'       => $required_role[ $recipe_id ][ $trigger_id ],
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
			$args      = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );
			if ( $args ) {
				foreach ( $args as $result ) {
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

						$r_l = array();

						if ( intval( '-1' ) === intval( $matched_recipe_id['role'] ) ) {
							$user_roles = $user->roles;
							foreach ( $user_roles as $r ) {
								$r_l[] = $roles[ $r ];
							}
						} else {
							$r_l = $roles[ $matched_recipe_id['role'] ];
						}

						$role_label = join( ' | ', $r_l );
						// Post Title Token
						$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta;
						$trigger_meta['meta_value'] = maybe_serialize( $role_label );
						Automator()->insert_trigger_meta( $trigger_meta );

						$uncanny_automator->maybe_trigger_complete( $result['args'] );
					}
				}
			}
		}
	}
}
