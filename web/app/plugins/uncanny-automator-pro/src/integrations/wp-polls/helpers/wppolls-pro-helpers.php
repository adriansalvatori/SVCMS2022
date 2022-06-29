<?php
namespace Uncanny_Automator_Pro;


use Uncanny_Automator\Wppolls_Helpers;
/**
 * Class Wppolls_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Wppolls_Pro_Helpers extends Wppolls_Helpers {

	/**
	 * Wppolls_Pro_Helpers constructor.
	 */
	public function __construct() {

		add_action( 'wp_ajax_select_answers_from_wppoll', array( $this, 'select_answers_from_wppoll' ) );
	}

	/**
	 * @param Wppolls_Pro_Helpers $pro
	 */
	public function setPro( Wppolls_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}

	/**
	 *
	 */
	public function select_answers_from_wppoll() {

		global $uncanny_automator;

		$uncanny_automator->utilities->ajax_auth_check( $_POST );
		$fields = [];
		if ( isset( $_POST ) && key_exists( 'value', $_POST ) ) {

			$poll_id = sanitize_text_field( $_POST['value'] );

			global $wpdb;

			// Get Poll Answers
			$answers = $wpdb->get_results( "SELECT polla_aid, polla_answers FROM $wpdb->pollsa WHERE polla_qid = $poll_id ORDER BY polla_aid DESC" );

			foreach ( $answers as $answer ) {
				$fields[] = [
					'value' => $answer->polla_aid,
					'text'  => $answer->polla_answers,
				];
			}
		}
		echo wp_json_encode( $fields );
		die();
	}
}