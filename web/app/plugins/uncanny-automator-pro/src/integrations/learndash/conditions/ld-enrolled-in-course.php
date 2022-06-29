<?php

namespace Uncanny_Automator_Pro;

/**
 * Class ENROLLED_IN_COURSE
 *
 * @package Uncanny_Automator_Pro
 */
class LD_ENROLLED_IN_COURSE extends Action_Condition {

	/**
	 * Define_condition
	 *
	 * @return void
	 */
	public function define_condition() {

		$this->integration = 'LD';
		/*translators: Token */
		$this->name = __( 'The user is enrolled in {{a course}}', 'uncanny-automator-pro' );
		$this->code = 'ENROLLED_IN_COURSE';
		// translators: A token matches a value
		$this->dynamic_name  = sprintf( esc_html__( 'A user is enrolled in {{a course:%1$s}}', 'uncanny-automator-pro' ), 'COURSE' );
		$this->is_pro        = true;
		$this->requires_user = true;
	}

	/**
	 * Fields
	 *
	 * @return array
	 */
	public function fields() {

		$courses_field_args = array(
			'option_code'           => 'COURSE',
			'label'                 => esc_html__( 'Select a course', 'uncanny-automator-pro' ),
			'required'              => true,
			'options'               => $this->ld_courses_options(),
			'supports_custom_value' => false,
		);

		return array(
			// Course field
			$this->field->select_field_args( $courses_field_args ),
		);
	}

	/**
	 * @return array[]
	 */
	public function ld_courses_options() {
		$args       = array(
			'post_type'      => 'sfwd-courses',
			'posts_per_page' => 9999, //phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		);
		$ld_courses = array();
		$courses    = Automator()->helpers->recipe->options->wp_query( $args, false, false );
		if ( empty( $courses ) ) {
			return array();
		}
		foreach ( $courses as $course_id => $course_title ) {
			$ld_courses[] = array(
				'value' => $course_id,
				'text'  => $course_title,
			);
		}

		return $ld_courses;
	}

	/**
	 * Evaluate_condition
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function evaluate_condition() {

		$parsed_course = $this->get_parsed_option( 'COURSE' );

		$user_is_enrolled = sfwd_lms_has_access( $parsed_course, $this->user_id );
		// Check if the user is enrolled in the course here
		if ( false === (bool) $user_is_enrolled ) {

			$message = __( 'User is not enrolled in ', 'uncanny-automator-pro' ) . $this->get_option( 'COURSE_readable' );
			$this->condition_failed( $message );
		}
	}
}
