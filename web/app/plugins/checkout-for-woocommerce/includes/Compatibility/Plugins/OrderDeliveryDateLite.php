<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class OrderDeliveryDateLite extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\Order_Delivery_Date_Lite' );
	}

	public function run() {
		if ( defined( 'ORDDD_LITE_SHOPPING_CART_HOOK' ) ) {
			remove_action( ORDDD_LITE_SHOPPING_CART_HOOK, array( 'Orddd_Lite_Process', 'orddd_lite_my_custom_checkout_field' ) );
			add_action( 'cfw_checkout_after_shipping_methods', array( 'Orddd_Lite_Process', 'orddd_lite_my_custom_checkout_field' ) );
		}
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'OrderDeliveryDate',
			'params' => array(),
		);

		return $compatibility;
	}
}
