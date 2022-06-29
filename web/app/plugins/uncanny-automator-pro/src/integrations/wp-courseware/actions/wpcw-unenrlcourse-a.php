<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WPCW_UNENRLCOURSE_A
 * @package Uncanny_Automator_Pro
 */
class WPCW_UNENRLCOURSE_A {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WPCW';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'WPCWUNENRLCOURSE-A';
		$this->action_meta = 'WPCWUNRLCOURSE';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/wp-courseware/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - WP Courseware */
			'sentence'           => sprintf( __( 'Remove the user from {{a course:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - WP Courseware */
			'select_option_name' => __( 'Remove the user from {{a course}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'wpcw_unenroll_in_course' ),
			'options'            => [
				$uncanny_automator->helpers->recipe->wp_courseware->options->all_wpcw_courses( __( 'Course', 'uncanny-automator' ), $this->action_meta, false ),
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
	public function wpcw_unenroll_in_course( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		if ( ! function_exists( 'WPCW_users_getUserCourseList' ) ) {
			$error_message = 'The function WPCW_users_getUserCourseList does not exist';
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$user_course_list = WPCW_users_getUserCourseList( $user_id );
		$course_id        = $action_data['meta'][ $this->action_meta ];
		$sync_course_list = array();

		if ( ! empty( $user_course_list ) ) {
			foreach ( $user_course_list as $course ) {
				if ( intval( $course->course_post_id ) !== intval( $course_id ) ) {
					$sync_course_list[ $course->course_id ] = $course->course_id;
					continue;
				}
			}
		}

		if ( ! function_exists( 'WPCW_courses_syncUserAccess' ) ) {
			$error_message = 'The function WPCW_courses_syncUserAccess does not exist';
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		//UNEnroll to New Course
		WPCW_courses_syncUserAccess( $user_id, $sync_course_list, 'sync' );

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
