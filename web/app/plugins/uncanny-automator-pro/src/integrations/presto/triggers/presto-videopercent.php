<?php

namespace Uncanny_Automator_Pro;

/**
 * Class PRESTO_VIDEOPERCENT
 * @package Uncanny_Automator_Pro
 */
class PRESTO_VIDEOPERCENT {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'PRESTO';

	private $trigger_code;
	private $trigger_meta;
	private $trigger_condition;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code      = 'PRESTOVIDEOPERCENT';
		$this->trigger_meta      = 'PRESTOVIDEO';
		$this->trigger_condition = 'VIDEOPERCENT';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/presto-player/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'is_pro'              => true,
			/* translators: Logged-in trigger - Presto Player */
			'sentence'            => sprintf( esc_attr__( 'A user watches at least {{a specific percentage:%1$s}} of {{a video:%2$s}}', 'uncanny-automator-pro' ), $this->trigger_condition, $this->trigger_meta ),
			/* translators: Logged-in trigger - Presto Player */
			'select_option_name'  => esc_attr__( 'A user watches at least {{a specific percentage}} of {{a video}}', 'uncanny-automator' ),
			'action'              => 'presto_player_progress',
			'priority'            => 20,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'video_progress' ),
			'options'             => array(

				$uncanny_automator->helpers->recipe->presto->options->list_presto_videos( null, $this->trigger_meta ),
				$uncanny_automator->helpers->recipe->field->select_field(
					$this->trigger_condition,
					__( 'Percentage', 'uncanny-automator-pro' ),
					array( //Added underscores here to prevent the dropdown displaying values as IDs
						'_10'  => _x( '10%', 'uncanny-automator-pro' ),
						'_20'  => _x( '20%', 'uncanny-automator-pro' ),
						'_30'  => _x( '30%', 'uncanny-automator-pro' ),
						'_40'  => _x( '40%', 'uncanny-automator-pro' ),
						'_50'  => _x( '50%', 'uncanny-automator-pro' ),
						'_60'  => _x( '60%', 'uncanny-automator-pro' ),
						'_70'  => _x( '70%', 'uncanny-automator-pro' ),
						'_80'  => _x( '80%', 'uncanny-automator-pro' ),
						'_90'  => _x( '90%', 'uncanny-automator-pro' ),
						'_100' => _x( '100%', 'uncanny-automator-pro' ),
					)
				),

			),
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * video_progress
	 *
	 * @param  string $video_id
	 * @param  string $percent
	 *
	 * @return void
	 */
	public function video_progress( $video_id, $percent ) {

		global $uncanny_automator;

		$user_id = get_current_user_id();

		$conditions = array(
			'code'                   => $this->trigger_code,
			$this->trigger_meta      => $video_id,
			$this->trigger_condition => '_' . $percent,
		);

		$recipe_args = array(
			'post_status'               => 'publish',
			'completed_by_current_user' => false,
		);

		$trigger_args = array(
			'post_status' => 'publish',
		);

		$triggers = $this->match_conditions( $conditions, $trigger_args, $recipe_args );

		if ( empty( $triggers ) ) {
			return;
		}

		foreach ( $triggers as $recipe_id => $trigger ) {

			if ( $uncanny_automator->is_recipe_completed( $recipe_id, $user_id ) ) {
				continue;
			}

			foreach ( $trigger as $trigger_id ) {

				$args = array(
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $user_id,
					'recipe_to_match'  => $recipe_id,
					'trigger_to_match' => $trigger_id,
					'post_id'          => $video_id,
				);

				$args = $uncanny_automator->maybe_add_trigger_entry( $args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {
							$uncanny_automator->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}

	}

	/**
	 * match_conditions
	 *
	 * @param  array $conditions
	 * @param  array $trigger_args
	 * @param  array $recipe_args
	 *
	 * @return array
	 */
	public function match_conditions( $conditions, $trigger_args = null, $recipe_args = null ) {

		global $uncanny_automator;

		$recipes = $uncanny_automator->get_recipes_data();

		$triggers = array();

		if ( empty( $recipes ) ) {
			return $triggers;
		}

		foreach ( $recipes as $recipe ) {

			if ( ! is_null( $recipe_args ) ) {
				foreach ( $recipe_args as $key => $value ) {

					if ( $recipe[ $key ] !== $value ) {
						continue 2;
					}
				}
			}

			foreach ( $recipe['triggers'] as $trigger ) {

				if ( ! is_null( $trigger_args ) ) {
					foreach ( $trigger_args as $key => $value ) {

						if ( $trigger[ $key ] !== $value ) {
							continue 2;
						}
					}
				}

				foreach ( $conditions as $trigger_meta => $value ) {

					if ( $trigger['meta'][ $trigger_meta ] == '-1' ) {
						continue;
					}

					if ( $trigger['meta'][ $trigger_meta ] != $value ) {
						continue 2;
					}
				}

				$triggers[ $recipe['ID'] ][] = $trigger['ID'];

			}
		}

		return $triggers;
	}
}
