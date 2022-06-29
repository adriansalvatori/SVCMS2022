<?php

$customizer = xt_woo_floating_cart()->customizer();
$options = $customizer->get_options();

$move_options = array(
    'cart_body_bg_color' => 'bg_color',
    'cart_body_text_color' => 'text_color',
    'cart_body_primary_color' => 'primary_color',
    'cart_body_accent_color' => 'accent_color',
    'cart_body_link_color' => 'link_color',
    'cart_body_link_hover_color' => 'link_hover_color',
    'cart_body_border_color' => 'border_color',
    'cart_header_error_color' => 'error_color'
);


foreach ( $move_options as $key => $new_key ) {

    $options[$new_key] = isset($options[$key]) ? $options[$key] : '';

    if(isset($options[$key])) {
        unset($options[$key]);
    }
}

$customizer->update_options($options);

