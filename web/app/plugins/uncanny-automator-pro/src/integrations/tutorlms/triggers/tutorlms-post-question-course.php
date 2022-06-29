<?php

namespace Uncanny_Automator_Pro;

/**
 * Class TUTORLMS_POST_QUESTION_COURSE
 *
 * @package Uncanny_Automator_Pro
 */
class TUTORLMS_POST_QUESTION_COURSE {

	public static $integration = 'TUTORLMS';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Constructor.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		$this->trigger_code = 'TUTORLMSQUESTIONPOSTED';
		$this->trigger_meta = 'TUTORLMSCOURSES';

		// hook into automator.
		$this->define_trigger();
	}

	/**
	 * Registers Course Enrollment trigger.
	 *
	 * @since 2.3.0
	 */
	public function define_trigger() {
		// setup trigger configuration.
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/tutor-lms/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - TutorLMS */
			'sentence'            => sprintf( esc_attr__( 'A user posts a question in {{a course:%1$s}}', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - TutorLMS */
			'select_option_name'  => esc_attr__( 'A user posts a question in {{a course}}', 'uncanny-automator-pro' ),
			'action'              => 'tutor_after_asked_question',
			'priority'            => 10,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'question_posted' ),
			'options'             => array(
				Automator()->helpers->recipe->tutorlms->options->all_tutorlms_courses( null, $this->trigger_meta, false, true ),
			),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * Validates Trigger.
	 *
	 * @since 2.3.0
	 */
	public function question_posted( $question ) {
		if ( $question['comment_parent'] != 0 ) {
			return;
		}

		$recipes            = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_course    = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = array();
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( intval( '-1' ) === intval( $required_course[ $recipe_id ][ $trigger_id ] ) ||
					 absint( $required_course[ $recipe_id ][ $trigger_id ] ) === absint( $question['comment_post_ID'] ) ) {
					$matched_recipe_ids[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					);
				}
			}
		}

		//	If recipe matches
		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$pass_args = array(
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $question['user_id'],
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'post_id'          => $question['comment_post_ID'],
				);

				$args = Automator()->maybe_add_trigger_entry( $pass_args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {
							$trigger_meta = array(
								'user_id'        => $question['user_id'],
								'trigger_id'     => $result['args']['trigger_id'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							);

							// Question author
							$trigger_meta['meta_key']   = 'TUTORLMSCOURSES_POSTEDBY';
							$trigger_meta['meta_value'] = maybe_serialize( $question['comment_author'] );
							Automator()->insert_trigger_meta( $trigger_meta );

							// Question content
							$trigger_meta['meta_key']   = 'TUTORLMSCOURSES_QUESTION';
							$trigger_meta['meta_value'] = maybe_serialize( $question['comment_content'] );
							Automator()->insert_trigger_meta( $trigger_meta );

							Automator()->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}

}
