<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class SUMOPaymentPlans extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\SUMOPaymentPlans' );
	}

	public function run() {
		add_action( 'cfw_checkout_before_payment_methods', 'SUMO_PP_Order_Payment_Plan::render_plan_selector' );
	}
}