<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Affwp_Helpers;

/**
 * Class Affwp_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Affwp_Pro_Helpers extends Affwp_Helpers {

	/**
	 * @param Affwp_Pro_Helpers $pro
	 */
	public function setPro( \Uncanny_Automator_Pro\Affwp_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}

	/**
	 * Affwp_Pro_Helpers constructor.
	 */
	public function __construct() {
	}

	/**
	 * Select all WooCommerce products
	 *
	 * @param null $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	public function get_wc_products( $label = null, $option_code = 'WCPRODUCTS', $args = [] ) {
		if ( ! $label ) {
			$label = __( 'Product', 'uncanny-automator-pro' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';
		$any_option   = key_exists( 'any_option', $args ) ? $args['any_option'] : false;

		$args = [
			'post_type'      => 'product',
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		];

		global $uncanny_automator;
		$options = $uncanny_automator->helpers->recipe->options->wp_query( $args, $any_option, esc_attr__( 'Any product', 'uncanny-automator' ) );

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
		];

		return apply_filters( 'uap_option_get_wc_products', $option );
	}

	/**
	 * Select all MemberPress products
	 *
	 * @param null $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	public function get_mp_products( $label = null, $option_code = 'MPPRODUCTS', $args = [] ) {
		if ( ! $label ) {
			$label = __( 'Product', 'uncanny-automator-pro' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';
		$any_option   = key_exists( 'any_option', $args ) ? $args['any_option'] : false;

		$args = [
			'post_type'      => 'memberpressproduct',
			'posts_per_page' => 999,
			'post_status'    => 'publish',
		];

		global $uncanny_automator;
		$options = $uncanny_automator->helpers->recipe->options->wp_query( $args, $any_option, esc_attr__( 'Any product', 'uncanny-automator' ) );

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
		];

		return apply_filters( 'uap_option_get_mp_products', $option );
	}

	/**
	 * Select all Easy Digital Downloads products
	 *
	 * @param null $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	public function get_edd_products( $label = null, $option_code = 'EDDPRODUCTS', $args = [] ) {
		if ( ! $label ) {
			$label = __( 'Product', 'uncanny-automator-pro' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';
		$any_option   = key_exists( 'any_option', $args ) ? $args['any_option'] : false;

		$args = [
			'post_type'      => 'download',
			'posts_per_page' => 999,
			'post_status'    => 'publish',
		];

		global $uncanny_automator;
		$options = $uncanny_automator->helpers->recipe->options->wp_query( $args, $any_option, esc_attr__( 'Any product', 'uncanny-automator' ) );

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
		];

		return apply_filters( 'uap_option_get_edd_products', $option );
	}

	/**
	 * @param null $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	public function get_affiliates( $label = null, $option_code = 'ALLAFFILIATES', $args = [] ) {
		if ( ! $label ) {
			$label = __( 'Affiliate', 'uncanny-automator-pro' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';
		$any_option   = key_exists( 'any_option', $args ) ? $args['any_option'] : false;
		$options      = [];

		if ( $any_option ) {
			$options['-1'] = esc_attr__( 'Any affiliate', 'uncanny-automator-pro' );
		}

		global $wpdb;
		$affiliates = $wpdb->get_results( "SELECT affiliate_id FROM {$wpdb->prefix}affiliate_wp_affiliates" );

		if ( is_array( $affiliates ) ) {
			foreach ( $affiliates as $affiliate ) {
				$options[ $affiliate->affiliate_id ] = affwp_get_affiliate_name( $affiliate->affiliate_id );
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
		];

		return apply_filters( 'uap_option_get_affiliates', $option );
	}
}