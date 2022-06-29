<?php

namespace Objectiv\Plugins\Checkout\Action;

/**
 * @link checkoutwc.com
 * @since 5.4.0
 * @package Objectiv\Plugins\Checkout\Action
 * @author Clifton Griffin <clif@objectiv.co>
 */
class UpdateSideCart extends CFWAction {

	public function __construct() {
		parent::__construct( 'update_side_cart' );
	}


	public function action() {
		check_ajax_referer( 'cfw-update-side-cart', 'security' );

		parse_str( wp_unslash( $_POST['cart_data'] ), $cart_data );

		if ( ! empty( $cart_data['cfw-promo-code'] ) ) {
			WC()->cart->add_discount( wc_format_coupon_code( wp_unslash( $cart_data['cfw-promo-code'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		do_action( 'cfw_before_update_side_cart_action', $cart_data );

		$this->out(
			array(
				'result'    => cfw_update_cart( $cart_data['cart'] ?? array() ),
				'cart_hash' => WC()->cart->get_cart_hash(),
			)
		);
	}
}
