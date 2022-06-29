<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Fires before <main> container
 *
 * @since 3.0.0
 *
 * @param WC_Checkout $checkout The checkout object
 */
do_action( 'cfw_checkout_before_main_container', WC()->checkout() ); ?>
	<main id="cfw" class="<?php echo cfw_main_container_classes(); ?>">
		<div class="row">
			<div class="col-lg-7 cfw-rp">
				<?php
				/**
				 * Fires at the beginning of the <main> container
				 *
				 * @since 3.0.0
				 */
				do_action( 'cfw_checkout_main_container_start' );
				?>
			</div>
		</div>
		<?php
		/**
		 * Filters whether to use the standard CheckoutWC form or allow it to be overridden
		 *
		 * @since 2.0.0
		 *
		 * @param bool $replace Whether to replace the form or use the standard one
		 */
		if ( ! apply_filters( 'cfw_replace_form', false ) ) :
			?>
			<form <?php cfw_form_attributes(); ?>>
				<!-- Order Review -->
				<?php
				/**
				 * Fires before the #order_review container inside the checkout form
				 *
				 * @since 3.0.0
				 */
				do_action( 'cfw_checkout_before_order_review_container' );
				?>

				<div id="order_review" class="col-lg-7 cfw-rp" role="main">
					<?php
					/**
					 * Fires at the top of the #order_review container
					 *
					 * @since 3.0.0
					 */
					do_action( 'cfw_checkout_before_order_review' );
					?>

					<?php
					/**
					 * Fires in the #order_review container
					 *
					 * @since 3.0.0
					 */
					do_action( 'cfw_checkout_tabs' );
					?>

					<?php
					/**
					 * Fires at the bottom of the #order_review container
					 *
					 * @since 3.0.0
					 */
					do_action( 'cfw_checkout_after_order_review' );
					?>
				</div>

				<?php
				/**
				 * Fires after the #order_review container inside the checkout form
				 *
				 * @since 3.0.0
				 */
				do_action( 'cfw_checkout_after_order_review_container' );
				?>

				<!-- Cart Summary -->
				<div id="cfw-cart-summary" class="col-lg-5" role="complementary">
					<?php
					/**
					 * Fires inside the cart summary sidebar container
					 *
					 * @since 3.0.0
					 */
					do_action( 'cfw_checkout_cart_summary' );
					?>
				</div>

				<?php
				/**
				 * Fires after inside the cart summary sidebar container
				 *
				 * @since 3.0.0
				 */
				do_action( 'cfw_checkout_after_cart_summary_container' );
				?>
			</form>
		<?php else : ?>
			<?php
			/**
			 * Fires to allow standard CheckoutWC form to be replaced.
			 *
			 * Only fires when cfw_replace_form is true
			 *
			 * @since 3.0.0
			 */
			do_action( 'cfw_checkout_form' );
			?>
		<?php endif; ?>

		<div class="row">
			<div class="col-lg-7 cfw-rp">
				<?php
				/**
				 * Fires at the bottom of <main> container
				 *
				 * @since 3.0.0
				 * @param WC_Checkout $checkout The checkout object
				 */
				do_action( 'cfw_checkout_main_container_end', WC()->checkout() );
				?>
			</div>
		</div>
	</main>
<?php
/**
 * Fires after the <main> container
 *
 * @since 3.0.0
 *
 * @param WC_Checkout $checkout The checkout object
 */
do_action( 'cfw_checkout_after_main_container', WC()->checkout() );
