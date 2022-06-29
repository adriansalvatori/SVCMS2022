<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BO_EARNSACHIEVEMENT
 * @package Uncanny_Automator_Pro
 */
class BO_EARNSACHIEVEMENT {
	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'BO';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'BOEARNSACHIEVEMENT';
		$this->trigger_meta = 'BOACHIEVEMENT';
		$this->define_trigger();
	}

	/**
	 * Define trigger settings
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/badgeos/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - BadgeOS */
			'sentence'            => sprintf( __( 'A user earns {{an achievement:%1$s}}', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - BadgeOS */
			'select_option_name'  => __( 'A user earns {{an achievement}}', 'uncanny-automator-pro' ),
			'action'              => 'badgeos_award_achievement',
			'priority'            => 20,
			'accepted_args'       => 5,
			'validation_function' => array( $this, 'earned_bo_achievement' ),
			'options'             => [],
			'options_group'       => [
				$this->trigger_meta => [
					$uncanny_automator->helpers->recipe->badgeos->options->list_bo_award_types(
						__( 'Achievement type', 'uncanny-automator-pro' ),
						'BOAWARDTYPES',
						[
							'token'        => false,
							'is_ajax'      => true,
							'is_any'       => true,
							'target_field' => $this->trigger_meta,
							'endpoint'     => 'select_achievements_from_types_BOAWARDACHIEVEMENT',
						]
					),
					$uncanny_automator->helpers->recipe->field->select_field( $this->trigger_meta, __( 'Award', 'uncanny-automator-pro' ) ),
				]
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * @param $user_id
	 * @param $achievement_id
	 * @param $this_trigger
	 * @param $site_id
	 * @param $args
	 */
	public function earned_bo_achievement( $user_id, $achievement_id, $this_trigger, $site_id, $args ) {
		global $uncanny_automator;
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( empty ( $user_id ) ) {
			return;
		}

		$pass_args = [
			'code'    => $this->trigger_code,
			'meta'    => $this->trigger_meta,
			'post_id' => absint( $achievement_id ),
			'user_id' => $user_id,
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

					$trigger_meta['meta_key']   = 'BOAWARDTYPES';
					$trigger_meta['meta_value'] = maybe_serialize( ucfirst( get_post_type( $achievement_id ) ) );
					$uncanny_automator->insert_trigger_meta( $trigger_meta );

					$uncanny_automator->maybe_trigger_complete( $result['args'] );
				}
			}
		}
	}
}