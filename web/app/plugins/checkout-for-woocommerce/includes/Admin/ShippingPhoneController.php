<?php

namespace Objectiv\Plugins\Checkout\Admin;

class ShippingPhoneController {
	public function __construct() {}

	public function init() {
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'shipping_phone_display_admin_order_meta' ), 10, 1 );
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_shipping_phone' ) );
	}

	public function shipping_phone_display_admin_order_meta( $order ) {
		if ( version_compare( WC()->version, '5.6.0', '<' ) ) {
			$shipping_phone = get_post_meta( $order->get_id(), '_shipping_phone', true );

			if ( empty( $shipping_phone ) ) {
				return;
			}

			/**
			 * Filter whether to enable editable shipping phone field in admin
			 *
			 * @since 3.0.0
			 *
			 * @param bool $enable_editable_admin_phone_field True show editable field, false show label
			 */
			if ( apply_filters( 'cfw_enable_editable_admin_shipping_phone_field', true ) ) {
				$field                = array();
				$field['placeholder'] = cfw__( 'Phone', 'woocommerce' );
				$field['label']       = cfw__( 'Phone', 'woocommerce' );
				$field['value']       = $shipping_phone;
				$field['name']        = '_cfw_shipping_phone';
				$field['id']          = 'cfw_shipping_phone';

				woocommerce_wp_text_input( $field );
			} else {
				echo '<p><strong>' . cfw__( 'Phone' ) . ':</strong><br /><a href="tel:' . $shipping_phone . '">' . $shipping_phone . '</a></p>';
			}
		}
	}

	/**
	 * @throws \WC_Data_Exception
	 */
	public function save_shipping_phone( $order_id ) {
		if ( isset( $_POST['_cfw_shipping_phone'] ) ) {
			$order = wc_get_order( $order_id );

			if ( version_compare( WC()->version, '5.6.0', '<' ) ) {
				$order->update_meta_data( '_shipping_phone', $_POST['_cfw_shipping_phone'] );
			} else {
				$order->set_shipping_phone( $_POST['_cfw_shipping_phone'] );
			}

			$order->save();
		}
	}
}
