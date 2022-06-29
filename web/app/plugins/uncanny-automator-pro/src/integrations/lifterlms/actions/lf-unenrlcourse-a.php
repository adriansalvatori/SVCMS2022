<?php

namespace Uncanny_Automator_Pro;

/**
 * Class LF_UNENRLCOURSE_A
 * @package Uncanny_Automator_Pro
 */
class LF_UNENRLCOURSE_A {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'LF';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'LFUNENRLCOURSE-A';
		$this->action_meta = 'LFUNRLCOURSE';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/lifterlms/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - LifterLMS */
			'sentence'           => sprintf( __( 'Remove the user from {{a course:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - LifterLMS */
			'select_option_name' => __( 'Remove the user from {{a course}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'lf_unenroll_in_course' ),
			'options'            => [
				$uncanny_automator->helpers->recipe->lifterlms->options->all_lf_courses( __( 'Course', 'uncanny-automator' ), 'LFUNRLCOURSE', false ),
			],
		);

		$uncanny_automator->register->action( $action );
	}


	/**
	 * Validation function when the action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function lf_unenroll_in_course( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		if ( ! function_exists( 'llms_unenroll_student' ) ) {
			$error_message = 'The function llms_unenroll_student does not exist';
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$course_id = $action_data['meta'][ $this->action_meta ];

		//Enroll to New Course
		llms_unenroll_student( $user_id, $course_id );

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
