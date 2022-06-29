<?php

namespace Objectiv\Plugins\Checkout\Adapters;

use Objectiv\Plugins\Checkout\Model\CartItem;
use WC_Cart;

class CartItemFactory {
	public static function get( WC_Cart $cart ): array {
		$items = array();

		foreach ( $cart->get_cart() as $key => $item ) {
			// Some of our callbacks rely on cart_item_key being a string
			// Since PHP coerces scalar types to strings for typed function arguments,
			// we just have to handle the situation where the key is null, which is
			// for some reason not coerced due to ancient secret PHP knowledge
			$key = $key ?? '';

			/** @var \WC_Product $product */
			$product = apply_filters( 'woocommerce_cart_item_product', $item['data'], $item, $key );

			$exists   = $product && $product->exists();
			$non_zero = $item['quantity'] > 0;
			$visible  = apply_filters( 'woocommerce_checkout_cart_item_visible', true, $item, $key );
			$include  = $exists && $non_zero && $visible;

			if ( ! $include ) {
				continue;
			}

			$items[] = new CartItem( $key, $item );
		}

		return $items;
	}
}
