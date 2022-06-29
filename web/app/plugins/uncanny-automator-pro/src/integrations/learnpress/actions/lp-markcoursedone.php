<?php

namespace Uncanny_Automator_Pro;

use LP_Global;
use LP_User_Item_Course;

/**
 * Class LP_MARKCOURSEDONE
 *
 * @package Uncanny_Automator_Pro
 */
class LP_MARKCOURSEDONE {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'LP';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'LPMARKCOURSEDONE-A';
		$this->action_meta = 'LPCOURSE';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;
		$args    = [
			'post_type'      => 'lp_course',
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		];
		$options = $uncanny_automator->helpers->recipe->options->wp_query( $args, false, __( 'Any course', 'uncanny-automator' ) );

		$action = [
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/learnpress/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - LearnPress */
			'sentence'           => sprintf( __( 'Mark {{a course:%1$s}} complete for the user', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - LearnPress */
			'select_option_name' => __( 'Mark {{a course}} complete for the user', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'lp_mark_course_done' ],
			'options'            => [
				$uncanny_automator->helpers->recipe->learnpress->options->all_lp_courses( null, $this->action_meta, false ),
			],
		];

		$uncanny_automator->register->action( $action );
	}


	/**
	 * Validation function when the action is hit.
	 *
	 * @param string $user_id user id.
	 * @param array $action_data action data.
	 * @param string $recipe_id recipe id.
	 */
	public function lp_mark_course_done( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		if ( ! function_exists( 'learn_press_get_current_user' ) ) {
			$error_message = 'The function learn_press_get_current_user does not exist';
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}
		$user = learn_press_get_user( $user_id );

		$course_id = $action_data['meta'][ $this->action_meta ];
		// Get All sections from course.
		$course = learn_press_get_course( $course_id );

		//Enroll to New Course
		if ( $course && $course->exists() ) {
			$enrol_result = $user->enroll( $course_id, 0, true );
		}

		$sections = $course->get_curriculum_raw();

		if ( ! empty( $sections ) ) {
			foreach ( $sections as $section ) {
				if ( isset( $section['items'] ) && is_array( $section['items'] ) ) {
					$lessons = $section['items'];
					// Mark lessons completed.
					foreach ( $lessons as $lesson ) {
						if ( $lesson['type'] === 'lp_lesson' ) {
							$result = $user->complete_lesson( $lesson['id'], $course_id );
						} elseif ( $lesson['type'] === 'lp_quiz' ) {
							$quiz_id = $lesson['id'];
							$user    = LP_Global::user();
							if ( ! $user->has_item_status( [ 'started', 'completed' ], $quiz_id, $course_id ) ) {
								$quiz_data = $user->start_quiz( $quiz_id, $course_id, false );
								$item      = new LP_User_Item_Course( $quiz_data );
								$item->finish();
							} else {
								$quiz_data = $user->get_item_data( $quiz_id, $course_id );
								$quiz_data->finish();
							}
						}
					}
				}
			}
		}

		$user_course = $user->get_course_data( $course_id );
		$result      = $user_course->finish();

		if ( $result ) {
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
		} else {
			$error_message = 'User not enrolled in course.';
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}
	}
}
