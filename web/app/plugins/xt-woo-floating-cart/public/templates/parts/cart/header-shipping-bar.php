<?php

/**
 * This file is used to markup the cart header message.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/cart/header-shipping-bar.php.
 *
 * Available global vars:
 *
 * @var $shipping_text
 * @var $fill_percentage
 * @var $fill_prev_percentage
 * @var $partial
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see         https://docs.xplodedthemes.com/article/127-template-structure
 * @author 		XplodedThemes
 * @package     XT_Woo_Floating_Cart/Templates
 * @version     2.3.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<?php if(!$partial): ?>

    <div class="xt_woofc-shipping-bar">
        <div class="xt_woofc-shipping-bar-inner">
            <div class="xt_woofc-shipping-bar-text"><?php echo wp_kses_post($shipping_text);?></div>
            <div class="xt_woofc-shipping-bar-perc" data-width="<?php echo esc_attr__( $fill_percentage ); ?>%">
                <span style="width:<?php echo esc_attr__( $fill_prev_percentage ); ?>%"></span>
            </div>
        </div>
    </div>

<?php else: ?>

    <div class="xt_woofc-shipping-bar-text"><?php echo esc_html($shipping_text);?></div>

<?php endif; ?>


