<?php

namespace Uncanny_Automator_Pro;

/**
 * Class MASTERSTUDY_QUIZ_PERCENTAGE
 *
 * @package Uncanny_Automator
 */
class MASTERSTUDY_QUIZ_PERCENTAGE {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'MSLMS';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'MSLMSQUIZPERCENTAGE';
		$this->trigger_meta = 'MSLMSQUIZ';
		$this->define_trigger();

	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		$args = array(
			'post_type'      => 'stm-courses',
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		);

		$options = Automator()->helpers->recipe->options->wp_query( $args, true, esc_attr__( 'Any course', 'uncanny-automator' ) );

		$course_relevant_tokens = array(
			'MSLMSCOURSE'           => esc_attr__( 'Course title', 'uncanny-automator-pro' ),
			'MSLMSCOURSE_ID'        => esc_attr__( 'Course ID', 'uncanny-automator-pro' ),
			'MSLMSCOURSE_URL'       => esc_attr__( 'Course URL', 'uncanny-automator-pro' ),
			'MSLMSCOURSE_THUMB_ID'  => esc_attr__( 'Course featured image ID', 'uncanny-automator-pro' ),
			'MSLMSCOURSE_THUMB_URL' => esc_attr__( 'Course featured image URL', 'uncanny-automator-pro' ),
		);

		$relevant_tokens = array(
			$this->trigger_meta                => esc_attr__( 'Quiz title', 'uncanny-automator-pro' ),
			$this->trigger_meta . '_ID'        => esc_attr__( 'Quiz ID', 'uncanny-automator-pro' ),
			$this->trigger_meta . '_URL'       => esc_attr__( 'Quiz URL', 'uncanny-automator-pro' ),
			$this->trigger_meta . '_THUMB_ID'  => esc_attr__( 'Quiz featured image ID', 'uncanny-automator-pro' ),
			$this->trigger_meta . '_THUMB_URL' => esc_attr__( 'Quiz featured image URL', 'uncanny-automator-pro' ),

		);

		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/masterstudy-lms/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'is_pro'              => true,
			/* translators: Logged-in trigger - MasterStudy LMS */
			'sentence'            => sprintf( esc_attr__( 'A user achieves a percentage {{greater than, less than or equal:%1$s}} to {{a value:%2$s}} on {{a quiz:%3$s}}', 'uncanny-automator-pro' ), $this->trigger_meta . '_NUMBERCOND', $this->trigger_meta . '_SCORE', $this->trigger_meta ),
			/* translators: Logged-in trigger - MasterStudy LMS */
			'select_option_name'  => esc_attr__( 'A user achieves a percentage {{greater than, less than or equal}} to {{a value}} on {{a quiz}}', 'uncanny-automator-pro' ),
			'action'              => array( 'stm_lms_quiz_passed', 'stm_lms_quiz_failed' ),
			'priority'            => 10,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'quiz_completed' ),
			'options'             => array(
				array(
					'option_code' => $this->trigger_meta . '_NUMBERCOND',
					/* translators: Noun */
					'label'       => esc_attr__( 'Condition', 'uncanny-automator-pro' ),
					'input_type'  => 'select',
					'required'    => true,
					// 'default_value'      => false,
					'options'     => array(
						'='  => esc_attr__( 'equal to', 'uncanny-automator' ),
						'!=' => esc_attr__( 'not equal to', 'uncanny-automator' ),
						'<'  => esc_attr__( 'less than', 'uncanny-automator' ),
						'>'  => esc_attr__( 'greater than', 'uncanny-automator' ),
						'>=' => esc_attr__( 'greater or equal to', 'uncanny-automator' ),
						'<=' => esc_attr__( 'less or equal to', 'uncanny-automator' ),
					),
				),
				Automator()->helpers->recipe->field->int(
					array(
						'option_code' => $this->trigger_meta . '_SCORE',
						'label'       => esc_attr__( 'Quiz score', 'uncanny-automator' ),
						'placeholder' => esc_attr__( 'Example: 10', 'uncanny-automator' ),
						'default'     => null,
					)
				),
			),
			'options_group'       => array(
				$this->trigger_meta => array(
					Automator()->helpers->recipe->field->select_field_ajax(
						'MSLMSCOURSE',
						esc_attr_x( 'Course', 'MasterStudy LMS', 'uncanny-automator' ),
						$options,
						'',
						'',
						false,
						true,
						array(
							'target_field' => $this->trigger_meta,
							'endpoint'     => 'select_mslms_quiz_from_course_QUIZ',
						),
						$course_relevant_tokens
					),
					Automator()->helpers->recipe->field->select_field( $this->trigger_meta, esc_attr__( 'Quiz', 'uncanny-automator' ), array(), false, false, false, $relevant_tokens ),
				),
			),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $data
	 */
	public function quiz_completed( $user_id, $quiz_id, $user_quiz_progress ) {
		$percentage          = $user_quiz_progress;
		$recipes             = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_percentage = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta . '_SCORE' );
		$required_quiz       = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$required_condition  = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta . '_NUMBERCOND' );
		$matched_recipe_ids  = array();

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				$r_quiz     = (int) $required_quiz[ $recipe_id ][ $trigger_id ];

				if (
					Automator()->utilities->match_condition_vs_number( $required_condition[ $recipe_id ][ $trigger_id ], $required_percentage[ $recipe_id ][ $trigger_id ], $percentage )
					&& ( intval( '-1' ) === intval( $r_quiz ) || $r_quiz === (int) $quiz_id )
				) {

					$matched_recipe_ids[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
						'NUMBERCOND' => $required_condition[ $recipe_id ][ $trigger_id ],
					);
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$args = array(
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'post_id'          => $quiz_id,
					'user_id'          => $user_id,
				);
				$args = Automator()->maybe_add_trigger_entry( $args, false );
				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {

							Automator()->insert_trigger_meta(
								array(
									'user_id'        => $user_id,
									'trigger_id'     => $result['args']['trigger_id'],
									'meta_key'       => $this->trigger_meta . '_SCORE',
									'meta_value'     => $percentage . '%',
									'trigger_log_id' => $result['args']['get_trigger_id'],
									'run_number'     => $result['args']['run_number'],
								)
							);

							Automator()->insert_trigger_meta(
								array(
									'user_id'        => $user_id,
									'trigger_id'     => $result['args']['trigger_id'],
									'meta_key'       => $this->trigger_meta . '_NUMBERCOND',
									'meta_value'     => $this->get_sign_text( $matched_recipe_id['NUMBERCOND'] ),
									'trigger_log_id' => $result['args']['get_trigger_id'],
									'run_number'     => $result['args']['run_number'],
								)
							);

							$source    = ( ! empty( $_POST['source'] ) ) ? intval( $_POST['source'] ) : '';
							$course_id = ( ! empty( $_POST['course_id'] ) ) ? intval( $_POST['course_id'] ) : '';
							$course_id = apply_filters( 'user_answers__course_id', $course_id, $source );

							Automator()->insert_trigger_meta(
								array(
									'user_id'        => $user_id,
									'trigger_id'     => $result['args']['trigger_id'],
									'meta_key'       => 'MSLMSCOURSE',
									'meta_value'     => $course_id,
									'trigger_log_id' => $result['args']['get_trigger_id'],
									'run_number'     => $result['args']['run_number'],
								)
							);

							Automator()->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}

	function get_sign_text( $val ) {

		switch ( $val ) {
			case '<':
				$val_text = esc_attr__( 'less than', 'uncanny-automator' );
				break;
			case '>':
				$val_text = esc_attr__( 'greater than', 'uncanny-automator' );
				break;
			case '=':
				$val_text = esc_attr__( 'equal to', 'uncanny-automator' );
				break;
			case '!=':
				$val_text = esc_attr__( 'not equal to', 'uncanny-automator' );
				break;
			case '>=':
				$val_text = esc_attr__( 'greater or equal to', 'uncanny-automator' );
				break;
			case '<=':
				$val_text = esc_attr__( 'less or equal to', 'uncanny-automator' );
				break;
			default:
				$val_text = '';
		}

		return $val_text;
	}
}
