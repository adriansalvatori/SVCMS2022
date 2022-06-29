<?php

$fields[] = array(
    'id'       => 'show_archive_add_to_cart_button',
    'section'  => 'shop',
    'label'    => esc_html__( 'Force showing add to cart button on shop page', 'xt-framework' ),
    'description'    => esc_html__( 'Some themes do not show the add to cart button on the catalog. This will force it to show. Disable it if you see a duplicated button.', 'xt-framework' ),
    'type'        => 'radio-buttonset',
    'choices'     => array(
        '0' => esc_html__( 'No', 'xt-framework' ),
        '1' => esc_html__( 'Yes', 'xt-framework' )
    ),
    'default'  => '0',
    'priority' => 10
);

$fields[] = array(
    'id'          => 'ajax_add_to_cart',
    'section'     => 'shop',
    'label'       => esc_html__( 'Enable Ajax Add To Cart', 'xt-framework' ),
    'description' => sprintf( esc_html__( 'Enable Ajax Add To Cart on Shop / Archive Pages', 'xt-framework' ), '<strong>', '</strong>' ),
    'type'        => 'radio-buttonset',
    'choices'     => array(
        '0' => esc_attr__( 'No', 'xt-framework' ),
        '1' => esc_attr__( 'Yes', 'xt-framework' )
    ),
    'transport' => 'postMessage',
    'default'     => '1'
);

$fields[] = array(
    'id'       => 'hide_view_cart_button',
    'section'  => 'shop',
    'label'    => esc_html__( 'Hide "View Cart" Link after add to cart', 'xt-framework' ),
    'type'        => 'radio-buttonset',
    'choices'     => array(
        '0' => esc_html__( 'No', 'xt-framework' ),
        '1' => esc_html__( 'Yes', 'xt-framework' )
    ),
    'default'  => '0',
    'transport' => 'postMessage',
    'js_vars'  => array(
        array(
            'element'  => 'body',
            'function' => 'toggleClass',
            'class' => 'xt_atc_hide_view_cart',
            'value' => '1'
        )
    ),
    'priority' => 10
);

$fields[] = array(
    'id'          => 'single_ajax_add_to_cart',
    'section'     => 'single',
    'label'       => esc_html__( 'Enable Ajax Add To Cart', 'xt-framework' ),
    'description' => sprintf( esc_html__( 'Enable Ajax Add To Cart on Single Product Pages', 'xt-framework' ), '<strong>', '</strong>' ),
    'type'        => 'radio-buttonset',
    'choices'     => array(
        '0' => esc_attr__( 'No', 'xt-framework' ),
        '1' => esc_attr__( 'Yes', 'xt-framework' )
    ),
    'transport' => 'postMessage',
    'default'     => '1'
);

$fields[] = array(
    'id'          => 'single_refresh_fragments',
    'section'     => 'single',
    'label'       => esc_html__( 'Force Refresh Fragments After Adding', 'xt-framework' ),
    'description' => sprintf( esc_html__( 'Enable this only if you notice that after adding a product to the cart, the totals are not correct due to conflicts with your theme or other plugins. ', 'xt-framework' ), '<strong>', '</strong>' ),
    'type'        => 'radio-buttonset',
    'choices'     => array(
        '0' => esc_attr__( 'No', 'xt-framework' ),
        '1' => esc_attr__( 'Yes', 'xt-framework' )
    ),
    'transport' => 'postMessage',
    'default'     => '0',
    'active_callback' => array(
        array(
            'setting'  => 'single_ajax_add_to_cart',
            'operator' => '==',
            'value'    => '1',
        ),
    ),
);

$fields[] = array(
    'id'          => 'single_added_scroll_to_notice',
    'section'     => 'single',
    'label'       => esc_html__( 'Scroll up to success notice', 'xt-framework' ),
    'description' => sprintf( esc_html__( 'Scroll up to "Added to cart" notice after successfully adding to cart.', 'xt-framework' ), '<strong>', '</strong>' ),
    'type'        => 'radio-buttonset',
    'choices'     => array(
        '0' => esc_attr__( 'No', 'xt-framework' ),
        '1' => esc_attr__( 'Yes', 'xt-framework' )
    ),
    'default'     => '1',
    'transport' => 'postMessage',
    'active_callback' => array(
        array(
            'setting'  => 'single_ajax_add_to_cart',
            'operator' => '==',
            'value'    => '1',
        ),
    ),
);

$fields[] = array(
    'id'          => 'override_spinner',
    'section'     => 'spinner',
    'label'       => esc_html__( 'Add To Cart Button Loading Spinner', 'xt-framework' ),
    'description' => sprintf( esc_html__( 'If your theme does not show a loading spinner on the add to cart button when adding a product, this option will try and add one', 'xt-framework' ), '<strong>', '</strong>' ),
    'type'        => 'radio-buttonset',
    'choices'     => array(
        '0' => esc_attr__( 'No', 'xt-framework' ),
        '1' => esc_attr__( 'Yes', 'xt-framework' )
    ),
    'default'     => '1'
);

$fields[] = array(
    'id'              => 'spinner_icon',
    'section'         => 'spinner',
    'label'           => esc_html__( 'Add To Cart Button Loading Spinner', 'xt-framework' ),
    'type'            => 'xticons',
    'choices'         => array( 'types' => array( 'spinner' ) ),
    'priority'        => 10,
    'default'         => 'xt_icon-spinner2',
    'transport' => 'postMessage',
    'js_vars'  => array(
        array(
            'element'  => '.xt_atc-button-spinner[class^="xt_icon-spinner"], .xt_atc-button-spinner[class*=" xt_icon-spinner"]',
            'function' => 'class'
        )
    ),
    'active_callback' => array(
        array(
            'setting'  => 'override_spinner',
            'operator' => '==',
            'value'    => '1',
        ),
    ),
);

$fields[] = array(
    'id'              => 'checkmark_icon',
    'section'         => 'spinner',
    'label'           => esc_html__( 'Add To Cart Button Added Checkmark', 'xt-framework' ),
    'type'            => 'xticons',
    'choices'         => array( 'types' => array( 'checkmark' ) ),
    'priority'        => 10,
    'default'         => 'xt_icon-checkmark',
    'transport' => 'postMessage',
    'js_vars'  => array(
        array(
            'element'  => '.xt_atc-button-spinner[class^="xt_icon-checkmark"], .xt_atc-button-spinner[class*=" xt_icon-checkmark"]',
            'function' => 'class'
        )
    ),
    'active_callback' => array(
        array(
            'setting'  => 'override_spinner',
            'operator' => '==',
            'value'    => '1',
        ),
    ),
);

$fields[] = array(
    'id'       => 'spinner_size',
    'section'  => 'spinner',
    'label'    => esc_html__( 'Spinner Size', 'xt-framework' ),
    'type'     => 'slider',
    'choices'  => array(
        'min'  => '1',
        'max'  => '2',
        'step' => '0.1',
        'suffix' => 'x',
    ),
    'priority'        => 10,
    'default'         => '1.3',
    'transport'       => 'auto',
    'output'          => array(
        array(
            'element'  => '.xt_atc-loading .xt_atc-button-spinner-wrap.xt_atc-button-spinner-ready',
            'property' => 'transform',
            'value_pattern' => 'scale($)!important'
        )
    ),
    'active_callback' => array(
        array(
            'setting'  => 'override_spinner',
            'operator' => '==',
            'value'    => '1',
        ),
    ),
);

$fields[] = array(
    'id'          => 'redirection_enabled',
    'section'     => 'redirections',
    'label'       => esc_html__( 'Enable Add To Cart Redirection', 'xt-framework' ),
    'type'        => 'radio-buttonset',
    'choices'     => array(
        '0' => esc_attr__( 'No', 'xt-framework' ),
        '1' => esc_attr__( 'Yes', 'xt-framework' )
    ),
    'default'     => '0'
);

$fields[] = array(
    'id'          => 'redirection_to',
    'section'     => 'redirections',
    'label'       => esc_html__( 'Redirect To', 'xt-framework' ),
    'type'        => 'radio-buttonset',
    'choices'     => array(
        'cart' => esc_attr__( 'Cart', 'xt-framework' ),
        'checkout' => esc_attr__( 'Checkout', 'xt-framework' ),
        'custom' => esc_attr__( 'Custom Page', 'xt-framework' )
    ),
    'default'     => 'cart',
    'active_callback' => array(
        array(
            'setting'  => 'redirection_enabled',
            'operator' => '==',
            'value'    => '1',
        ),
    ),
);

$fields[] = array(
    'id'          => 'redirection_to_custom',
    'section'     => 'redirections',
    'label'       => esc_html__( 'Redirect To Custom Page', 'xt-framework' ),
    'type'        => 'dropdown-pages',
    'placeholder' => esc_html__( 'Select a redirect page.', 'xt-framework' ),
    'default'     => '',
    'active_callback' => array(
        array(
            'setting'  => 'redirection_enabled',
            'operator' => '==',
            'value'    => '1',
        ),
        array(
            'setting'  => 'redirection_to',
            'operator' => '==',
            'value'    => 'custom',
        ),
    ),
);
