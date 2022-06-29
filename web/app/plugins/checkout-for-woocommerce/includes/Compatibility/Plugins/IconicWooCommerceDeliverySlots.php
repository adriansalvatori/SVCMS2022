<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class IconicWooCommerceDeliverySlots extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\Iconic_WDS' );
	}

	public function pre_init() {
		add_filter( 'iconic_wds_field_position_choices', array( $this, 'add_checkoutwc_choices' ) );
	}

	public function add_checkoutwc_choices( $choices ) {
		$choices['cfw_checkout_after_shipping_methods'] = 'CheckoutWC: After shipping methods';

		return $choices;
	}
}
