<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Recipe;

class Gp_Achievements_Revoked {

	use Recipe\Triggers;

	public function __construct() {

		add_filter( 'automator_auto_complete_trigger', '__return_false' );

		$this->setup_trigger();

	}

	/**
	 * Setup and registers Trigger.
	 *
	 * @return void.
	 */
	protected function setup_trigger() {

		$this->set_integration( 'GP' );

		$this->set_is_pro( true );

		$this->set_trigger_code( 'GP_ACHIEVEMENT_REVOKED_CODE' );

		$this->set_trigger_meta( 'GP_ACHIEVEMENT_REVOKED_META' );

		$this->add_action( 'gamipress_revoke_achievement_to_user', 99, 3 );

		$this->set_sentence(
			sprintf(
			/* Translators: %1$s is the achievement type */
				esc_attr__( '{{An achievement:%1$s}} is revoked from a user', 'uncanny-automator' ),
				$this->trigger_meta
			)
		);

		/* Translators: Achievement revoked sentence.*/
		$this->set_readable_sentence(
			esc_attr__(
				'{{An achievement}} is revoked from a user',
				'uncanny-automator'
			)
		);

		$this->set_options_group(
			array(
				$this->trigger_meta => $this->get_dropdown_options(),
			)
		);

		$this->register_trigger();

	}

	/**
	 * Validates the trigger.
	 *
	 * @return boolean True on success. Otherwise, false.
	 */
	public function validate_trigger( ...$action_args ) {

		$achievement_user_id = $action_args[0];

		$achievement_id = $action_args[1];

		if ( empty( $achievement_user_id ) || empty( $achievement_id ) ) {

			return false;

		}

		return true;

	}

	/**
	 * Run the trigger.
	 */
	protected function prepare_to_run( $action_args ) {

		$achievement_user_id = $action_args[0];

		$achievement_id = $action_args[1];
		$this->set_post_id( $achievement_id );
		$this->set_user_id( $achievement_user_id );

	}

	/**
	 * Get the dropdown options for award.
	 */
	private function get_dropdown_options() {

		return array(
			$this->get_options_achievement_type(),
			Automator()->helpers->recipe->field->select_field(
				$this->trigger_meta,
				__( 'Award', 'uncanny-automator' )
			),
		);
	}

	/**
	 * Get the dropdown options for achievement types.
	 */
	private function get_options_achievement_type() {

		return Automator()->helpers->recipe->gamipress->options->list_gp_award_types(
			__( 'Achievement type', 'uncanny-automator-pro' ),
			'GPAWARDTYPES',
			array(
				'token'        => false,
				'is_ajax'      => true,
				'target_field' => $this->trigger_meta,
				'endpoint'     => 'select_achievements_from_types_EARNSACHIEVEMENT',
			)
		);

	}

}
