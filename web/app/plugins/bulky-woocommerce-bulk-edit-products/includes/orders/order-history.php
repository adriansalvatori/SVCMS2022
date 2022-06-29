<?php

namespace WCBEditor\Includes\Orders;

use WCBEditor\Includes\Abstracts\History_Abstract;

defined( 'ABSPATH' ) || exit;

class Order_History extends History_Abstract {

	protected static $instance = null;

	public function __construct() {
		$this->type = 'orders';

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
		return Orders::instance()->get_setting( 'auto_remove_revision' );
	}

	public function revert_history_product_attribute() {
		$pid        = ! empty( $_POST['pid'] ) ? sanitize_text_field( $_POST['pid'] ) : '';
		$history_id = ! empty( $_POST['history_id'] ) ? sanitize_text_field( $_POST['history_id'] ) : '';
		$attribute  = ! empty( $_POST['attribute'] ) ? sanitize_text_field( $_POST['attribute'] ) : '';

		if ( $pid && $history_id && $attribute ) {
			$order = wc_get_order( $pid );

			if ( ! is_object( $order ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Order is not exist', 'bulky-woocommerce-bulk-edit-products' ) ] );
			}

			$history = $this->get_history_by_id( $history_id )->history;
			$pid     = $order->get_id();
			if ( isset( $history[ $pid ][ $attribute ] ) ) {
				$handle = Handle_Order::instance();
				$handle->parse_order_data_to_save( $order, $attribute, $history[ $pid ][ $attribute ] );
				$order->save();
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
			$handle = Handle_Order::instance();

			foreach ( $history as $pid => $data ) {
				$order = wc_get_order( $pid );

				if ( ! is_object( $order ) ) {
					continue;
				}

				if ( ! empty( $data ) && is_array( $data ) ) {
					foreach ( $data as $type => $value ) {
						$handle->parse_order_data_to_save( $order, $type, $value );
					}
				}

				$order->save();
			}
		}
	}

	public function revert_single_product() {
		$pid        = ! empty( $_POST['pid'] ) ? sanitize_text_field( $_POST['pid'] ) : '';
		$history_id = ! empty( $_POST['history_id'] ) ? sanitize_text_field( $_POST['history_id'] ) : '';

		if ( $pid && $history_id ) {
			$order = wc_get_order( $pid );

			if ( ! is_object( $order ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Order is not exist', 'bulky-woocommerce-bulk-edit-products' ) ] );
			}

			$history       = $this->get_history_by_id( $history_id )->history;
			$pid           = $order->get_id();
			$order_history = $history[ $pid ] ?? '';

			if ( ! empty( $order_history ) && is_array( $order_history ) ) {
				$handle = Handle_Order::instance();
				foreach ( $order_history as $type => $value ) {
					$handle->parse_order_data_to_save( $order, $type, $value );
				}

				$order->save();
			}
		}
	}

	public function compare_history_point_and_current( $id ) {
		$full_history = $this->get_history_by_id( $id );
		$orders       = $full_history->history;
		$columns      = Orders::instance()->define_columns();

		if ( ! empty( $orders ) && is_array( $orders ) ) {
			$r = [];
			foreach ( $orders as $pid => $history ) {
				$orders = wc_get_order( $pid );
				if ( ! is_object( $orders ) ) {
					continue;
				}

				$fields  = array_keys( $history );
				$current = Handle_Order::instance()->get_order_data( $orders, $fields );
				$current = array_combine( $fields, $current );

				$fields_parsed = [];
				foreach ( $fields as $key ) {
					$fields_parsed[ $key ] = $columns[ $key ]['title'] ?? '';
				}

				$r[ $pid ] = [
					'name'    => esc_html__( 'Order #', 'bulky-woocommerce-bulk-edit-products' ) . $orders->get_id(),
					'fields'  => $fields_parsed,
					'history' => $history,
					'current' => $current,
				];
			}
		}

		return [ 'compare' => $r ?? '', 'date' => date_i18n( wc_date_format() . ' ' . wc_time_format(), $full_history->date ) ];
	}

}
