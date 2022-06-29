<?php

namespace Uncanny_Automator_Pro;

/**
 * Class MYCRED_CURRENTBALANCE
 *
 * @package Uncanny_Automator_Pro
 */
class MYCRED_CURRENTBALANCE {
	/**
	 * integration code
	 *
	 * @var string
	 */
	public static $integration = 'MYCRED';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'MYCREDBALANCE';
		$this->trigger_meta = 'MYCREDPOINTTYPE';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/mycred/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - myCred */
			'sentence'            => sprintf( esc_attr__( "A user's current balance reaches {{a number of:%1\$s}} {{a specific type of:%2\$s}} points", 'uncanny-automator-pro' ), 'CURRENTBALANCE', $this->trigger_meta ),
			/* translators: Logged-in trigger - myCred */
			'select_option_name'  => esc_attr__( "A user's current balance reaches {{a number of}} {{a specific type of}} points", 'uncanny-automator-pro' ),
			'action'              => 'mycred_update_user_balance',
			'priority'            => 10,
			'accepted_args'       => 4,
			'validation_function' => array( $this, 'mycred_current_balance' ),
			'options'             => array(
				Automator()->helpers->recipe->field->float(
					array(
						'option_code' => 'CURRENTBALANCE',
						'label'       => esc_attr__( 'Balance amount', 'uncanny-automator-pro' ),
						'placeholder' => esc_attr__( 'Example: 1.1', 'uncanny-automator' ),
						'default'     => null,
					)
				),
				Automator()->helpers->recipe->mycred->options->list_mycred_points_types( esc_attr__( 'Points type', 'uncanny-automator-pro' ), $this->trigger_meta ),
			),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * @param $user_id
	 * @param $current_balance
	 * @param $amount
	 * @param $point_type
	 */
	public function mycred_current_balance( $user_id, $current_balance, $amount, $point_type ) {
		if ( 0 === $user_id ) {
			// Its a logged in recipe and
			// user ID is 0. Skip process
			return;
		}

		$recipes             = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_point_type = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$required_balance    = Automator()->get->meta_from_recipes( $recipes, 'CURRENTBALANCE' );
		$matched_recipe_ids  = array();

		//Add where Point Type & Current Balances Matches
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( isset( $required_point_type[ $recipe_id ] ) && isset( $required_point_type[ $recipe_id ][ $trigger_id ] ) && isset( $required_point_type[ $recipe_id ] ) && isset( $required_point_type[ $recipe_id ][ $trigger_id ] ) ) {
					$cur_balance = mycred_get_users_balance( $user_id, $required_point_type[ $recipe_id ][ $trigger_id ] );
					if ( $required_point_type[ $recipe_id ][ $trigger_id ] == $point_type && $required_balance[ $recipe_id ][ $trigger_id ] == $cur_balance ) {
						$matched_recipe_ids[] = array(
							'recipe_id'  => $recipe_id,
							'trigger_id' => $trigger_id,
						);
					}
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

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta;
							$trigger_meta['meta_value'] = maybe_serialize( $required_point_type[ $recipe_id ][ $trigger_id ] );
							Automator()->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . 'CURRENTBALANCE';
							$trigger_meta['meta_value'] = maybe_serialize( $required_balance[ $recipe_id ][ $trigger_id ] );
							Automator()->insert_trigger_meta( $trigger_meta );

							Automator()->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}
}
