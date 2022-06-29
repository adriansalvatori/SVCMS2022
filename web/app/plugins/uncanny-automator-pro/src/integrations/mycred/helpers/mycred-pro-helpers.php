<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Mycred_Helpers;

/**
 * Class Mycred_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Mycred_Pro_Helpers extends Mycred_Helpers {

	/**
	 * Mycred_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Mycred_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}

		add_action( 'wp_ajax_select_ranks_of_selected_POINTTYPES', [ $this, 'select_ranks_of_selected_POINTTYPES' ] );
	}

	/**
	 * @param Mycred_Pro_Helpers $pro
	 */
	public function setPro( Mycred_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}

	/**
	 * @param null $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	public function list_mycred_points_types_for_ranks( $label = null, $option_code = 'MYCREDPOINTSTYPES', $args = [] ) {
		if ( ! $label ) {
			$label = __( 'Point type', 'uncanny-automator' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';

		$options = [];

		$types = mycred_get_types();

		if ( ! empty( $types ) ) {
			foreach ( $types as $key => $type ) {
				$settings = mycred( $key );
				if ( isset( $settings->core['rank']['base'] ) && $settings->core['rank']['base'] == 'current' ) {
					$options[ $key ] = $type;
				}
			}
		}

		$option = [
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'supports_tokens' => $token,
			'is_ajax'         => $is_ajax,
			'fill_values_in'  => $target_field,
			'endpoint'        => $end_point,
			'options'         => $options,
			'description'     => 'Ranks must be set to “Use Current Balance” to be available in this dropdown',
		];

		return apply_filters( 'uap_option_list_mycred_points_types_for_ranks', $option );
	}

	/**
	 * @return 
	 */
	public function select_ranks_of_selected_POINTTYPES() {
		global $uncanny_automator;

		// Nonce and post object validation
		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$fields = [];
		if ( isset( $_POST['value'] ) && ! empty( $_POST['value'] ) ) {

			$ranks = mycred_get_ranks( 'publish', - 1, 'DESC', $_POST['value'] );

			if ( isset( $ranks ) && ! empty( $ranks ) ) {
				foreach ( $ranks as $rank ) {
					$fields[] = [
						'value' => $rank->post->ID,
						'text'  => $rank->post->post_title,
					];
				}
			}
		}
		echo wp_json_encode( $fields );
		die();
	}
}