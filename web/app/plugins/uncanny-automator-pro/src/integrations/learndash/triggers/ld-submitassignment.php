<?php

namespace Uncanny_Automator_Pro;

/**
 * Class LD_SUBMITASSIGNMENT
 *
 * @package Uncanny_Automator_Pro
 */
class LD_SUBMITASSIGNMENT {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'LD';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'LD_SUBMITASSIGNMENT';
		$this->trigger_meta = 'LDLESSONTOPIC';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$args = array(
			'post_type'      => 'sfwd-courses',
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		);

		$course_relevant_tokens = array(
			'LDCOURSE'           => __( 'Course title', 'uncanny-automator' ),
			'LDCOURSE_ID'        => __( 'Course ID', 'uncanny-automator' ),
			'LDCOURSE_URL'       => __( 'Course URL', 'uncanny-automator' ),
			'LDCOURSE_THUMB_ID'  => __( 'Course featured image ID', 'uncanny-automator' ),
			'LDCOURSE_THUMB_URL' => __( 'Course featured image URL', 'uncanny-automator' ),
		);
		$lesson_relevant_tokens = array(
			$this->trigger_meta                => __( 'Lesson/Topic title', 'uncanny-automator' ),
			$this->trigger_meta . '_ID'        => __( 'Lesson/Topic ID', 'uncanny-automator' ),
			$this->trigger_meta . '_URL'       => __( 'Lesson/Topic URL', 'uncanny-automator' ),
			$this->trigger_meta . '_THUMB_ID'  => __( 'Lesson/Topic featured image ID', 'uncanny-automator' ),
			$this->trigger_meta . '_THUMB_URL' => __( 'Lesson/Topic featured image URL', 'uncanny-automator' ),
		);

		$course_options = $uncanny_automator->helpers->recipe->options->wp_query( $args, true, __( 'Any course', 'uncanny-automator' ) );

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/learndash/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - LearnDash */
			'sentence'            => sprintf( __( 'A user submits an assignment for {{a lesson or topic:%1$s}}', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - LearnDash */
			'select_option_name'  => __( 'A user submits an assignment for {{a lesson or topic}}', 'uncanny-automator-pro' ),
			'action'              => 'learndash_assignment_uploaded',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'assignment_uploaded' ),
			'options'             => array(
				$uncanny_automator->helpers->recipe->options->number_of_times(),
			),
			'options_group'       => array(
				$this->trigger_meta => array(
					$uncanny_automator->helpers->recipe->field->select_field_ajax(
						'LDCOURSE',
						__( 'Course', 'uncanny-automator' ),
						$course_options,
						'',
						'',
						false,
						true,
						array(
							'target_field' => $this->trigger_meta,
							'endpoint'     => 'select_lessontopic_from_course_LD_SUBMITASSIGNMENT',
						),
						$course_relevant_tokens
					),
					$uncanny_automator->helpers->recipe->field->select_field(
						$this->trigger_meta,
						__( 'Select a Lesson/Topic', 'uncanny-automator-pro' ),
						array(),
						false,
						false,
						false,
						$lesson_relevant_tokens
					),
				),
			),
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $assignment_post_id
	 * @param $assignment_meta
	 */
	public function assignment_uploaded( $assignment_post_id, $assignment_meta ) {

		if ( empty( $assignment_meta ) ) {
			return;
		}

		global $uncanny_automator;

		$args = array(
			'code'         => $this->trigger_code,
			'meta'         => $this->trigger_meta,
			'post_id'      => $assignment_meta['lesson_id'],
			'user_id'      => $assignment_meta['user_id'],
			'is_signed_in' => true,
		);

		$args = $uncanny_automator->maybe_add_trigger_entry( $args, false );
		if ( $args ) {
			foreach ( $args as $result ) {
				if ( true === $result['result'] ) {
					$uncanny_automator->insert_trigger_meta(
						array(
							'user_id'        => $assignment_meta['user_id'],
							'trigger_id'     => $result['args']['trigger_id'],
							'meta_key'       => 'LDCOURSE',
							'meta_value'     => $assignment_meta['course_id'],
							'trigger_log_id' => $result['args']['get_trigger_id'],
							'run_number'     => $result['args']['run_number'],
						)
					);
					$uncanny_automator->maybe_trigger_complete( $result['args'] );
				}
			}
		}
	}
}
