<?php


namespace Uncanny_Automator_Pro;


use Uncanny_Automator\Memberpress_Helpers;

/**
 * Class Memberpress_Pro_Helpers
 *
 * @package Uncanny_Automator_Pro
 */
class Memberpress_Pro_Helpers extends Memberpress_Helpers {
	/**
	 * Memberpress_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Uncanny_Automator\Memberpress_Helpers', 'load_options' ) ) {
			global $uncanny_automator;
			$this->load_options = $uncanny_automator->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}

	}

	/**
	 * @param Memberpress_Pro_Helpers $pro
	 */
	public function setPro( Memberpress_Pro_Helpers $pro ) {
		parent::setPro( $pro );
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function all_memberpress_products( $label = null, $option_code = 'MPPRODUCT', $args = [] ) {
		if ( ! $this->load_options ) {
			global $uncanny_automator;

			return $uncanny_automator->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		if ( ! $label ) {
			$label = esc_attr__( 'Product', 'uncanny-automator' );
		}

		$args = wp_parse_args( $args,
			array(
				'uo_include_any' => false,
				'uo_any_label'   => esc_attr__( 'Any product', 'uncanny-automator' ),
			)
		);

		$options = [];
		global $uncanny_automator;

		if ( $args['uo_include_any'] ) {
			$options[ - 1 ] = $args['uo_any_label'];
		}
		$query_args = [
			'post_type'      => 'memberpressproduct',
			'posts_per_page' => 999,
			'post_status'    => 'publish',
			'meta_query'     => [
				'relation' => 'OR',
				[
					'key'     => '_mepr_product_period_type',
					'value'   => 'lifetime',
					'compare' => '!=',
				],
				[
					'key'     => '_mepr_product_period_type',
					'value'   => 'lifetime',
					'compare' => '=',
				]
			]
		];
		$options    = $options + $uncanny_automator->helpers->recipe->options->wp_query( $query_args );

		$option = [
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'options'         => $options,
			'relevant_tokens' => [
				$option_code                => esc_attr__( 'Product title', 'uncanny-automator' ),
				$option_code . '_ID'        => esc_attr__( 'Product ID', 'uncanny-automator' ),
				$option_code . '_URL'       => esc_attr__( 'Product URL', 'uncanny-automator' ),
				$option_code . '_THUMB_ID'  => esc_attr__( 'Product featured image ID', 'uncanny-automator' ),
				$option_code . '_THUMB_URL' => esc_attr__( 'Product featured image URL', 'uncanny-automator' ),
			],
		];


		return apply_filters( 'uap_option_all_memberpress_products', $option );
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function all_memberpress_products_recurring( $label = null, $option_code = 'MPPRODUCT', $args = [] ) {
		if ( ! $this->load_options ) {
			global $uncanny_automator;

			return $uncanny_automator->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		if ( ! $label ) {
			$label = esc_attr__( 'Product', 'uncanny-automator' );
		}

		$args = wp_parse_args( $args,
			array(
				'uo_include_any' => false,
				'uo_any_label'   => esc_attr__( 'Any recurring subscription product', 'uncanny-automator' ),
			)
		);

		$options = [];
		global $uncanny_automator;
		if ( $args['uo_include_any'] ) {
			$options[ - 1 ] = $args['uo_any_label'];
		}

		$query_args = [
			'post_type'      => 'memberpressproduct',
			'posts_per_page' => 999,
			'post_status'    => 'publish',
			'meta_query'     => [
				[
					'key'     => '_mepr_product_period_type',
					'value'   => 'lifetime',
					'compare' => '!=',
				]
			]
		];
		$qry        = $uncanny_automator->helpers->recipe->wp_query( $query_args );
		$options    = $options + $qry;

		$option = [
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'options'         => $options,
			'relevant_tokens' => [
				$option_code                => esc_attr__( 'Product title', 'uncanny-automator' ),
				$option_code . '_ID'        => esc_attr__( 'Product ID', 'uncanny-automator' ),
				$option_code . '_URL'       => esc_attr__( 'Product URL', 'uncanny-automator' ),
				$option_code . '_THUMB_ID'  => esc_attr__( 'Product featured image ID', 'uncanny-automator' ),
				$option_code . '_THUMB_URL' => esc_attr__( 'Product featured image URL', 'uncanny-automator' ),
			],
		];


		return apply_filters( 'uap_option_all_memberpress_products_recurring', $option );
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function all_mpca_corporate_accounts( $label = null, $option_code = 'MPCAACCOUNTS', $args = [] ) {
		if ( ! $this->load_options ) {
			return Automator()->helpers->recipe->build_default_options_array( $label, $option_code );
		}

		if ( ! $label ) {
			$label = esc_attr__( 'Parent account', 'uncanny-automator-pro' );
		}

		$args    = wp_parse_args( $args,
			array(
				'uo_include_any' => false,
				'uo_any_label'   => esc_attr__( 'Any parent account', 'uncanny-automator-pro' ),
			)
		);
		$options = array();

		if ( $args['uo_include_any'] ) {
			$options[ - 1 ] = $args['uo_any_label'];
		}

		$parent_accounts = \MPCA_Corporate_Account::get_all();

		foreach ( $parent_accounts as $parent_account ) {
			$user                                = get_userdata( $parent_account->user_id );
			$options[ $parent_account->user_id ] = $user->user_email;
		}

		$option = [
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'options'         => $options,
			'relevant_tokens' => [
				$option_code                  => esc_attr__( 'Parent account', 'uncanny-automator-pro' ),
				$option_code . '_SUBACCUNAME' => esc_attr__( 'Username', 'uncanny-automator-pro' ),
				$option_code . '_SUBACCFNAME' => esc_attr__( 'First name', 'uncanny-automator-pro' ),
				$option_code . '_SUBACCLNAME' => esc_attr__( 'Last name', 'uncanny-automator-pro' ),
				$option_code . '_SUBACCEMAIL' => esc_attr__( 'Email', 'uncanny-automator-pro' ),
			],
		];


		return apply_filters( 'uap_option_all_mpca_corporate_accounts', $option );
	}

}
