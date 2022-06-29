<?php

namespace WCBEditor\Includes\Orders;

defined( 'ABSPATH' ) || exit;

class Handle_Order {

	protected static $instance = null;
	protected $fields;
	protected $meta_fields;

	public function __construct() {
		$this->fields      = Orders::instance()->filter_fields();
		$this->meta_fields = get_option( 'vi_wbe_order_meta_fields' );
	}

	public static function instance() {
		return self::$instance == null ? self::$instance = new self : self::$instance;
	}

	public function get_order_data( $order, $fields ) {
		$p_data = [];
		foreach ( $fields as $field ) {
			switch ( $field ) {

				case 'id':
					$p_data[] = $order->get_id();
					break;

				case 'total':
					$p_data[] = $order->get_total( 'edit' );
					break;

				case 'post_date':
					$date     = $order->get_date_created( 'edit' );
					$p_data[] = $date ? $date->date_i18n( 'Y-m-d H:i' ) : '';
					break;

				case 'status':
					$p_data[] = 'wc-' . $order->get_status( 'edit' );
					break;

				case 'order_key':
					if ( method_exists( $order, 'get_order_key' ) ) {
						$p_data[] = $order->get_order_key();
					} else {
						$p_data[] = '';
					}
					break;

				case 'billing_fisrt_name':
					$p_data[] = $order->get_billing_first_name( 'edit' );
					break;

				case 'billing_last_name':
					$p_data[] = $order->get_billing_last_name( 'edit' );
					break;

				case 'billing_company':
					$p_data[] = $order->get_billing_company( 'edit' );
					break;

				case 'billing_address_1':
					$p_data[] = $order->get_billing_address_1( 'edit' );
					break;

				case 'billing_address_2':
					$p_data[] = $order->get_billing_address_2( 'edit' );
					break;

				case 'billing_city':
					$p_data[] = $order->get_billing_city( 'edit' );
					break;

				case 'billing_postcode':
					$p_data[] = $order->get_billing_postcode( 'edit' );
					break;

				case 'billing_country':
					$p_data[] = $order->get_billing_country( 'edit' );
					break;

				case 'billing_state':
					$p_data[] = $order->get_billing_state( 'edit' );
					break;

				case 'billing_email':
					$p_data[] = $order->get_billing_email( 'edit' );
					break;

				case 'billing_phone':
					$p_data[] = $order->get_billing_phone( 'edit' );
					break;

				case 'payment_method':
					$pm       = $order->get_payment_method( 'edit' );
					$p_data[] = $pm ? $pm : 'n/a';
					break;

				case 'transaction_id':
					$p_data[] = $order->get_transaction_id( 'edit' );
					break;

				case 'shipping_fisrt_name':
					$p_data[] = $order->get_shipping_first_name( 'edit' );
					break;

				case 'shipping_last_name':
					$p_data[] = $order->get_shipping_last_name( 'edit' );
					break;

				case 'shipping_company':
					$p_data[] = $order->get_shipping_company( 'edit' );
					break;

				case 'shipping_address_1':
					$p_data[] = $order->get_shipping_address_1( 'edit' );
					break;

				case 'shipping_address_2':
					$p_data[] = $order->get_shipping_address_2( 'edit' );
					break;

				case 'shipping_city':
					$p_data[] = $order->get_shipping_city( 'edit' );
					break;

				case 'shipping_postcode':
					$p_data[] = $order->get_shipping_postcode( 'edit' );
					break;

				case 'shipping_country':
					$p_data[] = $order->get_shipping_country( 'edit' );
					break;

				case 'shipping_state':
					$p_data[] = $order->get_shipping_state( 'edit' );
					break;

				case 'shipping_phone':
					$p_data[] = $order->get_shipping_phone( 'edit' );
					break;

				case 'post_excerpt':
					$p_data[] = $order->get_customer_note( 'edit' );
					break;

				case 'order_notes':
					$order_id = $order->get_id();
					$notes    = wc_get_order_notes( [ 'order_id' => $order_id ] );
					$_notes   = [];

					if ( ! empty( $notes ) ) {
						foreach ( $notes as $note ) {
							$_notes[] = [
								'id'            => $note->id,
								'customer_note' => $note->customer_note,
								'content'       => $note->content,
								'added_by'      => $note->added_by,
								'date'          => sprintf( esc_html__( '%1$s at %2$s by %3$s', 'bulky-woocommerce-bulk-edit-products' ),
									$note->date_created->date_i18n( wc_date_format() ), $note->date_created->date_i18n( wc_time_format() ), $note->added_by ),
							];
						}
					}

					$p_data[] = $_notes;
					break;

				case 'items':
					$items = $order->get_items();

					if ( empty( $items ) ) {
						$p_data[] = '';
					}

					$parse_items = '';
					foreach ( $items as $item ) {
						$data = $item->get_data();
						if ( isset( $data['name'], $data['quantity'], $data['total'] ) ) {
							$parse_items .= "{$data['name']} x {$data['quantity']} = {$data['total']}\n";
						}
					}

					$p_data[] = $parse_items;

					break;

				default:
					if ( ! empty( $this->meta_fields[ $field ] ) ) {
						$meta_type = $this->meta_fields[ $field ]['input_type'];
						$data      = get_post_meta( $order->get_id(), $field, true );
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

	public function get_order_data_for_edit( $order ) {
		return $this->get_order_data( $order, $this->fields );
	}

	public function parse_order_data_to_save( &$order, $type, $value ) {

		switch ( $type ) {
			case 'id':
			case 'items':
				break;

			case 'post_date':
				$order->set_date_created( $value );
				break;

			case 'status':
				$order->set_status( $value );
				break;

			case 'order_key':
				$order->set_order_key( $value );
				break;

			case 'billing_fisrt_name':
				$p_data[] = $order->set_billing_first_name( $value );
				break;

			case 'billing_last_name':
				$p_data[] = $order->set_billing_last_name( $value );
				break;

			case 'billing_company':
				$p_data[] = $order->set_billing_company( $value );
				break;

			case 'billing_address_1':
				$p_data[] = $order->set_billing_address_1( $value );
				break;

			case 'billing_address_2':
				$p_data[] = $order->set_billing_address_2( $value );
				break;

			case 'billing_city':
				$p_data[] = $order->set_billing_city( $value );
				break;

			case 'billing_postcode':
				$p_data[] = $order->set_billing_postcode( $value );
				break;

			case 'billing_country':
				$p_data[] = $order->set_billing_country( $value );
				break;

			case 'billing_state':
				$p_data[] = $order->set_billing_state( $value );
				break;

			case 'billing_email':
				$p_data[] = $order->set_billing_email( $value );
				break;

			case 'billing_phone':
				$p_data[] = $order->set_billing_phone( $value );
				break;

			case 'payment_method':
				$value    = $value == 'n/a' ? '' : $value;
				$p_data[] = $order->set_payment_method( $value );
				break;

			case 'transaction_id':
				$p_data[] = $order->set_transaction_id( $value );
				break;

			case 'shipping_fisrt_name':
				$p_data[] = $order->set_shipping_first_name( $value );
				break;

			case 'shipping_last_name':
				$p_data[] = $order->set_shipping_last_name( $value );
				break;

			case 'shipping_company':
				$p_data[] = $order->set_shipping_company( $value );
				break;

			case 'shipping_address_1':
				$p_data[] = $order->set_shipping_address_1( $value );
				break;

			case 'shipping_address_2':
				$p_data[] = $order->set_shipping_address_2( $value );
				break;

			case 'shipping_city':
				$p_data[] = $order->set_shipping_city( $value );
				break;

			case 'shipping_postcode':
				$p_data[] = $order->set_shipping_postcode( $value );
				break;

			case 'shipping_country':
				$p_data[] = $order->set_shipping_country( $value );
				break;

			case 'shipping_state':
				$p_data[] = $order->set_shipping_state( $value );
				break;

			case 'shipping_phone':
				$p_data[] = $order->set_shipping_phone( $value );
				break;

			case 'post_excerpt':
				$p_data[] = $order->set_customer_note( $value );
				break;

			default:
				$meta_fields = get_option( 'vi_wbe_order_meta_fields' );

				if ( ! empty( $meta_fields ) && is_array( $meta_fields ) && in_array( $type, array_keys( $meta_fields ) ) ) {
					$data_type = $meta_fields[ $type ]['input_type'] ?? '';
					$pid       = $order->get_id();

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
