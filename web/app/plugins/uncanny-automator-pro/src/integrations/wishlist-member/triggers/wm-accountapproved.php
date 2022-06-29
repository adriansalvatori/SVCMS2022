<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WM_ACCOUNTAPPROVED
 *
 * @package Uncanny_Automator_Pro
 */
class WM_ACCOUNTAPPROVED {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WISHLISTMEMBER';

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
		$this->trigger_code = 'WLMUSERAPPROVED';
		$this->trigger_meta = 'WMMEMBERSHIPLEVELS';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/wishlist-member/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - Wishlist Member */
			'sentence'            => sprintf( esc_attr__( "A user's {{specific:%1\$s}} membership level account is approved", 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - Wishlist Member */
			'select_option_name'  => esc_attr__( "A user's {{specific}} membership level account is approved", 'uncanny-automator-pro' ),
			'action'              => 'wishlistmember_approve_user_levels',
			'priority'            => 99,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'wm_user_approved' ),
			'options'             => array(
				Automator()->helpers->recipe->wishlist_member->options->wm_get_all_membership_levels(
					null,
					$this->trigger_meta,
					array( 'any' => true )
				),
			),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * @param $id
	 * @param $levels
	 * @param $method
	 *
	 * @return void
	 */
	public function wm_user_approved( $user_id, $levels, $method ) {
		if ( ! $user_id ) {
			return;
		}

		if ( empty( $levels ) ) {
			return;
		}

		$recipes            = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_level     = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = array();

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( intval( '-1' ) === intval( $required_level[ $recipe_id ][ $trigger_id ] ) || in_array( $required_level[ $recipe_id ][ $trigger_id ], $levels, true ) ) {
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
					'user_id'          => $user_id,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
					'is_signed_in'     => true,
				);

				$args = Automator()->maybe_add_trigger_entry( $pass_args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {
							$trigger_meta = array(
								'user_id'        => $user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							);

							foreach ( $levels as $level ) {
								$level_details              = wlmapi_get_level( $level );
								$trigger_meta['meta_key']   = $this->trigger_meta;
								$trigger_meta['meta_value'] = maybe_serialize( $level_details['level']['name'] );
								Automator()->insert_trigger_meta( $trigger_meta );
							}

							Automator()->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}

}
