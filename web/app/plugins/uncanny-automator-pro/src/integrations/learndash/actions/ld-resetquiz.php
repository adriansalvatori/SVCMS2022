<?php

namespace Uncanny_Automator_Pro;

use LDLMS_DB;

/**
 * Class LD_RESETQUIZ
 * @package Uncanny_Automator_Pro
 */
class LD_RESETQUIZ {

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
		$this->action_code = 'RESETQUIZ';
		$this->action_meta = 'LDQUIZ';
		$this->define_action();
	}

	/**
	 *
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/learndash/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - LearnDash */
			'sentence'           => sprintf( __( "Reset the user's attempts for {{a quiz:%1\$s}}", 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - LearnDash */
			'select_option_name' => __( "Reset the user's attempts for {{a quiz}}", 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'reset_quiz' ),
			'options'            => [
				$uncanny_automator->helpers->recipe->learndash->options->all_ld_quiz( null, $this->action_meta, false ),
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
	public function reset_quiz( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$quiz_id = intval( $action_data['meta'][ $this->action_meta ] );

		if ( '-1' !== $quiz_id ) {

			$this->delete_quiz_progress( $user_id, $quiz_id );
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
		}
	}


	/**
	 *
	 * Actually deleting quiz data from user meta and pro quiz activity table
	 *
	 * @param      $user_id
	 * @param null $quiz_id
	 */
	public function delete_quiz_progress( $user_id, $quiz_id = null ) {
		global $wpdb;

		if ( ! empty( $quiz_id ) ) {
			$usermeta       = get_user_meta( $user_id, '_sfwd-quizzes', true );
			$quizz_progress = empty( $usermeta ) ? [] : $usermeta;
			foreach ( $quizz_progress as $k => $p ) {
				if ( (int) $p['quiz'] !== (int) $quiz_id ) {
					continue;
				} else {
					$statistic_ref_id = $p['statistic_ref_id'];
					unset( $quizz_progress[ $k ] );
					if ( ! empty( $statistic_ref_id ) ) {

						if ( class_exists( '\LDLMS_DB' ) ) {
							$pro_quiz_stat_table     = LDLMS_DB::get_table_name( 'quiz_statistic' );
							$pro_quiz_stat_ref_table = LDLMS_DB::get_table_name( 'quiz_statistic_ref' );
						} else {
							$pro_quiz_stat_table     = $wpdb->prefix . 'wp_pro_quiz_statistic';
							$pro_quiz_stat_ref_table = $wpdb->prefix . 'wp_pro_quiz_statistic_ref';
						}

						$wpdb->query( "DELETE FROM {$pro_quiz_stat_table} WHERE statistic_ref_id = {$statistic_ref_id}" );
						$wpdb->query( "DELETE FROM {$pro_quiz_stat_ref_table} WHERE statistic_ref_id = {$statistic_ref_id}" );
					}
				}
			}
			update_user_meta( $user_id, '_sfwd-quizzes', $quizz_progress );
		}

	}
}
