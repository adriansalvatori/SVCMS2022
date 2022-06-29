<?php

/**
 * This file is used to markup the checkout complete thank you message.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/checkout/thank-you.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see         https://docs.xplodedthemes.com/article/127-template-structure
 * @author 		XplodedThemes
 * @package     XT_Woo_Floating_Cart/Templates
 * @version     2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$action = xt_woofc_option('cart_checkout_complete_action', 'redirect');

?>
<div id="xt_woofc-checkout-thankyou">
    <h2>
        <strong><?php echo apply_filters( 'xt_woofc_checkout_thankyou_text', esc_html__( 'Thank you.', 'woo-floating-cart' ) ); ?></strong>
        <?php echo apply_filters( 'xt_woofc_checkout_order_received_text', esc_html__( 'Your order has been received.', 'woo-floating-cart' ) ); ?>
    </h2>

    <?php if($action === 'redirect'): ?>

        <p><?php echo esc_html__('Please wait while redirecting to your order...', 'woo-floating-cart');?></p>
        <?php xt_woofc_spinner_html(); ?>

    <?php else: ?>

        <a href="#" class="button"><?php echo esc_html__('View Order', 'woo-floating-cart');?></a>

    <?php endif; ?>
</div>