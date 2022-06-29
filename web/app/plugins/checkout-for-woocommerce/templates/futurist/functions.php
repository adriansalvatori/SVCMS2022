<?php
/**
 * Add heading to cart
 *
 * Priority 21 puts it in the cart summary content div
 */

use Objectiv\Plugins\Checkout\Managers\SettingsManager;

add_action( 'cfw_checkout_cart_summary', 'cfw_futurist_cart_heading', 21 );
add_action( 'cfw_thank_you_cart_summary', 'cfw_futurist_cart_heading', 21 );
add_action( 'cfw_order_pay_cart_summary', 'cfw_futurist_cart_heading', 21 );

function cfw_futurist_cart_heading() {
	?>
	<h3>
		<?php _e( 'Your Cart', 'checkout-wc' ); ?>
	</h3>
	<?php
}

remove_action( 'cfw_checkout_before_order_review', 'cfw_breadcrumb_navigation', 10 );
add_action( 'cfw_checkout_main_container_start', 'futurist_breadcrumb_navigation', 10 );

function futurist_breadcrumb_navigation() {
	cfw_auto_wrap( 'cfw_breadcrumb_navigation' );
}

