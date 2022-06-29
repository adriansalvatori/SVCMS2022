<?php

namespace Uncanny_Automator_Pro;

/**
 * Class LD_MARKLESSONNOTCOMPLETE
 * @package Uncanny_Automator_Pro
 */
class LD_MARKLESSONNOTCOMPLETE {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'LD';

	private $action_code;
	private $action_meta;
	private $quiz_list;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'MARKLESSONNOTCOMPLETE';
		$this->action_meta = 'LDLESSON';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$args = [
			'post_type'      => 'sfwd-courses',
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		];

		$options = $uncanny_automator->helpers->recipe->options->wp_query( $args, false, __( 'Any course', 'uncanny-automator' ) );

		$action = array(
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/learndash/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - LearnDash */
			'sentence'           => sprintf( __( 'Mark {{a lesson:%1$s}} not complete for the user', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - LearnDash */
			'select_option_name' => __( 'Mark {{a lesson}} not complete for the user', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'mark_not_complete_a_lesson' ),
			'options_group'      => [
				$this->action_meta => [
					$uncanny_automator->helpers->recipe->field->select_field_ajax(
						'LDCOURSE',
						__( 'Course', 'uncanny-automator' ),
						$options,
						'',
						'',
						false,
						true,
						[
							'target_field' => $this->action_meta,
							'endpoint'     => 'select_lesson_from_course_MARKLESSONNOTCOMPLETE',
						]
					),
					$uncanny_automator->helpers->recipe->field->select_field( $this->action_meta, __( 'Select a Lesson', 'uncanny-automator' ) ),
				],
			],
		);

		$uncanny_automator->register->action( $action );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function mark_not_complete_a_lesson( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$lesson_id = $action_data['meta'][ $this->action_meta ];
		//Mark complete a lesson
		$course_id = learndash_get_course_id( $lesson_id );

		$this->mark_steps_incomplete( $user_id, $lesson_id );

		//Mark complete a lesson
		learndash_process_mark_incomplete( $user_id, $course_id, $lesson_id, false );

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}

	/**
	 * @param $user_id
	 * @param $lesson_id
	 */
	public function mark_steps_incomplete( $user_id, $lesson_id ) {

		$course_id  = learndash_get_course_id( $lesson_id );
		$topic_list = learndash_get_topic_list( $lesson_id, $course_id );

		if ( $topic_list ) {
			foreach ( $topic_list as $topic ) {
				learndash_process_mark_incomplete( $user_id, $course_id, $topic->ID, false );
				$topic_quiz_list = learndash_get_lesson_quiz_list( $topic->ID, $user_id, $course_id );
				if ( $topic_quiz_list ) {
					foreach ( $topic_quiz_list as $ql ) {
						learndash_delete_quiz_progress( $user_id, $ql['post']->ID );
					}
				}
			}
		}

		$lesson_quiz_list = learndash_get_lesson_quiz_list( $lesson_id, $user_id, $course_id );

		if ( $lesson_quiz_list ) {
			foreach ( $lesson_quiz_list as $ql ) {
				learndash_delete_quiz_progress( $user_id, $ql['post']->ID );
			}
		}

	}

	/**
	 * @param      $user_id
	 * @param null $course_id
	 */
	public function mark_quiz_incomplete( $user_id, $course_id = null ) {

		if ( ! empty( $this->quiz_list ) ) {
			foreach ( $this->quiz_list as $quiz_id => $quiz ) {
				learndash_delete_quiz_progress( $user_id, $quiz_id );
			}
		}

	}


}
