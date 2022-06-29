<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Masterstudy_Helpers;

/**
 * Class Masterstudy_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Masterstudy_Pro_Helpers extends Masterstudy_Helpers {


	public function __construct() {

		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Masterstudy_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}

		add_action( 'wp_ajax_select_mslms_lesson_from_course_x', array(
			$this,
			'select_lesson_from_course_func'
		) );
	}

	/**
	 * @param Masterstudy_Pro_Helpers $pro
	 */
	public function setPro( Masterstudy_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}

	/**
	 * Return all the specific fields of a form ID provided in ajax call
	 *
	 * @param string $include_any
	 */
	public function select_lesson_from_course_func() {

		global $uncanny_automator;

		// Nonce and post object validation
		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$fields = [];

		if ( ! isset( $_POST ) ) {
			echo wp_json_encode( $fields );
			die();
		}

		$mslms_course_id = $_POST['values']['MSLMSCOURSE'];

		if ( absint( $mslms_course_id ) ) {
			global $wpdb;

			$course_lessons_q =
				"Select ID, post_title
				FROM $wpdb->posts
				WHERE FIND_IN_SET(
					ID,
					(SELECT meta_value FROM wp_postmeta WHERE post_id = %d AND meta_key = 'curriculum')
				)
				AND post_type = 'stm-lessons'
				ORDER BY post_title ASC";

			$course_lessons_p = $wpdb->prepare( $course_lessons_q, absint( $mslms_course_id ) );

			$lessons = $wpdb->get_results( $course_lessons_p );

			foreach ( $lessons as $lesson ) {
				$fields[] = array(
					'value' => $lesson->ID,
					'text'  => $lesson->post_title,
				);
			}

		}

		echo wp_json_encode( $fields );
		die();
	}

}