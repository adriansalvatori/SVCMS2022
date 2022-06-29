<?php

namespace Uncanny_Automator_Pro;

/**
 * Class TUTORLMS_COURSEUNENROLL
 * @package Uncanny_Automator_Pro
 */
class TUTORLMS_COURSEUNENROLL {

	/**
	 * Integration code
	 * @var string
	 * @since 2.3.0
	 */
	public static $integration = 'TUTORLMS';

	/**
	 * Action code
	 *
	 * @var string
	 * @since 2.3.0
	 */
	private $action_code;

	/**
	 * Meta action code
	 *
	 * @var string
	 * @since 2.3.0
	 */
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 * @since 2.3.0
	 */
	public function __construct() {
		$this->action_code = 'TUTORLMSCOURSEUNENROLL';
		$this->action_meta = 'TUTORLMSCOURSE';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 *
	 * @since 2.3.0
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
			'sentence'           => sprintf( __( 'Unenroll a user from {{a course:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - TutorLMS */
			'select_option_name' => __( 'Unenroll a user from {{a course}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'unenroll' ),
			'options'            => [
				$uncanny_automator->helpers->recipe->tutorlms->options->all_tutorlms_courses( __( 'Course', 'uncanny-automator' ), $this->action_meta, true, true ),
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
	 *
	 * @since 2.3.0
	 */
	public function unenroll( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;


		if ( ! method_exists( '\TUTOR\Utils', 'cancel_course_enrol' ) ) {
			$error_message = 'The enrollment cancellation method does not exist';
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$course_id = isset( $action_data['meta'][ $this->action_meta ] ) ? $action_data['meta'][ $this->action_meta ] : '-1';

		if ( intval( '-1' ) === intval( $course_id ) ) {
			$courses_args = [
				'post_type'      => tutor()->course_post_type,
				'posts_per_page' => 999,
				'post_status'    => 'publish',
			];

			$courses_query = get_posts( $courses_args );
			if ( $courses_query ) {
				foreach ( $courses_query as $cq ) {
					$course_ids[] = $cq->ID;
				}
			}
		} else {
			$course_ids = array( $course_id );
		}

		if ( empty( $course_ids ) ) {
			return;
		}

		foreach ( $course_ids as $course_id ) {
			tutor_utils()->cancel_course_enrol( $course_id, $user_id );
		}


		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}

}
