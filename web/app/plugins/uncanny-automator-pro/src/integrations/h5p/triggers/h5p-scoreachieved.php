<?php

namespace Uncanny_Automator_Pro;


/**
 * Class H5P_SCOREACHIEVED
 * @package Uncanny_Automator_Pro
 */
class H5P_SCOREACHIEVED {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'H5P';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'H5PSCOREACHIEVED';
		$this->trigger_meta = 'H5PSCORE';
		$this->define_trigger();
	}

	/**
	 *
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/h5p/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - H5P */
			'sentence'            => sprintf( __( 'A user achieves a score {{greater than, less than or equal to:%1$s}} {{a value:%2$s}} on {{H5P content:%3$s}}', 'uncanny-automator-pro' ), 'NUMBERCOND', 'XSCORE', $this->trigger_meta ),
			/* translators: Logged-in trigger - H5P */
			'select_option_name'  => __( 'A user achieves a score {{greater than, less than or equal to}} {{a value}} on {{H5P content}}', 'uncanny-automator-pro' ),
			'action'              => 'h5p_alter_user_result',
			'priority'            => 20,
			'accepted_args'       => 4,
			'validation_function' => array( $this, 'h5p_content_completed' ),
			'options'             => [
				$uncanny_automator->helpers->recipe->h5p->options->pro->all_h5p_contents( null, $this->trigger_meta ),
				$uncanny_automator->helpers->recipe->less_or_greater_than(),
				$uncanny_automator->helpers->recipe->field->integer_field( 'XSCORE', __( 'Score', 'uncanny-automator' ) ),
			]
		);

		$uncanny_automator->register->trigger( $trigger );

		return true;
	}

	/**
	 * Trigger completion method.
	 *
	 * @param object $data Has the following properties
	 *                           score,max_score,opened,finished,time.
	 * @param int $result_id Only set if updating result.
	 * @param int $content_id Identifier of the H5P Content.
	 * @param int $user_id Identifier of the User.
	 */
	public function h5p_content_completed( $data, $result_id, $content_id, $user_id ) {
		global $uncanny_automator;
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( empty ( $user_id ) ) {
			return;
		}
		if ( ! isset ( $data['score'] ) ) {
			return;
		}

		$recipes             = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_content_id = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$required_conditions = $uncanny_automator->get->meta_from_recipes( $recipes, 'NUMBERCOND' );
		$required_score      = $uncanny_automator->get->meta_from_recipes( $recipes, 'XSCORE' );
		$matched_recipe_ids  = [];
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( $uncanny_automator->utilities->match_condition_vs_number( $required_conditions[ $recipe_id ][ $trigger_id ], $required_score[ $recipe_id ][ $trigger_id ], $data['score'] ) && ( intval( $required_content_id[ $recipe_id ][ $trigger_id ] ) == intval( $content_id ) || $required_content_id[ $recipe_id ][ $trigger_id ] === '-1' ) ) {
					$matched_recipe_ids[] = [
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					];
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$args = [
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $user_id,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
				];

				$uncanny_automator->maybe_add_trigger_entry( $args );
			}
		}
	}
}