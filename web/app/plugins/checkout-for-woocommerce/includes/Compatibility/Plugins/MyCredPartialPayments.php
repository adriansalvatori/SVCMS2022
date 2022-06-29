<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class MyCredPartialPayments extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'mycred_part_woo_ajax_handler' );
	}

	public function run() {
		// Catches any ajax requests
		mycred_part_woo_ajax_handler();
	}

	public function run_immediately() {
		add_action( 'woocommerce_checkout_after_order_review', 'mycred_part_woo_after_order_review' );
		add_action( 'woocommerce_review_order_before_order_total', array( $this, 'hide_order_total' ), 39 );
	}

	public function hide_order_total() {
		?>
		<style type="text/css">
			#cfw-totals-list tr.order-total {
				display: none;
			}
		</style>
		<?php
	}
}
