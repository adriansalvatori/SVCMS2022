<?php

namespace Uncanny_Automator_Pro;

/**
 * Class LD_UNENRLCOURSE
 * @package Uncanny_Automator_Pro
 */
class LD_UNENRLCOURSE {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'LD';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'UNENRLCOURSE';
		$this->action_meta = 'LDCOURSE';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$option                  = $uncanny_automator->helpers->recipe->learndash->options->all_ld_courses( null, 'LDCOURSE', true );
		$option['options']['-1'] = __( 'All Courses', 'uncanny-automator' );

		$action = array(
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/learndash/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - LearnDash */
			'sentence'           => sprintf( __( 'Unenroll the user from {{a course:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - LearnDash */
			'select_option_name' => __( 'Unenroll the user from {{a course}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'unenroll_in_course' ),
			'options'            => [
				$option,
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
	public function unenroll_in_course( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$course_id = $action_data['meta'][ $this->action_meta ];

		if ( '-1' === $course_id ) {

			$user_courses = learndash_user_get_enrolled_courses( $user_id );

			foreach ( $user_courses as $course_id ) {
				//Enroll from a Course
				ld_update_course_access( $user_id, $course_id, true );
			}
		} else {
			//Enroll from a Course
			ld_update_course_access( $user_id, $course_id, true );
		}

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
