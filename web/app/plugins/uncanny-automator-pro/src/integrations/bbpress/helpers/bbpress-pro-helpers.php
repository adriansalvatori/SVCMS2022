<?php


namespace Uncanny_Automator_Pro;


use Uncanny_Automator\Bbpress_Helpers;

/**
 * Class Buddypress_Pro_Helpers
 *
 * @package Uncanny_Automator_Pro
 */
class Bbpress_Pro_Helpers extends Bbpress_Helpers {

	/**
	 * Bbpress_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Bbpress_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}

		add_action( 'wp_ajax_select_topic_from_forum_TOPICREPLY', [ $this, 'select_topic_fields_func' ] );
		add_action( 'wp_ajax_select_topic_from_forum_BBTOPICREPLY_NOANY', [ $this, 'select_topic_fields_func_noany' ] );
	}

	/**
	 * @param Bbpress_Pro_Helpers $pro
	 */
	public function setPro( Bbpress_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}

	/**
	 * Return all the specific topics of a forum in ajax call
	 */
	public function select_topic_fields_func() {

		global $uncanny_automator;

		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$fields = [];
		if ( isset( $_POST ) ) {
			$fields[] = [
				'value' => - 1,
				'text'  => __( 'Any topic', 'uncanny-automator' ),
			];
			$forum_id = (int) $_POST['value'];

			if ( $forum_id > 0 ) {
				$args = [
					'post_type'      => bbp_get_topic_post_type(),
					'post_parent'    => $forum_id,
					'post_status'    => array_keys( get_post_stati() ),
					'posts_per_page' => 9999,
				];

				$topics = get_posts( $args );

				if ( ! empty( $topics ) ) {
					foreach ( $topics as $topic ) {
						$fields[] = [
							'value' => $topic->ID,
							'text'  => $topic->post_title,
						];
					}
				}
			}
		}
		echo wp_json_encode( $fields );
		die();
	}

	/**
	 * Return all the specific topics of a forum in ajax call
	 */
	public function select_topic_fields_func_noany() {

		global $uncanny_automator;

		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$fields = [];
		if ( isset( $_POST ) ) {

			$forum_id = (int) $_POST['value'];

			if ( $forum_id > 0 ) {
				$args = [
					'post_type'      => bbp_get_topic_post_type(),
					'post_parent'    => $forum_id,
					'post_status'    => array_keys( get_post_stati() ),
					'posts_per_page' => 9999,
				];

				$topics = get_posts( $args );

				if ( ! empty( $topics ) ) {
					foreach ( $topics as $topic ) {
						$fields[] = [
							'value' => $topic->ID,
							'text'  => $topic->post_title,
						];
					}
				}
			}
		}
		echo wp_json_encode( $fields );
		die();
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 *
	 * @return mixed
	 */
	public function list_bbpress_forums( $label = null, $option_code = 'BBFORUMS', $any_option = false, $multi_select = false ) {
		if ( ! $this->load_options ) {


			return Automator()->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		if ( ! function_exists( 'bbp_get_forum_post_type' ) ) {


			return Automator()->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		if ( ! $label ) {
			$label = esc_attr__( 'Forum', 'uncanny-automator' );
		}

		$any_label = null;

		if ( $any_option ) {
			$any_label = esc_attr__( 'Any forum', 'uncanny-automator' );
		}

		$args = [
			'post_type'      => bbp_get_forum_post_type(),
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => [ 'publish', 'private' ],
		];


		$options = Automator()->helpers->recipe->options->wp_query( $args, $any_option, $any_label );

		$option = [
			'option_code'              => $option_code,
			'label'                    => $label,
			'input_type'               => 'select',
			'supports_multiple_values' => $multi_select,
			'required'                 => true,
			'options'                  => $options,
			'relevant_tokens'          => [
				$option_code          => esc_attr__( 'Forum title', 'uncanny-automator' ),
				$option_code . '_ID'  => esc_attr__( 'Forum ID', 'uncanny-automator' ),
				$option_code . '_URL' => esc_attr__( 'Forum URL', 'uncanny-automator' ),
			],
		];

		return apply_filters( 'uap_option_list_bbpress_forums', $option );
	}
}
