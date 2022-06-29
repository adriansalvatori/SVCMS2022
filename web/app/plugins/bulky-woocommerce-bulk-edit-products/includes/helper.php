<?php

namespace WCBEditor\Includes;

use WCBEditor\Includes\Coupons\Coupon_History;
use WCBEditor\Includes\Orders\Order_History;
use WCBEditor\Includes\Products\Product_History;

defined( 'ABSPATH' ) || exit;

class Helper {
	protected static $instance = null;

	public function __construct() {

	}

	public static function instance() {
		return self::$instance == null ? self::$instance = new self : self::$instance;
	}

	public static function allowed_html() {
		return wp_parse_args(
			[
				'input'  => [ 'class' => true, 'name' => true ],
				'select' => [ 'class' => true, 'name' => true, 'multiple' => true ],
				'option' => [ 'value' => true, 'selected' => true ],
				'div'    => [ 'value' => true, 'class' => true ]
			],
			wp_kses_allowed_html()
		);
	}

	public static function isHTML( $string ) {
		return $string != strip_tags( $string );
	}

	public static function sanitize( $var ) {
		if ( is_array( $var ) ) {
			return array_map( [ __CLASS__, 'sanitize' ], $var );
		} elseif ( self::isHTML( $var ) ) {
			return wp_kses_post( $var );
		} else {
			return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
		}
	}

	public static function get_history_instance( $type ) {
		switch ( $type ) {
			case 'products':
				return Product_History::instance();
			case 'orders':
				return Order_History::instance();
			case 'coupons':
				return Coupon_History::instance();
			default :
				return '';
		}
	}

	public static function order_actions() {
		return apply_filters( 'bulky_woocommerce_order_actions',
			[
				'send_order_details'              => esc_html__( 'Email invoice / order details to customer', 'woocommerce' ),
				'send_order_details_admin'        => esc_html__( 'Resend new order notification', 'woocommerce' ),
				'regenerate_download_permissions' => esc_html__( 'Regenerate download permissions', 'woocommerce' ),
			]
		);
	}

	public static function post_statuses() {
		return [
			'publish' => esc_html__( 'Published', 'bulky-woocommerce-bulk-edit-products' ),
			'future'  => esc_html__( 'Scheduled', 'bulky-woocommerce-bulk-edit-products' ),
			'pending' => esc_html__( 'Pending', 'bulky-woocommerce-bulk-edit-products' ),
			'draft'   => esc_html__( 'Draft', 'bulky-woocommerce-bulk-edit-products' ),
			'private' => esc_html__( 'Private', 'bulky-woocommerce-bulk-edit-products' ),
		];
	}

	public static function get_categories( $select2 = false ) {
		$categories = get_categories( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
		$categories = json_decode( wp_json_encode( $categories ), true );

		return $select2 ? self::build_select2_categories_tree( $categories, 0 ) : self::build_dropdown_categories_tree( $categories, 0 );
	}

	private static function build_dropdown_categories_tree( $all_cats, $parent_cat, $level = 1 ) {
		$res = [];
		foreach ( $all_cats as $cat ) {
			if ( $cat['parent'] == $parent_cat ) {
				$prefix                 = str_repeat( '- ', $level - 1 );
				$res[ $cat['term_id'] ] = $prefix . $cat['name'];
				$child_cats             = self::build_dropdown_categories_tree( $all_cats, $cat['term_id'], $level + 1 );
				if ( $child_cats ) {
					$res += $child_cats;
				}
			}
		}

		return $res;
	}

	private static function build_select2_categories_tree( $all_cats, $parent_cat, $level = 1 ) {
		$res = [];
		foreach ( $all_cats as $cat ) {
			$new_cat = [];
			if ( $cat['parent'] == $parent_cat ) {
				$prefix          = str_repeat( '- ', $level - 1 );
				$new_cat['id']   = $cat['term_id'];
				$new_cat['text'] = $prefix . $cat['name'];
				$res[]           = $new_cat;
				$child_cats      = self::build_select2_categories_tree( $all_cats, $cat['term_id'], $level + 1 );
				if ( $child_cats ) {
					$res = array_merge( $res, $child_cats );
				}
			}
		}

		return $res;
	}

	public static function get_multiple_products( $product_ids ) {
		$result = [];
		if ( ! empty( $product_ids ) ) {
			$products = wc_get_products( [ 'limit' => - 1, 'include' => $product_ids ] );
			if ( ! empty( $products ) ) {
				foreach ( $products as $p ) {
					$result[] = [ 'id' => $p->get_id(), 'text' => $p->get_name() ];
				}
			}
		}

		return $result;
	}

	public static function get_extra_product_taxonomies() {
		global $wp_taxonomies;
		$filtered_taxonomies = [];
		foreach ( $wp_taxonomies as $key => $taxonomy ) {
			if ( empty( array_intersect( [ 'product', 'product_variation' ], $taxonomy->object_type ) ) ) {
				continue;
			}
			if ( substr( $key, 0, 3 ) === 'pa_' ) {
				continue;
			}
			if ( in_array( $key, [ 'product_type', 'product_visibility', 'product_cat', 'product_tag', 'product_shipping_class' ] ) ) {
				continue;
			}
			$filtered_taxonomies[ $key ] = $taxonomy->labels->name ?? '';
		}

		return $filtered_taxonomies;
	}

	public static function get_taxonomy_source( $taxonomy, $select2 = false ) {
		$categories = get_categories( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		$categories = json_decode( wp_json_encode( $categories ), true );

		return $select2 ? self::build_select2_categories_tree( $categories, 0 ) : self::build_dropdown_categories_tree( $categories, 0 );
	}
}

