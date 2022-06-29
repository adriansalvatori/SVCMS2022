<?php

namespace WCBEditor\Includes\Products;

use WCBEditor\Includes\Abstracts\History_Abstract;

defined( 'ABSPATH' ) || exit;

class Product_History extends History_Abstract {

	protected static $instance = null;

	public function __construct() {
		$this->type = 'products';

		if ( ! wp_next_scheduled( 'vi_wbe_remove_revision' ) ) {
			wp_schedule_event( time(), 'daily', 'vi_wbe_remove_revision' );
		}

		add_action( 'vi_wbe_remove_revision', array( $this, 'remove_revision' ) );

		parent::__construct();
	}

	public static function instance() {
		return self::$instance == null ? self::$instance = new self : self::$instance;
	}

	public function get_remove_history_time() {
		return Products::instance()->get_setting( 'auto_remove_revision' );
	}

	public function revert_history_product_attribute() {
		$pid        = ! empty( $_POST['pid'] ) ? sanitize_text_field( $_POST['pid'] ) : '';
		$history_id = ! empty( $_POST['history_id'] ) ? sanitize_text_field( $_POST['history_id'] ) : '';
		$attribute  = ! empty( $_POST['attribute'] ) ? sanitize_text_field( $_POST['attribute'] ) : '';

		if ( $pid && $history_id && $attribute ) {
			$product = wc_get_product( $pid );

			if ( ! is_object( $product ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Product is not exist', 'bulky-woocommerce-bulk-edit-products' ) ] );
			}

			$history = $this->get_history_by_id( $history_id )->history;
			$pid     = $product->get_id();
			if ( isset( $history[ $pid ][ $attribute ] ) ) {
				$handle = Handle_Product::instance();
				$handle->parse_product_data_to_save( $product, $attribute, $history[ $pid ][ $attribute ] );
				$product->save();
			}
		}

		wp_send_json_success();
	}

	public function revert_history_all_products() {
		$history_id = ! empty( $_POST['history_id'] ) ? sanitize_text_field( $_POST['history_id'] ) : '';
		if ( ! $history_id ) {
			wp_send_json_error( [ 'message' => esc_html__( 'No history id', 'bulky-woocommerce-bulk-edit-products' ) ] );
		}
		$history = $this->get_history_by_id( $history_id )->history;

		if ( ! empty( $history ) && is_array( $history ) ) {
			$handle = Handle_Product::instance();

			foreach ( $history as $pid => $data ) {
				$product = wc_get_product( $pid );

				if ( ! is_object( $product ) ) {
					continue;
				}

				if ( ! empty( $data ) && is_array( $data ) ) {
					foreach ( $data as $type => $value ) {
						$handle->parse_product_data_to_save( $product, $type, $value );
					}
				}

				$product->save();
			}
		}
	}

	public function revert_single_product() {
		$pid        = ! empty( $_POST['pid'] ) ? sanitize_text_field( $_POST['pid'] ) : '';
		$history_id = ! empty( $_POST['history_id'] ) ? sanitize_text_field( $_POST['history_id'] ) : '';

		if ( $pid && $history_id ) {
			$product = wc_get_product( $pid );

			if ( ! is_object( $product ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Product is not exist', 'bulky-woocommerce-bulk-edit-products' ) ] );
			}

			$history         = $this->get_history_by_id( $history_id )->history;
			$pid             = $product->get_id();
			$product_history = $history[ $pid ] ?? '';

			if ( ! empty( $product_history ) && is_array( $product_history ) ) {
				$handle = Handle_Product::instance();
				foreach ( $product_history as $type => $value ) {
					$handle->parse_product_data_to_save( $product, $type, $value );
				}

				$product->save();
			}
		}
	}

	public function compare_history_point_and_current( $id ) {
		$full_history = $this->get_history_by_id( $id );
		$products     = $full_history->history;
		$columns      = Products::instance()->define_columns();

		if ( ! empty( $products ) && is_array( $products ) ) {
			$r = [];
			foreach ( $products as $pid => $history ) {
				$product = wc_get_product( $pid );
				if ( ! is_object( $product ) ) {
					continue;
				}

				$fields  = array_keys( $history );
				$current = Handle_Product::instance()->get_product_data( $product, $fields );
				$current = array_combine( $fields, $current );

				$fields_parsed = [];
				foreach ( $fields as $key ) {
					$fields_parsed[ $key ] = $columns[ $key ]['title'] ?? '';
				}

				$r[ $pid ] = [
					'name'    => $product->get_name(),
					'fields'  => $fields_parsed,
					'history' => $history,
					'current' => $current,
				];
			}
		}

		return [ 'compare' => $r ?? '', 'date' => date_i18n( wc_date_format() . ' ' . wc_time_format(), $full_history->date ) ];
	}

}
