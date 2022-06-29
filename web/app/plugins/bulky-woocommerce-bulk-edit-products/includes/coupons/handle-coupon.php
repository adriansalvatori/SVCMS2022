<?php

namespace WCBEditor\Includes\Coupons;

use WCBEditor\Includes\Helper;

defined( 'ABSPATH' ) || exit;

class Handle_Coupon {

	protected static $instance = null;
	protected $fields;
	protected $meta_fields;

	public function __construct() {
		$this->fields      = Coupons::instance()->filter_fields();
		$this->meta_fields = get_option( 'vi_wbe_coupon_meta_fields' );
	}

	public static function instance() {
		return self::$instance == null ? self::$instance = new self : self::$instance;
	}

	public function get_coupon_data( \WC_Coupon $coupon, $post, $fields ) {
		$p_data = [];
		foreach ( $fields as $field ) {
			switch ( $field ) {

				case 'id':
					$p_data[] = $coupon->get_id();
					break;

				case 'post_date':
					$date     = $coupon->get_date_created( 'edit' );
					$p_data[] = $date ? $date->date_i18n( 'Y-m-d H:i' ) : '';
					break;

				case 'status':
					$p_data[] = $post->post_status;
					break;

				case 'code':
					$p_data[] = $coupon->get_code( 'edit' );
					break;

				case 'excerpt':
					$p_data[] = $coupon->get_description( 'edit' );
					break;

				case 'discount_type':
					$p_data[] = $coupon->get_discount_type( 'edit' );
					break;

				case 'amount':
					$p_data[] = $coupon->get_amount( 'edit' );
					break;

				case 'allow_free_shipping':
					$p_data[] = $coupon->get_free_shipping( 'edit' );
					break;

				case 'expiry_date':
					$date = $coupon->get_date_expires( 'edit' );
					$date = $date ? $date->date_i18n( 'Y-m-d' ) : '';

					$p_data[] = $date;
					break;

				case 'minimum_amount':
					$p_data[] = $coupon->get_minimum_amount( 'edit' );
					break;

				case 'maximum_amount':
					$p_data[] = $coupon->get_maximum_amount( 'edit' );
					break;

				case 'individual_use':
					$p_data[] = $coupon->get_individual_use( 'edit' );
					break;

				case 'exclude_sale_items':
					$p_data[] = $coupon->get_exclude_sale_items( 'edit' );
					break;

				case 'product_ids':
					$p_data[] = Helper::get_multiple_products( $coupon->get_product_ids( 'edit' ) );
					break;

				case 'exclude_product_ids':
					$p_data[] = Helper::get_multiple_products( $coupon->get_excluded_product_ids( 'edit' ) );
					break;

				case 'product_categories':
					$p_data[] = $coupon->get_product_categories( 'edit' );
					break;

				case 'exclude_product_categories':
					$p_data[] = $coupon->get_excluded_product_categories( 'edit' );
					break;

				case 'customer_email':
					$p_data[] = implode( ',', $coupon->get_email_restrictions( 'edit' ) );
					break;

				case 'usage_limit':
					$p_data[] = $coupon->get_usage_limit( 'edit' );
					break;

				case 'limit_usage_to_x_items':
					$p_data[] = $coupon->get_limit_usage_to_x_items( 'edit' );
					break;

				case 'usage_limit_per_user':
					$p_data[] = $coupon->get_usage_limit_per_user( 'edit' );
					break;

				default:
					if ( ! empty( $this->meta_fields[ $field ] ) ) {
						$meta_type = $this->meta_fields[ $field ]['input_type'];
						$data      = get_post_meta( $coupon->get_id(), $field, true );
						if ( $meta_type == 'json' && ! is_array( $data ) ) {
							$data = json_decode( $data, true );
						}
					}
					$p_data[] = $data ?? '';
					break;
			}
		}

		return $p_data;
	}

	public function get_coupon_data_for_edit( $coupon, $post ) {
		return $this->get_coupon_data( $coupon, $post, $this->fields );
	}

	public function parse_coupon_data_to_save( \WC_Coupon &$coupon, $type, $value ) {

		switch ( $type ) {
			case 'id':
			case 'items':
				break;

			case 'post_date':
				$coupon->set_date_created( $value );
				break;

			case 'code':
				$p_data[] = $coupon->set_code( $value );
				break;

			case 'excerpt':
				$p_data[] = $coupon->set_description( $value );
				break;

			case 'discount_type':
				$p_data[] = $coupon->set_discount_type( $value );
				break;

			case 'amount':
				$p_data[] = $coupon->set_amount( $value );
				break;

			case 'allow_free_shipping':
				$p_data[] = $coupon->set_free_shipping( $value );
				break;

			case 'expiry_date':
				$p_data[] = $coupon->set_date_expires( $value );
				break;

			case 'minimum_amount':
				$p_data[] = $coupon->set_minimum_amount( $value );
				break;

			case 'maximum_amount':
				$p_data[] = $coupon->set_maximum_amount( $value );
				break;

			case 'individual_use':
				$p_data[] = $coupon->set_individual_use( $value );
				break;

			case 'exclude_sale_items':
				$p_data[] = $coupon->set_exclude_sale_items( $value );
				break;

			case 'product_ids':
				if ( ! is_array( $value ) ) {
					$value = [];
				}
				$product_ids = wp_list_pluck( $value, 'id' );
				$p_data[]    = $coupon->set_product_ids( $product_ids );
				break;

			case 'exclude_product_ids':
				if ( ! is_array( $value ) ) {
					$value = [];
				}
				$ex_product_ids = wp_list_pluck( $value, 'id' );
				$p_data[]       = $coupon->set_excluded_product_ids( $ex_product_ids );
				break;

			case 'product_categories':
				if ( is_string( $value ) ) {
					$value = explode( ';', $value );
				}
				$p_data[] = $coupon->set_product_categories( $value );
				break;

			case 'exclude_product_categories':
				if ( is_string( $value ) ) {
					$value = explode( ';', $value );
				}
				$p_data[] = $coupon->set_excluded_product_categories( $value );
				break;

			case 'customer_email':
				$value    = explode( ',', $value );
				$p_data[] = $coupon->set_email_restrictions( $value );
				break;

			case 'usage_limit':
				$p_data[] = $coupon->set_usage_limit( $value );
				break;

			case 'limit_usage_to_x_items':
				$p_data[] = $coupon->set_limit_usage_to_x_items( $value );
				break;

			case 'usage_limit_per_user':
				$p_data[] = $coupon->set_usage_limit_per_user( $value );
				break;

			default:
				$meta_fields = get_option( 'vi_wbe_coupon_meta_fields' );

				if ( ! empty( $meta_fields ) && is_array( $meta_fields ) && in_array( $type, array_keys( $meta_fields ) ) ) {
					$data_type = $meta_fields[ $type ]['input_type'] ?? '';
					$pid       = $coupon->get_id();

					if ( $data_type ) {
						if ( $data_type === 'json' ) {
							$value = json_encode( $value );
						}
						update_post_meta( $pid, $type, $value );
					}
				}
				break;
		}

	}

}
