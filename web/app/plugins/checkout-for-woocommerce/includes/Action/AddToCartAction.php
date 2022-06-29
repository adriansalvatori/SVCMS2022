<?php

namespace Objectiv\Plugins\Checkout\Action;

/**
 * @link checkoutwc.com
 * @since 5.4.0
 * @package Objectiv\Plugins\Checkout\Action
 * @author Clifton Griffin <clif@objectiv.co>
 */
class AddToCartAction extends CFWAction {

	public function __construct() {
		parent::__construct( 'cfw_add_to_cart' );
	}


	public function action() {
		$result = false;

		if ( isset( $_POST['add-to-cart'] ) && empty( wc_get_notices( 'error' ) ) ) {
			do_action( 'woocommerce_ajax_added_to_cart', intval( $_POST['add-to-cart'] ) );
			$result = true;
		}

		$quantity   = $_REQUEST['quantity'] ?? 1;
		$product_id = $_REQUEST['add-to-cart'];

		cfw_remove_add_to_cart_notice( $product_id, $quantity );

		$this->out(
			array(
				'result'    => $result,
				'cart_hash' => WC()->cart->get_cart_hash(),
				'redirect'  => apply_filters( 'cfw_add_to_cart_redirect', false, $product_id ),
			)
		);
	}
}
