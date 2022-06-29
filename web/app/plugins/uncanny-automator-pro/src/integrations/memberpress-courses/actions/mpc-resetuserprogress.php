<?php

namespace Uncanny_Automator_Pro;

use memberpress\courses\models as models;

/**
 * Class MPC_RESETUSERPROGRESS
 * @package Uncanny_Automator_Pro
 */
class MPC_RESETUSERPROGRESS {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'MPC';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'MPCRESETUSERPROGRESS';
		$this->action_meta = 'MPCCOURSERESET';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/memberpress-courses/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - MemberPress */
			'sentence'           => sprintf( __( 'Reset the user\'s progress in {{a course:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - MemberPress */
			'select_option_name' => __( 'Reset the user\'s progress in {{a course}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'reset_progress' ),
			'options_group'      => [
				$this->action_meta => [
					Automator()->helpers->recipe->memberpress_courses->all_mp_courses( null, 'MPCCOURSERESET', false ),
				],
			],
		);

		$uncanny_automator->register->action( $action );
	}

	/**
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function reset_progress( $user_id, $action_data, $recipe_id, $args ) {
		global $wpdb;

		$course_id       = $action_data['meta'][ $this->action_meta ];
		$user_progresses = (array) models\UserProgress::find_all_by_user_and_course( $user_id, $course_id );

		if ( count( $user_progresses ) == 0 ) {
			return;
		}
		
		foreach ( $user_progresses as $user_progress ) {
			$user_progress->destroy();
		}

		Automator()->complete_action( $user_id, $action_data, $recipe_id );
	}
}
