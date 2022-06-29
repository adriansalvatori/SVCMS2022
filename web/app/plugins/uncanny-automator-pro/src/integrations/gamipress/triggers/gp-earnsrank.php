<?php

namespace Uncanny_Automator_Pro;

/**
 * Class GP_EARNSRANK
 * @package Uncanny_Automator_Pro
 */
class GP_EARNSRANK {

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
		$this->trigger_code = 'GPEARNSRANK';
		$this->trigger_meta = 'GPRANK';
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
			/* translators: Logged-in trigger - GamiPress */
			'sentence'            => sprintf( __( 'A user attains {{a rank:%1$s}}', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - GamiPress */
			'select_option_name'  => __( 'A user attains {{a rank}}', 'uncanny-automator-pro' ),
			'action'              => 'gamipress_update_user_rank',
			'priority'            => 20,
			'accepted_args'       => 5,
			'validation_function' => array( $this, 'earned_rank' ),
			'options'             => [],
			'options_group'       => [
				$this->trigger_meta => [
					$uncanny_automator->helpers->recipe->gamipress->options->list_gp_rank_types(
						__( 'Rank type', 'uncanny-automator' ),
						'GPRANKTYPES',
						[
							'token'        => false,
							'is_ajax'      => true,
							'target_field' => $this->trigger_meta,
							'endpoint'     => 'select_ranks_from_types_EARNSRANK',
						]
					),
					$uncanny_automator->helpers->recipe->field->select_field( $this->trigger_meta, __( 'Rank', 'uncanny-automator' ) ),
				],
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * Trigger handler function.
	 *
	 * @param sting $user_id .
	 * @param object $new_rank .
	 * @param object $old_rank .
	 * @param string $admin_id .
	 * @param string $achievement_id .
	 */
	public function earned_rank( $user_id, $new_rank, $old_rank, $admin_id, $achievement_id ) {
		global $uncanny_automator;
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( empty( $user_id ) ) {
			return;
		}

		$args = [
			'code'    => $this->trigger_code,
			'meta'    => $this->trigger_meta,
			'post_id' => absint( $new_rank->ID ),
			'user_id' => $user_id,
		];

		$uncanny_automator->maybe_add_trigger_entry( $args );
	}

}
