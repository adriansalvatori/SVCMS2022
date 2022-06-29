<?php

namespace Uncanny_Automator_Pro;

/**
 * Class LP_REMOVECOURSE
 *
 * @package Uncanny_Automator_Pro
 */
class LP_REMOVECOURSE {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'LP';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'LPREMOVECOURSE';
		$this->action_meta = 'LPCOURSE';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;
		$args    = [
			'post_type'      => 'lp_course',
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		];
		$options = $uncanny_automator->helpers->recipe->options->wp_query( $args, false, __( 'Any course', 'uncanny-automator-pro' ) );

		$action = [
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/learnpress/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - LearnPress */
			'sentence'           => sprintf( __( 'Remove the user from {{a course:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - LearnPress */
			'select_option_name' => __( 'Remove the user from {{a course}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'lp_remove_course' ],
			'options'            => [
				$uncanny_automator->helpers->recipe->learnpress->options->all_lp_courses( null, $this->action_meta, false ),
			],
		];

		$uncanny_automator->register->action( $action );
	}

	/**
	 * Validation function when the action is hit.
	 *
	 * @param string $user_id user id.
	 * @param array $action_data action data.
	 * @param string $recipe_id recipe id.
	 */
	public function lp_remove_course( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		if ( ! function_exists( 'learn_press_delete_user_data' ) ) {
			$error_message = 'The function learn_press_delete_user_data does not exist';
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}
		$course_id = $action_data['meta'][ $this->action_meta ];
		// delete user course.
		learn_press_delete_user_data( $user_id, $course_id );

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );

		return;
	}
}
