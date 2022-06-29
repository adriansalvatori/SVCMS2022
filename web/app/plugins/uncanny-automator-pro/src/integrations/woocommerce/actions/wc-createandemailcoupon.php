<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WC_CREATEANDEMAILCOUPON
 * @package Uncanny_Automator_Pro
 */
class WC_CREATEANDEMAILCOUPON {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WC';

	/**
	 * @var string
	 */
	private $action_code;
	/**
	 * @var string
	 */
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'WCCREATEANDEMAILCOUPON';
		$this->action_meta = 'EMAILCOUPON';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/woocommerce/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - WooCommerce */
			'sentence'           => sprintf( __( 'Generate and email a coupon {{code:%1$s}} to the user', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - WooCommerce */
			'select_option_name' => __( 'Generate and email a coupon {{code}} to the user', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'create_and_email_coupon' ),
			'options_callback'   => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->action( $action );
	}

	/**
	 * @return array
	 */
	public function load_options() {
		global $uncanny_automator;
		$available_codes = apply_filters(
			'automator_woocommerce_coupon_tokens',
			array(
				'{{coupon_code}}',
				'{{coupon_amount}}',
				'{{coupon_expiry_date}}',
				'{{coupon_minimum_spend}}',
			)
		);
		$options_array   = array(
			'options_group' => array(
				$this->action_meta => array(
					$uncanny_automator->helpers->recipe->field->text_field( 'CODETITLE', __( 'Coupon code', 'uncanny-automator-pro' ), true, 'text', '', false, _x( 'A random string of 8 characters will be generated if coupon code field is left empty.', 'WooCommerce coupon', 'uncanny-automator-pro' ), $this->unique_coupon_id() ),
					$uncanny_automator->helpers->recipe->field->text_field( 'CODEDESCRIPTION', __( 'Description', 'uncanny-automator-pro' ), true, 'text', '', false ),
					$uncanny_automator->helpers->recipe->field->select_field(
						'DISCOUNTTYPE',
						__( 'Discount type', 'uncanny-automator-pro' ),
						array(
							'percent'       => __( 'Percentage discount', 'uncanny-automator-pro' ),
							'fixed_cart'    => __( 'Fixed cart discount', 'uncanny-automator-pro' ),
							'fixed_product' => __( 'Fixed product discount', 'uncanny-automator-pro' ),
						)
					),
					$uncanny_automator->helpers->recipe->field->text_field( 'COUPONAMOUNT', __( 'Coupon amount', 'uncanny-automator-pro' ), true, 'float', '', false, __( 'Value of the coupon.', 'uncanny-automator-pro' ), _x( '0.00', 'WooCommerce coupon', 'uncanny-automator-pro' ) ),
					$uncanny_automator->helpers->recipe->field->text_field( 'ALLOWFREESHIPPING', __( 'Allow free shipping', 'uncanny-automator-pro' ), true, 'checkbox', '', false, sprintf( __( 'Check this option if the coupon grants free shipping. A <a href="%s" target="_blank">free shipping method</a> must be enabled in your shipping zone and be set to require "a valid free shipping coupon" (see the "Free Shipping Requires" setting).', 'uncanny-automator-pro' ), 'https://docs.woocommerce.com/document/free-shipping/' ) ),
					$uncanny_automator->helpers->recipe->field->text_field( 'COUPONEXPIRYDATE', __( 'Coupon expiry date', 'uncanny-automator-pro' ), true, 'text', '', false, __( 'Enter a number of days until expiry or enter a specific date in YYYY-MM-DD format. The coupon will expire at 00:00:00 on the expiry date.', 'uncanny-automator-pro' ), '' ),
					$uncanny_automator->helpers->recipe->field->text_field( 'MINIMUMSPEND', __( 'Minimum spend', 'uncanny-automator-pro' ), true, 'int', '', false, __( 'This field allows you to set the minimum spend (subtotal) allowed to use the coupon.', 'uncanny-automator-pro' ), _x( '0.00', 'WooCommerce coupon', 'uncanny-automator-pro' ) ),
					$uncanny_automator->helpers->recipe->field->text_field( 'MAXIMUMSPEND', __( 'Maximum spend', 'uncanny-automator-pro' ), true, 'int', '', false, __( 'This field allows you to set the maximum spend (subtotal) allowed when using the coupon.', 'uncanny-automator-pro' ), _x( '0.00', 'WooCommerce coupon', 'uncanny-automator-pro' ) ),
					$uncanny_automator->helpers->recipe->field->text_field( 'INDIVIDUALUSEONLY', __( 'Individual use only', 'uncanny-automator-pro' ), true, 'checkbox', '', false, __( 'Check this box if the coupon cannot be used in conjunction with other coupons.', 'uncanny-automator-pro' ) ),
					$uncanny_automator->helpers->recipe->field->text_field( 'EXCLUDESALEITEMS', __( 'Exclude sale items', 'uncanny-automator-pro' ), true, 'checkbox', '', false, __( 'Check this box if the coupon should not apply to items on sale. Per-item coupons will only work if the item is not on sale. Per-cart coupons will only work if there are items in the cart that are not on sale.', 'uncanny-automator-pro' ) ),
					$uncanny_automator->helpers->recipe->woocommerce->pro->all_wc_products_multiselect(
						__( 'Products', 'uncanny-automator-pro' ),
						'PRODUCTSIDS',
						array(
							'description' => __( 'Products that the coupon will be applied to, or that need to be in the cart in order for the "Fixed cart discount" to be applied.', 'uncanny-automator-pro' ),
							'required'    => false,
						)
					),
					$uncanny_automator->helpers->recipe->woocommerce->pro->all_wc_products_multiselect(
						__( 'Exclude products', 'uncanny-automator-pro' ),
						'EXCLUDEPRODUCTSIDS',
						array(
							'description' => __( 'Products that the coupon will not be applied to, or that cannot be in the cart in order for the "Fixed cart discount" to be applied.', 'uncanny-automator-pro' ),
							'required'    => false,
						)
					),
					$uncanny_automator->helpers->recipe->woocommerce->pro->all_wc_product_categories(
						__( 'Product categories', 'uncanny-automator-pro' ),
						'PRODUCTCATS',
						array(
							'description'              => __( 'Product categories that the coupon will be applied to, or that need to be in the cart in order for the "Fixed cart discount" to be applied.', 'uncanny-automator-pro' ),
							'supports_multiple_values' => true,
							'required'                 => false,
						)
					),
					$uncanny_automator->helpers->recipe->woocommerce->pro->all_wc_product_categories(
						__( 'Exclude categories', 'uncanny-automator-pro' ),
						'EXCLUDEPRODUCTCATS',
						array(
							'description'              => __( 'Product categories that the coupon will not be applied to, or that cannot be in the cart in order for the "Fixed cart discount" to be applied.', 'uncanny-automator-pro' ),
							'supports_multiple_values' => true,
							'required'                 => false,
						)
					),
					$uncanny_automator->helpers->recipe->field->text_field( 'CUSTOMEREMAILS', __( 'Allowed emails', 'uncanny-automator-pro' ), true, 'text', '', false, __( 'List of allowed billing emails to check against when an order is placed. Separate email addresses with commas. You can also use an asterisk (*) to match parts of an email. For example "*@gmail.com" would match all gmail addresses.', 'uncanny-automator-pro' ) ),
					$uncanny_automator->helpers->recipe->field->text_field( 'USAGELIMITPERCOUPON', __( 'Usage limit per coupon', 'uncanny-automator-pro' ), true, 'int', '', false, __( 'How many times this coupon can be used before it is void.', 'uncanny-automator-pro' ), __( 'Unlimited usage', 'uncanny-automator-pro' ) ),
					$uncanny_automator->helpers->recipe->field->text_field( 'LIMITUSAGEXITEMS', __( 'Limit usage to X items', 'uncanny-automator-pro' ), true, 'int', '', false, __( 'The maximum number of individual items this coupon can apply to when using product discounts. Leave blank to apply to all qualifying items in cart.', 'uncanny-automator-pro' ), __( 'Unlimited usage', 'uncanny-automator-pro' ) ),
					$uncanny_automator->helpers->recipe->field->text_field( 'USAGELIMITPERUSER', __( 'Usage limit per user', 'uncanny-automator-pro' ), true, 'int', '', false, __( 'How many times this coupon can be used by an individual user. Uses billing email for guests, and user ID for logged in users.', 'uncanny-automator-pro' ), __( 'Unlimited usage', 'uncanny-automator-pro' ) ),
					$uncanny_automator->helpers->recipe->field->text_field( 'EMAILFROM', __( 'From', 'uncanny-automator-pro' ), true, 'email', '{{admin_email}}', true, '' ),
					$uncanny_automator->helpers->recipe->field->text_field( 'EMAILTO', __( 'To:', 'uncanny-automator-pro' ), true, 'email', '{{user_email}}', true, esc_html__( 'Separate multiple email addresses with a comma', 'uncanny-automator-pro' ) ),
					$uncanny_automator->helpers->recipe->field->text_field( 'EMAILCC', __( 'CC', 'uncanny-automator-pro' ), true, 'email', '', false ),
					$uncanny_automator->helpers->recipe->field->text_field( 'EMAILBCC', __( 'BCC', 'uncanny-automator-pro' ), true, 'email', '', false ),
					$uncanny_automator->helpers->recipe->field->text_field( 'EMAILSUBJECT', __( 'Subject', 'uncanny-automator-pro' ), true ),

					// Email Content Field.
					$uncanny_automator->helpers->recipe->field->text(
						array(
							'option_code'               => 'EMAILBODY',
							/* translators: Email field */
							'label'                     => esc_attr__( 'Email body', 'uncanny-automator' ),
							'input_type'                => 'textarea',
							'supports_fullpage_editing' => true,
							'default'                   => __(
								'Hi {{user_firstname}},
						Use coupon code {{coupon_code}} for a discount on your next purchase!
						The {{site_name}} team',
								'uncanny-automator-pro'
							),
							'description'               => sprintf( __( 'Use following tokens in email: %s', 'uncanny-automator-pro' ), join( '<br />', $available_codes ) ),
						)
					),

				),
			),
		);

		return Automator()->utilities->keep_order_of_options( $options_array );
	}

	/**
	 * @param int $l
	 *
	 * @return string
	 */
	public function unique_coupon_id( $l = 8 ) {
		return strtoupper( substr( str_shuffle( str_repeat( '0123456789abcdefghijklmnopqrstuvwxyz', $l ) ), 0, $l ) );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function create_and_email_coupon( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$coupon_code = $uncanny_automator->parse->text( $action_data['meta']['CODETITLE'], $recipe_id, $user_id, $args );
		if ( empty( $coupon_code ) ) {
			$coupon_code = $this->unique_coupon_id();
		}
		$coupon_description         = $uncanny_automator->parse->text( $action_data['meta']['CODEDESCRIPTION'], $recipe_id, $user_id, $args );
		$discount_type              = $uncanny_automator->parse->text( $action_data['meta']['DISCOUNTTYPE'], $recipe_id, $user_id, $args );
		$coupon_amount              = $uncanny_automator->parse->text( $action_data['meta']['COUPONAMOUNT'], $recipe_id, $user_id, $args );
		$allow_free_shipping        = $uncanny_automator->parse->text( $action_data['meta']['ALLOWFREESHIPPING'], $recipe_id, $user_id, $args );
		$coupon_expiry_date         = $uncanny_automator->parse->text( $action_data['meta']['COUPONEXPIRYDATE'], $recipe_id, $user_id, $args );
		$minimum_spend              = $uncanny_automator->parse->text( $action_data['meta']['MINIMUMSPEND'], $recipe_id, $user_id, $args );
		$maximum_spend              = $uncanny_automator->parse->text( $action_data['meta']['MAXIMUMSPEND'], $recipe_id, $user_id, $args );
		$individual_use             = $uncanny_automator->parse->text( $action_data['meta']['INDIVIDUALUSEONLY'], $recipe_id, $user_id, $args );
		$exclude_sale_items         = $uncanny_automator->parse->text( $action_data['meta']['EXCLUDESALEITEMS'], $recipe_id, $user_id, $args );
		$products_ids               = json_decode( $action_data['meta']['PRODUCTSIDS'] );
		$exclude_product_ids        = json_decode( $action_data['meta']['EXCLUDEPRODUCTSIDS'] );
		$product_categories         = json_decode( $action_data['meta']['PRODUCTCATS'] );
		$exclude_product_categories = json_decode( $action_data['meta']['EXCLUDEPRODUCTCATS'] );
		$customer_emails            = $uncanny_automator->parse->text( $action_data['meta']['CUSTOMEREMAILS'], $recipe_id, $user_id, $args );
		$usage_limit_per_coupon     = $uncanny_automator->parse->text( $action_data['meta']['USAGELIMITPERCOUPON'], $recipe_id, $user_id, $args );
		$limit_usage_to_x_items     = $uncanny_automator->parse->text( $action_data['meta']['LIMITUSAGEXITEMS'], $recipe_id, $user_id, $args );
		$usage_limit_per_user       = $uncanny_automator->parse->text( $action_data['meta']['USAGELIMITPERUSER'], $recipe_id, $user_id, $args );

		$allow_free_shipping = 'true' === $allow_free_shipping ? 'yes' : 'no';
		$individual_use      = 'true' === $individual_use ? 'yes' : 'no';
		$exclude_sale_items  = 'true' === $exclude_sale_items ? 'yes' : 'no';
		// Check if date is a number or a date string
		if ( is_numeric( $coupon_expiry_date ) ) {
			$coupon_expiry_date = absint( $coupon_expiry_date );
			$coupon_expiry_date = date( 'Y-m-d', strtotime( '+' . $coupon_expiry_date . 'Days' ) );
		}
		$coupon_expiry_date         = ! empty( $coupon_expiry_date ) ? date( 'Y-m-d', strtotime( $coupon_expiry_date ) ) : '';
		$coupon_amount              = ! empty( $coupon_amount ) ? wc_format_decimal( $coupon_amount ) : '0';
		$minimum_spend              = ! empty( $minimum_spend ) ? wc_format_decimal( $minimum_spend ) : '';
		$maximum_spend              = ! empty( $maximum_spend ) ? wc_format_decimal( $maximum_spend ) : '';
		$products_ids               = ! empty( $products_ids ) ? implode( ',', array_map( 'intval', $products_ids ) ) : '';
		$exclude_product_ids        = ! empty( $exclude_product_ids ) ? implode( ',', array_map( 'intval', $exclude_product_ids ) ) : '';
		$product_categories         = ! empty( $product_categories ) ? array_filter( array_map( 'intval', $product_categories ) ) : '';
		$exclude_product_categories = ! empty( $exclude_product_categories ) ? array_filter( array_map( 'intval', $exclude_product_categories ) ) : '';
		$customer_emails            = ! empty( $customer_emails ) ? array_filter( array_map( 'sanitize_email', explode( ',', $customer_emails ) ) ) : '';
		$usage_limit_per_coupon     = ! empty( $usage_limit_per_coupon ) ? absint( $usage_limit_per_coupon ) : '';
		$limit_usage_to_x_items     = ! empty( $limit_usage_to_x_items ) ? absint( $limit_usage_to_x_items ) : '';
		$usage_limit_per_user       = ! empty( $usage_limit_per_user ) ? absint( $usage_limit_per_user ) : '';
		$coupon                     = array(
			'post_title'   => $coupon_code,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_author'  => $user_id,
			'post_type'    => 'shop_coupon',
			'post_excerpt' => $coupon_description,
		);

		$coupon_id = wp_insert_post( $coupon, true );

		if ( ! $coupon_id ) {
			if ( is_wp_error( $coupon_id ) ) {
				$error_message = $coupon_id->get_error_message();
			} else {
				$error_message = __( 'Unknown error while creating coupon.', 'uncanny-automator-pro' );
			}
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		update_post_meta( $coupon_id, 'discount_type', $discount_type );
		update_post_meta( $coupon_id, 'coupon_amount', $coupon_amount );
		update_post_meta( $coupon_id, 'free_shipping', $allow_free_shipping );
		update_post_meta( $coupon_id, 'expiry_date', $coupon_expiry_date );
		update_post_meta( $coupon_id, 'date_expires', $coupon_expiry_date );
		update_post_meta( $coupon_id, 'minimum_amount', $minimum_spend );
		update_post_meta( $coupon_id, 'maximum_amount', $maximum_spend );
		update_post_meta( $coupon_id, 'individual_use', $individual_use );
		update_post_meta( $coupon_id, 'exclude_sale_items', $exclude_sale_items );
		update_post_meta( $coupon_id, 'product_ids', $products_ids );
		update_post_meta( $coupon_id, 'exclude_product_ids', $exclude_product_ids );
		update_post_meta( $coupon_id, 'product_categories', $product_categories );
		update_post_meta( $coupon_id, 'exclude_product_categories', $exclude_product_categories );
		update_post_meta( $coupon_id, 'customer_email', $customer_emails );
		update_post_meta( $coupon_id, 'usage_limit', $usage_limit_per_coupon );
		update_post_meta( $coupon_id, 'limit_usage_to_x_items', $limit_usage_to_x_items );
		update_post_meta( $coupon_id, 'usage_limit_per_user', $usage_limit_per_user );

		$to         = $uncanny_automator->parse->text( $action_data['meta']['EMAILTO'], $recipe_id, $user_id, $args );
		$from       = $uncanny_automator->parse->text( $action_data['meta']['EMAILFROM'], $recipe_id, $user_id, $args );
		$cc         = $uncanny_automator->parse->text( $action_data['meta']['EMAILCC'], $recipe_id, $user_id, $args );
		$bcc        = $uncanny_automator->parse->text( $action_data['meta']['EMAILBCC'], $recipe_id, $user_id, $args );
		$subject    = $action_data['meta']['EMAILSUBJECT'];
		$subject    = str_ireplace( '{{coupon_code}}', get_the_title( $coupon_id ), $subject );
		$subject    = str_ireplace( '{{coupon_amount}}', $coupon_amount, $subject );
		$subject    = str_ireplace( '{{coupon_expiry_date}}', $coupon_expiry_date, $subject );
		$subject    = str_ireplace( '{{coupon_minimum_spend}}', $minimum_spend, $subject );
		$subject    = apply_filters( 'automator_woocommerce_coupon_email_subject', $subject, $coupon_id );
		$subject    = $uncanny_automator->parse->text( $subject, $recipe_id, $user_id, $args );
		$email_body = $action_data['meta']['EMAILBODY'];
		$email_body = str_ireplace( '{{coupon_code}}', get_the_title( $coupon_id ), $email_body );
		$email_body = str_ireplace( '{{coupon_amount}}', $coupon_amount, $email_body );
		$email_body = str_ireplace( '{{coupon_expiry_date}}', $coupon_expiry_date, $email_body );
		$email_body = str_ireplace( '{{coupon_minimum_spend}}', $minimum_spend, $email_body );
		$email_body = apply_filters( 'automator_woocommerce_coupon_email_body', $email_body, $coupon_id );
		$email_body = $uncanny_automator->parse->text( $email_body, $recipe_id, $user_id, $args );
		$email_body = do_shortcode( $email_body );

		$headers[] = 'From: <' . $from . '>';

		if ( ! empty( $cc ) ) {
			$headers[] = 'Cc: ' . $cc;
		}

		if ( ! empty( $bcc ) ) {
			$headers[] = 'Bcc: ' . $bcc;
		}

		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers   = apply_filters( 'automator_pro_woo_create_and_email_coupon', $headers, $this );
		//$email_body = wpautop( $email_body );
		$mailed = wp_mail( $to, $subject, $email_body, $headers );

		if ( ! $mailed ) {
			$error_message                       = $uncanny_automator->error_message->get( 'email-failed' );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}

}
