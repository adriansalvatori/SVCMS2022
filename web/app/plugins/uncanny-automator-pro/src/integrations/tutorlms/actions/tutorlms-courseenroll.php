<?php
/**
 * Contains Course enrollment action
 *
 * @since 2.3.0
 * @version 2.3.0
 *
 * @package Uncanny_Automator_Pro
 */

namespace Uncanny_Automator_Pro;

use function tutor_utils;

/**
 * Course Enrollment Action
 *
 * @since 2.3.0
 */
class TUTORLMS_COURSEENROLL {

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
		$this->action_code = 'TUTORLMSCOURSEENROLL';
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
			'sentence'           => sprintf( __( 'Enroll the user in {{a course:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - TutorLMS */
			'select_option_name' => __( 'Enroll the user in {{a course}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'enroll' ),
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
	public function enroll( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;


		if ( ! method_exists( '\TUTOR\Utils', 'do_enroll' ) ) {
			$error_message = 'The enrollment method does not exist';
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

			// filter purchaseability to always return false when enrolling through this action.
			// See: https://github.com/themeum/tutor/blob/master/classes/Course.php#L523
			add_filter( 'is_course_purchasable', '__return_false', 10 );

			// Enroll into Course. Tutor introduced an order Id in the middle, which is what this false is
			tutor_utils()->do_enroll( $course_id, false, $user_id );

			// remove the filter so standard enrollments can still continue
			remove_filter( 'is_course_purchasable', '__return_false', 10 );
		}


		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}

}
