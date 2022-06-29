<?php
/**
 * Contains Course Completion action
 *
 * @since 2.3.0
 * @version 2.3.0
 *
 * @package Uncanny_Automator_Pro
 */

namespace Uncanny_Automator_Pro;

/**
 * Course Completion action
 *
 * @since 2.3.0
 */
class TUTORLMS_COURSECOMPLETE {

	/**
	 * Integration code
	 *
	 * @var string
	 *
	 * @since 2.3.0
	 */
	public static $integration = 'TUTORLMS';

	/**
	 * Action Code
	 *
	 * @var string
	 *
	 * @since 2.3.0
	 */
	private $action_code;

	/**
	 * Action Meta Code
	 *
	 * @var string
	 *
	 * @since 2.3.0
	 */
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'TUTORLMSCOURSECOMPLETE';
		$this->action_meta = 'TUTORLMSCOURSE';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/tutor-lms/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - TutorLMS */
			'sentence'           => sprintf( __( 'Mark {{a course:%1$s}} complete for the user', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - TutorLMS */
			'select_option_name' => __( 'Mark {{a course}} complete for the user', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'complete' ),
			'options'            => [
				$uncanny_automator->helpers->recipe->tutorlms->options->all_tutorlms_courses( __( 'Course', 'uncanny-automator-pro' ), $this->action_meta, false ),
			],
		);

		$uncanny_automator->register->action( $action );
	}


	/**
	 * Validation function when the action is hit.
	 *
	 * @param string $user_id user id.
	 * @param array $action_data action data.
	 * @param string $recipe_id recipe id.
	 */
	public function complete( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$course_id = $action_data['meta'][ $this->action_meta ];

		// only complete if a course isn't already complete.
		if ( ! tutils()->is_completed_course( $course_id, $user_id ) ) {

			// get the course completion mode.
			$completion_mode = tutils()->get_option( 'course_completion_process' );

			// if in strict completion mode, complete lessons before we can complete the course.
			if ( 'strict' === $completion_mode ) {

				// get all the lessons.
				$lesson_query = tutils()->get_lesson( $course_id, - 1 );

				// only if there are lessons, we complete them
				if ( count( $lesson_query ) ) {

					foreach ( $lesson_query as $lesson ) {

						// otherwise, complete the lesson.
						tutils()->mark_lesson_complete( $lesson->ID, $user_id );
					}

				}

				// can't generate a fake quiz attempt to pass quizzes automatically, so they'll be ignored

			}

			// all lessons have been completed, go ahead and complete the course
			$uncanny_automator->helpers->recipe->tutorlms->pro->complete_course( $course_id, $user_id );

		}

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}

}
