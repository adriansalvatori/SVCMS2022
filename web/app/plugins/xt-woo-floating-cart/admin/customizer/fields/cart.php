<?php
/* @var $customizer XT_Framework_Customizer */

$fields[] = array(
    'id'        => 'active_cart_body_overlay_color',
    'section'   => 'cart',
    'label'     => esc_html__( 'Overlay Color', 'woo-floating-cart' ),
    'description' => esc_html__( 'Set the Overlay Color on top of the page content, behind the cart. This helps focusing on the cart.', 'woo-floating-cart' ),
    'type'      => 'color',
    'choices'   => array(
        'alpha' => true
    ),
    'priority'  => 10,
    'default'   => 'rgba(0,0,0,.5)',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'  => ':root',
            'property' => '--xt-woofc-overlay-color',
        )
    )
);

$fields[] = array(
	'id'        => 'position',
	'section'   => 'cart',
	'label'     => esc_html__( 'Trigger / Cart Position', 'woo-floating-cart' ),
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
			'prefix'      => 'xt_woofc-pos-',
			'media_query' => $customizer->media_query('desktop', 'min'),
		),
		array(
			'element'     => '.xt_woofc',
			'function'    => 'html',
			'attr'        => 'data-position',
			'media_query' => $customizer->media_query('desktop', 'min'),
		)
	),
	'default'   => 'bottom-right',
	'screen' => 'desktop'
);

$fields[] = array(
	'id'        => 'position_tablet',
	'section'   => 'cart',
	'label'     => esc_html__( 'Trigger / Cart Position', 'woo-floating-cart' ),
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
			'prefix'      => 'xt_woofc-tablet-pos-',
			'media_query' => $customizer->media_query('tablet', 'max'),
		),
		array(
			'element'     => '.xt_woofc',
			'function'    => 'html',
			'attr'        => 'data-tablet_position',
			'media_query' => $customizer->media_query('tablet', 'max'),
		)
	),
	'default'   => 'bottom-right',
	'screen' => 'tablet'
);

$fields[] = array(
	'id'        => 'position_mobile',
	'section'   => 'cart',
	'label'     => esc_html__( 'Trigger / Cart Position', 'woo-floating-cart' ),
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
			'prefix'      => 'xt_woofc-mobile-pos-',
			'media_query' => $customizer->media_query('mobile', 'max'),
		),
		array(
			'element'     => '.xt_woofc',
			'function'    => 'html',
			'attr'        => 'data-mobile_position',
			'media_query' => $customizer->media_query('mobile', 'max'),
		)
	),
	'default'   => 'bottom-right',
	'screen' => 'mobile'
);

$fields[] = array(
    'id'        => 'cart_hoffset',
    'section'   => 'cart',
    'label'     => esc_html__( 'Trigger / Cart X Offset', 'woo-floating-cart' ),
    'type'      => 'slider',
    'choices'   => array(
        'min'  => '0',
        'max'  => '300',
        'step' => '1',
        'suffix' => 'px',
    ),
    'priority'  => 10,
    'default'   => '20',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'  => ':root',
            'property' => '--xt-woofc-hoffset',
            'value_pattern' => '$px'
        )
    ),
    'screen' => 'desktop'
);

$fields[] = array(
    'id'        => 'cart_hoffset_tablet',
    'section'   => 'cart',
    'label'     => esc_html__( 'Trigger / Cart X Offset', 'woo-floating-cart' ),
    'type'      => 'slider',
    'choices'   => array(
        'min'  => '0',
        'max'  => '300',
        'step' => '1',
        'suffix' => 'px',
    ),
    'priority'  => 10,
    'default'   => '20',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'  => ':root',
            'property' => '--xt-woofc-hoffset',
            'value_pattern' => '$px'
        )
    ),
    'screen' => 'tablet'
);

$fields[] = array(
    'id'        => 'cart_hoffset_mobile',
    'section'   => 'cart',
    'label'     => esc_html__( 'Trigger / Cart X Offset', 'woo-floating-cart' ),
    'type'      => 'slider',
    'choices'   => array(
        'min'  => '0',
        'max'  => '300',
        'step' => '1',
        'suffix' => 'px',
    ),
    'priority'  => 10,
    'default'   => '0',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'  => ':root',
            'property' => '--xt-woofc-hoffset',
            'value_pattern' => '$px'
        )
    ),
    'screen' => 'mobile'
);

$fields[] = array(
    'id'        => 'cart_voffset',
    'section'   => 'cart',
    'label'     => esc_html__( 'Trigger / Cart Y Offset', 'woo-floating-cart' ),
    'type'      => 'slider',
    'choices'   => array(
        'min'  => '0',
        'max'  => '300',
        'step' => '1',
        'suffix' => 'px',
    ),
    'default'   => '20',
    'priority'  => 10,
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'  => ':root',
            'property' => '--xt-woofc-voffset',
            'value_pattern' => '$px'
        )
    ),
    'screen' => 'desktop'
);

$fields[] = array(
    'id'        => 'cart_voffset_tablet',
    'section'   => 'cart',
    'label'     => esc_html__( 'Trigger / Cart Y Offset', 'woo-floating-cart' ),
    'type'      => 'slider',
    'choices'   => array(
        'min'  => '0',
        'max'  => '300',
        'step' => '1',
        'suffix' => 'px',
    ),
    'default'   => '20',
    'priority'  => 10,
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'  => ':root',
            'property' => '--xt-woofc-voffset',
            'value_pattern' => '$px'
        )
    ),
    'screen' => 'tablet'
);

$fields[] = array(
    'id'        => 'cart_voffset_mobile',
    'section'   => 'cart',
    'label'     => esc_html__( 'Trigger / Cart Y Offset', 'woo-floating-cart' ),
    'type'      => 'slider',
    'choices'   => array(
        'min'  => '0',
        'max'  => '300',
        'step' => '1',
        'suffix' => 'px',
    ),
    'default'   => '0',
    'priority'  => 10,
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'  => ':root',
            'property' => '--xt-woofc-voffset',
            'value_pattern' => '$px'
        )
    ),
    'screen' => 'mobile'
);

if ( $this->core->access_manager()->can_use_premium_code__premium_only() ) {

    $fields[] = array(
        'id'        => 'modal_mode',
        'section'   => 'cart',
        'label'     => esc_html__( 'Modal Mode', 'woo-floating-cart' ),
        'description' => esc_html__( 'When enabled, the cart will open as a modal in the middle of the screen', 'woo-floating-cart' ),
        'type'        => 'radio-buttonset',
        'priority'  => 10,
        'choices'   => array(
            '0'     => esc_html__( 'Disabled', 'woo-floating-cart' ),
            '1'    => esc_html__( 'Enabled', 'woo-floating-cart' )
        ),
        'default'   => '0'
    );

    $fields[] = array(
        'id'        => 'animation_type',
        'section'   => 'cart',
        'label'     => esc_html__( 'Animation Type', 'woo-floating-cart' ),
        'type'        => 'radio-buttonset',
        'priority'  => 10,
        'choices'   => array(
            'morph'     => esc_html__( 'Morph', 'woo-floating-cart' ),
            'slide'    => esc_html__( 'Slide', 'woo-floating-cart' )
        ),
        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'     => '.xt_woofc',
                'function'    => 'class',
                'prefix'      => 'xt_woofc-animation-'
            ),
            array(
                'element'     => '.xt_woofc',
                'function'    => 'html',
                'attr'        => 'data-animation',
            )
        ),
        'default'   => 'morph',
        'active_callback' => array(
            array(
                'setting'  => 'modal_mode',
                'operator' => '==',
                'value'    => '0',
            ),
        ),
    );

    $fields[] = array(
        'id'        => 'cart_autoheight_enabled',
        'section'   => 'cart',
        'label'     => esc_html__( 'Cart Auto Height', 'woo-floating-cart' ),
        'type'        => 'radio-buttonset',
        'priority'  => 10,
        'choices'   => array(
            '0'    => esc_html__( 'Disabled', 'woo-floating-cart' ),
            '1'     => esc_html__( 'Enabled', 'woo-floating-cart' )
        ),
        'transport' => 'postMessage',
        'default'   => '0'
    );

    $fields[] = array(
		'id'        => 'cart_dimensions_unit',
		'section'   => 'cart',
		'label'     => esc_html__( 'Cart Dimensions Unit', 'woo-floating-cart' ),
		'type'        => 'radio-buttonset',
		'priority'  => 10,
		'choices'   => array(
			'pixels'     => esc_html__( 'Pixels', 'woo-floating-cart' ),
			'percent'    => esc_html__( 'Percent', 'woo-floating-cart' )
		),
		'default'   => 'pixels',
		'transport' => 'postMessage',
		'js_vars'   => array(
			array(
				'element'     => '.xt_woofc',
				'function'    => 'class',
				'prefix'      => 'xt_woofc-dimensions-'
			)
		),
	);

	$fields[] = array(
		'id'              => 'cart_width',
		'section'         => 'cart',
		'label'           => esc_html__( 'Cart Width (px)', 'woo-floating-cart' ),
		'type'            => 'slider',
		'choices'         => array(
			'min'  => '250',
			'max'  => '1000',
			'step' => '5',
            'suffix' => 'px',
		),
		'default'         => '440',
		'priority'        => 10,
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-width',
				'value_pattern' => '$px'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_dimensions_unit',
				'operator' => '==',
				'value'    => 'pixels',
			),
		),
		'screen' => 'desktop'
	);

	$fields[] = array(
		'id'              => 'cart_height',
		'section'         => 'cart',
		'label'           => esc_html__( 'Cart Height (px)', 'woo-floating-cart' ),
		'type'            => 'slider',
		'choices'         => array(
			'min'  => '240',
			'max'  => '1000',
			'step' => '5',
            'suffix' => 'px',
		),
		'default'         => '400',
		'priority'        => 10,
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-height',
				'value_pattern' => '$px'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_autoheight_enabled',
				'operator' => '!=',
				'value'    => '1',
			),
            array(
                'setting'  => 'cart_dimensions_unit',
                'operator' => '==',
                'value'    => 'pixels',
            ),
		),
		'screen' => 'desktop'
	);

	$fields[] = array(
		'id'              => 'cart_width_percent',
		'section'         => 'cart',
		'label'           => esc_html__( 'Cart Width (%)', 'woo-floating-cart' ),
		'type'            => 'slider',
		'choices'         => array(
			'min'  => '30',
			'max'  => '100',
			'step' => '1',
            'suffix' => '%',
		),
		'default'         => '30',
		'priority'        => 10,
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => ':root .xt_woofc-dimensions-percent',
				'property' => '--xt-woofc-width',
				'value_pattern' => '$vw'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_dimensions_unit',
				'operator' => '==',
				'value'    => 'percent',
			),
		),
		'screen' => 'desktop'
	);

	$fields[] = array(
		'id'              => 'cart_height_percent',
		'section'         => 'cart',
		'label'           => esc_html__( 'Cart Height (%)', 'woo-floating-cart' ),
		'type'            => 'slider',
		'choices'         => array(
			'min'  => '30',
			'max'  => '100',
			'step' => '1',
            'suffix' => '%',
		),
		'default'         => '50',
		'priority'        => 10,
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => ':root .xt_woofc-dimensions-percent',
				'property' => '--xt-woofc-height',
				'value_pattern' => '$vh'
			)
		),
		'active_callback' => array(
            array(
                'setting'  => 'cart_autoheight_enabled',
                'operator' => '!=',
                'value'    => '1',
            ),
			array(
				'setting'  => 'cart_dimensions_unit',
				'operator' => '==',
				'value'    => 'percent',
			),
		),
		'screen' => 'desktop'
	);

	$fields[] = array(
		'id'              => 'cart_width_tablet',
		'section'         => 'cart',
		'label'           => esc_html__( 'Cart Width (px)', 'woo-floating-cart' ),
		'type'            => 'slider',
		'choices'         => array(
			'min'  => '250',
			'max'  => '1000',
			'step' => '5',
            'suffix' => 'px',
		),
		'default'         => '440',
		'priority'        => 10,
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-width',
				'value_pattern' => '$px'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_dimensions_unit',
				'operator' => '==',
				'value'    => 'pixels',
			),
		),
		'screen' => 'tablet'
	);

	$fields[] = array(
		'id'              => 'cart_height_tablet',
		'section'         => 'cart',
		'label'           => esc_html__( 'Cart Height (px)', 'woo-floating-cart' ),
		'type'            => 'slider',
		'choices'         => array(
			'min'  => '240',
			'max'  => '1000',
			'step' => '5',
            'suffix' => 'px',
		),
		'default'         => '400',
		'priority'        => 10,
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-height',
				'value_pattern' => '$px'
			)
		),
		'active_callback' => array(
            array(
                'setting'  => 'cart_autoheight_enabled',
                'operator' => '!=',
                'value'    => '1',
            ),
			array(
				'setting'  => 'cart_dimensions_unit',
				'operator' => '==',
				'value'    => 'pixels',
			),
		),
		'screen' => 'tablet'
	);

	$fields[] = array(
		'id'              => 'cart_width_percent_tablet',
		'section'         => 'cart',
		'label'           => esc_html__( 'Cart Width (%)', 'woo-floating-cart' ),
		'type'            => 'slider',
		'choices'         => array(
			'min'  => '30',
			'max'  => '100',
			'step' => '1',
            'suffix' => '%',
		),
		'default'         => '40',
		'priority'        => 10,
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => ':root .xt_woofc-dimensions-percent',
				'property' => '--xt-woofc-width',
				'value_pattern' => '$vw'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_dimensions_unit',
				'operator' => '==',
				'value'    => 'percent',
			),
		),
		'screen' => 'tablet'
	);

	$fields[] = array(
		'id'              => 'cart_height_percent_tablet',
		'section'         => 'cart',
		'label'           => esc_html__( 'Cart Height (%)', 'woo-floating-cart' ),
		'type'            => 'slider',
		'choices'         => array(
			'min'  => '30',
			'max'  => '100',
			'step' => '1',
            'suffix' => '%',
		),
		'default'         => '80',
		'priority'        => 10,
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => ':root .xt_woofc-dimensions-percent',
				'property' => '--xt-woofc-height',
				'value_pattern' => '$vh'
			)
		),
		'active_callback' => array(
            array(
                'setting'  => 'cart_autoheight_enabled',
                'operator' => '!=',
                'value'    => '1',
            ),
			array(
				'setting'  => 'cart_dimensions_unit',
				'operator' => '==',
				'value'    => 'percent',
			),
		),
		'screen' => 'tablet'
	);

	$fields[] = array(
		'id'              => 'cart_width_mobile',
		'section'         => 'cart',
		'label'           => esc_html__( 'Cart Width (px)', 'woo-floating-cart' ),
		'type'            => 'slider',
		'choices'         => array(
			'min'  => '250',
			'max'  => '1000',
			'step' => '5',
            'suffix' => 'px',
		),
		'default'         => '440',
		'priority'        => 10,
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-width',
				'value_pattern' => '$px'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_dimensions_unit',
				'operator' => '==',
				'value'    => 'pixels',
			),
		),
		'screen' => 'mobile'
	);

	$fields[] = array(
		'id'              => 'cart_height_mobile',
		'section'         => 'cart',
		'label'           => esc_html__( 'Cart Height (px)', 'woo-floating-cart' ),
		'type'            => 'slider',
		'choices'         => array(
			'min'  => '240',
			'max'  => '1000',
			'step' => '5',
            'suffix' => 'px',
		),
		'default'         => '1000',
		'priority'        => 10,
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'       => ':root',
				'property'      => '--xt-woofc-height',
				'value_pattern' => '$px'
			)
		),
		'active_callback' => array(
            array(
                'setting'  => 'cart_autoheight_enabled',
                'operator' => '!=',
                'value'    => '1',
            ),
			array(
				'setting'  => 'cart_dimensions_unit',
				'operator' => '==',
				'value'    => 'pixels',
			),
		),
		'screen' => 'mobile'
	);

	$fields[] = array(
		'id'              => 'cart_width_percent_mobile',
		'section'         => 'cart',
		'label'           => esc_html__( 'Cart Width (%)', 'woo-floating-cart' ),
		'type'            => 'slider',
		'choices'         => array(
			'min'  => '60',
			'max'  => '100',
			'step' => '1',
            'suffix' => '%',
		),
		'default'         => '100',
		'priority'        => 10,
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'       => ':root .xt_woofc-dimensions-percent',
				'property'      => '--xt-woofc-width',
				'value_pattern' => '$vw'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_dimensions_unit',
				'operator' => '==',
				'value'    => 'percent',
			),
		),
		'screen' => 'mobile'
	);

	$fields[] = array(
		'id'              => 'cart_height_percent_mobile',
		'section'         => 'cart',
		'label'           => esc_html__( 'Cart Height (%)', 'woo-floating-cart' ),
		'type'            => 'slider',
		'choices'         => array(
			'min'  => '30',
			'max'  => '100',
			'step' => '1',
            'suffix' => '%',
		),
		'default'         => '100',
		'priority'        => 10,
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'       => ':root .xt_woofc-dimensions-percent',
				'property'      => '--xt-woofc-height',
				'value_pattern' => '$vh'
			)
		),
		'active_callback' => array(
            array(
                'setting'  => 'cart_autoheight_enabled',
                'operator' => '!=',
                'value'    => '1',
            ),
			array(
				'setting'  => 'cart_dimensions_unit',
				'operator' => '==',
				'value'    => 'percent',
			),
		),
		'screen' => 'mobile'
	);

    $fields[] = array(
        'id'        => 'border_radius_expanded',
        'section'   => 'cart',
        'label'     => esc_html__( 'Cart Border Radius', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '0',
            'max'  => '25',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '6',
        'priority'  => 10,
        'transport' => 'auto',
        'output'    => array(
	        array(
		        'element'       => '.xt_woofc-cart-open',
		        'property'      => '--xt-woofc-radius',
		        'value_pattern' => '$px'
	        )
        ),
        'screen' => 'desktop'
    );

    $fields[] = array(
        'id'        => 'border_radius_expanded_tablet',
        'section'   => 'cart',
        'label'     => esc_html__( 'Cart Border Radius', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '0',
            'max'  => '25',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '6',
        'priority'  => 10,
        'transport' => 'auto',
        'output'    => array(
	        array(
		        'element'       => '.xt_woofc-cart-open',
		        'property'      => '--xt-woofc-radius',
		        'value_pattern' => '$px'
	        )
        ),
        'screen' => 'tablet'
    );

    $fields[] = array(
        'id'        => 'border_radius_expanded_mobile',
        'section'   => 'cart',
        'label'     => esc_html__( 'Cart Border Radius', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '0',
            'max'  => '25',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '6',
        'priority'  => 10,
        'transport' => 'auto',
        'output'    => array(
	        array(
		        'element'       => '.xt_woofc-cart-open',
		        'property'      => '--xt-woofc-radius',
		        'value_pattern' => '$px'
	        )
        ),
        'screen' => 'mobile'
    );

} else {

	$fields[] = array(
		'id'      => 'cart_features',
		'section' => 'cart',
		'type'    => 'xt-premium',
		'default' => array(
			'type'  => 'image',
			'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/cart.png',
			'link'  => $this->core->plugin_upgrade_url()
		)
	);
}