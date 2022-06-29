<?php

namespace Uncanny_Automator_Pro;

use LDLMS_DB;

/**
 * Class LD_RESETPROGRESS
 * @package Uncanny_Automator_Pro
 */
class LD_RESETPROGRESS {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'LD';

	private $action_code;
	private $action_meta;
	private $quiz_list;
	private $assignment_list;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'RESETPROGRESS';
		$this->action_meta = 'LDCOURSE';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$options_group = [
			Automator()->helpers->recipe->learndash->options->all_ld_courses( null, $this->action_meta, false ),
		];
		if ( class_exists( '\UCTINCAN\Database\Admin' ) ) {
			$options_group[] = Automator()->helpers->recipe->field->text_field( 'RESETTINCANNYDATA', esc_attr__( 'Reset Tin Canny data', 'uncanny-automator-pro' ), true, 'checkbox', '', false, esc_attr__( 'Also reset SCORM/xAPI records and bookmark data for this course', 'uncanny-automator-pro' ) );
		}
		$action = array(
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/learndash/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - LearnDash */
			'sentence'           => sprintf( __( "Reset the user's progress in {{a course:%1\$s}}", 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - LearnDash */
			'select_option_name' => __( "Reset the user's progress in {{a course}}", 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'reset_course_progress' ),
			'options_group'      => [
				$this->action_meta => $options_group,
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
	public function reset_course_progress( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$course_id     = intval( $action_data['meta'][ $this->action_meta ] );
		$reset_tc_data = false;
		if ( isset( $action_data['meta']['RESETTINCANNYDATA'] ) && class_exists( '\UCTINCAN\Database\Admin' ) ) {
			$reset_tc_data = $action_data['meta']['RESETTINCANNYDATA'];
		}

		if ( '-1' !== $course_id ) {
			$this->delete_user_activity( $user_id, $course_id );
			if ( $this->delete_course_progress( $user_id, $course_id ) ) {
				$this->reset_quiz_progress( $user_id, $course_id );
				$this->delete_assignments();
			}
			if ( "true" === $reset_tc_data && class_exists( '\UCTINCAN\Database\Admin' ) ) {
				$this->reset_tincanny_data( $user_id, $course_id );
			}
			$this->reset_quiz_progress( $user_id, $course_id );
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
		}
	}

	/**
	 *
	 * Delete course related meta keys from user meta table.
	 * Delete all activity related to a course from LD tables
	 *
	 * @param $user_id
	 * @param $course_id
	 */
	public function delete_user_activity( $user_id, $course_id ) {
		global $wpdb;
		delete_user_meta( $user_id, 'completed_' . $course_id );
		//delete_user_meta( $user_id, 'course_' . $course_id . '_access_from' );
		delete_user_meta( $user_id, 'course_completed_' . $course_id );
		delete_user_meta( $user_id, 'learndash_course_expired_' . $course_id );

		$activity_ids = $wpdb->get_results( 'SELECT activity_id FROM ' . $wpdb->prefix . 'learndash_user_activity WHERE course_id = ' . $course_id . ' AND user_id = ' . $user_id );

		if ( $activity_ids ) {
			foreach ( $activity_ids as $activity_id ) {
				$wpdb->query( "DELETE FROM  {$wpdb->prefix}learndash_user_activity_meta WHERE activity_id = {$activity_id->activity_id}" );
				$wpdb->query( "DELETE FROM {$wpdb->prefix}learndash_user_activity WHERE activity_id = {$activity_id->activity_id}" );
			}
		}
	}

	/**
	 *
	 * Delete course progress from Usermeta Table
	 *
	 * @param $user_id
	 * @param $course_id
	 */
	public function delete_course_progress( $user_id, $course_id ) {
		$usermeta = get_user_meta( $user_id, '_sfwd-course_progress', true );
		if ( ! empty( $usermeta ) && isset( $usermeta[ $course_id ] ) ) {
			unset( $usermeta[ $course_id ] );
			update_user_meta( $user_id, '_sfwd-course_progress', $usermeta );

			return true;
		}

		return false;
	}

	/**
	 *
	 * Get lesson quiz list
	 * Get Lesson assignment list
	 * Delete quiz progress, related to course, quiz etc
	 *
	 * @param $user_id
	 * @param $course_id
	 */
	public function reset_quiz_progress( $user_id, $course_id ) {
		$lessons = learndash_get_lesson_list( $course_id, array( 'num' => 0 ) );
		foreach ( $lessons as $lesson ) {
			$this->get_topics_quiz( $user_id, $lesson->ID, $course_id );
			$lesson_quiz_list = learndash_get_lesson_quiz_list( $lesson->ID, $user_id, $course_id );

			if ( $lesson_quiz_list ) {
				foreach ( $lesson_quiz_list as $ql ) {
					$this->quiz_list[ $ql['post']->ID ] = 0;
				}
			}

			//grabbing lesson related assignments
			$assignments = get_posts( [
				'post_type'      => 'sfwd-assignment',
				'posts_per_page' => 999,
				'meta_query'     => [
					'relation' => 'AND',
					[
						'key'     => 'lesson_id',
						'value'   => $lesson->ID,
						'compare' => '=',
					],
					[
						'key'     => 'course_id',
						'value'   => $course_id,
						'compare' => '=',
					],
					[
						'key'     => 'user_id',
						'value'   => $user_id,
						'compare' => '=',
					],
				],
			] );

			if ( $assignments ) {
				foreach ( $assignments as $assignment ) {
					$this->assignment_list[] = $assignment->ID;
				}
			}
		}

		$this->delete_quiz_progress( $user_id, $course_id );
	}

	/**
	 *
	 * Get topic quiz + assignment list
	 *
	 * @param $user_id
	 * @param $lesson_id
	 * @param $course_id
	 */
	public function get_topics_quiz( $user_id, $lesson_id, $course_id ) {
		$topic_list = learndash_get_topic_list( $lesson_id, $course_id );
		if ( $topic_list ) {
			foreach ( $topic_list as $topic ) {
				$topic_quiz_list = learndash_get_lesson_quiz_list( $topic->ID, $user_id, $course_id );
				if ( $topic_quiz_list ) {
					foreach ( $topic_quiz_list as $ql ) {
						$this->quiz_list[ $ql['post']->ID ] = 0;
					}
				}

				$assignments = get_posts( [
					'post_type'      => 'sfwd-assignment',
					'posts_per_page' => 999,
					'meta_query'     => [
						'relation' => 'AND',
						[
							'key'     => 'lesson_id',
							'value'   => $topic->ID,
							'compare' => '=',
						],
						[
							'key'     => 'course_id',
							'value'   => $course_id,
							'compare' => '=',
						],
						[
							'key'     => 'user_id',
							'value'   => $user_id,
							'compare' => '=',
						],
					],
				] );

				if ( $assignments ) {
					foreach ( $assignments as $assignment ) {
						$this->assignment_list[] = $assignment->ID;
					}
				}
			}
		}
	}

	/**
	 *
	 * Actually deleting quiz data from user meta and pro quiz activity table
	 *
	 * @param      $user_id
	 * @param null $course_id
	 */
	public function delete_quiz_progress( $user_id, $course_id = null ) {
		$quizzes = learndash_get_course_quiz_list( $course_id, $user_id );
		if ( $quizzes ) {
			foreach ( $quizzes as $quiz ) {
				$this->quiz_list[ $quiz['post']->ID ] = 0;
			}
		}
		global $wpdb;

		$quizz_progress = [];
		if ( ! empty( $this->quiz_list ) ) {
			$usermeta       = get_user_meta( $user_id, '_sfwd-quizzes', true );
			$quizz_progress = empty( $usermeta ) ? array() : $usermeta;
			foreach ( $quizz_progress as $k => $p ) {
				if ( key_exists( $p['quiz'], $this->quiz_list ) && $p['course'] == $course_id ) {
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
		}

		update_user_meta( $user_id, '_sfwd-quizzes', $quizz_progress );
		// Get quiz progress again for attempts
		$usermeta       = get_user_meta( $user_id, '_sfwd-quizzes', true );
		$quizz_progress = empty( $usermeta ) ? array() : $usermeta;

		foreach ( $quizz_progress as $k => $p ) {
			if ( $p['course'] == $course_id ) {
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

	/**
	 * Delete assignments of course, related to lessons / topics
	 */
	public function delete_assignments() {
		global $wpdb;
		$assignments = $this->assignment_list;
		//Utilities::log( $this->assignment_list, '$this->assignment_list', true, 'reset' );
		if ( $assignments ) {
			foreach ( $assignments as $assignment ) {
				$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE ID = {$assignment}" );
				$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id = {$assignment}" );
			}
		}
	}

	/**
	 * Delete tin canny data on reset.
	 *
	 * @param $user_id
	 * @param $course_id
	 */
	public function reset_tincanny_data( $user_id, $course_id ) {
		global $wpdb;
		$table_reporting = \UCTINCAN\Database\Admin::TABLE_REPORTING;
		$table_quiz      = \UCTINCAN\Database\Admin::TABLE_QUIZ;
		$table_resume    = \UCTINCAN\Database\Admin::TABLE_RESUME;
		$query           = sprintf( "DELETE FROM %s%s WHERE `user_id` = %s AND `course_id` = %s; ",
			$wpdb->prefix,
			$table_reporting,
			$user_id,
			$course_id
		);
		$wpdb->query( $query );

		$query = sprintf( "DELETE FROM %s%s WHERE `user_id` = %s AND `course_id` = %s;",
			$wpdb->prefix,
			$table_quiz,
			$user_id,
			$course_id
		);
		$wpdb->query( $query );

		$query = sprintf( "DELETE FROM %s%s WHERE `user_id` = %s AND `course_id` = %s; ",
			$wpdb->prefix,
			$table_resume,
			$user_id,
			$course_id
		);
		$wpdb->query( $query );

	}
}
