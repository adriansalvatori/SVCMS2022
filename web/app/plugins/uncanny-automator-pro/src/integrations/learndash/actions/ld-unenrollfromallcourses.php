<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Recipe;

/**
 * Class LD_UNENROLLFROMALLCOURSES
 *
 * @package Uncanny_Automator_Pro
 */
class LD_UNENROLLFROMALLCOURSES {
	use Recipe\Actions;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->setup_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	protected function setup_action() {
		$this->set_integration( 'LD' );
		$this->set_action_code( 'UNENROLLCOURSES_CODE' );
		$this->set_action_meta( 'UNENROLLCOURSES_META' );
		$this->set_support_link( $this->get_action_code(), 'integration/learndash/' );
		$this->set_requires_user( true );
		$this->set_is_pro( true );
		/* translators: Action - LearnDash */
		$this->set_sentence( esc_attr__( 'Unenroll the user from all courses', 'uncanny-automator-pro' ) );
		/* translators: Action - LearnDash */
		$this->set_readable_sentence( esc_attr__( 'Unenroll the user from all courses', 'uncanny-automator-pro' ) );
		$this->set_options( array() );
		$this->register_action();
	}

	/**
	 * Process the action.
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 * @param $args
	 * @param $parsed
	 *
	 * @return void.
	 */
	protected function process_action( $user_id, $action_data, $recipe_id, $args, $parsed ) {
		$user_courses = learndash_user_get_enrolled_courses( $user_id );

		if ( empty( $user_courses ) ) {
			$action_data['complete_with_errors'] = true;
			$error_message                       = esc_html__( 'User is not enrolled in any of the courses.', 'uncanny-automator-pro' );
			Automator()->complete->action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		foreach ( $user_courses as $course_id ) {
			//Unenroll from all courses
			ld_update_course_access( $user_id, $course_id, true );
		}

		Automator()->complete->action( $user_id, $action_data, $recipe_id );

	}
}
