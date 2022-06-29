<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WooCommerceSubscriptionGifting extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\WCSG_Cart' );
	}

	public function run() {
		add_action( 'cfw_after_cart_item_row', array( $this, 'maybe_display_gifting_information' ), 10, 2 );
	}

	public function maybe_display_gifting_information( $cart_item, $cart_item_key ) {
		?>
		<tr class="cfw-woocommerce-subscription-gifting">
			<td colspan="4">
				<?php \WCSG_Cart::maybe_display_gifting_information( $cart_item, $cart_item_key, true ); ?>
			</td>
		</tr>
		<?php
	}
}
