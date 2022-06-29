<?php
/**
 * Comtains Quiz Percent based Trigger
 *
 * @package Uncanny_Automator_Pro
 *
 * @since 2.3.0
 * @version 2.3.0
 */

namespace Uncanny_Automator_Pro;

/**
 * Quiz Percent Score Trigger
 *
 * @since 2.3.0
 */
class TUTORLMS_QUIZPERCENT {

	/**
	 * Integration code
	 *
	 * @var string
	 * @since 2.3.0
	 */
	public static $integration = 'TUTORLMS';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		$this->trigger_code = 'TUTORLMSQUIZPERCENT';
		$this->trigger_meta = 'TUTORLMSQUIZ';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 *
	 * @since 2.3.0
	 */
	public function define_trigger() {
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/tutor-lms/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - TutorLMS */
			'sentence'            => sprintf( __( 'A user achieves a percentage {{greater than, less than or equal:%1$s}} to {{a value:%2$s}} on {{a quiz:%3$s}} {{a number of:%4$s}} times', 'uncanny-automator' ), 'NUMBERCOND', 'QUIZPERCENT', $this->trigger_meta, 'NUMTIMES' ),
			/* translators: Logged-in trigger - TutorLMS */
			'select_option_name'  => __( 'A user achieves a percentage {{greater than, less than or equal}} to {{a value}} on {{a quiz}}', 'uncanny-automator-pro' ),
			'action'              => 'tutor_quiz/attempt_ended',
			'priority'            => 10,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'completed' ),
			// very last call in WP, we need to make sure they viewed the page and didn't skip before is was fully viewable
			'options'             => array(
				Automator()->helpers->recipe->field->less_or_greater_than(),
				Automator()->helpers->recipe->field->int(
					array(
						'option_code' => 'QUIZPERCENT',
						'label'       => esc_attr__( 'Percentage', 'uncanny-automator' ),
						'description' => '',
						'placeholder' => esc_attr__( 'Example: 10', 'uncanny-automator' ),
						'default'     => null,
					)
				),
				Automator()->helpers->recipe->tutorlms->options->all_tutorlms_quizzes( null, $this->trigger_meta, true ),
				Automator()->helpers->recipe->options->number_of_times(),
			),
		);

		Automator()->register->trigger( $trigger );

	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $attempt_id The ID of the quiz attempt
	 *
	 * @since 2.3.0
	 */
	public function completed( $attempt_id ) {

		// get the quiz attempt.
		$attempt = tutor_utils()->get_attempt( $attempt_id );

		$quiz_id = $attempt->quiz_id;

		// Bail if this not the registered quiz post type
		if ( 'tutor_quiz' !== get_post_type( $quiz_id ) ) {
			return;
		}

		// bail if the attempt isn't finished yet.
		if ( ! in_array( $attempt->attempt_status, array( 'attempt_ended', 'review_required' ) ) ) {
			return;
		}

		$percentage          = Automator()->helpers->recipe->tutorlms->options->get_percentage_scored( $attempt );
		$recipes             = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_percentage = Automator()->get->meta_from_recipes( $recipes, 'QUIZPERCENT' );
		$required_quiz       = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$required_conditions = Automator()->get->meta_from_recipes( $recipes, 'NUMBERCOND' );
		$matched_recipe_ids  = array();

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( Automator()->utilities->match_condition_vs_number( $required_conditions[ $recipe_id ][ $trigger_id ], $required_percentage[ $recipe_id ][ $trigger_id ], $percentage ) ) {
					$matched_recipe_ids[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					);
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				//Any Quiz OR a specific quiz
				$r_quiz = (int) $required_quiz[ $matched_recipe_id['recipe_id'] ][ $matched_recipe_id['trigger_id'] ];
				if ( - 1 === $r_quiz || $r_quiz === (int) $quiz_id ) {
					$args = array(
						'code'             => $this->trigger_code,
						'meta'             => $this->trigger_meta,
						'user_id'          => get_current_user_id(),
						'recipe_to_match'  => $matched_recipe_id['recipe_id'],
						'trigger_to_match' => $matched_recipe_id['trigger_id'],
						'post_id'          => $quiz_id,
					);
					Automator()->maybe_add_trigger_entry( $args );
				}
			}
		}
	}
}
