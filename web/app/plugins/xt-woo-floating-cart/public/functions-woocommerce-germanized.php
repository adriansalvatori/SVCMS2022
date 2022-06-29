<?php

function xt_woofc_woocommerce_germanized_init() {

    xt_woo_floating_cart()->frontend()->define_cart_constant();

    foreach ( wc_gzd_get_cart_shopmarks() as $shopmark ) {
        $shopmark->execute();
    }
}

function xt_woofc_woocommerce_germanized_loaded() {

    if(function_exists('wc_gzd_get_cart_shopmarks')) {

        add_action( 'xt_woofc_before_product', 'xt_woofc_woocommerce_germanized_init', 10);
    }
}
add_action( 'init', 'xt_woofc_woocommerce_germanized_loaded');