<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Tutorlms_Helpers;

/**
 * Class Tutorlms_Pro_Helpers
 *
 * @since 2.3.0
 */
class Tutorlms_Pro_Helpers extends Tutorlms_Helpers {
	/**
	 * Tutorlms_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options.
		if ( property_exists( '\Uncanny_Automator\Tutorlms_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}

		add_action(
			'wp_ajax_select_lesson_from_course_MARKLESSONCOMPLETED',
			array(
				$this,
				'pro_select_lesson_from_course_func',
			)
		);

		add_filter( 'uap_option_all_tutorlms_courses', array( $this, 'add_q_n_a_tokens' ), 99, 3 );
	}

	/**
	 * @param Tutorlms_Pro_Helpers $pro
	 */
	public function setPro( Tutorlms_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}

	/**
	 * Marks a course complete
	 *
	 * @param int $course_id
	 * @param int $user_id
	 *
	 * @since 2.3.0
	 *
	 * @see https://github.com/themeum/tutor/blob/7f23f52050b66f45dfc63d729f6dab2725016366/classes/Course.php#L544-L598
	 */
	public function complete_course( $course_id, $user_id ) {

		do_action( 'tutor_course_complete_before', $course_id );

		global $wpdb;

		$date = date( 'Y-m-d H:i:s', tutor_time() );

		// Making sure that hash is unique.
		do {
			$hash     = substr( md5( wp_generate_password( 32 ) . $date . $course_id . $user_id ), 0, 16 );
			$has_hash = (int) $wpdb->get_var( "SELECT COUNT(comment_ID) from {$wpdb->comments} WHERE comment_agent = 'TutorLMSPlugin' AND comment_type = 'course_completed' AND comment_content = '{$hash}' " );
		} while ( $has_hash > 0 );

		$data = array(
			'comment_post_ID'  => $course_id,
			'comment_author'   => $user_id,
			'comment_date'     => $date,
			'comment_date_gmt' => get_gmt_from_date( $date ),
			'comment_content'  => $hash, //Identification Hash.
			'comment_approved' => 'approved',
			'comment_agent'    => 'TutorLMSPlugin',
			'comment_type'     => 'course_completed',
			'user_id'          => $user_id,
		);

		$wpdb->insert( $wpdb->comments, $data );

		do_action( 'tutor_course_complete_after', $course_id );

	}

	public function pro_select_lesson_from_course_func() {
		$this->load_any_options = false;
		$this->select_lesson_from_course_func();
		$this->load_any_options = true;
	}

	public function add_q_n_a_tokens( $options ) {
		if ( empty( $options ) ) {
			return $options;
		}

		if ( 'TUTORLMSCOURSES' !== $options['option_code'] ) {
			return $options;
		}

		$q_a_tokens                 = array(
			'TUTORLMSCOURSES_QUESTION' => esc_attr__( 'Question', 'uncanny-automator' ),
			'TUTORLMSCOURSES_POSTEDBY' => esc_attr__( 'Posted by', 'uncanny-automator' ),
		);
		$options['relevant_tokens'] = $options['relevant_tokens'] + $q_a_tokens;

		return $options;
	}
}
