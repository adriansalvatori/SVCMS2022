<?php

/**
 * This file is used to markup the cart header message.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/cart/header-message.php.
 *
 * Available global vars:
 *
 * @var $message
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
 * @version     2.1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if(!$partial) {
    $container = '<div class="xt_woofc-header-message">%s</div>';
}else{
    $container = '%s';
}

printf( $container, $message );
