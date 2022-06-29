<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Upsell_Plugin_Pro_Helpers
 * @package Uncanny_Automator_Pro
 */
class Upsell_Plugin_Pro_Helpers extends \Uncanny_Automator\Upsell_Plugin_Helpers{

	/**
	 * @var Upsell_Plugin_Pro_Helpers
	 */
	/**
	 * @param \Uncanny_Automator_Pro\Upsell_Plugin_Pro_Helpers $pro
	 */
	public function setPro( \Uncanny_Automator_Pro\Upsell_Plugin_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 *
	 * @return mixed
	 */
	public function all_upsell_subscriptions( $label = null, $option_code = 'USSUBSCRIPTION' ) {

		if ( ! $label ) {
			$label = __( 'subscription', 'uncanny-automator-pro' );
		}

		global $wpdb;
		$q = "
			SELECT posts.ID, posts.post_title FROM $wpdb->posts as posts
			LEFT JOIN $wpdb->postmeta as rel ON (posts.ID = rel.post_id)
			WHERE rel.meta_key LIKE '_payment' AND rel.meta_value LIKE 'subscription'
			AND posts.post_type = 'upsell_product'
			AND posts.post_status = 'publish'
			ORDER BY post_title
		";

		// Query all subscription products based on the assigned product_type category (new WC type) and post_type shop_"
		$subscriptions = $wpdb->get_results( $q );

		$options       = [];
		$options['-1'] = __( 'Any subscription', 'uncanny-automator-pro' );

		foreach ( $subscriptions as $post ) {
			$title = $post->post_title;

			if ( empty( $title ) ) {
				$title = sprintf( __( 'ID: %s (no title)', 'uncanny-automator-pro' ), $post->ID );
			}

			$options[ $post->ID ] = $title;
		}

		$option = [
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'options'         => $options,
			'relevant_tokens' => [
				$option_code          => __( 'Product title', 'uncanny-automator' ),
				$option_code . '_ID'  => __( 'Product ID', 'uncanny-automator' ),
				$option_code . '_URL' => __( 'Product URL', 'uncanny-automator' ),
				$option_code . '_THUMB_ID'  => __( 'Product featured image ID', 'uncanny-automator' ),
				$option_code . '_THUMB_URL' => __( 'Product featured image URL', 'uncanny-automator' ),
			],
		];

		return apply_filters( 'uap_option_all_upsell_products', $option );
	}

}