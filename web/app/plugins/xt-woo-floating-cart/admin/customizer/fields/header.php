<?php

// HEADER COLORS

$fields[] = array(
    'id' => 'header_colors',
    'section'     => 'header',
    'label'     => esc_html__( 'Header Colors', 'woo-floating-cart' ),
    'type' => 'custom'
);

$fields[] = array(
    'id'        => 'cart_header_bg_color',
    'section'   => 'header',
    'label'     => esc_html__( 'Cart Header Bg Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-header-bg-color',
        )
    )
);

$fields[] = array(
    'id'        => 'cart_header_bottom_border_color',
    'section'   => 'header',
    'label'     => esc_html__( 'Cart Header Bottom Border Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-header-border-color',
        )
    )
);

$fields[] = array(
    'id'        => 'cart_header_title_color',
    'section'   => 'header',
    'label'     => esc_html__( 'Cart Header Title Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-header-title-color',
        )
    )
);

$fields[] = array(
    'id'        => 'cart_header_color',
    'section'   => 'header',
    'label'     => esc_html__( 'Cart Header Text Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-header-color',
        )
    )
);

$fields[] = array(
    'id'        => 'cart_header_link_color',
    'section'   => 'header',
    'label'     => esc_html__( 'Cart Header Link Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-header-link-color',
        )
    )
);
$fields[] = array(
    'id'        => 'cart_header_link_hover_color',
    'section'   => 'header',
    'label'     => esc_html__( 'Cart Header Link Hover Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-header-link-hover-color',
        )
    )
);

$fields[] = array(
    'id'        => 'cart_header_error_color',
    'section'   => 'header',
    'label'     => esc_html__( 'Cart Header Error Message Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-header-error-color',
        )
    )
);

if ( $this->core->access_manager()->can_use_premium_code__premium_only() ) {

    // HEADER CLOSE BUTTON

    $fields[] = array(
        'id' => 'header_close',
        'section'     => 'header',
        'label'     => esc_html__( 'Header Close Button', 'woo-floating-cart' ),
        'type' => 'custom'
    );

    $fields[] = array(
        'id'          => 'cart_header_close_enabled',
        'section'     => 'header',
        'label'       => esc_html__( 'Enable cart close icon in the header', 'woo-floating-cart' ),
        'description' => sprintf( esc_html__( 'This is useful when the cart animation is set to "Slide". The "Morph" Animation already has a close button in the footer', 'woo-floating-cart' ), '<strong>', '</strong>' ),
        'type'        => 'radio-buttonset',
        'choices'     => array(
            '1' => esc_attr__( 'Enable', 'woo-floating-cart' ),
            '0' => esc_attr__( 'Disable', 'woo-floating-cart' )
        ),
        'default'     => '0'
    );

    $fields[] = array(
        'id'              => 'cart_header_close_icon',
        'section'         => 'header',
        'label'           => esc_html__( 'Cart Header Close Icon', 'woo-floating-cart' ),
        'type'            => 'xticons',
        'choices'         => array( 'types' => array( 'close' ) ),
        'priority'        => 10,
        'default'         => 'xt_woofcicon-close-2',
        'transport'       => 'postMessage',
        'js_vars'         => array(
            array(
                'element'  => '.xt_woofc-header-close',
                'function' => 'class'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_header_close_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
        'id'              => 'cart_header_close_icon_color',
        'section'         => 'header',
        'label'           => esc_html__( 'Cart Header Close Icon Color', 'woo-floating-cart' ),
        'type'            => 'color',
        'priority'        => 10,
        'default'         => '',
        'transport'       => 'auto',
        'output'          => array(
            array(
                'element'       => ':root',
                'property' => '--xt-woofc-header-close-color',
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_header_close_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        )
    );

    // HEADER CLEAR BUTTON

    $fields[] = array(
        'id' => 'header_clear',
        'section'     => 'header',
        'label'     => esc_html__( 'Header Clear All Button', 'woo-floating-cart' ),
        'type' => 'custom'
    );

    $fields[] = array(
        'id'          => 'cart_header_clear_enabled',
        'section'     => 'header',
        'label'       => esc_html__( 'Enable clear cart icon in the header', 'woo-floating-cart' ),
        'description' => sprintf( esc_html__( 'This could be useful to let customers clear all items in the cart in 1 click.', 'woo-floating-cart' ), '<strong>', '</strong>' ),
        'type'        => 'radio-buttonset',
        'choices'     => array(
            '1' => esc_attr__( 'Enable', 'woo-floating-cart' ),
            '0' => esc_attr__( 'Disable', 'woo-floating-cart' )
        ),
        'default'     => '0'
    );

    $fields[] = array(
        'id'              => 'cart_header_clear_icon',
        'section'         => 'header',
        'label'           => esc_html__( 'Cart Header Clear Icon', 'woo-floating-cart' ),
        'type'            => 'xticons',
        'choices'         => array( 'types' => array( 'trash' ) ),
        'priority'        => 10,
        'default'         => 'xt_icon-trash1',
        'transport'       => 'postMessage',
        'js_vars'         => array(
            array(
                'element'  => '.xt_woofc-header-clear',
                'function' => 'class'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_header_clear_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
        'id'              => 'cart_header_clear_icon_color',
        'section'         => 'header',
        'label'           => esc_html__( 'Cart Header Clear Icon Color', 'woo-floating-cart' ),
        'type'            => 'color',
        'priority'        => 10,
        'default'         => '',
        'transport'       => 'auto',
        'output'          => array(
            array(
                'element'       => ':root',
                'property' => '--xt-woofc-header-clear-color',
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_header_clear_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
        'id'              => 'cart_header_clear_icon_hover_color',
        'section'         => 'header',
        'label'           => esc_html__( 'Cart Header Clear Icon Hover Color', 'woo-floating-cart' ),
        'type'            => 'color',
        'priority'        => 10,
        'default'         => '',
        'transport'       => 'auto',
        'output'          => array(
            array(
                'element'       => ':root',
                'property' => '--xt-woofc-header-clear-hover-color',
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_header_clear_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        )
    );

    // HEADER MSG

    $fields[] = array(
        'id' => 'shipping_bar',
        'section'     => 'header',
        'label'     => esc_html__( 'Shipping Bar', 'woo-floating-cart' ),
        'type' => 'custom'
    );

    $fields[] = array(
        'id'          => 'cart_shipping_bar_enabled',
        'section'     => 'header',
        'label'       => esc_html__( 'Shipping Bar Enabled', 'woo-floating-cart' ),
        'description' => esc_html('Display a message and a progress bar, letting customers know how much more they need to spend before getting free shipping', 'xt-woo-floating-cart'),
        'type'        => 'radio-buttonset',
        'choices'     => array(
            '1' => esc_attr__( 'Enable', 'woo-floating-cart' ),
            '0' => esc_attr__( 'Disable', 'woo-floating-cart' )
        ),
        'default'     => '0'
    );

    $fields[] = array(
        'id'          => 'cart_shipping_bar_free_text',
        'section'     => 'header',
        'label'       => esc_html__( 'Free Shipping Text', 'woo-floating-cart' ),
        'type'        => 'text',
        'transport'   => 'postMessage',
        'partial_refresh'    => [
            'cart_shipping_bar_free_text' => [
                'selector'        => '.xt_woofc-shipping-bar-text',
                'render_callback' => function() {
                    $this->core->frontend()->render_shipping_bar(true);
                },
            ]
        ],
        'active_callback' => array(
            array(
                'setting'  => 'cart_shipping_bar_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        ),
        'default' => esc_html__('Congrats! You get free shipping.', 'xt-woo-floating-cart')
    );

    $fields[] = array(
        'id'          => 'cart_shipping_bar_remaining_text',
        'section'     => 'header',
        'label'       => esc_html__( 'Amount Left for Free Shipping Text', 'woo-floating-cart' ),
        'description' => esc_html__('Make sure sure to keep the "%s" variable within the text. It will be replaced by the remaining amount.', 'xt-woo-floating-cart'),
        'type'        => 'text',
        'transport'   => 'postMessage',
        'partial_refresh'    => [
            'cart_shipping_bar_remaining_text' => [
                'selector'        => '.xt_woofc-shipping-bar-text',
                'render_callback' => function() {
                    $this->core->frontend()->render_shipping_bar(true);
                },
            ]
        ],
        'active_callback' => array(
            array(
                'setting'  => 'cart_shipping_bar_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        ),
        'default' => esc_html__('You\'re %s away from free shipping.', 'xt-woo-floating-cart')
    );

    $fields[] = array(
        'id'        => 'cart_shipping_bar_bg_color',
        'section'   => 'header',
        'label'     => esc_html__( 'Shipping Bar Bg Color', 'woo-floating-cart' ),
        'type'      => 'color',
        'priority'  => 10,
        'default'   => '',
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'       => ':root',
                'property' => '--xt-woofc-shipping-bar-bg-color',
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_shipping_bar_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
        'id'        => 'cart_header_shipping_bar_text_color',
        'section'   => 'header',
        'label'     => esc_html__( 'Shipping Bar Text Color', 'woo-floating-cart' ),
        'type'      => 'color',
        'priority'  => 10,
        'default'   => '',
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'       => ':root',
                'property' => '--xt-woofc-shipping-bar-color',
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_shipping_bar_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
        'id'        => 'cart_shipping_bar_progress_inactive_color',
        'section'   => 'header',
        'label'     => esc_html__( 'Shipping Bar Progress Inactive Color', 'woo-floating-cart' ),
        'type'      => 'color',
        'priority'  => 10,
        'default'   => '',
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'       => ':root',
                'property' => '--xt-woofc-shipping-bar-progress-inactive-color',
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_shipping_bar_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
        'id'        => 'cart_shipping_bar_progress_active_color',
        'section'   => 'header',
        'label'     => esc_html__( 'Shipping Bar Progress Active Color', 'woo-floating-cart' ),
        'type'      => 'color',
        'priority'  => 10,
        'default'   => '',
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'       => ':root',
                'property' => '--xt-woofc-shipping-bar-progress-active-color',
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_shipping_bar_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
        'id'        => 'cart_shipping_bar_progress_completed_color',
        'section'   => 'header',
        'label'     => esc_html__( 'Shipping Bar Progress Completed Color', 'woo-floating-cart' ),
        'type'      => 'color',
        'priority'  => 10,
        'default'   => '',
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'       => ':root',
                'property' => '--xt-woofc-shipping-bar-progress-completed-color',
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_shipping_bar_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        )
    );


    // HEADER MSG

    $fields[] = array(
        'id' => 'header_msg',
        'section'     => 'header',
        'label'     => esc_html__( 'Header Message', 'woo-floating-cart' ),
        'type' => 'custom'
    );

	$fields[] = array(
		'id'          => 'cart_header_msg_enabled',
		'section'     => 'header',
		'label'       => esc_html__( 'Cart Header Message Enabled', 'woo-floating-cart' ),
		'type'        => 'radio-buttonset',
		'choices'     => array(
			'1' => esc_attr__( 'Enable', 'woo-floating-cart' ),
			'0' => esc_attr__( 'Disable', 'woo-floating-cart' )
		),
		'default'     => '0'
	);

	$fields[] = array(
		'id'          => 'cart_header_msg',
		'section'     => 'header',
		'label'       => esc_html__( 'Cart Header Message', 'woo-floating-cart' ),
		'type'        => 'text',
		'transport'   => 'postMessage',
		'partial_refresh'    => [
			'cart_header_msg' => [
				'selector'        => '.xt_woofc-header-message',
				'render_callback' => function() {
					$this->core->frontend()->render_header_message(true);
				},
			]
		],
		'active_callback' => array(
			array(
				'setting'  => 'cart_header_msg_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		),
		'default'     => ''
	);

	$fields[] = array(
		'id'        => 'cart_header_message_bg_color',
		'section'   => 'header',
		'label'     => esc_html__( 'Cart Header Message Bg Color', 'woo-floating-cart' ),
		'type'      => 'color',
		'priority'  => 10,
		'default'   => '',
		'transport' => 'auto',
		'output'    => array(
			array(
				'element'       => ':root',
				'property' => '--xt-woofc-header-msg-bg-color',
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_header_msg_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		)
	);

	$fields[] = array(
		'id'        => 'cart_header_message_text_color',
		'section'   => 'header',
		'label'     => esc_html__( 'Cart Header Message Text Color', 'woo-floating-cart' ),
		'type'      => 'color',
		'priority'  => 10,
		'default'   => '',
		'transport' => 'auto',
		'output'    => array(
			array(
				'element'       => ':root',
				'property' => '--xt-woofc-header-msg-color',
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_header_msg_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		)
	);

} else {

	$fields[] = array(
		'id'      => 'header_features',
		'section' => 'header',
		'type'    => 'xt-premium',
		'default' => array(
			'type'  => 'image',
			'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/header.png',
			'link'  => $this->core->plugin_upgrade_url()
		)
	);
}