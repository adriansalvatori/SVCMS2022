<?php

namespace Uncanny_Automator_Pro;

/**
 * Class GP_TOTALPOINTS
 *
 * @package Uncanny_Automator_Pro
 */
class GP_TOTALPOINTS {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'GP';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'GPTOTALPOINTS';
		$this->trigger_meta = 'GPPOINTS';
		$this->define_trigger();
	}

	/**
	 * Define trigger settings
	 */
	public function define_trigger() {
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/gamipress/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - GamiPress */
			'sentence'            => sprintf( esc_attr__( "A user's total {{of a specific type of:%2\$s}} points reaches {{a specific threshold:%1\$s}}", 'uncanny-automator-pro' ), 'GPPOINTVALUE', $this->trigger_meta ),
			/* translators: Logged-in trigger - GamiPress */
			'select_option_name'  => esc_attr__( "A user's total points reaches {{a specific threshold}}", 'uncanny-automator-pro' ),
			'action'              => 'gamipress_update_user_points',
			'priority'            => 20,
			'accepted_args'       => 8,
			'validation_function' => array( $this, 'earned_points' ),
			'options'             => array(),
			'options_group'       => array(
				$this->trigger_meta => array(
					Automator()->helpers->recipe->gamipress->options->list_gp_points_types( esc_attr__( 'Point type', 'uncanny-automator' ), $this->trigger_meta ),
				),
				'GPPOINTVALUE'      => array(
					Automator()->helpers->recipe->field->int(
						array(
							'option_code' => 'GPPOINTVALUE',
							'label'       => esc_attr__( 'Point threshold', 'uncanny-automator-pro' ),
							'description' => '',
							'placeholder' => esc_attr__( 'Example: 15', 'uncanny-automator-pro' ),
							'required'    => true,
							'input_type'  => 'int',
							'default'     => '',
						)
					),
				),
			),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * Trigger handler function.
	 *
	 * @param string $user_id .
	 * @param string $new_points .
	 * @param string $total_points .
	 * @param string $admin_id .
	 * @param string $achievement_id .
	 * @param string $points_type .
	 * @param string $reason .
	 * @param string $log_type .
	 */
	public function earned_points( $user_id, $new_points, $total_points, $admin_id, $achievement_id, $points_type, $reason, $log_type ) {
		$recipes                   = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_type             = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$required_threshold_points = Automator()->get->meta_from_recipes( $recipes, 'GPPOINTVALUE' );
		$matched_recipe_ids        = array();

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];//return early for all products
				if ( isset( $required_type[ $recipe_id ] ) && isset( $required_type[ $recipe_id ][ $trigger_id ] ) ) {
					if ( (string) $required_type[ $recipe_id ][ $trigger_id ] === (string) $points_type ) {
						$total_points_by_type = gamipress_get_user_points( $user_id, $points_type );
						$before_update_points = ( $total_points_by_type - $new_points );
						if ( $before_update_points < $required_threshold_points[ $recipe_id ][ $trigger_id ] ) {
							$matched_recipe_ids[] = array(
								'recipe_id'             => $recipe_id,
								'trigger_id'            => $trigger_id,
								'previous_total_points' => $before_update_points,
								'new_total_points'      => $total_points_by_type,
								'threshold_points'      => $required_threshold_points[ $recipe_id ][ $trigger_id ],
							);
						}
					}
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				if ( ! Automator()->is_recipe_completed( $matched_recipe_id['recipe_id'], $user_id ) ) {
					$pass_args = array(
						'code'             => $this->trigger_code,
						'meta'             => $this->trigger_meta,
						'recipe_to_match'  => $matched_recipe_id['recipe_id'],
						'trigger_to_match' => $matched_recipe_id['trigger_id'],
						'ignore_post_id'   => true,
						'user_id'          => $user_id,
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

								$trigger_meta['meta_key']   = $this->trigger_meta;
								$trigger_meta['meta_value'] = maybe_serialize( gamipress_get_points_type_singular( $points_type ) );
								Automator()->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = 'GPPOINTVALUE';
								$trigger_meta['meta_value'] = maybe_serialize( $matched_recipe_id['threshold_points'] );
								Automator()->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = 'GPPERVIOUSPOINTS';
								$trigger_meta['meta_value'] = maybe_serialize( $matched_recipe_id['previous_total_points'] );
								Automator()->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = 'GPNEWPOINTS';
								$trigger_meta['meta_value'] = maybe_serialize( $matched_recipe_id['new_total_points'] );
								Automator()->insert_trigger_meta( $trigger_meta );

								Automator()->maybe_trigger_complete( $result['args'] );
							}
						}
					}
				}
			}
		}
	}

}
