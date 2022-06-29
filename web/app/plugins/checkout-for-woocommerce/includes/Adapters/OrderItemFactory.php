<?php

namespace Objectiv\Plugins\Checkout\Adapters;

use Objectiv\Plugins\Checkout\Model\OrderItem;
use WC_Order;

class OrderItemFactory {
	public static function get( WC_Order $order ): array {
		$items = array();

		foreach ( $order->get_items() as $item ) {
			if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
				continue;
			}

			$items[] = new OrderItem( $item );
		}

		return $items;
	}
}
