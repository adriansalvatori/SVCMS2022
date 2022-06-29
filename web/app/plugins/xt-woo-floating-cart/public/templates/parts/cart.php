<?php

/**
 * This file is used to markup the cart part of the minicart.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/cart.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see         https://docs.xplodedthemes.com/article/127-template-structure
 * @author 		XplodedThemes
 * @package     XT_Woo_Floating_Cart/Templates
 * @version     2.1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div class="xt_woofc-inner">

    <div class="xt_woofc-wrapper">
    <?php
        xt_woo_floating_cart()->get_template( 'parts/cart/header' );
	    xt_woo_floating_cart()->get_template( 'parts/cart/body' );
		xt_woo_floating_cart()->get_template( 'parts/cart/footer' );

        do_action('xt_woofc_custom_payment_buttons');

        xt_woofc_spinner_html();
    ?>
    </div> <!-- .xt_woofc-wrapper -->
</div> <!-- .xt_woofc-inner -->