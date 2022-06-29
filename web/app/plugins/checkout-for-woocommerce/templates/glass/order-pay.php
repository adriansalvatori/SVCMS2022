<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Fires before <main> container on order pay page
 *
 * @since 3.0.0
 *
 * @param WC_Order $order The order object
 */
do_action( 'cfw_order_pay_before_main_container', $order ); ?>
	<main id="cfw" class="<?php echo cfw_main_container_classes( 'order-pay' ); ?> cfw-payment-method-active">
		<?php
		/**
		 * Fires at top of <main> container on order pay page
		 *
		 * @since 3.0.0
		 *
		 * @param WC_Order $order The order object
		 */
		do_action( 'cfw_order_pay_main_container_start', $order );
		?>

		<?php if ( ! empty( $order ) ) : ?>
			<div class="row">
				<!-- Order Review -->
				<div id="cfw-order-review" class="col-lg-7 cfw-rp" role="main">
					<?php
					/**
					 * Fires at top of #order_review on order pay page
					 *
					 * @since 3.0.0
					 */
					do_action( 'cfw_order_pay_before_order_review' );

					/**
					 * Fires in #order_review container on order pay page
					 *
					 * @since 3.0.0
					 *
					 * @param WC_Order $order The order object
					 * @param bool $call_receipt_hook Whether to call receipt hook
					 * @param array $available_gateways The available gateways
					 * @param string $order_button_text The text to use for the place order button
					 */
					do_action( 'cfw_order_pay_content', $order, $call_receipt_hook, $available_gateways, $order_button_text );

					/**
					 * Fires at bottom of #order_review on order pay page
					 *
					 * @since 3.0.0
					 */
					do_action( 'cfw_order_pay_after_order_review' );
					?>
				</div>

				<!-- Cart / Sidebar Column -->
				<div id="cfw-cart-summary" class="col-lg-5" role="complementary">
					<?php
					/**
					 * Fires in cart summary sidebar container
					 *
					 * @since 3.0.0
					 *
					 * @param WC_Order $order The order object
					 */
					do_action( 'cfw_order_pay_cart_summary', $order );
					?>
				</div>
			</div>
			<?php
		endif;

		/**
		 * Fires at bottom of <main> container on order pay page
		 *
		 * @since 3.0.0
		 *
		 * @param WC_Order $order The order object
		 */
		do_action( 'cfw_order_pay_main_container_end', $order );
		?>
	</main>
<?php
/**
 * Fires after <main> container on order pay page
 *
 * @since 3.0.0
 *
 * @param WC_Order $order The order object
 */
do_action( 'cfw_order_pay_after_main_container', $order );
