<?php


namespace Uncanny_Automator_Pro;

use Uncanny_Automator\PeepSo_Helpers;

/**
 * Class PeepSo_Pro_Helpers
 *
 * @package Uncanny_Automator_Pro
 */
class PeepSo_Pro_Helpers extends PeepSo_Helpers {

	/**
	 * PeepSo_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\PeepSo_Helpers', 'load_options' ) ) {
			$this->load_options = true;
		}
	}

	/**
	 * @param PeepSo_Pro_Helpers $pro
	 */
	public function setPro( PeepSo_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}

	/**
	 * @param Get roles
	 */
	public function get_roles( $label = null, $option_code = 'PPUSERSROLE', $args = array() ) {

		if ( ! $this->load_options ) {
			return Automator()->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		if ( ! $label ) {
			$label = esc_attr__( 'Roles', 'uncanny-automator' );
		}

		$args = wp_parse_args(
			$args,
			array(
				'uo_include_any' => false,
				'uo_any_label'   => esc_attr__( 'Any role', 'uncanny-automator' ),
			)
		);

		$options = array();

		if ( $args['uo_include_any'] ) {
			$options[- 1] = $args['uo_any_label'];
		}
		$options = $options + $this->filtered_roles();
		$option  = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'options'         => $options,
			'relevant_tokens' => array(
				$option_code => esc_attr__( 'Role', 'uncanny-automator' ),
			),
		);

		return apply_filters( 'uap_option_peepso_all_user_roles', $option );
	}

	/**
	 * @param Get filtered url roles
	 */
	public function filtered_roles() {

		$existing_roles = $this->get_translated_roles();
		$new_roles      = array();
		foreach ( $existing_roles as $key => $role ) {
			$new_roles[ $key ] = $role;
		}

		return $new_roles;
	}

	/**
	 * @param Get filtered translated roles
	 */
	public function get_translated_roles() {
		$ret = array(
			'member'    => __( 'Community Member', 'peepso' ),
			'moderator' => __( 'Community Moderator', 'peepso-core' ),
			'admin'     => __( 'Community Administrator', 'peepso' ),
			'ban'       => __( 'Banned', 'peepso' ),
			'register'  => __( 'Pending user email verification', 'peepso' ),
			'verified'  => __( 'Pending admin approval', 'peepso' ),
			#'user'         => __('role_user',      'peepso'),
		);

		foreach ( $ret as $k => $v ) {
			if ( stristr( $v, 'role_' ) ) {
				$ret[ $k ] = ucwords( $k );
			}
		}

		return $ret;
	}


}
