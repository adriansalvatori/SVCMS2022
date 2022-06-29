<?php

$fields[] = array(
	'id' => 'cart_zindex_desktop',
	'section'   => 'visibility',
	'label' => esc_html__('Cart Z-Index', 'woo-floating-cart'),
	'description' => esc_html__('Set the stack order for the cart. An element with greater stack order is always in front of an element with a lower stack order. Use this option to adjust the cart order if for some reason it is not visible on your theme or maybe being overlapped by other elements', 'woo-floating-cart'),
	'type' => 'slider',
	'choices' => array(
		'min' => '999',
		'max' => '999999',
		'step' => '9',
	),
	'priority' => 10,
	'default' => '90200',
	'transport' => 'auto',
	'screen' => 'desktop',
	'output' => array(
		array(
			'element' => ':root',
			'property' => '--xt-woofc-zindex'
		)
	)
);

$fields[] = array(
    'id' => 'cart_zindex_tablet',
    'section'   => 'visibility',
    'label' => esc_html__('Cart Z-Index', 'woo-floating-cart'),
    'description' => esc_html__('Set the stack order for the cart. An element with greater stack order is always in front of an element with a lower stack order. Use this option to adjust the cart order if for some reason it is not visible on your theme or maybe being overlapped by other elements', 'woo-floating-cart'),
    'type' => 'slider',
    'choices' => array(
        'min' => '999',
        'max' => '999999',
        'step' => '9',
    ),
    'priority' => 10,
    'default' => '90200',
    'transport' => 'auto',
    'screen' => 'tablet',
    'output' => array(
	    array(
		    'element' => ':root',
		    'property' => '--xt-woofc-zindex'
	    )
    )
);

$fields[] = array(
    'id' => 'cart_zindex_mobile',
    'section'   => 'visibility',
    'label' => esc_html__('Cart Z-Index', 'woo-floating-cart'),
    'description' => esc_html__('Set the stack order for the cart. An element with greater stack order is always in front of an element with a lower stack order. Use this option to adjust the cart order if for some reason it is not visible on your theme or maybe being overlapped by other elements', 'woo-floating-cart'),
    'type' => 'slider',
    'choices' => array(
        'min' => '999',
        'max' => '999999',
        'step' => '9',
    ),
    'priority' => 10,
    'default' => '90200',
    'transport' => 'auto',
    'screen' => 'mobile',
    'output' => array(
	    array(
		    'element' => ':root',
		    'property' => '--xt-woofc-zindex'
	    )
    )
);

$fields[] = array(
    'id'          => 'hidden_on_pages',
    'section'     => 'visibility',
    'label'       => esc_html__( 'Hide cart on these pages', 'woo-floating-cart' ),
    'description' => esc_html__( 'Note: The cart is automatically disabled on WooCommerce native cart & checkout pages', 'woo-floating-cart' ),
    'type'        => 'select',
    'multiple'    => 999,
    'choices'     => XT_Framework_Customizer_Options::get_page_options(),
    'priority'    => 10,
    'default'     => ''
);

$fields[] = array(
    'id'       => 'visible_on_empty',
    'section'  => 'visibility',
    'label'    => esc_html__( 'Keep visible on empty', 'woo-floating-cart' ),
    'type'        => 'radio-buttonset',
    'choices'     => array(
        '0' => esc_html__( 'No', 'woo-floating-cart' ),
        '1' => esc_html__( 'Yes', 'woo-floating-cart' )
    ),
    'default'  => '0',
    'priority' => 10
);

$fields[] = array(
    'id'       => 'visibility',
    'section'  => 'visibility',
    'label'    => esc_html__( 'Device Visibility', 'woo-floating-cart' ),
    'type'     => 'radio',
    'choices'  => array(
        'show-on-mobile-only'    => esc_attr__( 'Show on mobile only', 'woo-floating-cart' ),
        'show-on-tablet-mobile'  => esc_attr__( 'Show on tablet and mobile', 'woo-floating-cart' ),
        'show-on-tablet-desktop' => esc_attr__( 'Show on tablet and desktop', 'woo-floating-cart' ),
        'show-on-desktop-only'   => esc_attr__( 'Show on desktop only', 'woo-floating-cart' ),
        'show-on-all'            => esc_attr__( 'Show on all', 'woo-floating-cart' ),
    ),
    'default'  => 'show-on-all',
    'priority' => 10
);
