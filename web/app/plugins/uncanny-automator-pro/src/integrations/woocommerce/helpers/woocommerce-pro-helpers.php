<?php


namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Woocommerce_Helpers;
use WC_Countries;
use WC_Product;
use WC_Subscriptions_Product;

/**
 * Class Woocommerce_Pro_Helpers
 *
 * @package Uncanny_Automator_Pro
 */
class Woocommerce_Pro_Helpers extends Woocommerce_Helpers {

	/**
	 * Woocommerce_Pro_Helpers constructor.
	 */
	public function __construct() {

		add_action(
			'wp_ajax_select_variations_from_WOOSELECTVARIATION',
			array(
				$this,
				'select_all_product_variations',
			)
		);
		add_action(
			'wp_ajax_select_variations_from_WOOSELECTVARIATION_with_any_option',
			array(
				$this,
				'select_all_product_variations_with_any',
			)
		);
	}

	/**
	 * @param Woocommerce_Pro_Helpers $pro
	 */
	public function setPro( Woocommerce_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}


	/**
	 * This method is used to list all input options for the action/triggers.
	 *
	 * @param $payment_method
	 *
	 * @return array
	 */
	public function load_options_input( $payment_method = false, $order_type = 'simple' ) {

		$defaults = array(
			'option_code'           => 'WCDETAILS',
			'label'                 => esc_attr__( 'Use the same info for shipping?', 'uncanny-automator' ),
			'input_type'            => 'select',
			'supports_tokens'       => false,
			'required'              => true,
			'default_value'         => 'YES',
			'supports_custom_value' => false,
			'options'               => array(
				'YES' => __( 'Yes', 'uncanny-automator-pro' ),
				'NO'  => __( 'No', 'uncanny-automator-pro' ),
			),
		);

		$bl_countries = array(
			'option_code'           => 'WCCOUNTRY',
			'label'                 => esc_attr__( 'Billing country/region', 'uncanny-automator' ),
			'input_type'            => 'select',
			'supports_tokens'       => false,
			'required'              => true,
			'default_value'         => null,
			'supports_custom_value' => false,
			'options'               => $this->get_countries(),
		);

		$sp_countries = array(
			'option_code'           => 'WC_SHP_COUNTRY',
			'label'                 => esc_attr__( 'Shipping country/region', 'uncanny-automator' ),
			'input_type'            => 'select',
			'supports_tokens'       => false,
			'required'              => false,
			'default_value'         => null,
			'supports_custom_value' => false,
			'options'               => $this->get_countries(),
		);

		$options_array = array(
			array(
				'option_code'       => 'WC_PRODUCTS_FIELDS',
				'input_type'        => 'repeater',
				'label'             => __( 'Order items', 'uncanny-automator-pro' ),
				'required'          => true,
				'fields'            => array(
					array(
						'option_code' => 'WC_PRODUCT_ID',
						'label'       => __( 'Product', 'uncanny-automator-pro' ),
						'input_type'  => 'select',
						'required'    => true,
						'read_only'   => false,
						'options'     => $this->all_wc_products_list( $order_type ),
					),
					Automator()->helpers->recipe->field->text(
						array(
							'option_code' => 'WC_PRODUCT_QTY',
							'label'       => __( 'Quantity', 'uncanny-automator-pro' ),
							'input_type'  => 'text',
							'tokens'      => false,
						)
					),
				),
				'add_row_button'    => __( 'Add product', 'uncanny-automator-pro' ),
				'remove_row_button' => __( 'Remove product', 'uncanny-automator-pro' ),
				'hide_actions'      => false,
			),
			Automator()->helpers->recipe->woocommerce->options->wc_order_statuses( __( 'Order status', 'uncanny-automator-pro' ) ),
			Automator()->helpers->recipe->woocommerce->pro->all_wc_payment_gateways( __( 'Payment gateway', 'uncanny-automator-pro' ), 'WOOPAYMENTGATEWAY', array(), false ),
			Automator()->helpers->recipe->field->text_field( 'WCORDERNOTE', __( 'Order note', 'uncanny-automator-pro' ), true, 'text', '', false ),
			Automator()->helpers->recipe->field->text_field( 'WCFIRST_NAME', __( 'Billing first name', 'uncanny-automator-pro' ), true, 'text', '', true ),
			Automator()->helpers->recipe->field->text_field( 'WCLAST_NAME', __( 'Billing last name', 'uncanny-automator-pro' ), true, 'text', '', true ),
			Automator()->helpers->recipe->field->text_field( 'WCEMAIL', __( 'Billing email', 'uncanny-automator-pro' ), true, 'text', '', true ),
			Automator()->helpers->recipe->field->text_field( 'WCCOMPANYNAME', __( 'Billing company name', 'uncanny-automator-pro' ), true, 'text', '', false ),
			Automator()->helpers->recipe->field->text_field( 'WCPHONE', __( 'Billing phone number', 'uncanny-automator-pro' ), true, 'text', '', true ),
			Automator()->helpers->recipe->field->text_field( 'WCADDRESSONE', __( 'Billing address 1', 'uncanny-automator-pro' ), true, 'text', '', true ),
			Automator()->helpers->recipe->field->text_field( 'WCADDRESSTWO', __( 'Billing address 2', 'uncanny-automator-pro' ), true, 'text', '', false ),
			Automator()->helpers->recipe->field->text_field( 'WCPOSTALCODE', __( 'Billing zip/postal code', 'uncanny-automator-pro' ), true, 'text', '', true ),
			Automator()->helpers->recipe->field->text_field( 'WCCITY', __( 'Billing city', 'uncanny-automator-pro' ), true, 'text', '', true ),
			Automator()->helpers->recipe->field->text_field( 'WCSTATE', __( 'Billing state/province', 'uncanny-automator-pro' ), true, 'text', '', true, __( 'Enter the two-letter state or province abbreviation.', 'uncanny-automator-pro' ) ),
			Automator()->helpers->recipe->field->select( $bl_countries ),
			Automator()->helpers->recipe->field->select( $defaults ),
			Automator()->helpers->recipe->field->text_field( 'WC_SHP_FIRST_NAME', __( 'Shipping first name', 'uncanny-automator-pro' ), true, 'text', '', false ),
			Automator()->helpers->recipe->field->text_field( 'WC_SHP_LAST_NAME', __( 'Shipping last name', 'uncanny-automator-pro' ), true, 'text', '', false ),
			Automator()->helpers->recipe->field->text_field( 'WC_SHP_EMAIL', __( 'Shipping email', 'uncanny-automator-pro' ), true, 'text', '', false ),
			Automator()->helpers->recipe->field->text_field( 'WC_SHP_COMPANYNAME', __( 'Shipping company name', 'uncanny-automator-pro' ), true, 'text', '', false ),
			Automator()->helpers->recipe->field->text_field( 'WC_SHP_PHONE', __( 'Shipping phone number', 'uncanny-automator-pro' ), true, 'text', '', false ),
			Automator()->helpers->recipe->field->text_field( 'WC_SHP_ADDRESSONE', __( 'Shipping address 1', 'uncanny-automator-pro' ), true, 'text', '', false ),
			Automator()->helpers->recipe->field->text_field( 'WC_SHP_ADDRESSTWO', __( 'Shipping address 2', 'uncanny-automator-pro' ), true, 'text', '', false ),
			Automator()->helpers->recipe->field->text_field( 'WC_SHP_POSTALCODE', __( 'Shipping zip/postal code', 'uncanny-automator-pro' ), true, 'text', '', false ),
			Automator()->helpers->recipe->field->text_field( 'WC_SHP_CITY', __( 'Shipping city', 'uncanny-automator-pro' ), true, 'text', '', false ),
			Automator()->helpers->recipe->field->text_field( 'WC_SHP_STATE', __( 'Shipping state/province', 'uncanny-automator-pro' ), true, 'text', '', false, __( 'Enter the two-letter state or province abbreviation.', 'uncanny-automator-pro' ) ),
			Automator()->helpers->recipe->field->select( $sp_countries ),
		);

		if ( 'subscription' === $order_type ) {
			unset( $options_array[1] );
		}

		if ( false === $payment_method ) {
			unset( $options_array[2] );
		}

		return Automator()->utilities->keep_order_of_options( $options_array );
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 *
	 * @return mixed
	 */
	public function all_wc_products_list( $product_type = 'simple' ) {

		if ( 'subscription' === $product_type ) {

			global $wpdb;
			$q = "
			SELECT posts.ID, posts.post_title FROM $wpdb->posts as posts
			LEFT JOIN $wpdb->term_relationships as rel ON (posts.ID = rel.object_id)
			WHERE rel.term_taxonomy_id IN (SELECT term_id FROM $wpdb->terms WHERE slug IN ('subscription','variable-subscription'))
			AND posts.post_type = 'product'
			AND posts.post_status = 'publish'
			UNION ALL
			SELECT ID, post_title FROM $wpdb->posts
			WHERE post_type = 'shop_subscription'
			AND post_status = 'publish'
			ORDER BY post_title
		";

			// Query all subscription products based on the assigned product_type category (new WC type) and post_type shop_"
			$subscriptions = $wpdb->get_results( $q );
			$products_list = array();
			$temp_array    = array();

			foreach ( $subscriptions as $post ) {
				$temp_array['value'] = $post->ID;
				$temp_array['text']  = __( $post->post_title, 'uncanny-automator-pro' );
				array_push( $products_list, $temp_array );
			}
		} else {
			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => 999,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'post_status'    => 'publish',
			);

			$options       = Automator()->helpers->recipe->options->wp_query( $args, false );
			$products_list = array();
			$temp_array    = array();
			if ( is_array( $options ) ) {
				foreach ( $options as $key => $value ) {
					$temp_array['value'] = $key;
					$temp_array['text']  = __( $value, 'uncanny-automator-pro' );
					array_push( $products_list, $temp_array );
				}
			}
		}

		return apply_filters( 'uap_option_all_wc_products_list', $products_list );
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 *
	 * @return mixed
	 */
	public function all_wc_subscriptions( $label = null, $option_code = 'WOOSUBSCRIPTIONS', $is_any = true ) {

		if ( ! $label ) {
			$label = __( 'Select a subscription', 'uncanny-automator-pro' );
		}

		global $wpdb;
		$q = "
			SELECT posts.ID, posts.post_title FROM $wpdb->posts as posts
			LEFT JOIN $wpdb->term_relationships as rel ON (posts.ID = rel.object_id)
			WHERE rel.term_taxonomy_id IN (SELECT term_id FROM $wpdb->terms WHERE slug IN ('subscription','variable-subscription'))
			AND posts.post_type = 'product'
			AND posts.post_status = 'publish'
			UNION ALL
			SELECT ID, post_title FROM $wpdb->posts
			WHERE post_type = 'shop_subscription'
			AND post_status = 'publish'
			ORDER BY post_title
		";

		// Query all subscription products based on the assigned product_type category (new WC type) and post_type shop_"
		$subscriptions = $wpdb->get_results( $q );

		$options = array();

		if ( $is_any === true ) {
			$options['-1'] = __( 'Any subscription', 'uncanny-automator-pro' );
		}

		foreach ( $subscriptions as $post ) {
			$title = $post->post_title;

			if ( empty( $title ) ) {
				$title = sprintf( __( 'ID: %s (no title)', 'uncanny-automator-pro' ), $post->ID );
			}

			$options[ $post->ID ] = $title;
		}

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'options'         => $options,
			'relevant_tokens' => array(
				$option_code                            => __( 'Product title', 'uncanny-automator' ),
				$option_code . '_ID'                    => __( 'Product ID', 'uncanny-automator' ),
				$option_code . '_URL'                   => __( 'Product URL', 'uncanny-automator' ),
				$option_code . '_THUMB_URL'             => __( 'Product featured image URL', 'uncanny-automator' ),
				$option_code . '_THUMB_ID'              => __( 'Product featured image ID', 'uncanny-automator' ),
				$option_code . '_SUBSCRIPTION_ID'       => __( 'Subscription ID', 'uncanny-automator-pro' ),
				$option_code . '_SUBSCRIPTION_STATUS'   => __( 'Subscription status', 'uncanny-automator-pro' ),
				$option_code . '_SUBSCRIPTION_TRIAL_END_DATE' => __( 'Subscription trial end date', 'uncanny-automator-pro' ),
				$option_code . '_SUBSCRIPTION_END_DATE' => __( 'Subscription end date', 'uncanny-automator-pro' ),
				$option_code . '_SUBSCRIPTION_NEXT_PAYMENT_DATE' => __( 'Subscription next payment date', 'uncanny-automator-pro' ),
			),
		);

		return apply_filters( 'uap_option_all_wc_subscriptions', $option );
	}

	/**
	 * @param null $label
	 * @param string $option_code
	 *
	 * @return array|mixed|void
	 */
	public function all_wc_variation_subscriptions( $label = null, $option_code = 'WOOSUBSCRIPTIONS', $args = array() ) {

		if ( ! $label ) {
			$label = __( 'Select a variable subscription', 'uncanny-automator-pro' );
		}

		$token         = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax       = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$target_field  = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point     = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';
		$options       = array();
		$options['-1'] = __( 'Any variable subscription', 'uncanny-automator-pro' );

		$subscription_products = array();

		if ( function_exists( 'wc_get_products' ) ) {
			$subscription_products = wc_get_products(
				array(
					'type'    => array( 'variable-subscription' ),
					'limit'   => - 1,
					'orderby' => 'date',
					'order'   => 'DESC',
				)
			);
		}

		foreach ( $subscription_products as $product ) {

			$title = $product->get_title();

			if ( empty( $title ) ) {
				$title = sprintf( __( 'ID: %s (no title)', 'uncanny-automator-pro' ), $product->get_id() );
			}

			$options[ $product->get_id() ] = $title;

		}

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'supports_tokens' => $token,
			'is_ajax'         => $is_ajax,
			'fill_values_in'  => $target_field,
			'endpoint'        => $end_point,
			'options'         => $options,
			'relevant_tokens' => array(
				$option_code                            => __( 'Variation title', 'uncanny-automator-pro' ),
				$option_code . '_ID'                    => __( 'Variation ID', 'uncanny-automator-pro' ),
				$option_code . '_URL'                   => __( 'Variation URL', 'uncanny-automator-pro' ),
				$option_code . '_THUMB_URL'             => __( 'Variation featured image URL', 'uncanny-automator-pro' ),
				$option_code . '_THUMB_ID'              => __( 'Variation featured image ID', 'uncanny-automator-pro' ),
				$option_code . '_PRODUCT'               => __( 'Product title', 'uncanny-automator' ),
				$option_code . '_PRODUCT_ID'            => __( 'Product ID', 'uncanny-automator' ),
				$option_code . '_PRODUCT_URL'           => __( 'Product URL', 'uncanny-automator' ),
				$option_code . '_PRODUCT_THUMB_URL'     => __( 'Product featured image URL', 'uncanny-automator' ),
				$option_code . '_PRODUCT_THUMB_ID'      => __( 'Product featured image ID', 'uncanny-automator' ),
				$option_code . '_SUBSCRIPTION_ID'       => __( 'Subscription ID', 'uncanny-automator-pro' ),
				$option_code . '_SUBSCRIPTION_STATUS'   => __( 'Subscription status', 'uncanny-automator-pro' ),
				$option_code . '_SUBSCRIPTION_TRIAL_END_DATE' => __( 'Subscription trial end date', 'uncanny-automator-pro' ),
				$option_code . '_SUBSCRIPTION_END_DATE' => __( 'Subscription end date', 'uncanny-automator-pro' ),
				$option_code . '_SUBSCRIPTION_NEXT_PAYMENT_DATE' => __( 'Subscription next payment date', 'uncanny-automator-pro' ),
			),
		);

		return apply_filters( 'uap_option_all_wc_variation_subscriptions', $option );
	}

	/**
	 * @param null $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	public function all_wc_variable_products( $label = null, $option_code = 'WOOVARIABLEPRODUCTS', $args = array() ) {

		if ( ! $label ) {
			$label = __( 'Product', 'uncanny-automator' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';

		global $wpdb;
		$query = "
			SELECT posts.ID, posts.post_title FROM $wpdb->posts as posts
			LEFT JOIN $wpdb->term_relationships as rel ON (posts.ID = rel.object_id)
			WHERE rel.term_taxonomy_id IN (SELECT term_id FROM $wpdb->terms WHERE slug IN ('variable'))
			AND posts.post_type = 'product'
			AND posts.post_status = 'publish'
			ORDER BY post_title
		";

		$all_products  = $wpdb->get_results( $query );
		$options       = array();
		$options['-1'] = __( 'Any product', 'uncanny-automator' );

		foreach ( $all_products as $product ) {
			$title = $product->post_title;
			if ( empty( $title ) ) {
				$title = sprintf( __( 'ID: %s (no title)', 'uncanny-automator-pro' ), $product->ID );
			}
			$options[ $product->ID ] = $title;
		}

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'supports_tokens' => $token,
			'is_ajax'         => $is_ajax,
			'fill_values_in'  => $target_field,
			'endpoint'        => $end_point,
			'options'         => $options,
			'relevant_tokens' => array(
				$option_code                => __( 'Product title', 'uncanny-automator' ),
				$option_code . '_ID'        => __( 'Product ID', 'uncanny-automator' ),
				$option_code . '_URL'       => __( 'Product URL', 'uncanny-automator' ),
				$option_code . '_THUMB_ID'  => __( 'Product featured image ID', 'uncanny-automator' ),
				$option_code . '_THUMB_URL' => __( 'Product featured image URL', 'uncanny-automator' ),
			),
		);

		return apply_filters( 'uap_option_all_wc_variable_products', $option );
	}

	/**
	 * @param null $label
	 * @param string $option_code
	 *
	 * @return mixed|void
	 */
	public function all_wc_product_categories( $label = null, $option_code = 'WOOPRODCAT', $args = array() ) {

		$supports_multiple_values = key_exists( 'supports_multiple_values', $args ) ? $args['supports_multiple_values'] : false;
		$description              = key_exists( 'description', $args ) ? $args['description'] : false;
		$required                 = key_exists( 'required', $args ) ? $args['required'] : true;
		if ( ! $label ) {
			$label = __( 'Categories', 'uncanny-automator-pro' );
		}

		global $wpdb;
		$query = "
			SELECT terms.term_id,terms.name FROM $wpdb->terms as terms
			LEFT JOIN $wpdb->term_taxonomy as rel ON (terms.term_id = rel.term_id)
			WHERE rel.taxonomy = 'product_cat'
			ORDER BY terms.name";

		$categories = $wpdb->get_results( $query );

		$options = array();

		foreach ( $categories as $category ) {
			$title = $category->name;
			if ( empty( $title ) ) {
				$title = sprintf( __( 'ID: %s (no title)', 'uncanny-automator-pro' ), $category->term_id );
			}
			$options[ $category->term_id ] = $title;
		}

		$option = array(
			'option_code'              => $option_code,
			'label'                    => $label,
			'description'              => $description,
			'input_type'               => 'select',
			'required'                 => $required,
			'options'                  => $options,
			'supports_multiple_values' => $supports_multiple_values,
			'relevant_tokens'          => array(
				$option_code          => __( 'Category title', 'uncanny-automator' ),
				$option_code . '_ID'  => __( 'Category ID', 'uncanny-automator' ),
				$option_code . '_URL' => __( 'Category URL', 'uncanny-automator' ),
			),
		);

		return apply_filters( 'uap_option_all_wc_product_categories', $option );
	}

	/**
	 * @param null $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return array|mixed|void
	 */
	public function all_wc_payment_gateways( $label = null, $option_code = 'WOOPAYMENTGATEWAY', $args = array(), $is_any = true ) {

		$description = key_exists( 'description', $args ) ? $args['description'] : false;
		$required    = key_exists( 'required', $args ) ? $args['required'] : true;
		if ( ! $label ) {
			$label = __( 'Payment method', 'uncanny-automator-pro' );
		}

		$methods = WC()->payment_gateways->payment_gateways();

		$options = array();

		if ( $is_any === true ) {
			$options['-1'] = __( 'Any payment method', 'uncanny-automator-pro' );
		}

		foreach ( $methods as $method ) {
			if ( $method->enabled == 'yes' ) {
				$title = $method->title;
				if ( empty( $title ) ) {
					$title = sprintf( __( 'ID: %s (no title)', 'uncanny-automator-pro' ), $method->id );
				}
				$options[ $method->id ] = $title;
			}
		}

		$option = array(
			'option_code'              => $option_code,
			'label'                    => $label,
			'description'              => $description,
			'input_type'               => 'select',
			'required'                 => $required,
			'options'                  => $options,
			'supports_multiple_values' => false,
		);

		return apply_filters( 'uap_option_all_wc_payment_gateways', $option );
	}

	/**
	 * @param null $label
	 * @param string $option_code
	 *
	 * @return mixed|void
	 */
	public function all_wc_product_tags( $label = null, $option_code = 'WOOPRODTAG' ) {

		if ( ! $label ) {
			$label = __( 'Categories', 'uncanny-automator-pro' );
		}

		global $wpdb;
		$query = "
			SELECT terms.term_id,terms.name FROM $wpdb->terms as terms
			LEFT JOIN $wpdb->term_taxonomy as rel ON (terms.term_id = rel.term_id)
			WHERE rel.taxonomy = 'product_tag'
			ORDER BY terms.name";

		$tags = $wpdb->get_results( $query );

		$options       = array();
		$options['-1'] = __( 'Any tag', 'uncanny-automator-pro' );

		foreach ( $tags as $tag ) {
			$title = $tag->name;
			if ( empty( $title ) ) {
				$title = sprintf( __( 'ID: %s (no title)', 'uncanny-automator-pro' ), $tag->term_id );
			}
			$options[ $tag->term_id ] = $title;
		}

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'options'         => $options,
			'relevant_tokens' => array(
				$option_code          => __( 'Tag title', 'uncanny-automator' ),
				$option_code . '_ID'  => __( 'Tag ID', 'uncanny-automator' ),
				$option_code . '_URL' => __( 'Tag URL', 'uncanny-automator' ),
			),
		);

		return apply_filters( 'uap_option_all_wc_product_tags', $option );
	}

	/**
	 * get all variations of selected variable product
	 */
	public function select_all_product_variations() {
		global $uncanny_automator;

		// Nonce and post object validation
		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$fields = array();
		if ( isset( $_POST['value'] ) && ! empty( $_POST['value'] ) ) {

			$args = array(
				'post_type'      => 'product_variation',
				'post_parent'    => absint( $_POST['value'] ),
				'posts_per_page' => 999,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'post_status'    => 'publish',
			);

			$options = get_posts( $args );
			if ( isset( $options ) && ! empty( $options ) ) {
				foreach ( $options as $option ) {
					$fields[] = array(
						'value' => $option->ID,
						'text'  => ! empty( $option->post_excerpt ) ? $option->post_excerpt : $option->post_title,
					);
				}
			} else {
				$fields[] = array(
					'value' => - 1,
					'text'  => __( 'Any variation', 'uncanny-automator-pro' ),
				);
			}
		}
		echo wp_json_encode( $fields );
		die();
	}

	/**
	 * get all variations of selected variable product
	 */
	public function select_all_product_variations_with_any() {
		global $uncanny_automator;

		// Nonce and post object validation
		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$fields = array();
		if ( isset( $_POST['value'] ) && ! empty( $_POST['value'] ) ) {

			$fields[] = array(
				'value' => - 1,
				'text'  => __( 'Any variation', 'uncanny-automator-pro' ),
			);

			$args = array(
				'post_type'      => 'product_variation',
				'post_parent'    => absint( $_POST['value'] ),
				'posts_per_page' => 999,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'post_status'    => 'publish',
			);

			$options = get_posts( $args );
			if ( isset( $options ) && ! empty( $options ) ) {
				foreach ( $options as $option ) {
					$fields[] = array(
						'value' => $option->ID,
						'text'  => ! empty( $option->post_excerpt ) ? $option->post_excerpt : $option->post_title,
					);
				}
			}
		}
		echo wp_json_encode( $fields );
		die();
	}

	/**
	 * Fetch labels for trigger conditions.
	 *
	 * @return array
	 * @since 2.10
	 */
	public function get_trigger_condition_labels() {
		/**
		 * Filters WooCommerce Integrations' trigger conditions.
		 *
		 * @param array $trigger_conditions An array of key-value pairs of action hook handle and human readable label.
		 */
		return apply_filters(
			'uap_wc_trigger_conditions',
			array(
				'woocommerce_payment_complete'       => _x( 'pays for', 'WooCommerce', 'uncanny-automator-pro' ),
				'woocommerce_order_status_completed' => _x( 'completes', 'WooCommerce', 'uncanny-automator-pro' ),
				'woocommerce_thankyou'               => _x( 'lands on a thank you page for', 'WooCommerce', 'uncanny-automator-pro' ),
			)
		);
	}

	/**
	 * Fetch labels for trigger conditions.
	 *
	 * @return array
	 * @since 3.4
	 */
	public function get_order_item_trigger_condition_labels() {
		/**
		 * Filters WooCommerce Integrations' trigger conditions.
		 *
		 * @param array $trigger_conditions An array of key-value pairs of action hook handle and human readable label.
		 */
		return apply_filters(
			'uap_wc_order_item_trigger_conditions',
			array(
				'woocommerce_payment_complete'       => _x( 'paid for', 'WooCommerce', 'uncanny-automator-pro' ),
				'woocommerce_order_status_completed' => _x( 'completed', 'WooCommerce', 'uncanny-automator-pro' ),
				'woocommerce_thankyou'               => _x( 'thank you page visited', 'WooCommerce', 'uncanny-automator-pro' ),
			)
		);
	}

	/**
	 * @param string $code
	 *
	 * @return mixed|void
	 */
	public function get_woocommerce_trigger_conditions( $code = 'TRIGGERCOND' ) {
		$options = array(
			'option_code' => $code,
			/* translators: Noun */
			'label'       => esc_attr__( 'Trigger condition', 'uncanny-automator-pro' ),
			'input_type'  => 'select',
			'required'    => true,
			'options'     => $this->get_trigger_condition_labels(),
		);

		return apply_filters( 'uap_option_woocommerce_trigger_conditions', $options );
	}

	/**
	 * @param string $code
	 *
	 * @return mixed|void
	 */
	public function get_woocommerce_order_item_trigger_conditions( $code = 'TRIGGERCOND' ) {
		$options = array(
			'option_code' => $code,
			/* translators: Noun */
			'label'       => esc_attr__( 'Trigger condition', 'uncanny-automator-pro' ),
			'input_type'  => 'select',
			'required'    => true,
			'options'     => $this->get_order_item_trigger_condition_labels(),
		);

		return apply_filters( 'uap_option_woocommerce_order_item_trigger_conditions', $options );
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 *
	 * @return mixed
	 */
	public function all_wc_products_multiselect( $label = null, $option_code = 'WOOPRODUCT', $settings = array() ) {

		if ( ! $label ) {
			$label = esc_attr__( 'Product', 'uncanny-automator' );
		}
		$description = '';
		if ( isset( $settings['description'] ) ) {
			$description = $settings['description'];
		}

		$required = key_exists( 'required', $settings ) ? $settings['required'] : true;

		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		);

		global $uncanny_automator;
		$options = $uncanny_automator->helpers->recipe->options->wp_query( $args, false, esc_attr__( 'Any product', 'uncanny-automator' ) );

		$option = array(
			'option_code'              => $option_code,
			'label'                    => $label,
			'description'              => $description,
			'input_type'               => 'select',
			'required'                 => $required,
			'options'                  => $options,
			'supports_multiple_values' => true,
			'relevant_tokens'          => array(
				$option_code                => esc_attr__( 'Product title', 'uncanny-automator' ),
				$option_code . '_ID'        => esc_attr__( 'Product ID', 'uncanny-automator' ),
				$option_code . '_URL'       => esc_attr__( 'Product URL', 'uncanny-automator' ),
				$option_code . '_THUMB_ID'  => esc_attr__( 'Product featured image ID', 'uncanny-automator' ),
				$option_code . '_THUMB_URL' => esc_attr__( 'Product featured image URL', 'uncanny-automator' ),
			),
		);

		return apply_filters( 'uap_option_all_wc_products', $option );
	}

	/**
	 * @param null $label
	 * @param string $option_code
	 *
	 * @return mixed|void
	 */
	public function get_wcs_statuses( $label = null, $option_code = 'WOOSUBSCRIPTIONSTATUS', $is_any = true ) {
		if ( ! $label ) {
			$label = esc_attr__( 'Status', 'uncanny-automator' );
		}
		$statuses = wcs_get_subscription_statuses();
		$options  = array();

		if ( $is_any === true ) {
			$options['-1'] = __( 'Any status', 'uncanny-automator-pro' );
		}

		$options = $options + $statuses;
		$option  = array(
			'option_code' => $option_code,
			'label'       => $label,
			'input_type'  => 'select',
			'required'    => true,
			'options'     => $options,
		);

		return apply_filters( 'uap_option_all_wc_statuses', $option );
	}


	/**
	 * @param $item_id
	 * @param $order_id
	 *
	 * @return array|\WC_Order_Item
	 */
	public static function get_order_item_by_id( $item_id, $order_id ) {
		$order = wc_get_order( $order_id );
		foreach ( $order->get_items() as $line_item_id => $line_item ) {
			if ( $item_id === $line_item_id ) {
				return $line_item;
			}
		}

		return array();
	}

	/**
	 * @param $country_name
	 *
	 * @return int|string|void
	 */
	public function get_countries() {
		$cnt     = new WC_Countries();
		$options = $cnt->get_countries();

		return apply_filters( 'uap_option_all_wc_countries', $options );
	}

	/**
	 * @return void
	 */
	public function wc_create_order( $user_id, $action_data, $recipe_id, $args, $payment_method = false, $order_type = 'simple' ) {

		$products = json_decode( $action_data['meta']['WC_PRODUCTS_FIELDS'] );
		if ( 'simple' === $order_type ) {
			$order_status = sanitize_text_field( $action_data['meta']['WCORDERSTATUS'] );
		}
		$payment_gateways = '';
		if ( true === $payment_method ) {
			$payment_gateways = sanitize_text_field( $action_data['meta']['WOOPAYMENTGATEWAY'] );
		}
		$billing_first_name   = Automator()->parse->text( $action_data['meta']['WCFIRST_NAME'], $recipe_id, $user_id, $args );
		$billing_last_name    = Automator()->parse->text( $action_data['meta']['WCLAST_NAME'], $recipe_id, $user_id, $args );
		$billing_email        = Automator()->parse->text( $action_data['meta']['WCEMAIL'], $recipe_id, $user_id, $args );
		$billing_company_name = Automator()->parse->text( $action_data['meta']['WCCOMPANYNAME'], $recipe_id, $user_id, $args );
		$billing_phone        = Automator()->parse->text( $action_data['meta']['WCPHONE'], $recipe_id, $user_id, $args );
		$billing_address_1    = Automator()->parse->text( $action_data['meta']['WCADDRESSONE'], $recipe_id, $user_id, $args );
		$billing_address_2    = Automator()->parse->text( $action_data['meta']['WCADDRESSTWO'], $recipe_id, $user_id, $args );
		$billing_pincode      = Automator()->parse->text( $action_data['meta']['WCPOSTALCODE'], $recipe_id, $user_id, $args );
		$billing_city         = Automator()->parse->text( $action_data['meta']['WCCITY'], $recipe_id, $user_id, $args );
		$billing_state        = Automator()->parse->text( $action_data['meta']['WCSTATE'], $recipe_id, $user_id, $args );
		$billing_country      = Automator()->parse->text( $action_data['meta']['WCCOUNTRY'], $recipe_id, $user_id, $args );
		$details_chk          = Automator()->parse->text( $action_data['meta']['WCDETAILS'], $recipe_id, $user_id, $args );

		$shipping_first_name   = Automator()->parse->text( $action_data['meta']['WC_SHP_FIRST_NAME'], $recipe_id, $user_id, $args );
		$shipping_last_name    = Automator()->parse->text( $action_data['meta']['WC_SHP_LAST_NAME'], $recipe_id, $user_id, $args );
		$shipping_email        = Automator()->parse->text( $action_data['meta']['WC_SHP_EMAIL'], $recipe_id, $user_id, $args );
		$shipping_company_name = Automator()->parse->text( $action_data['meta']['WC_SHP_COMPANYNAME'], $recipe_id, $user_id, $args );
		$shipping_phone        = Automator()->parse->text( $action_data['meta']['WC_SHP_PHONE'], $recipe_id, $user_id, $args );
		$shipping_address_1    = Automator()->parse->text( $action_data['meta']['WC_SHP_ADDRESSONE'], $recipe_id, $user_id, $args );
		$shipping_address_2    = Automator()->parse->text( $action_data['meta']['WC_SHP_ADDRESSTWO'], $recipe_id, $user_id, $args );
		$shipping_pincode      = Automator()->parse->text( $action_data['meta']['WC_SHP_POSTALCODE'], $recipe_id, $user_id, $args );
		$shipping_city         = Automator()->parse->text( $action_data['meta']['WC_SHP_CITY'], $recipe_id, $user_id, $args );
		$shipping_state        = Automator()->parse->text( $action_data['meta']['WC_SHP_STATE'], $recipe_id, $user_id, $args );
		$shipping_country      = Automator()->parse->text( $action_data['meta']['WC_SHP_COUNTRY'], $recipe_id, $user_id, $args );
		$order_note            = __( Automator()->parse->text( $action_data['meta']['WCORDERNOTE'], $recipe_id, $user_id, $args ), 'uncanny-automator-pro' );

		$address = array(
			'first_name' => $billing_first_name,
			'last_name'  => $billing_last_name,
			'company'    => $billing_company_name,
			'email'      => $billing_email,
			'phone'      => $billing_phone,
			'address_1'  => $billing_address_1,
			'address_2'  => $billing_address_2,
			'city'       => $billing_city,
			'state'      => $billing_state,
			'postcode'   => $billing_pincode,
			'country'    => $billing_country,
		);

		$shipping_address = array(
			'first_name' => $shipping_first_name,
			'last_name'  => $shipping_last_name,
			'company'    => $shipping_company_name,
			'email'      => $shipping_email,
			'phone'      => $shipping_phone,
			'address_1'  => $shipping_address_1,
			'address_2'  => $shipping_address_2,
			'city'       => $shipping_city,
			'state'      => $shipping_state,
			'postcode'   => $shipping_pincode,
			'country'    => $shipping_country,
		);

		$username           = $billing_email;
		$newly_created_user = false;

		$user_id = email_exists( $billing_email );
		if ( ! $user_id ) {
			$user_id = username_exists( $billing_email );
			if ( ! $user_id ) {
				$password   = wp_generate_password();
				$wc_user_id = wp_create_user( $username, $password, $billing_email );
				if ( ! is_wp_error( $wc_user_id ) ) {
					$user = get_user_by( 'id', $wc_user_id );
					$user->add_role( 'customer' );
					$newly_created_user = true;
				}
			} else {
				$wc_user_id = $user_id;
			}
		} else {
			$wc_user_id = $user_id;
		}

		$user       = get_user_by( 'id', $wc_user_id );
		$wc_user_id = ( isset( $user->ID ) ) ? $user->ID : $wc_user_id;
		// Now we create the order
		$order = wc_create_order( array( 'customer_id' => $wc_user_id ) );

		// Add the note
		$order->add_order_note( $order_note );

		if ( is_array( $products ) ) {
			foreach ( $products as $product ) {
				if ( isset( $product->WC_PRODUCT_ID ) ) {
					$order->add_product( wc_get_product( intval( $product->WC_PRODUCT_ID ) ), intval( $product->WC_PRODUCT_QTY ) );
				}
			}
		}
		$order->set_address( $address, 'billing' );

		if ( true === $newly_created_user ) {
			//user's billing data
			update_user_meta( $wc_user_id, 'billing_address_1', $order->get_billing_address_1() );
			update_user_meta( $wc_user_id, 'billing_address_2', $order->get_billing_address_2() );
			update_user_meta( $wc_user_id, 'billing_city', $order->get_billing_city() );
			update_user_meta( $wc_user_id, 'billing_company', $order->get_billing_company() );
			update_user_meta( $wc_user_id, 'billing_country', $order->get_billing_country() );
			update_user_meta( $wc_user_id, 'billing_email', $order->get_billing_email() );
			update_user_meta( $wc_user_id, 'billing_first_name', $order->get_billing_first_name() );
			update_user_meta( $wc_user_id, 'billing_last_name', $order->get_billing_last_name() );
			update_user_meta( $wc_user_id, 'billing_phone', $order->get_billing_phone() );
			update_user_meta( $wc_user_id, 'billing_postcode', $order->get_billing_postcode() );
			update_user_meta( $wc_user_id, 'billing_state', $order->get_billing_state() );
		}

		if ( 'YES' === $details_chk ) {
			$order->set_address( $address, 'shipping' );

			if ( true === $newly_created_user ) {
				//user's shipping data
				update_user_meta( $wc_user_id, 'shipping_address_1', $order->get_billing_address_1() );
				update_user_meta( $wc_user_id, 'shipping_address_2', $order->get_billing_address_2() );
				update_user_meta( $wc_user_id, 'shipping_city', $order->get_billing_city() );
				update_user_meta( $wc_user_id, 'shipping_company', $order->get_billing_company() );
				update_user_meta( $wc_user_id, 'shipping_country', $order->get_billing_country() );
				update_user_meta( $wc_user_id, 'shipping_email', $order->get_billing_email() );
				update_user_meta( $wc_user_id, 'shipping_first_name', $order->get_billing_first_name() );
				update_user_meta( $wc_user_id, 'shipping_last_name', $order->get_billing_last_name() );
				update_user_meta( $wc_user_id, 'shipping_phone', $order->get_billing_phone() );
				update_user_meta( $wc_user_id, 'shipping_postcode', $order->get_billing_postcode() );
				update_user_meta( $wc_user_id, 'shipping_state', $order->get_billing_state() );
			}
		} else {
			$order->set_address( $shipping_address, 'shipping' );

			if ( true === $newly_created_user ) {
				// user's shipping data
				update_user_meta( $wc_user_id, 'shipping_address_1', $order->get_shipping_address_1() );
				update_user_meta( $wc_user_id, 'shipping_address_2', $order->get_shipping_address_2() );
				update_user_meta( $wc_user_id, 'shipping_city', $order->get_shipping_city() );
				update_user_meta( $wc_user_id, 'shipping_company', $order->get_shipping_company() );
				update_user_meta( $wc_user_id, 'shipping_country', $order->get_shipping_country() );
				update_user_meta( $wc_user_id, 'shipping_first_name', $order->get_shipping_first_name() );
				update_user_meta( $wc_user_id, 'shipping_last_name', $order->get_shipping_last_name() );
				update_user_meta( $wc_user_id, 'shipping_method', $order->get_shipping_method() );
				update_user_meta( $wc_user_id, 'shipping_postcode', $order->get_shipping_postcode() );
				update_user_meta( $wc_user_id, 'shipping_state', $order->get_shipping_state() );
			}
		}

		if ( 'subscription' === $order_type ) {
			if ( is_array( $products ) ) {
				foreach ( $products as $product ) {
					if ( isset( $product->WC_PRODUCT_ID ) ) {
						$sub = wcs_create_subscription(
							array(
								'order_id'         => $order->get_id(),
								'status'           => 'pending',
								// Status should be initially set to pending to match how normal checkout process goes
								'billing_period'   => WC_Subscriptions_Product::get_period( intval( $product->WC_PRODUCT_ID ) ),
								'billing_interval' => WC_Subscriptions_Product::get_interval( intval( $product->WC_PRODUCT_ID ) ),
							)
						);

						if ( is_wp_error( $sub ) ) {
							$error_message                       = sprintf( __( 'Failed to create a subscription. %s', 'uncanny-automator-pro' ), $sub->get_error_message() );
							$action_data['do-nothing']           = true;
							$action_data['complete_with_errors'] = true;
							Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

							return;
						}

						$sub->add_product( wc_get_product( intval( $product->WC_PRODUCT_ID ) ), intval( $product->WC_PRODUCT_QTY ) );

						// Modeled after WC_Subscriptions_Cart::calculate_subscription_totals()
						$start_date = gmdate( 'Y-m-d H:i:s' );
						// Add product to subscription

						$dates = array(
							'trial_end'    => WC_Subscriptions_Product::get_trial_expiration_date( intval( $product->WC_PRODUCT_ID ), $start_date ),
							'next_payment' => WC_Subscriptions_Product::get_first_renewal_payment_date( intval( $product->WC_PRODUCT_ID ), $start_date ),
							'end'          => WC_Subscriptions_Product::get_expiration_date( intval( $product->WC_PRODUCT_ID ), $start_date ),
						);

						$sub->update_dates( $dates );
						$sub->add_order_note( $order_note );
						$sub->update_status( 'active' );
						$sub->calculate_totals();
					}
				}
			}

			$order->update_status( 'completed' );
			// Also update subscription status to active from pending (and add note)
			$order->calculate_totals();
		}

		if ( true === $payment_method ) {
			$order->set_payment_method( $payment_gateways );
		}

		if ( 'simple' === $order_type ) {
			$order->calculate_totals();
			$order->update_status( $order_status, $order_note, true );
		}

		if ( '' === $billing_country ) {
			$error_message                       = __( 'Failed to create order. Billing country is required.', 'uncanny-automator-pro' );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$order->save();

		if ( ! is_wp_error( $order ) ) {
			return true;
		} else {
			return false;
		}
	}

}
