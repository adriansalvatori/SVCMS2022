<?php

namespace Uncanny_Automator_Pro;

use LLMS_Student_Quizzes;

/**
 * Class LF_RESETQUIZATTEMPTS_A
 *
 * @package Uncanny_Automator_Pro
 */
class LF_RESETQUIZATTEMPTS_A {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'LF';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'LFRESETQUIZATTEMPTS-A';
		$this->action_meta = 'LFRESETQUIZ';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = [
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/lifterlms/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - LifterLMS */
			'sentence'           => sprintf( __( "Reset the user's attempts for {{a quiz:%1\$s}}", 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - LifterLMS */
			'select_option_name' => __( "Reset the user's attempts for {{a quiz}}", 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'lf_reset_quiz_attempts' ],
			'options'            => [
				$uncanny_automator->helpers->recipe->lifterlms->options->all_lf_quizs( __( 'Select a Quiz', 'uncanny-automator' ), $this->action_meta, false ),
			],
		];

		$uncanny_automator->register->action( $action );
	}


	/**
	 * Validation function when the action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function lf_reset_quiz_attempts( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		if ( empty( $user_id ) ) {
			return false;
		}

		$student_attempts = new LLMS_Student_Quizzes( $user_id );
		$quiz_id          = $action_data['meta'][ $this->action_meta ];
		$attempts         = $student_attempts->get_attempts_by_quiz( $quiz_id );

		if ( empty( $attempts ) ) {
			return false;
		}

		if ( ! empty( $attempts ) ) {
			foreach ( $attempts as $attempt ) {
				$attempt->delete();
			}
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
		}
	}
}
