<?php
/**
 * Contains Reset Course Progress action
 *
 * @since 2.8.0
 * @version 2.8.0
 *
 * @package Uncanny_Automator_Pro
 */

namespace Uncanny_Automator_Pro;

/**
 * Reset Course Progress action
 *
 * @since 2.8.0
 */
class TUTORLMS_RESETCOURSEPROGRESS {

	/**
	 * Integration code
	 *
	 * @var string
	 *
	 * @since 2.8.0
	 */
	public static $integration = 'TUTORLMS';

	/**
	 * Action Code
	 *
	 * @var string
	 *
	 * @since 2.8.0
	 */
	private $action_code;

	/**
	 * Action Meta Code
	 *
	 * @var string
	 *
	 * @since 2.8.0
	 */
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'TUTORLMSRESETCOURSEPROGRESS';
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
			'sentence'           => sprintf( __( "Reset the user's progress in {{a course:%1\$s}}", 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - TutorLMS */
			'select_option_name' => __( "Reset the user's progress in {{a course}}", 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'reset_course_progress' ),
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
	public function reset_course_progress( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$course_id = $action_data['meta'][ $this->action_meta ];

		global $wpdb;
		// Get all lessons of a course.
		$completed_lesson_ids = $wpdb->get_col( "select post_id from {$wpdb->postmeta} where meta_key = '_tutor_course_id_for_lesson' AND meta_value = {$course_id} " );

		// Delete all lesson completion
		if ( is_array( $completed_lesson_ids ) && count( $completed_lesson_ids ) ) {
			$completed_lesson_meta_ids = [];
			foreach ( $completed_lesson_ids as $lesson_id ) {
				$completed_lesson_meta_ids[] = '_tutor_completed_lesson_id_' . $lesson_id;
			}
			$in_ids = implode( "','", $completed_lesson_meta_ids );

			$wpdb->query( "DELETE from {$wpdb->usermeta} WHERE user_id = '{$user_id}' AND meta_key in('{$in_ids}') " );
		}

		// Delete all quiz and assignment attempts
		$course_contents = tutils()->get_course_contents_by_id( $course_id );
		if ( tutils()->count( $course_contents ) ) {
			foreach ( $course_contents as $content ) {
				if ( 'tutor_quiz' === $content->post_type ) {
					$quiz_id = $content->ID;
					$wpdb->query( "DELETE FROM {$wpdb->tutor_quiz_attempts} WHERE quiz_id = {$quiz_id} AND user_id = {$user_id} ; " );
				} elseif ( 'tutor_assignments' === $content->post_type ) {
					$assignment_id = $content->ID;
					$wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_type = 'tutor_assignment' AND user_id = {$user_id} AND comment_post_ID = {$assignment_id} " );
				}
			}
		}

		// Delete course completion flag.
		$wpdb->query( " DELETE from {$wpdb->comments} WHERE comment_agent = 'TutorLMSPlugin' AND comment_type = 'course_completed' AND comment_post_ID = {$course_id} AND user_id = {$user_id} ;" );
		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
