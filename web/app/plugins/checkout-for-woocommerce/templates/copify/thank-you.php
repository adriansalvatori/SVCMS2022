<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Fires before <main> container on thank you page
 *
 * @since 3.0.0
 *
 * @param WC_Order $order The order object
 */
do_action( 'cfw_thank_you_before_main_container', $order ); ?>
<main id="cfw" class="<?php echo cfw_main_container_classes( 'thank-you' ); ?>">
	<?php
	/**
	 * Fires at top of <main> container on thank you page
	 *
	 * @since 3.0.0
	 *
	 * @param WC_Order $order The order object
	 */
	do_action( 'cfw_thank_you_main_container_start', $order );
	?>

	<?php if ( ! empty( $order ) ) : ?>
		<div class="row">
			<!-- Order Review -->
			<div id="order_review" class="col-lg-7 cfw-rp" role="main">
				<?php
				/**
				 * Fires at top of #order_review on thank you page
				 *
				 * @since 3.0.0
				 */
				do_action( 'cfw_thank_you_before_order_review' );

				/**
				 * Fires before <main> container on thank you page
				 *
				 * @since 3.0.0
				 *
				 * @param WC_Order $order The order object
				 * @param array $order_statuses The order statuses we are progressing through
				 * @param bool $show_downloads Whether to show downloads section
				 * @param array $downloads The downloads
				 */
				do_action( 'cfw_thank_you_content', $order, $order_statuses, $show_downloads, $downloads );

				/**
				 * Prevent thank you hooks from running when viewing order
				 *
				 * If we don't do this, tracking scripts will be distorted.
				 */
				if ( empty( $_GET['view'] ) ) :
					/** This action is documented in woocommerce/templates/checkout/thankyou.php **/
					do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
					do_action( 'woocommerce_thankyou', $order->get_id() );
				else :
					do_action( 'woocommerce_view_order', $order->get_id() );
				endif;

				/**
				 * Fires at the end of <main> container on thank you page
				 *
				 * @since 3.0.0
				 */
				do_action( 'cfw_thank_you_after_order_review' );
				?>
			</div>

			<!-- Cart / Sidebar Column -->
			<div id="cfw-cart-summary" class="col-lg-5" role="complementary">
				<?php
				/**
				 * Fires in cart summary sidebar container on thank you page
				 *
				 * @since 3.0.0
				 *
				 * @param WC_Order $order The order object
				 */
				do_action( 'cfw_thank_you_cart_summary', $order );
				?>
			</div>
		</div>
		<?php
	endif;

	/**
	 * Fires at the bottom of <main> container on thank you page
	 *
	 * @since 3.0.0
	 *
	 * @param WC_Order $order The order object
	 */
	do_action( 'cfw_thank_you_main_container_end', $order );
	?>
</main>
<?php
/**
 * Fires after <main> container on thank you page
 *
 * @since 3.0.0
 *
 * @param WC_Order $order The order object
 */
do_action( 'cfw_thank_you_after_main_container', $order );
