<?php

namespace Uncanny_Automator_Pro;

/**
 * Class GP_EARNSPOINTS
 *
 * @package Uncanny_Automator_Pro
 */
class GP_EARNSPOINTS {

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
		$this->trigger_code = 'GPEARNSPOINTS';
		$this->trigger_meta = 'GPPOINTS';
		$this->define_trigger();
	}

	/**
	 * Define trigger settings
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/gamipress/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'is_deprecated'       => true,
			/* translators: Logged-in trigger - GamiPress */
			'sentence'            => sprintf( __( 'A user earns {{a number:%1$s}} {{of a specific type of:%2$s}} points', 'uncanny-automator-pro' ), 'GPPOINTVALUE', $this->trigger_meta ),
			/* translators: Logged-in trigger - GamiPress */
			'select_option_name'  => __( 'A user earns {{a number}} {{of a specific type of}} points', 'uncanny-automator-pro' ),
			'action'              => 'gamipress_update_user_points',
			'priority'            => 20,
			'accepted_args'       => 8,
			'validation_function' => array( $this, 'earned_points' ),
			'options'             => array(),
			'options_group'       => array(
				$this->trigger_meta => array(
					$uncanny_automator->helpers->recipe->gamipress->options->list_gp_points_types(
						__( 'Point type', 'uncanny-automator' ),
						$this->trigger_meta,
						array(
							'token'   => false,
							'is_ajax' => false,
						)
					),
				),
				'GPPOINTVALUE'      => array(
					$uncanny_automator->helpers->recipe->field->integer_field( 'GPPOINTVALUE', __( 'Points', 'uncanny-automator' ), false, '0' ),
				),
			),
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
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
		global $uncanny_automator;
		$recipes = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );

		$entry      = array( $points_type => $new_points );
		$conditions = $this->match_condition( $entry, $recipes, $this->trigger_meta, $this->trigger_code, 'GPPOINTVALUE' );

		if ( ! $conditions ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( ! empty( $conditions ) ) {
			foreach ( $conditions['recipe_ids'] as $trigger_id => $recipe_id ) {
				if ( ! $uncanny_automator->is_recipe_completed( $recipe_id, $user_id ) ) {
					$args = array(
						'code'             => $this->trigger_code,
						'meta'             => $this->trigger_meta,
						'recipe_to_match'  => $recipe_id,
						'trigger_to_match' => $trigger_id,
						'ignore_post_id'   => true,
						'user_id'          => $user_id,
					);

					$uncanny_automator->maybe_add_trigger_entry( $args );
				}
			}
		}
	}

	/**
	 * Points matching function.
	 *
	 * @param array $entry .
	 * @param null $recipes .
	 * @param null $trigger_meta .
	 * @param null $trigger_code .
	 * @param null $trigger_second_code .
	 *
	 * @return array|bool
	 */
	public function match_condition( $entry, $recipes = null, $trigger_meta = null, $trigger_code = null, $trigger_second_code = null ) {
		if ( null === $recipes ) {
			return false;
		}

		$matches    = array();
		$recipe_ids = array();

		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				if ( key_exists( $trigger_meta, $trigger['meta'] ) ) {
					$matches[ $trigger['ID'] ]    = array(
						'field' => $trigger['meta'][ $trigger_meta ],
						'value' => $trigger['meta'][ $trigger_second_code ],
					);
					$recipe_ids[ $trigger['ID'] ] = $recipe['ID'];
				}
			}
		}

		if ( ! empty( $matches ) ) {
			foreach ( $matches as $trigger_id => $match ) {
				if ( $entry[ $match['field'] ] != $match['value'] ) {
					unset( $recipe_ids[ $trigger_id ] );
				}
			}
		}

		if ( ! empty( $recipe_ids ) ) {
			return array(
				'recipe_ids' => $recipe_ids,
				'result'     => true,
			);
		}

		return false;
	}
}
