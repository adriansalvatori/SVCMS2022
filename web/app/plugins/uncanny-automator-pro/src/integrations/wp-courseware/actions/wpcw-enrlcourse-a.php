<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WPCW_ENRLCOURSE_A
 * @package Uncanny_Automator_Pro
 */
class WPCW_ENRLCOURSE_A {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WPCW';

	private $action_code;
	private $action_meta;

	/**
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		$this->action_code = 'WPCWENRLCOURSE_A';
		$this->action_meta = 'WPCWENRLCOURSE';
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
			'sentence'           => sprintf( __( 'Enroll the user in {{a course:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - WP Courseware */
			'select_option_name' => __( 'Enroll the user in {{a course}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'wpcw_enroll_in_course' ),
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
	public function wpcw_enroll_in_course( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		if ( ! function_exists( 'wpcw_get_courses' ) ) {
			$error_message = 'The function wpcw_get_courses does not exist';
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$course_list        = wpcw_get_courses();
		$enroll_course_list = array();
		$course_id          = $action_data['meta'][ $this->action_meta ];

		if ( ! empty( $course_list ) ) {
			foreach ( $course_list as $course ) {
				if ( intval( $course->course_post_id ) == intval( $course_id ) ) {
					$enroll_course_list[ $course->course_id ] = $course->course_id;
					continue;
				}
			}
		}

		if ( ! function_exists( 'WPCW_courses_syncUserAccess' ) ) {
			$error_message = 'The function WPCW_courses_syncUserAccess does not exist';
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		//Enroll to New Course
		WPCW_courses_syncUserAccess( $user_id, $enroll_course_list, 'add' );

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
