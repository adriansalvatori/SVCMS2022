<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class AstraAddon extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'ASTRA_EXT_VER' );
	}

	public function run() {
		// This is technically running *inside* `wp` but because Astra hooks
		// their stuff on wp priority 10 and this runs at 0 we have to put our stuff after to scrub it
		add_action(
			'wp',
			function() {
				remove_action( 'woocommerce_checkout_before_order_review', 'astra_two_step_checkout_order_review_wrap', 1 );
			},
			20
		);

		$this->nuke_astra_styles_on_our_pages();
	}

	public function run_on_thankyou() {
		$this->nuke_astra_styles_on_our_pages();
	}

	public function nuke_astra_styles_on_our_pages() {
		add_filter( 'astra_addon_enqueue_assets', '__return_false' );
	}
}
