<?php

$fields[] = array(
    'id' => 'api_description',
    'section' => 'api',
    'type' => 'custom',
    'label' => esc_html__('JS API', 'woo-floating-cart'),
    'default' => '<div>'.esc_html__('These JS functions can be used to programmatically control the cart. They can be tested within your browser console.', 'woo-floating-cart').'</div>',
    'priority' => 10
);

$fields[] = array(
    'id' => 'api_toggle_cart',
    'section' => 'api',
    'type' => 'custom',
    'label' => esc_html__('Toggle Cart', 'woo-floating-cart'),
    'default' => '<input readonly="readonly" class="xirki-code-input" value="xt_woofc_toggle_cart()" /> <span class="xt-jsapi" data-function="xt_woofc_toggle_cart">Test</span>',
    'priority' => 10
);

$fields[] = array(
    'id' => 'api_open_cart',
    'section' => 'api',
    'type' => 'custom',
    'label' => esc_html__('Open Cart', 'woo-floating-cart'),
    'default' => '<input readonly="readonly" class="xirki-code-input" value="xt_woofc_open_cart()" /> <span class="xt-jsapi" data-function="xt_woofc_open_cart">Test</span>',
    'priority' => 10
);

$fields[] = array(
    'id' => 'api_close_cart',
    'section' => 'api',
    'type' => 'custom',
    'label' => esc_html__('Close Cart', 'woo-floating-cart'),
    'default' => '<input readonly="readonly" class="xirki-code-input" value="xt_woofc_close_cart()" /> <span class="xt-jsapi" data-function="xt_woofc_close_cart">Test</span>',
    'priority' => 10
);

$fields[] = array(
    'id' => 'api_refresh_cart',
    'section' => 'api',
    'type' => 'custom',
    'label' => esc_html__('Refresh Cart', 'woo-floating-cart'),
    'default' => '<input readonly="readonly" class="xirki-code-input" value="xt_woofc_refresh_cart()" /> <span class="xt-jsapi" data-function="xt_woofc_refresh_cart">Test</span>',
    'priority' => 10
);

$fields[] = array(
	'id' => 'api_is_cart_open',
	'section' => 'api',
	'type' => 'custom',
	'label' => esc_html__('Is Cart Open ?', 'woo-floating-cart'),
	'default' => '<input readonly="readonly" class="xirki-code-input" value="xt_woofc_is_cart_open()" /> <span class="xt-jsapi" data-function="xt_woofc_is_cart_open">Test</span>',
	'priority' => 10
);

$fields[] = array(
    'id' => 'api_is_cart_empty',
    'section' => 'api',
    'type' => 'custom',
    'label' => esc_html__('Is Cart Empty ?', 'woo-floating-cart'),
    'default' => '<input readonly="readonly" class="xirki-code-input" value="xt_woofc_is_cart_empty()" /> <span class="xt-jsapi" data-function="xt_woofc_is_cart_empty">Test</span>',
    'priority' => 10
);

$fields[] = array(
    'id' => 'api_scroll_to_top',
    'section' => 'api',
    'type' => 'custom',
    'label' => esc_html__('Scroll To Top', 'woo-floating-cart'),
    'default' => '<input readonly="readonly" class="xirki-code-input" value="xt_woofc_scroll_to_top()" /> <span class="xt-jsapi" data-function="xt_woofc_scroll_to_top">Test</span>',
    'priority' => 10
);

$fields[] = array(
    'id' => 'api_scroll_to_bottom',
    'section' => 'api',
    'type' => 'custom',
    'label' => esc_html__('Scroll To Bottom', 'woo-floating-cart'),
    'default' => '<input readonly="readonly" class="xirki-code-input" value="xt_woofc_scroll_to_bottom()" /> <span class="xt-jsapi" data-function="xt_woofc_scroll_to_bottom">Test</span>',
    'priority' => 10
);