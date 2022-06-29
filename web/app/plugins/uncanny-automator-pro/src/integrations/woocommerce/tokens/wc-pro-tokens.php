<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Wc_Tokens;
use WC_Order;
use WC_Order_Item_Product;

/**
 * Class Wc_Pro_Tokens
 *
 * @package Uncanny_Automator
 */
class Wc_Pro_Tokens extends Wc_Tokens {
	/**
	 * @var array
	 */
	public $possible_order_fields_pro = array();

	/**
	 * Wc_Pro_Tokens constructor.
	 */
	public function __construct() {
		$this->possible_order_fields_pro = array(
			'billing_first_name'   => esc_attr__( 'Billing first name', 'uncanny-automator' ),
			'billing_last_name'    => esc_attr__( 'Billing last name', 'uncanny-automator' ),
			'billing_company'      => esc_attr__( 'Billing company', 'uncanny-automator' ),
			'billing_country'      => esc_attr__( 'Billing country', 'uncanny-automator' ),
			'billing_address_1'    => esc_attr__( 'Billing address line 1', 'uncanny-automator' ),
			'billing_address_2'    => esc_attr__( 'Billing address line 2', 'uncanny-automator' ),
			'billing_city'         => esc_attr__( 'Billing city', 'uncanny-automator' ),
			'billing_state'        => esc_attr__( 'Billing state', 'uncanny-automator' ),
			'billing_postcode'     => esc_attr__( 'Billing postcode', 'uncanny-automator' ),
			'billing_phone'        => esc_attr__( 'Billing phone', 'uncanny-automator' ),
			'billing_email'        => esc_attr__( 'Billing email', 'uncanny-automator' ),
			'shipping_first_name'  => esc_attr__( 'Shipping first name', 'uncanny-automator' ),
			'shipping_last_name'   => esc_attr__( 'Shipping last name', 'uncanny-automator' ),
			'shipping_company'     => esc_attr__( 'Shipping company', 'uncanny-automator' ),
			'shipping_country'     => esc_attr__( 'Shipping country', 'uncanny-automator' ),
			'shipping_address_1'   => esc_attr__( 'Shipping address line 1', 'uncanny-automator' ),
			'shipping_address_2'   => esc_attr__( 'Shipping address line 2', 'uncanny-automator' ),
			'shipping_city'        => esc_attr__( 'Shipping city', 'uncanny-automator' ),
			'shipping_state'       => esc_attr__( 'Shipping state', 'uncanny-automator' ),
			'shipping_postcode'    => esc_attr__( 'Shipping postcode', 'uncanny-automator' ),
			'order_id'             => esc_attr__( 'Order ID', 'uncanny-automator' ),
			'order_comments'       => esc_attr__( 'Order comments', 'uncanny-automator' ),
			'order_total'          => esc_attr__( 'Order total', 'uncanny-automator' ),
			'order_total_raw'      => esc_attr__( 'Order total (unformatted)', 'uncanny-automator' ),
			'order_status'         => esc_attr__( 'Order status', 'uncanny-automator' ),
			'order_subtotal'       => esc_attr__( 'Order subtotal', 'uncanny-automator' ),
			'order_subtotal_raw'   => esc_attr__( 'Order subtotal (unformatted)', 'uncanny-automator' ),
			'order_tax'            => esc_attr__( 'Order tax', 'uncanny-automator' ),
			'order_tax_raw'        => esc_attr__( 'Order tax (unformatted)', 'uncanny-automator' ),
			'order_discounts'      => esc_attr__( 'Order discounts', 'uncanny-automator' ),
			'order_discounts_raw'  => esc_attr__( 'Order discounts (unformatted)', 'uncanny-automator' ),
			'order_coupons'        => esc_attr__( 'Order coupons', 'uncanny-automator' ),
			'order_products'       => esc_attr__( 'Order products', 'uncanny-automator' ),
			'order_products_qty'   => esc_attr__( 'Order products and quantity', 'uncanny-automator' ),
			'payment_method'       => esc_attr__( 'Payment method', 'uncanny-automator' ),
			'order_products_links' => esc_attr__( 'Order products links', 'uncanny-automator' ),
			'order_summary'        => esc_attr__( 'Order summary', 'uncanny-automator' ),
		);

		add_action( 'uap_wc_trigger_save_meta', array( $this, 'uap_wc_trigger_save_meta_func' ), 40, 4 );
		add_action( 'uap_wc_order_item_meta', array( $this, 'uap_wc_order_item_meta_func' ), 40, 4 );

		add_action(
			'uap_wc_trigger_save_product_meta',
			array(
				$this,
				'uap_wc_trigger_save_product_meta_func',
			),
			40,
			4
		);

		add_filter(
			'automator_maybe_trigger_wc_wooprodcat_tokens',
			array(
				$this,
				'wc_wooprodcat_possible_tokens',
			),
			20,
			2
		);

		add_filter(
			'automator_maybe_trigger_wc_woovariproduct_tokens',
			array(
				$this,
				'wc_wooprodcat_possible_tokens',
			),
			20,
			2
		);

		add_filter(
			'automator_maybe_trigger_wc_wooprodtag_tokens',
			array(
				$this,
				'wc_wooprodcat_possible_tokens',
			),
			20,
			2
		);

		add_filter(
			'automator_maybe_trigger_wc_wcprodreview_tokens',
			array(
				$this,
				'wc_wcprodreview_possible_tokens',
			),
			200,
			2
		);

		add_filter(
			'automator_maybe_trigger_wc_wooproduct_tokens',
			array(
				$this,
				'wc_wcprodreview_possible_tokens',
			),
			200,
			2
		);

		//Adding WC tokens
		add_filter(
			'automator_maybe_trigger_wc_wcshipstationproductshipped_tokens',
			array(
				$this,
				'wc_order_possible_tokens',
			),
			20,
			2
		);
		add_filter(
			'automator_maybe_trigger_wc_wcshipstationordertotalshipped_tokens',
			array(
				$this,
				'wc_order_possible_tokens',
			),
			20,
			2
		);

		add_filter(
			'automator_maybe_trigger_wc_woopaymentgateway_tokens',
			array(
				$this,
				'wc_wooprodcat_possible_tokens',
			),
			20,
			2
		);

		add_filter(
			'automator_maybe_trigger_wc_anonorderitemcreated_tokens',
			array(
				$this,
				'wc_wooorderitemadded_possible_tokens',
			),
			20,
			2
		);

		add_filter(
			'automator_maybe_trigger_wc_addedtocart_tokens',
			array(
				$this,
				'wc_addedtocart_possible_tokens',
			),
			20,
			2
		);

		add_filter( 'automator_maybe_parse_token', array( $this, 'wc_addedtocart_tokens_pro' ), 26, 6 );

		add_filter( 'automator_maybe_parse_token', array( $this, 'wc_ordertotal_tokens_pro' ), 26, 6 );
		add_filter( 'automator_maybe_parse_token', array( $this, 'parse_subscription_tokens_pro' ), 36, 6 );
	}

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	public function wc_wcprodreview_possible_tokens( $tokens = array(), $args = array() ) {
		if ( ! automator_pro_do_identify_tokens() ) {
			return $tokens;
		}
		$trigger_meta = isset( $args['triggers_meta'] ) ? $args['triggers_meta'] : '';
		if ( isset( $trigger_meta['code'] ) && ( 'WCPRODREVIEW' === (string) $trigger_meta['code'] || 'WCPRODREVIEWAPPRVD' === (string) $trigger_meta['code'] || 'WCPRODREVIEWRATING' === (string) $trigger_meta['code'] ) ) {
			$tokens   = array();
			$tokens[] = array(
				'tokenId'         => 'product_review',
				'tokenName'       => __( 'Product review', 'uncanny-automator-pro' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta['code'],
			);

			if ( 'WCPRODREVIEWRATING' !== (string) $trigger_meta['code'] ) {
				$tokens[] = array(
					'tokenId'         => 'product_rating',
					'tokenName'       => __( 'Product rating', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => $trigger_meta['code'],
				);
			}
		}

		return $tokens;
	}

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	public function wc_wooprodcat_possible_tokens( $tokens = array(), $args = array() ) {
		if ( ! automator_pro_do_identify_tokens() ) {
			return $tokens;
		}

		return $this->wc_possible_tokens( $tokens, $args, 'product' );
	}

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	public function wc_wooorderitemadded_possible_tokens( $tokens = array(), $args = array() ) {
		if ( ! automator_pro_do_identify_tokens() ) {
			return $tokens;
		}
		$trigger_meta     = $args['meta'];
		$trigger_specific = array(
			array(
				'tokenId'         => 'item_total',
				'tokenName'       => __( 'Product total', 'uncanny-automator-pro' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			),
			array(
				'tokenId'         => 'product_price',
				'tokenName'       => __( 'Product price', 'uncanny-automator-pro' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			),
			array(
				'tokenId'         => 'product_sale_price',
				'tokenName'       => __( 'Product sale price', 'uncanny-automator-pro' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			),
			array(
				'tokenId'         => 'product_sku',
				'tokenName'       => __( 'Product SKU', 'uncanny-automator-pro' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			),
		);

		return array_merge( $trigger_specific, $tokens );
	}

	/**
	 * @param array $tokens
	 * @param array $args
	 * @param string $type
	 *
	 * @return array
	 */
	public function wc_possible_tokens( $tokens = array(), $args = array(), $type = 'order' ) {
		if ( ! automator_pro_do_identify_tokens() ) {
			return $tokens;
		}

		$fields          = array();
		$trigger_meta    = $args['meta'];
		$possible_tokens = apply_filters( 'automator_woocommerce_possible_tokens', $this->possible_order_fields_pro );

		if ( 'WOOPAYMENTGATEWAY' === $trigger_meta ) {
			unset( $possible_tokens['payment_method'] );
		}

		foreach ( $possible_tokens as $token_id => $input_title ) {
			if ( 'billing_email' === (string) $token_id || 'shipping_email' === (string) $token_id ) {
				$input_type = 'email';
			} else {
				$input_type = 'text';
			}
			$fields[] = array(
				'tokenId'         => $token_id,
				'tokenName'       => $input_title,
				'tokenType'       => $input_type,
				'tokenIdentifier' => $trigger_meta,
			);
		}
		$tokens = array_merge( $tokens, $fields );

		return $tokens;
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 * @param $replace_args
	 *
	 * @return string|null
	 */
	public function wc_ordertotal_tokens_pro( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		$to_match = array(
			'WOOQNTY',
			'WCQNTYPURCHPROD',
			'WCPRODREVIEW',
			'WCPRODREVIEWRATING',
			'WCPRODREVIEWAPPRVD',
			'WOOPRODCAT',
			'WOOPRODTAG',
			'WCPURCHPRODUCTINCAT',
			'ANONWCPURCHPRODUCTINCAT',
			'WCPURCHPRODINCAT',
			'WCPURCHPRODUCTINTAG',
			'WCPURCHPRODINTAG',
			'WOOVARIPRODUCT',
			'WCPURCHVARIPROD',
			'WOORDERTOTAL',
			'WOOPRODUCT',
			'WCORDERSTATUS',
			'WCORDERCOMPLETE',
			'WCSHIPSTATIONPRODUCTSHIPPED',
			'WCSHIPSTATIONORDERTOTALSHIPPED',
			'TRIGGERCOND',
			'WOOPAYMENTGATEWAY',
			'NUMBERCOND',
			'WOOORDERQTYTOTAL',
			'ANONORDERITEMCREATED',
		);

		if ( $pieces ) {
			if ( array_intersect( $to_match, $pieces ) ) {
				$value = $this->replace_values_pro( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args );
			}
		}
		$to_match = array(
			'WCPRODREVIEW',
			'WCPRODREVIEWAPPRVD',
			'WCPRODREVIEWRATING',
		);
		if ( in_array( 'WOOPRODUCT', $pieces, false ) ) {
			$to_match[] = 'WOOPRODUCT';
		}
		if ( $pieces ) {
			if ( array_intersect( $to_match, $pieces ) ) {
				$value = $this->replace_review_values_pro( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args );
			}
		}
		if ( in_array( 'ANONORDERITEMCREATED', $pieces, true ) ) {
			$to_match = array(
				'ANONORDERITEMCREATED',
				'WOOPRODUCT',
			);
		}

		if ( $pieces ) {
			if ( array_intersect( $to_match, $pieces ) ) {
				$value = $this->replace_item_created_values_pro( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args );
			}
		}

		return $value;
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 * @param $replace_args
	 *
	 * @return float|int|mixed|string|null
	 */
	public function parse_subscription_tokens_pro( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		$to_match = array(
			'WCSUBSCRIPTIONSTATUSCHANGED',
			'WCSUBSCRIPTIONSUBSCRIBE',
			'WCSUBSCRIPTIONVARIATION',
			'WCSPECIFICSUBVARIATION',
			'WCVARIATIONSUBSCRIPTIONEXPIRED',
			'WCVARIATIONSUBSCRIPTIONSTATUSCHANGED',
			'WOOSUBSCRIPTIONSTATUS',
			'WOOSUBSCRIPTIONSTATUS_ID',
			'WOOSUBSCRIPTIONSTATUS_END_DATE',
			'WOOSUBSCRIPTIONSTATUS_TRIAL_END_DATE',
			'WOOSUBSCRIPTIONS_SUBSCRIPTION_ID',
			'WOOSUBSCRIPTIONS_SUBSCRIPTION_STATUS',
			'WOOSUBSCRIPTIONS_SUBSCRIPTION_END_DATE',
			'WOOSUBSCRIPTIONS_SUBSCRIPTION_TRIAL_END_DATE',
			'WOOSUBSCRIPTIONS_SUBSCRIPTION_NEXT_PAYMENT_DATE',
			'WOOSUBSCRIPTIONS_THUMB_URL',
			'WOOSUBSCRIPTIONS_THUMB_ID',
			'WCSUBSCRIPTIONTRIALEXPIRES',
			'WCVARIATIONSUBSCRIPTIONRENEWED',
			'WCVARIATIONSUBSCRIPTIONTRIALEXPIRES',
		);

		if ( $pieces ) {
			if ( array_intersect( $to_match, $pieces ) ) {
				$value = $this->replace_wcs_values_pro( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args );
			}
		}

		return $value;
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 * @param $replace_args
	 *
	 * @return mixed|string|null
	 */
	public function replace_values_pro( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		global $uncanny_automator;
		$trigger_meta         = $pieces[1];
		$parse                = $pieces[2];
		$multi_line_separator = apply_filters( 'automator_woo_multi_item_separator', ' | ', $pieces );
		$recipe_log_id        = isset( $replace_args['recipe_log_id'] ) ? (int) $replace_args['recipe_log_id'] : $uncanny_automator->maybe_create_recipe_log_entry( $recipe_id, $user_id )['recipe_log_id'];
		if ( ! $trigger_data || ! $recipe_log_id ) {
			return $value;
		}
		if ( ! is_array( $trigger_data ) ) {
			return $value;
		}
		foreach ( $trigger_data as $trigger ) {
			if ( ! is_array( $trigger ) || empty( $trigger ) ) {
				continue;
			}
			if ( ! key_exists( $trigger_meta, $trigger['meta'] ) && ( ! isset( $trigger['meta']['code'] ) && $trigger_meta !== $trigger['meta']['code'] ) ) {
				continue;
			}
			$trigger_id     = $trigger['ID'];
			$trigger_log_id = $replace_args['trigger_log_id'];
			$order_id       = $uncanny_automator->helpers->recipe->get_form_data_from_trigger_meta( 'order_id', $trigger_id, $trigger_log_id, $user_id );
			if ( empty( $order_id ) ) {
				continue;
			}
			$order = wc_get_order( $order_id );
			if ( ! $order instanceof WC_Order ) {
				continue;
			}
			switch ( $parse ) {
				case 'order_id':
					$value = $order_id;
					break;
				case 'WCORDERSTATUS':
					$value = $order->get_status();
					break;
				case 'WOOPAYMENTGATEWAY':
					$value = $order->get_payment_method_title();
					break;
				case 'WOOPRODCAT':
				case 'WCPURCHPRODINCAT':
				case 'WCPURCHPRODUCTINCAT':
				case 'ANONWCPURCHPRODUCTINCAT':
					$value_to_match = isset( $trigger['meta'][ $parse ] ) ? $trigger['meta'][ $parse ] : - 1;
					$value          = $this->get_woo_product_categories_from_items( $order, $value_to_match );
					break;
				case 'WOOPRODTAG':
				case 'WCPURCHPRODINTAG':
				case 'WCPURCHPRODUCTINTAG':
					$value_to_match = isset( $trigger['meta'][ $parse ] ) ? $trigger['meta'][ $parse ] : - 1;
					$value          = $this->get_woo_product_tags_from_items( $order, $value_to_match );
					break;
				case 'WOOPRODUCT':
				case 'WOOVARIABLEPRODUCTS':
					$value_to_match = isset( $trigger['meta'][ $parse ] ) ? $trigger['meta'][ $parse ] : - 1;
					$product_ids    = array_map( 'intval', explode( ',', $this->get_woo_product_ids_from_items( $order, $value_to_match ) ) );
					$value          = '';
					if ( ! empty( $product_ids ) ) {
						$product_names = array();
						foreach ( $product_ids as $woo_product_id ) {
							$parent_product = get_post( $woo_product_id );

							if ( $parent_product->post_parent ) {
								$parent_product = get_post( $parent_product->post_parent );
							}

							$product_names[] = $parent_product->post_title;
						}
						$value = join( ',', $product_names );
					}
					break;
				case 'WOOVARIPRODUCT':
					$value_to_match = isset( $trigger['meta'][ $parse ] ) ? $trigger['meta'][ $parse ] : - 1;
					$value          = $this->get_woo_product_names_from_items( $order, $value_to_match );
					break;
				case 'WOOQNTY':
					$value_to_match = isset( $trigger['meta'][ $parse ] ) ? $trigger['meta'][ $parse ] : - 1;
					$value          = $value_to_match;
					break;
				case 'WOOPRODTAG_ID':
					$value_to_match = isset( $trigger['meta'][ $parse ] ) ? $trigger['meta'][ $parse ] : - 1;
					$value          = $this->get_woo_terms_ids_from_items( $order, $value_to_match, 'product_tag' );
					break;
				case 'WOOPRODCAT_ID':
					$value_to_match = isset( $trigger['meta'][ $parse ] ) ? $trigger['meta'][ $parse ] : - 1;
					$value          = $this->get_woo_terms_ids_from_items( $order, $value_to_match, 'product_cat' );
					break;
				case 'WOOPRODTAG_URL':
					$value_to_match = isset( $trigger['meta'][ $parse ] ) ? $trigger['meta'][ $parse ] : - 1;
					$value          = $this->get_woo_terms_links_from_items( $order, $value_to_match, 'product_tag' );
					break;
				case 'WOOPRODCAT_URL':
					$value_to_match = isset( $trigger['meta'][ $parse ] ) ? $trigger['meta'][ $parse ] : - 1;
					$value          = $this->get_woo_terms_links_from_items( $order, $value_to_match, 'product_cat' );
					break;
				case 'WOOPRODUCT_ID':
				case 'WOOVARIABLEPRODUCTS_ID':
					$value_to_match = isset( $trigger['meta'][ $parse ] ) ? $trigger['meta'][ $parse ] : - 1;
					$value          = $this->get_woo_product_ids_from_items( $order, $value_to_match );
					break;
				case 'WOOPRODUCT_URL':
				case 'WOOVARIABLEPRODUCTS_URL':
					$value_to_match = isset( $trigger['meta'][ $parse ] ) ? $trigger['meta'][ $parse ] : - 1;
					$value          = $this->get_woo_product_urls_from_items( $order, $value_to_match );
					break;
				case 'WOOPRODUCT_THUMB_ID':
				case 'WOOVARIABLEPRODUCTS_THUMB_ID':
					$value_to_match = isset( $trigger['meta'][ $parse ] ) ? $trigger['meta'][ $parse ] : - 1;
					$value          = $this->get_woo_product_image_ids_from_items( $order, $value_to_match );
					break;
				case 'WOOPRODUCT_THUMB_URL':
				case 'WOOVARIABLEPRODUCTS_THUMB_URL':
					$value_to_match = isset( $trigger['meta'][ $parse ] ) ? $trigger['meta'][ $parse ] : - 1;
					$value          = $this->get_woo_product_image_urls_from_items( $order, $value_to_match );
					break;
				case 'WOORDERTOTAL':
					$value = wc_price( $order->get_total() );
					break;
				case 'TRIGGERCOND':
					$trigger_condition_labels = $uncanny_automator->helpers->recipe->woocommerce->pro->get_trigger_condition_labels();

					$value_to_match = isset( $trigger['meta'][ $parse ] ) ? $trigger['meta'][ $parse ] : - 1;
					$value          = $trigger_condition_labels[ $value_to_match ];
					break;
				case 'WOOORDERQTYTOTAL':
					$value = isset( $trigger['meta'][ $parse ] ) ? $trigger['meta'][ $parse ] : '';
					break;
				case 'NUMBERCOND':
					$val = isset( $trigger['meta'][ $parse ] ) ? $trigger['meta'][ $parse ] : '';
					switch ( $val ) {
						case '<':
							$value = esc_attr__( 'less than', 'uncanny-automator' );
							break;
						case '>':
							$value = esc_attr__( 'greater than', 'uncanny-automator' );
							break;
						case '=':
							$value = esc_attr__( 'equal to', 'uncanny-automator' );
							break;
						case '!=':
							$value = esc_attr__( 'not equal to', 'uncanny-automator' );
							break;
						case '>=':
							$value = esc_attr__( 'greater or equal to', 'uncanny-automator' );
							break;
						case '<=':
							$value = esc_attr__( 'less or equal to', 'uncanny-automator' );
							break;
						default:
							$value = '';
							break;
					}
					break;
				case 'NUMTIMES':
					$value = absint( $replace_args['run_number'] );
					break;
				case 'billing_first_name':
					$value = $order->get_billing_first_name();
					break;
				case 'billing_last_name':
					$value = $order->get_billing_last_name();
					break;
				case 'billing_company':
					$value = $order->get_billing_company();
					break;
				case 'billing_country':
					$value = $order->get_billing_country();
					break;
				case 'billing_address_1':
					$value = $order->get_billing_address_1();
					break;
				case 'billing_address_2':
					$value = $order->get_billing_address_2();
					break;
				case 'billing_city':
					$value = $order->get_billing_city();
					break;
				case 'billing_state':
					$value = $order->get_billing_state();
					break;
				case 'billing_postcode':
					$value = $order->get_billing_postcode();
					break;
				case 'billing_phone':
					$value = $order->get_billing_phone();
					break;
				case 'billing_email':
					$value = $order->get_billing_email();
					break;
				case 'shipping_first_name':
					$value = $order->get_shipping_first_name();
					break;
				case 'shipping_last_name':
					$value = $order->get_shipping_last_name();
					break;
				case 'shipping_company':
					$value = $order->get_shipping_company();
					break;
				case 'shipping_country':
					$value = $order->get_shipping_country();
					break;
				case 'shipping_address_1':
					$value = $order->get_shipping_address_1();
					break;
				case 'shipping_address_2':
					$value = $order->get_shipping_address_2();
					break;
				case 'shipping_city':
					$value = $order->get_shipping_city();
					break;
				case 'shipping_state':
					$value = $order->get_shipping_state();
					break;
				case 'shipping_postcode':
					$value = $order->get_shipping_postcode();
					break;
				case 'shipping_phone':
					$value = get_post_meta( $order_id, 'shipping_phone', true );
					break;
				case 'order_comments':
					$comments = $order->get_customer_note();
					if ( is_array( $comments ) ) {
						$comments = join( $multi_line_separator, $comments );
					}
					$value = ! empty( $comments ) ? $comments : '';
					break;
				case 'order_status':
					$value = $order->get_status();
					break;
				case 'order_total':
					$value = strip_tags( wc_price( $order->get_total() ) );
					break;
				case 'order_total_raw':
					$value = $order->get_total();
					break;
				case 'order_subtotal':
					$value = strip_tags( wc_price( $order->get_subtotal() ) );
					break;
				case 'order_subtotal_raw':
					$value = $order->get_subtotal();
					break;
				case 'order_tax':
					$value = strip_tags( wc_price( $order->get_total_tax() ) );
					break;
				case 'order_tax_raw':
					$value = $order->get_total_tax();
					break;
				case 'order_discounts':
					$value = strip_tags( wc_price( $order->get_discount_total() * - 1 ) );
					break;
				case 'order_discounts_raw':
					$value = ( $order->get_discount_total() * - 1 );
					break;
				case 'order_coupons':
					$coupons = $order->get_coupon_codes();
					$value   = join( ', ', $coupons );
					break;
				case 'order_products':
					$items = $order->get_items();
					$prods = array();
					if ( $items ) {
						/** @var WC_Order_Item_Product $item */
						foreach ( $items as $item ) {
							$product = $item->get_product();
							$prods[] = $product->get_title();
						}
					}
					$value = join( $multi_line_separator, $prods );

					break;
				case 'order_products_qty':
					$items = $order->get_items();
					$prods = array();
					if ( $items ) {
						/** @var WC_Order_Item_Product $item */
						foreach ( $items as $item ) {
							$product = $item->get_product();
							$prods[] = $product->get_title() . ' x ' . $item->get_quantity();
						}
					}
					$value = join( $multi_line_separator, $prods );

					break;
				case 'order_qty':
					$qty = 0;
					/** @var WC_Order_Item_Product $item */
					$items = $order->get_items();
					foreach ( $items as $item ) {
						$qty = $qty + $item->get_quantity();
					}
					$value = $qty;
					break;
				case 'order_products_links':
					$items = $order->get_items();
					$prods = array();
					if ( $items ) {
						/** @var WC_Order_Item_Product $item */
						foreach ( $items as $item ) {
							$product = $item->get_product();
							$prods[] = '<a href="' . $product->get_permalink() . '">' . $product->get_title() . '</a>';
						}
					}

					$value = join( $multi_line_separator, $prods );
					break;
				case 'payment_method':
					$value = $order->get_payment_method_title();
					break;

				case 'CARRIER':
					$value = $uncanny_automator->helpers->recipe->get_form_data_from_trigger_meta( 'WOOORDER_CARRIER', $trigger_id, $trigger_log_id, $user_id );
					break;
				case 'TRACKING_NUMBER':
					$value = $uncanny_automator->helpers->recipe->get_form_data_from_trigger_meta( 'WOOORDER_TRACKING_NUMBER', $trigger_id, $trigger_log_id, $user_id );
					break;
				case 'SHIP_DATE':
					$value = $uncanny_automator->helpers->recipe->get_form_data_from_trigger_meta( 'WOOORDER_SHIP_DATE', $trigger_id, $trigger_log_id, $user_id );
					$value = $value ? date( 'Y-m-d H:i:s', $value ) : '';
					break;
				case 'order_summary':
					$value = $this->build_summary_style_html( $order );
					break;
				default:
					$this->handle_default_switch( $value, $parse, $pieces, $order );
					break;
			}
			$value = apply_filters( 'automator_woocommerce_token_parser', $value, $parse, $pieces, $order );
		}

		return $value;
	}

	/**
	 * @param $value
	 * @param $parse
	 * @param $pieces
	 * @param $order
	 *
	 * @return mixed|void
	 */
	public function handle_default_switch( $value, $parse, $pieces, $order ) {
		if ( ! $order instanceof WC_Order ) {
			return $value;
		}
		$multi_line_separator = apply_filters( 'automator_woo_multi_item_separator', ' | ', $pieces );
		if ( preg_match( '/custom_order_meta/', $parse ) ) {
			$custom_meta = explode( '|', $parse );
			if ( ! empty( $custom_meta ) && count( $custom_meta ) > 1 && 'custom_order_meta' === $custom_meta[0] ) {
				$meta_key = $custom_meta[1];
				if ( $order->meta_exists( $meta_key ) ) {
					$value = $order->get_meta( $meta_key );
					if ( is_array( $value ) ) {
						$value = join( $multi_line_separator, $value );
					}
				}
				$value = apply_filters( 'automator_woocommerce_custom_order_meta_token_parser', $value, $meta_key, $pieces, $order );
			}
		}
		if ( preg_match( '/custom_item_meta/', $parse ) ) {
			$custom_meta = explode( '|', $parse );
			if ( ! empty( $custom_meta ) && count( $custom_meta ) > 1 && 'custom_item_meta' === $custom_meta[0] ) {
				$meta_key = $custom_meta[1];
				$items    = $order->get_items();
				if ( $items ) {
					/** @var WC_Order_Item_Product $item */
					foreach ( $items as $item ) {
						if ( $item->meta_exists( $meta_key ) ) {
							$value = $item->get_meta( $meta_key );
						}
						$value = apply_filters( 'automator_woocommerce_custom_item_meta_token_parser', $value, $meta_key, $pieces, $order, $item );
					}
				}
			}
		}

		return $value;
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 * @param $replace_args
	 *
	 * @return mixed|string
	 */
	public function replace_wcs_values_pro( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		global $uncanny_automator;
		$trigger_meta  = $pieces[1];
		$parse         = $pieces[2];
		$recipe_log_id = isset( $replace_args['recipe_log_id'] ) ? (int) $replace_args['recipe_log_id'] : $uncanny_automator->maybe_create_recipe_log_entry( $recipe_id, $user_id )['recipe_log_id'];
		if ( ! $trigger_data || ! $recipe_log_id ) {
			return $value;
		}
		foreach ( $trigger_data as $trigger ) {
			if ( ! is_array( $trigger ) || empty( $trigger ) ) {
				continue;
			}
			if ( ! key_exists( $trigger_meta, $trigger['meta'] ) && ( ! isset( $trigger['meta']['code'] ) && $trigger_meta !== $trigger['meta']['code'] ) ) {
				continue;
			}
			$trigger_id      = $trigger['ID'];
			$trigger_log_id  = $replace_args['trigger_log_id'];
			$subscription_id = $uncanny_automator->helpers->recipe->get_form_data_from_trigger_meta( 'subscription_id', $trigger_id, $trigger_log_id, $user_id );
			if ( empty( $subscription_id ) ) {
				continue;
			}
			$subscription = wcs_get_subscription( $subscription_id );
			if ( ! $subscription instanceof WC_Order ) {
				continue;
			}
			switch ( $parse ) {
				case 'WCSUBSCRIPTIONSTATUSCHANGED':
					$value = $subscription_id;
					break;
				case 'WOOSUBSCRIPTIONS_PRODUCT':
					$items         = $subscription->get_items();
					$product_names = array();
					foreach ( $items as $item ) {
						/** @var WC_Order_Item_Product $product */
						$product = $item->get_product();
						if ( class_exists( '\WC_Subscriptions_Product' ) && \WC_Subscriptions_Product::is_subscription( $product ) ) {
							$parent_product  = get_post_parent( $product->get_id() );
							$product_names[] = $parent_product->post_title;
						}
					}
					$value = implode( ', ', $product_names );
					break;
				case 'WOOSUBSCRIPTIONS':
					$items         = $subscription->get_items();
					$product_names = array();
					foreach ( $items as $item ) {
						/** @var WC_Order_Item_Product $product */
						$product = $item->get_product();
						if ( class_exists( '\WC_Subscriptions_Product' ) && \WC_Subscriptions_Product::is_subscription( $product ) ) {
							if ( get_post_type( $product->get_id() ) === 'product_variation' ) {
								$variation_product = get_post( $product->get_id() );
								$product_names[]   = ! empty( $variation_product->post_excerpt ) ? $variation_product->post_excerpt : $variation_product->post_title;
							} else {
								$product_names[] = $product->get_name();
							}
						}
					}
					$value = implode( ', ', $product_names );
					break;
				case 'WOOSUBSCRIPTIONS_ID':
					$items         = $subscription->get_items();
					$product_names = array();
					foreach ( $items as $item ) {
						/** @var WC_Order_Item_Product $product */
						$product = $item->get_product();
						if ( class_exists( '\WC_Subscriptions_Product' ) && \WC_Subscriptions_Product::is_subscription( $product ) ) {
							$product_names[] = $product->get_id();
						}
					}
					$value = implode( ', ', $product_names );
					break;
				case 'WOOSUBSCRIPTIONS_PRODUCT_ID':
					$items       = $subscription->get_items();
					$product_ids = array();
					foreach ( $items as $item ) {
						/** @var WC_Order_Item_Product $product */
						$product = $item->get_product();
						if ( class_exists( '\WC_Subscriptions_Product' ) && \WC_Subscriptions_Product::is_subscription( $product ) ) {
							$product_ids [] = wp_get_post_parent_id( $product->get_id() );
						}
					}
					$value = implode( ', ', $product_ids );
					break;
				case 'WOOSUBSCRIPTIONS_URL':
					$items         = $subscription->get_items();
					$product_names = array();
					foreach ( $items as $item ) {
						/** @var WC_Order_Item_Product $product */
						$product = $item->get_product();
						if ( class_exists( '\WC_Subscriptions_Product' ) && \WC_Subscriptions_Product::is_subscription( $product ) ) {
							$product_names[] = get_permalink( $product->get_id() );
						}
					}
					$value = implode( ', ', $product_names );
					break;
				case 'WOOSUBSCRIPTIONS_PRODUCT_URL':
					$items        = $subscription->get_items();
					$product_urls = array();
					foreach ( $items as $item ) {
						/** @var WC_Order_Item_Product $product */
						$product = $item->get_product();
						if ( class_exists( '\WC_Subscriptions_Product' ) && \WC_Subscriptions_Product::is_subscription( $product ) ) {
							$product_urls[] = get_permalink( wp_get_post_parent_id( $product->get_id() ) );
						}
					}
					$value = implode( ', ', $product_urls );
					break;
				case 'WOOSUBSCRIPTIONS_THUMB_ID':
					$items         = $subscription->get_items();
					$product_thumb = array();
					foreach ( $items as $item ) {
						/** @var WC_Order_Item_Product $product */
						$product = $item->get_product();
						if ( class_exists( '\WC_Subscriptions_Product' ) && \WC_Subscriptions_Product::is_subscription( $product ) ) {
							$product_thumb[] = get_post_thumbnail_id( $product->get_id() );
						}
					}
					$value = implode( ', ', $product_thumb );
					if ( empty( $value ) || $value == 0 ) {
						$value = 'N/A';
					}
					break;
				case 'WOOSUBSCRIPTIONS_PRODUCT_THUMB_ID':
					$items         = $subscription->get_items();
					$product_thumb = array();
					foreach ( $items as $item ) {
						/** @var WC_Order_Item_Product $product */
						$product = $item->get_product();
						if ( class_exists( '\WC_Subscriptions_Product' ) && \WC_Subscriptions_Product::is_subscription( $product ) ) {
							$product_thumb[] = get_post_thumbnail_id( wp_get_post_parent_id( $product->get_id() ) );
						}
					}
					$value = implode( ', ', $product_thumb );
					if ( empty( $value ) || $value == 0 ) {
						$value = 'N/A';
					}
					break;
				case 'WOOSUBSCRIPTIONS_THUMB_URL':
					$items            = $subscription->get_items();
					$product_thumburl = array();
					foreach ( $items as $item ) {
						/** @var WC_Order_Item_Product $product */
						$product = $item->get_product();
						if ( class_exists( '\WC_Subscriptions_Product' ) && \WC_Subscriptions_Product::is_subscription( $product ) ) {
							$product_thumburl[] = get_the_post_thumbnail_url( $product->get_id() );
						}
					}
					$value = implode( ', ', $product_thumburl );
					if ( empty( $value ) ) {
						$value = 'N/A';
					}
					break;
				case 'WOOSUBSCRIPTIONS_PRODUCT_THUMB_URL':
					$items            = $subscription->get_items();
					$product_thumburl = array();
					foreach ( $items as $item ) {
						/** @var WC_Order_Item_Product $product */
						$product = $item->get_product();
						if ( class_exists( '\WC_Subscriptions_Product' ) && \WC_Subscriptions_Product::is_subscription( $product ) ) {
							$product_thumburl[] = get_the_post_thumbnail_url( wp_get_post_parent_id( $product->get_id() ) );
						}
					}
					$value = implode( ', ', $product_thumburl );
					if ( empty( $value ) ) {
						$value = 'N/A';
					}
					break;
				case 'WOOSUBSCRIPTIONSTATUS':
				case 'WOOSUBSCRIPTIONS_SUBSCRIPTION_STATUS':
					$value = $subscription->get_status();
					break;
				case 'WOOSUBSCRIPTIONSTATUS_ID':
				case 'WOOSUBSCRIPTIONS_SUBSCRIPTION_ID':
					$value = $subscription->get_id();
					break;
				case 'WOOSUBSCRIPTIONSTATUS_END_DATE':
				case 'WOOSUBSCRIPTIONS_SUBSCRIPTION_END_DATE':
					$value = $subscription->get_date( 'end' );
					if ( empty( $value ) || $value == 0 ) {
						$value = 'N/A';
					}
					break;
				case 'WOOSUBSCRIPTIONSTATUS_NEXT_PAYMENT_DATE':
				case 'WOOSUBSCRIPTIONS_SUBSCRIPTION_NEXT_PAYMENT_DATE':
					$value = $subscription->get_date( 'next_payment' );
					break;
				case 'WOOSUBSCRIPTIONSTATUS_TRIAL_END_DATE':
				case 'WOOSUBSCRIPTIONS_SUBSCRIPTION_TRIAL_END_DATE':
					$value = $subscription->get_date( 'trial_end' );
					if ( empty( $value ) || $value == 0 ) {
						$value = 'N/A';
					}
					break;
				default:
					$this->handle_default_switch( $value, $parse, $pieces, $subscription );
					break;

			}
		}

		return $value;
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 * @param $replace_args
	 *
	 * @return mixed|string|null
	 */
	public function replace_review_values_pro( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		if ( empty( $trigger_data ) || empty( $replace_args ) ) {
			return $value;
		}
		global $uncanny_automator;
		$trigger_meta  = $pieces[1];
		$parse         = $pieces[2];
		$recipe_log_id = isset( $replace_args['recipe_log_id'] ) ? (int) $replace_args['recipe_log_id'] : $uncanny_automator->maybe_create_recipe_log_entry( $recipe_id, $user_id )['recipe_log_id'];
		if ( ! $trigger_data || ! $recipe_log_id ) {
			return $value;
		}

		foreach ( $trigger_data as $trigger ) {
			if ( empty( $trigger ) ) {
				continue;
			}
			if ( ! key_exists( $trigger_meta, $trigger['meta'] ) ) {
				if ( isset( $trigger['meta']['code'] ) && (string) strtolower( $trigger_meta ) !== (string) strtolower( $trigger['meta']['code'] ) ) {
					continue;
				}
			}
			$trigger_id     = $trigger['ID'];
			$trigger_log_id = $replace_args['trigger_log_id'];
			$comment_id     = $uncanny_automator->helpers->recipe->get_form_data_from_trigger_meta( 'comment_id', $trigger_id, $trigger_log_id, $user_id );
			$comment        = get_comment( $comment_id );
			if ( empty( $comment_id ) ) {
				continue;
			}
			switch ( $parse ) {
				case 'product_id':
					$value = $comment->comment_post_ID;
					break;
				case 'WOOPRODUCT':
					$value = get_the_title( $comment->comment_post_ID );
					break;
				case 'WOOPRODUCT_ID':
					$value = $comment->comment_post_ID;
					break;
				case 'WOOPRODUCT_URL':
					$value = get_permalink( $comment->comment_post_ID );
					break;
				case 'product_review':
					$value = $comment->comment_content;
					break;
				case 'product_rating':
					$value = get_comment_meta( $comment->comment_ID, 'rating', true );
					if ( empty( $value ) ) {
						$value = $uncanny_automator->helpers->recipe->get_form_data_from_trigger_meta( 'rating', $trigger_id, $trigger_log_id, $user_id );
					}
					break;
				case 'NUMTIMES':
					$value = absint( $replace_args['run_number'] );
					break;
				default:
					$this->handle_default_switch( $value, $parse, $pieces, array() );
					break;
			}
		}

		return $value;
	}

	/**
	 * @param $order_id
	 * @param $recipe_id
	 * @param $args
	 * @param $type
	 */
	public function uap_wc_trigger_save_meta_func( $order_id, $recipe_id, $args, $type ) {
		if ( ! empty( $order_id ) && is_array( $args ) && $recipe_id ) {
			foreach ( $args as $trigger_result ) {
				if ( true === $trigger_result['result'] ) {
					global $uncanny_automator;
					$recipe = $uncanny_automator->get_recipes_data( true, $recipe_id );
					if ( is_array( $recipe ) ) {
						$recipe = array_pop( $recipe );
					}
					$triggers = $recipe['triggers'];
					if ( $triggers ) {
						foreach ( $triggers as $trigger ) {
							$trigger_id = $trigger['ID'];
							if ( ! key_exists( 'WOOPRODCAT', $trigger['meta'] ) &&
								 ! key_exists( 'WOOPRODTAG', $trigger['meta'] ) &&
								 ! key_exists( 'WOOVARIPRODUCT', $trigger['meta'] ) &&
								 ! key_exists( 'WOOSUBSCRIPTIONS', $trigger['meta'] ) &&
								 ! key_exists( 'WOOPAYMENTGATEWAY', $trigger['meta'] ) ) {
								continue;
							} else {
								$user_id        = (int) $trigger_result['args']['user_id'];
								$trigger_log_id = (int) $trigger_result['args']['get_trigger_id'];
								$run_number     = (int) $trigger_result['args']['run_number'];

								$args = array(
									'user_id'        => $user_id,
									'trigger_id'     => $trigger_id,
									'meta_key'       => 'order_id',
									'meta_value'     => $order_id,
									'run_number'     => $run_number, //get run number
									'trigger_log_id' => $trigger_log_id,
								);

								$uncanny_automator->insert_trigger_meta( $args );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @param $item_id
	 * @param $item
	 * @param $order_id
	 * @param $recipe_id
	 * @param $args
	 */
	public function uap_wc_order_item_meta_func( $item_id, $order_id, $recipe_id, $args ) {
		if ( empty( $order_id ) ) {
			return;
		}
		if ( ! is_array( $args ) ) {
			return;
		}
		if ( ! $recipe_id ) {
			return;
		}
		foreach ( $args as $trigger_result ) {
			if ( true !== $trigger_result['result'] ) {
				continue;
			}
			global $uncanny_automator;
			$recipe = $uncanny_automator->get_recipes_data( true, $recipe_id );
			if ( is_array( $recipe ) ) {
				$recipe = array_pop( $recipe );
			}
			$triggers = $recipe['triggers'];
			if ( $triggers ) {
				foreach ( $triggers as $trigger ) {
					$trigger_id     = $trigger['ID'];
					$user_id        = (int) $trigger_result['args']['user_id'];
					$trigger_log_id = (int) $trigger_result['args']['trigger_log_id'];
					$run_number     = (int) $trigger_result['args']['run_number'];
					$args           = array(
						'user_id'        => $user_id,
						'trigger_id'     => $trigger_id,
						'run_number'     => $run_number, //get run number
						'trigger_log_id' => $trigger_log_id,
					);
					$meta_value     = array(
						// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
						'order_item' => $item_id,
						'order_id'   => $order_id,
					);
					$uncanny_automator->db->trigger->add_token_meta( 'order_item_details', maybe_serialize( $meta_value ), $args );
				}
			}
		}
	}

	/**
	 * @param $product_id
	 * @param $recipe_id
	 * @param $args
	 * @param $type
	 */
	public function uap_wc_trigger_save_product_meta_func( $product_id, $recipe_id, $args, $type ) {
		if ( ! empty( $product_id ) && is_array( $args ) && $recipe_id ) {
			foreach ( $args as $trigger_result ) {
				if ( true === $trigger_result['result'] ) {
					global $uncanny_automator;
					$recipe = $uncanny_automator->get_recipes_data( true, $recipe_id );
					if ( is_array( $recipe ) ) {
						$recipe = array_pop( $recipe );
					}
					$triggers = $recipe['triggers'];
					if ( $triggers ) {
						foreach ( $triggers as $trigger ) {
							$trigger_id = $trigger['ID'];
							if ( ! key_exists( 'WOOPRODCAT', $trigger['meta'] ) &&
								 ! key_exists( 'WOOPRODTAG', $trigger['meta'] ) &&
								 ! key_exists( 'WOOVARIPRODUCT', $trigger['meta'] ) &&
								 ! key_exists( 'WOOSUBSCRIPTIONS', $trigger['meta'] ) ) {
								if ( in_array( 'WCPRODREVIEW', $trigger['meta'], false ) || in_array( 'WCPRODREVIEWRATING', $trigger['meta'], false ) ) {
									$user_id        = (int) $trigger_result['args']['user_id'];
									$trigger_log_id = (int) $trigger_result['args']['get_trigger_id'];
									$run_number     = (int) $trigger_result['args']['run_number'];

									$args = array(
										'user_id'        => $user_id,
										'trigger_id'     => $trigger_id,
										'meta_key'       => 'comment_id',
										'meta_value'     => $product_id,
										'run_number'     => $run_number, //get run number
										'trigger_log_id' => $trigger_log_id,
									);

									$uncanny_automator->insert_trigger_meta( $args );
									if ( isset( $_POST['rating'] ) ) {
										$rating = apply_filters( 'automator_woocommerce_product_rating', absint( $_POST['rating'] ), $_POST, $trigger_result );
										$args   = array(
											'user_id'    => $user_id,
											'trigger_id' => $trigger_id,
											'meta_key'   => 'rating',
											'meta_value' => $rating,
											'run_number' => $run_number, //get run number
											'trigger_log_id' => $trigger_log_id,
										);

										$uncanny_automator->insert_trigger_meta( $args );
									}
								}
							} else {
								$user_id        = (int) $trigger_result['args']['user_id'];
								$trigger_log_id = (int) $trigger_result['args']['get_trigger_id'];
								$run_number     = (int) $trigger_result['args']['run_number'];

								$args = array(
									'user_id'        => $user_id,
									'trigger_id'     => $trigger_id,
									'meta_key'       => 'product_id',
									'meta_value'     => $product_id,
									'run_number'     => $run_number, //get run number
									'trigger_log_id' => $trigger_log_id,
								);

								$uncanny_automator->insert_trigger_meta( $args );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @param WC_Order $order
	 * @param $value_to_match
	 *
	 * @return string
	 */
	public function get_woo_product_categories_from_items( WC_Order $order, $value_to_match ) {
		if ( intval( '-1' ) === intval( $value_to_match ) ) {
			$return = array();
			if ( $order->get_items() ) {
				/** @var \WC_Order_Item_Product $item */
				foreach ( $order->get_items() as $item ) {
					$terms = wp_get_post_terms( $item->get_product_id(), 'product_cat' );
					if ( $terms ) {
						foreach ( $terms as $term ) {
							$return[] = $term->name;
						}
					}
				}
			}

			//array_unique( $return );

			return join( ', ', $return );
		} else {
			$term = get_term_by( 'ID', $value_to_match, 'product_cat' );
			if ( ! $term ) {
				return '';
			}

			return $term->name;
		}

	}

	/**
	 * @param WC_Order $order
	 * @param $value_to_match
	 *
	 * @return string
	 */
	public function get_woo_product_tags_from_items( WC_Order $order, $value_to_match ) {
		if ( intval( '-1' ) === intval( $value_to_match ) ) {
			$return = array();
			if ( $order->get_items() ) {
				foreach ( $order->get_items() as $item ) {
					$terms = wp_get_post_terms( $item->get_product_id(), 'product_tag' );
					if ( $terms ) {
						foreach ( $terms as $term ) {
							$return[] = $term->name;
						}
					}
				}
			}

			//array_unique( $return );

			return join( ', ', $return );
		} else {
			$term = get_term_by( 'ID', $value_to_match, 'product_tag' );
			if ( ! $term ) {
				return '';
			}

			return $term->name;
		}
	}

	/**
	 * @param WC_Order $order
	 * @param $value_to_match
	 *
	 * @param string $term_type
	 *
	 * @return string
	 */
	public function get_woo_terms_ids_from_items( WC_Order $order, $value_to_match, $term_type = 'product_cat' ) {
		if ( intval( '-1' ) === intval( $value_to_match ) ) {
			$return = array();
			if ( $order->get_items() ) {
				foreach ( $order->get_items() as $item ) {
					$terms = wp_get_post_terms( $item->get_product_id(), $term_type );
					if ( $terms ) {
						foreach ( $terms as $term ) {
							$return[] = $term->term_id;
						}
					}
				}
			}

			//array_unique( $return );

			return join( ', ', $return );
		} else {
			$term = get_term_by( 'ID', $value_to_match, $term_type );
			if ( ! $term ) {
				return '';
			}

			return $term->term_id;
		}
	}

	/**
	 * @param WC_Order $order
	 * @param $value_to_match
	 *
	 * @param string $term_type
	 *
	 * @return string
	 */
	public function get_woo_terms_links_from_items( WC_Order $order, $value_to_match, $term_type = 'product_cat' ) {
		if ( intval( '-1' ) === intval( $value_to_match ) ) {
			$return = array();
			if ( $order->get_items() ) {
				foreach ( $order->get_items() as $item ) {
					$terms = wp_get_post_terms( $item->get_product_id(), $term_type );
					if ( $terms ) {
						foreach ( $terms as $term ) {
							$return[] = get_term_link( $term, $term_type );
						}
					}
				}
			}

			//array_unique( $return );

			return join( ', ', $return );
		} else {
			$term = get_term_by( 'ID', $value_to_match, $term_type );
			if ( ! $term ) {
				return '';
			}

			return get_term_link( $term, $term_type );
		}
	}

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	public function wc_order_possible_tokens( $tokens = array(), $args = array() ) {
		if ( ! automator_pro_do_identify_tokens() ) {
			return $tokens;
		}

		//$args['meta'] = 'WCSHIPSTATIONSHIPPED';
		$fields   = array();
		$fields[] = array(
			'tokenId'         => 'TRACKING_NUMBER',
			'tokenName'       => esc_attr__( 'Shipping tracking number', 'uncanny-automator' ),
			'tokenType'       => 'text',
			'tokenIdentifier' => $args['meta'],
		);
		$fields[] = array(
			'tokenId'         => 'CARRIER',
			'tokenName'       => esc_attr__( 'Shipping carrier', 'uncanny-automator' ),
			'tokenType'       => 'text',
			'tokenIdentifier' => $args['meta'],
		);
		$fields[] = array(
			'tokenId'         => 'SHIP_DATE',
			'tokenName'       => esc_attr__( 'Ship date', 'uncanny-automator' ),
			'tokenType'       => 'text',
			'tokenIdentifier' => $args['meta'],
		);
		$tokens   = array_merge( $tokens, $fields );

		return $tokens;
	}

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	public function wc_addedtocart_possible_tokens( $tokens = array(), $args = array() ) {
		if ( ! automator_pro_do_identify_tokens() ) {
			return $tokens;
		}
		$trigger_meta = $args['meta'];
		$fields       = array(
			array(
				'tokenId'         => 'PRODUCT_PRICE',
				'tokenName'       => __( 'Price', 'uncanny-automator-pro' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			),
			array(
				'tokenId'         => 'PRODUCT_QUANTITY',
				'tokenName'       => __( 'Product quantity', 'uncanny-automator-pro' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			),
			array(
				'tokenId'         => 'PRODUCT_VARIATION',
				'tokenName'       => __( 'Variation', 'uncanny-automator-pro' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			),
		);

		$tokens = array_merge( $tokens, $fields );

		return $tokens;
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 * @param $replace_args
	 *
	 * @return mixed
	 */
	public function wc_addedtocart_tokens_pro( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		$to_match = array(
			'PRODUCT_PRICE',
			'PRODUCT_QUANTITY',
			'PRODUCT_VARIATION',
		);
		if ( $pieces && isset( $pieces[2] ) ) {
			$meta_field = $pieces[2];
			if ( ! empty( $meta_field ) && in_array( $meta_field, $to_match, false ) ) {
				if ( $trigger_data ) {
					global $wpdb;
					foreach ( $trigger_data as $trigger ) {
						if ( empty( $trigger ) ) {
							continue;
						}
						$meta_value = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}uap_trigger_log_meta WHERE meta_key = %s AND automator_trigger_id = %d AND automator_trigger_log_id = %d ORDER BY ID DESC LIMIT 0,1", $meta_field, $trigger['ID'], $replace_args['trigger_log_id'] ) );
						if ( ! empty( $meta_value ) ) {
							$value = maybe_unserialize( $meta_value );
						}
					}
				}
			}
		}

		return $value;
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 * @param $replace_args
	 *
	 * @return false|int|mixed|string|\WP_Error
	 */
	public function replace_item_created_values_pro( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		global $uncanny_automator;
		$trigger_meta  = $pieces[1];
		$parse         = $pieces[2];
		$recipe_log_id = isset( $replace_args['recipe_log_id'] ) ? (int) $replace_args['recipe_log_id'] : $uncanny_automator->maybe_create_recipe_log_entry( $recipe_id, $user_id )['recipe_log_id'];
		if ( ! $trigger_data || ! $recipe_log_id ) {
			return $value;
		}
		foreach ( $trigger_data as $trigger ) {
			if ( empty( $trigger ) ) {
				continue;
			}
			if ( ! key_exists( $trigger_meta, $trigger['meta'] ) && ( ! isset( $trigger['meta']['code'] ) && $trigger_meta !== $trigger['meta']['code'] ) ) {
				continue;
			}
			$trigger_id         = $trigger['ID'];
			$trigger_log_id     = $replace_args['trigger_log_id'];
			$token_meta_args    = array(
				'trigger_id'     => $trigger_id,
				'trigger_log_id' => $trigger_log_id,
				'user_id'        => $user_id,
			);
			$order_item_details = maybe_unserialize( $uncanny_automator->db->trigger->get_token_meta( 'order_item_details', $token_meta_args ) );
			if ( empty( $order_item_details ) ) {
				continue;
			}
			$order_id = $order_item_details['order_id'];
			$order    = wc_get_order( $order_id );
			if ( ! $order instanceof WC_Order ) {
				continue;
			}
			$order_item_id = $order_item_details['order_item'];
			/** @var WC_Order_Item_Product $order_item */
			$order_item = Woocommerce_Pro_Helpers::get_order_item_by_id( $order_item_id, $order_id );
			switch ( $parse ) {
				case 'order_id':
					$value = $order_id;
					break;
				case 'WOOPRODUCT':
					$value = $order_item->get_product()->get_name();
					break;
				case 'WOOPRODUCT_ID':
					if ( 'ANONORDERITEMCREATED' === $trigger_meta ) {
						$value = $order_item->get_product()->get_id();
					}
					break;
				case 'WOOPRODUCT_URL':
					if ( 'ANONORDERITEMCREATED' === $trigger_meta ) {
						$value = get_permalink( $order_item->get_product()->get_id() );
					}
					break;
				case 'WOOPRODUCT_ORDER_QTY':
					$value = $order_item->get_quantity();
					break;
				case 'item_total':
					$value = $order_item->get_total();
					break;
				case 'product_price':
					$value = $order_item->get_product()->get_price();
					break;
				case 'product_sale_price':
					$value = $order_item->get_product()->get_sale_price();
					break;
				case 'product_sku':
					$value = $order_item->get_product()->get_sku();
					break;
				default:
					$value = $this->handle_default_switch( $value, $parse, $pieces, $order );
					$value = apply_filters( 'automator_woocommerce_order_item_created_token_parser', $value, $parse, $pieces, $order_item, $order );
					break;
			}
			$value = apply_filters( 'automator_woocommerce_token_parser', $value, $parse, $pieces, $order );
		}

		return $value;
	}

	/**
	 * @param $order
	 *
	 * @return string
	 */
	public function build_summary_style_html( $order ) {
		$font_colour      = apply_filters( 'automator_woocommerce_order_summary_text_color', '#000', $order );
		$font_family      = apply_filters( 'automator_woocommerce_order_summary_font_family', "'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif", $order );
		$table_styles     = apply_filters( 'automator_woocommerce_order_summary_table_style', '', $order );
		$border_colour    = apply_filters( 'automator_woocommerce_order_summary_border_color', '#eee', $order );
		$tr_border_colour = apply_filters( 'automator_woocommerce_order_summary_tr_border_color', '#e5e5e5', $order );
		$tr_text_colour   = apply_filters( 'automator_woocommerce_order_summary_tr_text_color', '#636363', $order );
		$td_border_colour = apply_filters( 'automator_woocommerce_order_summary_td_border_color', '#e5e5e5', $order );
		$td_text_colour   = apply_filters( 'automator_woocommerce_order_summary_td_text_color', '#636363', $order );

		$html   = array();
		$html[] = sprintf(
			'<table class="td" cellspacing="0" cellpadding="6" border="1" style="color:%s; border: 1px solid %s; vertical-align: middle; width: 100%%; font-family: %s;%s">',
			$font_colour,
			$border_colour,
			$font_family,
			$table_styles
		);
		$items  = $order->get_items();
		$html[] = '<thead>';
		$html[] = '<tr class="row">';
		$th     = sprintf(
			'<th class="td" scope="col" style="color: %s; border: 1px solid %s; vertical-align: middle; padding: 12px; text-align: left;">',
			$tr_text_colour,
			$tr_border_colour
		);
		$html[] = $th . '<strong>' . apply_filters( 'automator_woocommerce_order_summary_product_title', esc_attr__( 'Product', 'uncanny-automator' ) ) . '</strong></th>';
		$html[] = $th . '<strong>' . apply_filters( 'automator_woocommerce_order_summary_quantity_title', esc_attr__( 'Quantity', 'uncanny-automator' ) ) . '</strong></th>';
		$html[] = $th . '<strong>' . apply_filters( 'automator_woocommerce_order_summary_price_title', esc_attr__( 'Price', 'uncanny-automator' ) ) . '</strong></th>';
		$html[] = '</thead>';
		if ( $items ) {
			/** @var WC_Order_Item_Product $item */
			$td = sprintf(
				'<td class="td" style="color: %s; border: 1px solid %s; padding: 12px; text-align: left; vertical-align: middle; font-family: %s">',
				$td_text_colour,
				$td_border_colour,
				$font_family
			);
			foreach ( $items as $item ) {
				$product = $item->get_product();
				if ( true === apply_filters( 'automator_woocommerce_order_summary_show_product_in_invoice', true, $product, $item, $order ) ) {
					$html[] = '<tr class="order_item">';
					$title  = $product->get_title();
					if ( $item->get_variation_id() ) {
						$variation      = new \WC_Product_Variation( $item->get_variation_id() );
						$variation_name = implode( ' / ', $variation->get_variation_attributes() );
						$title          = apply_filters( 'automator_woocommerce_order_summary_line_item_title', "$title - $variation_name", $product, $item, $order );
					}
					if ( true === apply_filters( 'automator_woocommerce_order_summary_link_to_line_item', true, $product, $item, $order ) ) {
						$title = sprintf( '<a style="color: %s; vertical-align: middle; padding: 12px 0; text-align: left;" href="%s">%s</a>', $td_text_colour, $product->get_permalink(), $title );
					}
					$html[] = sprintf( '%s %s</td>', $td, $title );
					$html[] = $td . $item->get_quantity() . '</td>';
					$html[] = $td . wc_price( $item->get_total() ) . '</td>';
					$html[] = '</tr>';
				}
			}
		}

		$td       = sprintf(
			'<td colspan="2" class="td" style="color: %s; border: 1px solid %s; vertical-align: middle; padding: 12px; text-align: left; border-top-width: 4px;">',
			$td_text_colour,
			$td_border_colour
		);
		$td_right = sprintf(
			'<td class="td" style="color: %s; border: 1px solid %s; vertical-align: middle; padding: 12px; text-align: left; border-top-width: 4px;">',
			$td_text_colour,
			$td_border_colour
		);
		// Subtotal
		if ( true === apply_filters( 'automator_woocommerce_order_summary_show_subtotal', true, $order ) ) {
			$html[] = '<tr>';
			$html[] = $td;
			$html[] = apply_filters( 'automator_woocommerce_order_summary_subtotal_title', esc_attr__( 'Subtotal:', 'uncanny-automator' ) );
			$html[] = '</td>';
			$html[] = $td_right;
			$html[] = $order->get_subtotal_to_display();
			$html[] = '</td>';
			$html[] = '</tr>';
		}
		// Tax
		if ( true === apply_filters( 'automator_woocommerce_order_summary_show_taxes', true, $order ) ) {
			if ( ! empty( $order->get_taxes() ) ) {
				$html[] = '<tr>';
				$html[] = $td;
				$html[] = apply_filters( 'automator_woocommerce_order_summary_tax_title', esc_attr__( 'Tax:', 'uncanny-automator' ) );
				$html[] = '</td>';
				$html[] = $td_right;
				$html[] = wc_price( $order->get_total_tax() );
				$html[] = '</td>';
				$html[] = '</tr>';
			}
		}
		// Payment method
		if ( true === apply_filters( 'automator_woocommerce_order_summary_show_payment_method', true, $order ) ) {
			$html[] = '<tr>';
			$html[] = $td;
			$html[] = apply_filters( 'automator_woocommerce_order_summary_payment_method_title', esc_attr__( 'Payment method:', 'uncanny-automator' ) );
			$html[] = '</td>';
			$html[] = $td_right;
			$html[] = $order->get_payment_method_title();
			$html[] = '</td>';
			$html[] = '</tr>';
		}
		// Total
		if ( true === apply_filters( 'automator_woocommerce_order_summary_show_total', true, $order ) ) {
			$html[] = '<tr>';
			$html[] = $td;
			$html[] = apply_filters( 'automator_woocommerce_order_summary_total_title', esc_attr__( 'Total:', 'uncanny-automator' ) );
			$html[] = '</td>';
			$html[] = $td_right;
			$html[] = $order->get_formatted_order_total();
			$html[] = '</td>';
			$html[] = '</tr>';
		}
		$html[] = '</table>';
		$html   = apply_filters( 'automator_order_summary_html_raw', $html, $order );

		return implode( PHP_EOL, $html );
	}
}
