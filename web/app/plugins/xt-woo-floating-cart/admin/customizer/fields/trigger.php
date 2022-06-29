<?php
/* @var $customizer XT_Framework_Customizer */

if ( $this->core->access_manager()->can_use_premium_code__premium_only() ) {

    $fields[] = array(
        'id'          => 'trigger_extra_selectors',
        'section'     => 'trigger',
        'label'       => esc_html__( 'Trigger Extra Selectors', 'woo-floating-cart' ),
        'description' => esc_html__( 'You can add multiple css selectors. These elements will then act as a cart trigger. This is useful if you would like to use your existing theme cart menu icon or any other element to open the cart.', 'woo-floating-cart' ),
        'type'        => 'repeater',
        'row_label'   => array(
            'type'  => 'text',
            'value' => esc_html__( 'CSS Selector', 'woo-floating-cart' ),
        ),
        'default'     => array(),
        'fields'      => array(
            'selector' => array(
                'type'    => 'text',
                'label'   => esc_html__( 'CSS Selector', 'woo-floating-cart' ),
                'default' => '',
            )
        )
    );
}

$fields[] = array(
    'id'       => 'trigger_hidden',
    'section'  => 'trigger',
    'label'    => esc_html__( 'Cart Trigger Hidden', 'woo-floating-cart' ),
    'description'    => esc_html__( 'Hide main trigger if you only wish to trigger the cart using the API, shortcode, menu cart item or from custom selectors.', 'woo-floating-cart' ),
    'type'     => 'radio-buttonset',
    'choices'  => array(
        '0' => esc_html__( 'No', 'woo-floating-cart' ),
        '1' => esc_html__( 'Yes', 'woo-floating-cart' )
    ),
    'default'  => '0',
    'priority' => 10
);

$fields[] = array(
    'id'       => 'trigger_event_type',
    'section'  => 'trigger',
    'label'    => esc_html__( 'Cart Trigger Event Type', 'woo-floating-cart' ),
    'type'     => 'radio-buttonset',
    'choices'  => array(
        'vclick'     => esc_attr__( 'Click Only', 'woo-floating-cart' ),
        'mouseenter' => esc_attr__( 'Mouse Over Or Click', 'woo-floating-cart' )
    ),
    'default'  => 'vclick',
    'priority' => 10,
    'active_callback' => array(
        array(
            'setting'  => 'trigger_hidden',
            'operator' => '!=',
            'value'    => '1',
        ),
    )
);

$fields[] = array(
    'id'              => 'trigger_hover_delay',
    'section'         => 'trigger',
    'label'           => esc_html__( 'Mouse Over delay before trigger', 'woo-floating-cart' ),
    'type'            => 'slider',
    'choices'         => array(
        'min'  => '0',
        'max'  => '1500',
        'step' => '10',
        'suffix' => 'ms',
    ),
    'priority'        => 10,
    'default'         => 200,
    'active_callback' => array(
        array(
            'setting'  => 'trigger_hidden',
            'operator' => '!=',
            'value'    => '1',
        ),
        array(
            'setting'  => 'trigger_event_type',
            'operator' => '==',
            'value'    => 'mouseenter',
        ),
    )
);

$fields[] = array(
    'id'        => 'counter_position',
    'section'   => 'trigger',
    'label'     => esc_html__( 'Product Counter Position', 'woo-floating-cart' ),
    'type'      => 'radio-buttonset',
    'input_attrs' => array(
        'data-col' => '2'
    ),
    'priority'  => 10,
    'choices'   => array(
        'top-left'     => esc_html__( 'Top Left', 'woo-floating-cart' ),
        'top-right'    => esc_html__( 'Top Right', 'woo-floating-cart' ),
        'bottom-left'  => esc_html__( 'Bottom Left', 'woo-floating-cart' ),
        'bottom-right' => esc_html__( 'Bottom Right', 'woo-floating-cart' )
    ),
    'transport' => 'postMessage',
    'js_vars'   => array(
        array(
            'element'     => '.xt_woofc',
            'function'    => 'class',
            'prefix'      => 'xt_woofc-counter-pos-',
            'media_query' => $customizer->media_query('desktop', 'min'),
        )
    ),
    'default'   => 'top-left',
    'screen' => 'desktop',
    'active_callback' => array(
        array(
            'setting'  => 'trigger_hidden',
            'operator' => '!=',
            'value'    => '1',
        ),
    )
);

$fields[] = array(
    'id'        => 'counter_position_tablet',
    'section'   => 'trigger',
    'label'     => esc_html__( 'Product Counter Position', 'woo-floating-cart' ),
    'type'      => 'radio-buttonset',
    'input_attrs' => array(
        'data-col' => '2'
    ),
    'priority'  => 10,
    'choices'   => array(
        'top-left'     => esc_html__( 'Top Left', 'woo-floating-cart' ),
        'top-right'    => esc_html__( 'Top Right', 'woo-floating-cart' ),
        'bottom-left'  => esc_html__( 'Bottom Left', 'woo-floating-cart' ),
        'bottom-right' => esc_html__( 'Bottom Right', 'woo-floating-cart' )
    ),
    'transport' => 'postMessage',
    'js_vars'   => array(
        array(
            'element'     => '.xt_woofc',
            'function'    => 'class',
            'prefix'      => 'xt_woofc-counter-tablet-pos-',
            'media_query' => $customizer->media_query('tablet', 'max'),
        )
    ),
    'default'   => 'top-left',
    'screen' => 'tablet',
    'active_callback' => array(
        array(
            'setting'  => 'trigger_hidden',
            'operator' => '!=',
            'value'    => '1',
        ),
    )
);

$fields[] = array(
    'id'        => 'counter_position_mobile',
    'section'   => 'trigger',
    'label'     => esc_html__( 'Product Counter Position', 'woo-floating-cart' ),
    'type'      => 'radio-buttonset',
    'input_attrs' => array(
        'data-col' => '2'
    ),
    'priority'  => 10,
    'choices'   => array(
        'top-left'     => esc_html__( 'Top Left', 'woo-floating-cart' ),
        'top-right'    => esc_html__( 'Top Right', 'woo-floating-cart' ),
        'bottom-left'  => esc_html__( 'Bottom Left', 'woo-floating-cart' ),
        'bottom-right' => esc_html__( 'Bottom Right', 'woo-floating-cart' )
    ),
    'transport' => 'postMessage',
    'js_vars'   => array(
        array(
            'element'     => '.xt_woofc',
            'function'    => 'class',
            'prefix'      => 'xt_woofc-counter-mobile-pos-',
            'media_query' => $customizer->media_query('mobile', 'max'),
        )
    ),
    'default'   => 'top-left',
    'screen' => 'mobile',
    'active_callback' => array(
        array(
            'setting'  => 'trigger_hidden',
            'operator' => '!=',
            'value'    => '1',
        ),
    )
);

if ( $this->core->access_manager()->can_use_premium_code__premium_only() ) {

    $fields[] = array(
        'id'        => 'trigger_size',
        'section'   => 'trigger',
        'label'     => esc_html__( 'Trigger Size', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '40',
            'max'  => '100',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '72',
        'priority'  => 10,
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'  => ':root',
                'property' => '--xt-woofc-trigger-size',
                'value_pattern' => '$px'
            )
        ),
        'screen' => 'desktop',
        'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
        'id'        => 'trigger_size_tablet',
        'section'   => 'trigger',
        'label'     => esc_html__( 'Trigger Size', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '40',
            'max'  => '100',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '72',
        'priority'  => 10,
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'  => ':root',
                'property' => '--xt-woofc-trigger-size',
                'value_pattern' => '$px'
            )
        ),
        'screen' => 'tablet',
        'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
        'id'        => 'trigger_size_mobile',
        'section'   => 'trigger',
        'label'     => esc_html__( 'Trigger Size', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '40',
            'max'  => '100',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '72',
        'priority'  => 10,
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'  => ':root',
                'property' => '--xt-woofc-trigger-size',
                'value_pattern' => '$px'
            )
        ),
        'screen' => 'mobile',
        'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
        'id'        => 'counter_size',
        'section'   => 'trigger',
        'label'     => esc_html__( 'Product Counter Size', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '20',
            'max'  => '40',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '25',
        'priority'  => 10,
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'  => ':root',
                'property' => '--xt-woofc-counter-size',
                'value_pattern' => '$px'
            )
        ),
        'screen' => 'desktop',
        'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
        'id'        => 'counter_size_tablet',
        'section'   => 'trigger',
        'label'     => esc_html__( 'Product Counter Size', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '20',
            'max'  => '40',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '25',
        'priority'  => 10,
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'  => ':root',
                'property' => '--xt-woofc-counter-size',
                'value_pattern' => '$px'
            )
        ),
        'screen' => 'tablet',
        'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
        'id'        => 'counter_size_mobile',
        'section'   => 'trigger',
        'label'     => esc_html__( 'Product Counter Size', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '20',
            'max'  => '40',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '25',
        'priority'  => 10,
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'  => ':root',
                'property' => '--xt-woofc-counter-size',
                'value_pattern' => '$px'
            )
        ),
        'screen' => 'mobile',
        'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            ),
        )
    );
    $fields[] = array(
        'id'        => 'border_radius',
        'section'   => 'trigger',
        'label'     => esc_html__( 'Trigger Border Radius', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '0',
            'max'  => '35',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '6',
        'priority'  => 10,
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'       => '.xt_woofc:not(.xt_woofc-cart-open)',
                'property'      => '--xt-woofc-radius',
                'value_pattern' => '$px'
            )
        ),
        'screen' => 'desktop',
        'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
        'id'        => 'border_radius_tablet',
        'section'   => 'trigger',
        'label'     => esc_html__( 'Trigger Border Radius', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '0',
            'max'  => '35',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '6',
        'priority'  => 10,
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'       => '.xt_woofc:not(.xt_woofc-cart-open)',
                'property'      => '--xt-woofc-radius',
                'value_pattern' => '$px'
            )
        ),
        'screen' => 'tablet',
        'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
        'id'        => 'border_radius_mobile',
        'section'   => 'trigger',
        'label'     => esc_html__( 'Trigger Border Radius', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '0',
            'max'  => '35',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '6',
        'priority'  => 10,
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'       => '.xt_woofc:not(.xt_woofc-cart-open)',
                'property'      => '--xt-woofc-radius',
                'value_pattern' => '$px'
            )
        ),
        'screen' => 'mobile',
        'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
		'id'       => 'trigger_icon_type',
		'section'  => 'trigger',
		'label'    => esc_html__( 'Cart Trigger Icon Type', 'woo-floating-cart' ),
		'type'     => 'radio-buttonset',
		'choices'  => array(
			'image' => esc_attr__( 'Image / SVG', 'woo-floating-cart' ),
			'font'  => esc_attr__( 'Font Icon', 'woo-floating-cart' )
		),
		'default'  => 'image',
		'priority' => 10,
		'js_vars'  => array(
			array(
				'element'  => '.xt_woofc-trigger',
				'function' => 'class',
				'prefix'   => 'xt_woofc-icontype-'
			)
		),
        'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            ),
        )
	);

	$fields[] = array(
		'id'              => 'cart_trigger_icon',
		'section'         => 'trigger',
		'label'           => esc_html__( 'Cart Trigger Icon', 'woo-floating-cart' ),
		'type'            => 'xticons',
		'choices'         => array( 'types' => array( 'cart' ) ),
		'priority'        => 10,
		'default'         => 'xt_woofcicon-groceries-store',
		'transport'       => 'postMessage',
		'js_vars'         => array(
			array(
				'element'  => '.xt_woofc-trigger-cart-icon',
				'function' => 'class'
			)
		),
		'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            ),
			array(
				'setting'  => 'trigger_icon_type',
				'operator' => '==',
				'value'    => 'font',
			),
		)
	);


	$fields[] = array(
		'id'              => 'cart_trigger_icon_image',
		'section'         => 'trigger',
		'label'           => esc_html__( 'Cart Trigger Icon Image', 'woo-floating-cart' ),
		'type'            => 'image',
		'default'         => $this->core->plugin_url( 'public/assets/img', 'open.svg' ),
		'priority'        => 10,
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => '.xt_woofc-trigger.xt_woofc-icontype-image .xt_woofc-trigger-cart-icon',
				'property' => 'background-image',
			)
		),
		'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            ),
			array(
				'setting'  => 'trigger_icon_type',
				'operator' => '==',
				'value'    => 'image',
			),
		)
	);

	$fields[] = array(
		'id'              => 'cart_trigger_close_icon',
		'section'         => 'trigger',
		'label'           => esc_html__( 'Close Cart Trigger Icon', 'woo-floating-cart' ),
		'type'            => 'xticons',
		'choices'         => array( 'types' => array( 'close' ) ),
		'priority'        => 10,
		'default'         => 'xt_woofcicon-close-2',
		'transport'       => 'postMessage',
		'js_vars'         => array(
			array(
				'element'  => '.xt_woofc-trigger-close-icon',
				'function' => 'class'
			)
		),
		'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            ),
			array(
				'setting'  => 'trigger_icon_type',
				'operator' => '==',
				'value'    => 'font',
			),
			array(
				'setting'  => 'animation_type',
				'operator' => '==',
				'value'    => 'morph',
			),
		)
	);

	$fields[] = array(
		'id'              => 'cart_trigger_close_icon_image',
		'section'         => 'trigger',
		'label'           => esc_html__( 'Close Cart Trigger Icon Image', 'woo-floating-cart' ),
		'type'            => 'image',
		'default'         => $this->core->plugin_url( 'public/assets/img', 'close.svg' ),
		'priority'        => 10,
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => '.xt_woofc-trigger.xt_woofc-icontype-image .xt_woofc-trigger-close-icon',
				'property' => 'background-image',
			)
		),
		'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            ),
			array(
				'setting'  => 'trigger_icon_type',
				'operator' => '==',
				'value'    => 'image',
			),
			array(
				'setting'  => 'animation_type',
				'operator' => '==',
				'value'    => 'morph',
			),
		)
	);

	$fields[] = array(
		'id'        => 'cart_trigger_bg_color',
		'section'   => 'trigger',
		'label'     => esc_html__( 'Cart Trigger Bg Color', 'woo-floating-cart' ),
		'type'      => 'color',
		'priority'  => 10,
		'default'   => '',
		'transport' => 'auto',
		'output'    => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-trigger-bg-color',
			)
		),
        'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            ),
        )
	);

	$fields[] = array(
		'id'              => 'cart_trigger_icon_color',
		'section'         => 'trigger',
		'label'           => esc_html__( 'Cart Trigger Icon Color', 'woo-floating-cart' ),
		'type'            => 'color',
        'choices' => array(
            'alpha' => true,
        ),
		'priority'        => 10,
		'default'         => '',
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-trigger-cart-icon-color',
			)
		),
		'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            ),
			array(
				'setting'  => 'trigger_icon_type',
				'operator' => '==',
				'value'    => 'font',
			),
		)
	);

	$fields[] = array(
		'id'        => 'cart_trigger_active_bg_color',
		'section'   => 'trigger',
		'label'     => esc_html__( 'Close Cart Trigger Bg Color', 'woo-floating-cart' ),
		'type'      => 'color',
		'priority'  => 10,
		'default'   => '',
		'transport' => 'auto',
		'output'    => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-trigger-bg-active-color',
			)
		),
		'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            ),
			array(
				'setting'  => 'animation_type',
				'operator' => '==',
				'value'    => 'morph',
			),
		)
	);

	$fields[] = array(
		'id'              => 'cart_trigger_close_icon_color',
		'section'         => 'trigger',
		'label'           => esc_html__( 'Close Cart Trigger Icon Color', 'woo-floating-cart' ),
		'type'            => 'color',
        'choices' => array(
            'alpha' => true,
        ),
		'priority'        => 10,
		'default'         => '',
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-trigger-close-icon-color',
			)
		),
		'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            ),
			array(
				'setting'  => 'trigger_icon_type',
				'operator' => '==',
				'value'    => 'font',
			),
			array(
				'setting'  => 'animation_type',
				'operator' => '==',
				'value'    => 'morph',
			),
		)
	);

	$fields[] = array(
		'id'        => 'counter_bg_color',
		'section'   => 'trigger',
		'label'     => esc_html__( 'Product Counter Bg Color', 'woo-floating-cart' ),
		'type'      => 'color',
		'priority'  => 10,
		'default'   => '#e94b35',
		'transport' => 'auto',
		'output'    => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-counter-bg-color',
			)
		),
        'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            )
        )
	);

	$fields[] = array(
		'id'        => 'counter_text_color',
		'section'   => 'trigger',
		'label'     => esc_html__( 'Product Counter Text Color', 'woo-floating-cart' ),
		'type'      => 'color',
		'priority'  => 10,
		'default'   => '#ffffff',
		'transport' => 'auto',
		'output'    => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-counter-color',
			)
		),
        'active_callback' => array(
            array(
                'setting'  => 'trigger_hidden',
                'operator' => '!=',
                'value'    => '1',
            )
        )
	);

} else {

	$fields[] = array(
		'id'      => 'trigger_features',
		'section' => 'trigger',
		'type'    => 'xt-premium',
		'default' => array(
			'type'  => 'image',
			'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/trigger.png',
			'link'  => $this->core->plugin_upgrade_url()
		)
	);
}