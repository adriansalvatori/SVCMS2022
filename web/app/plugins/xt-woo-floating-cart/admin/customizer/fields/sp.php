<?php
if ( $this->core->access_manager()->can_use_premium_code__premium_only() ) {

    $fields[] = array(
        'id'          => 'suggested_products_enabled',
        'section'     => 'sp',
        'label'       => esc_html__( 'Enable Suggested Products', 'woo-floating-cart' ),
        'type'        => 'radio-buttonset',
        'choices'     => array(
            '1' => esc_attr__( 'Enable', 'woo-floating-cart' ),
            '0' => esc_attr__( 'Disable', 'woo-floating-cart' )
        ),
        'default'    => '1'
    );

    $fields[] = array(
        'id'          => 'suggested_products_mobile_enabled',
        'section'     => 'sp',
        'label'       => esc_html__( 'Enable on Mobile', 'woo-floating-cart' ),
        'type'        => 'radio-buttonset',
        'choices'     => array(
            '1' => esc_attr__( 'Enable', 'woo-floating-cart' ),
            '0' => esc_attr__( 'Disable', 'woo-floating-cart' )
        ),
        'default'     => '0',
        'active_callback' => array(
            array(
                'setting'  => 'suggested_products_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        ),
        'transport'  => 'postMessage',
        'partial_refresh'    => [
            'suggested_products_mobile_enabled' => [
                'selector'        => '.xt_woofc-body-footer',
                'render_callback' => function() {
                    do_action( 'xt_woofc_cart_body_footer' );
                },
            ]
        ],
    );

    $fields[] = array(
        'id'          => 'suggested_products_type',
        'section'     => 'sp',
        'label'       => esc_html__( 'Query Type', 'woo-floating-cart' ),
        'type'        => 'radio-buttonset',
        'input_attrs' => array(
	        'data-col' => '2'
        ),
        'choices'     => array(
	        'cross_sells' => esc_attr__( 'Cross-Sells', 'woo-floating-cart' ),
	        'up_sells'   => esc_attr__( 'Up-Sells', 'woo-floating-cart' ),
            'related'    => esc_attr__( 'Related', 'woo-floating-cart' ),
	        'selection'    => esc_attr__( 'Selection', 'woo-floating-cart' )
        ),
        'default'     => 'cross_sells',
        'active_callback' => array(
            array(
                'setting'  => 'suggested_products_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        ),
        'transport'  => 'postMessage',
        'partial_refresh'    => [
            'suggested_products_type' => [
                'selector'        => '.xt_woofc-body-footer',
                'render_callback' => function() {
                    do_action( 'xt_woofc_cart_body_footer' );
                },
            ]
        ],
    );

	$fields[] = array(
		'id'          => 'suggested_products_selection',
		'section'     => 'sp',
		'label'       => esc_html__( 'Selection', 'woo-floating-cart' ),
		'description' => esc_html__( 'Enter product ids separated by a comma', 'woo-floating-cart' ),
		'type'        => 'text',
		'default'     => '',
		'active_callback' => array(
			array(
				'setting'  => 'suggested_products_enabled',
				'operator' => '==',
				'value'    => '1',
			),
			array(
				'setting'  => 'suggested_products_type',
				'operator' => '==',
				'value'    => 'selection',
			),
		),
        'transport'  => 'postMessage',
        'partial_refresh'    => [
            'suggested_products_selection' => [
                'selector'        => '.xt_woofc-body-footer',
                'render_callback' => function() {
                    do_action( 'xt_woofc_cart_body_footer' );
                },
            ]
        ],
	);

	$fields[] = array(
		'id'          => 'suggested_products_position',
		'section'     => 'sp',
		'label'       => esc_html__( 'Position', 'woo-floating-cart' ),
		'type'        => 'radio',
		'choices'     => array(
			'below_list'   => esc_attr__( 'Below Cart List', 'woo-floating-cart' ),
			'above_totals' => esc_attr__( 'Above Cart Totals', 'woo-floating-cart' ),
			'below_totals'    => esc_attr__( 'Below Cart Totals', 'woo-floating-cart' )
		),
		'default'     => 'below_list',
		'active_callback' => array(
			array(
				'setting'  => 'suggested_products_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		),
        'transport'  => 'postMessage',
        'partial_refresh'    => [
            'suggested_products_position' => [
                'selector'        => '.xt_woofc-body-footer',
                'render_callback' => function() {
                    do_action( 'xt_woofc_cart_body_footer' );
                },
            ]
        ],
	);

    $fields[] = array(
        'id'          => 'suggested_products_title',
        'section'     => 'sp',
        'label'       => esc_html__( 'Title', 'woo-floating-cart' ),
        'type'        => 'text',
        'default'     => esc_html__('Products you might like','woo-floating-cart'),
        'active_callback' => array(
            array(
                'setting'  => 'suggested_products_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        ),
        'transport'  => 'postMessage',
        'partial_refresh'    => [
            'suggested_products_title' => [
                'selector'        => '.xt_woofc-body-footer',
                'render_callback' => function() {
                    do_action( 'xt_woofc_cart_body_footer' );
                },
            ]
        ],
    );

    $fields[] = array(
        'id'        => 'suggested_products_title_color',
        'section'   => 'sp',
        'label'     => esc_html__( 'Title Color', 'woo-floating-cart' ),
        'type'      => 'color',
        'priority'  => 10,
        'default'   => '',
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'  => ':root',
                'property' => '--xt-woofc-sp-title-color',
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'suggested_products_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        ),
    );

    $fields[] = array(
        'id'          => 'suggested_products_count',
        'section'     => 'sp',
        'label'       => esc_html__( 'Count', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '1',
            'max'  => '10',
            'step' => '1',
        ),
        'default'   => '5',
        'active_callback' => array(
            array(
                'setting'  => 'suggested_products_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        ),
        'transport'  => 'postMessage',
        'partial_refresh'    => [
            'suggested_products_count' => [
                'selector'        => '.xt_woofc-body-footer',
                'render_callback' => function() {
                    do_action( 'xt_woofc_cart_body_footer' );
                },
            ]
        ],
    );

    $fields[] = array(
        'id'          => 'suggested_products_display_type',
        'section'     => 'sp',
        'label'       => esc_html__( 'Display Type', 'woo-floating-cart' ),
        'type'        => 'radio-buttonset',
        'input_attrs' => array(
            'data-col' => '2'
        ),
        'choices'     => array(
            'slider' => esc_attr__( 'Slider', 'woo-floating-cart' ),
            'rows'   => esc_attr__( 'Rows', 'woo-floating-cart' )
        ),
        'default'     => 'slider',
        'active_callback' => array(
            array(
                'setting'  => 'suggested_products_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
        'id'          => 'suggested_products_hide_atc',
        'section'     => 'sp',
        'label'       => esc_html__( 'Hide Add To Cart Button', 'woo-floating-cart' ),
        'type'        => 'radio-buttonset',
        'input_attrs' => array(
            'data-col' => '2'
        ),
        'choices'     => array(
            '0' => esc_attr__( 'No', 'woo-floating-cart' ),
            '1'   => esc_attr__( 'Yes', 'woo-floating-cart' )
        ),
        'default'     => '0',
        'active_callback' => array(
            array(
                'setting'  => 'suggested_products_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        ),
        'transport'  => 'postMessage',
        'partial_refresh'    => [
            'suggested_products_hide_atc' => [
                'selector'        => '.xt_woofc-body-footer',
                'render_callback' => function() {
                    do_action( 'xt_woofc_cart_body_footer' );
                },
            ]
        ],
    );

    $fields[] = array(
        'id'              => 'suggested_products_arrow',
        'section'         => 'sp',
        'label'           => esc_html__( 'Arrows Icon', 'woo-floating-cart' ),
        'type'            => 'xticons',
        'choices'         => array( 'types' => array( 'arrow' ) ),
        'priority'        => 10,
        'default'         => 'xt_wooqvicon-arrows-28',
        'transport'       => 'postMessage',
        'js_vars'         => array(
            array(
                'element'  => '.xt_woofc-inner .xt_woofc-sp-arrow-icon',
                'function' => 'class'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'suggested_products_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
            array(
                'setting'  => 'suggested_products_display_type',
                'operator' => '==',
                'value'    => 'slider',
            )
        ),
    );

	$fields[] = array(
		'id' => 'suggested_products_arrow_size',
		'section' => 'sp',
		'label' => esc_html__( 'Arrows Size', 'woo-floating-cart' ),
		'type' => 'slider',
		'choices' => array(
			'min' => '14',
			'max' => '30',
			'step' => '1',
            'suffix' => 'px',
		),
		'priority' => 10,
		'default' => '20',
		'transport' => 'auto',
		'output' => array(
			array(
				'element' => ':root',
				'property' => '--xt-woofc-sp-arrow-size',
				'value_pattern' => '$px'
			),
		),
		'active_callback' => array(
			array(
				'setting' => 'suggested_products_enabled',
				'operator' => '==',
				'value' => '1',
			),
            array(
                'setting'  => 'suggested_products_display_type',
                'operator' => '==',
                'value'    => 'slider',
            )
		)
	);

	$fields[] = array(
		'id' => 'suggested_products_arrow_color',
		'section' => 'sp',
		'label' => esc_html__( 'Arrows Color', 'woo-floating-cart' ),
		'type' => 'color',
		'priority' => 10,
		'default' => '',
		'transport' => 'auto',
		'output' => array(
			array(
				'element' => ':root',
				'property' => '--xt-woofc-sp-arrow-color',
			)
		),
		'active_callback' => array(
			array(
				'setting' => 'suggested_products_enabled',
				'operator' => '==',
				'value' => '1',
			),
            array(
                'setting'  => 'suggested_products_display_type',
                'operator' => '==',
                'value'    => 'slider',
            )
		)
	);

	$fields[] = array(
		'id' => 'suggested_products_arrow_hover_color',
		'section' => 'sp',
		'label' => esc_html__( 'Arrows Hover Color', 'woo-floating-cart' ),
		'type' => 'color',
		'priority' => 10,
		'default' => '',
		'transport' => 'auto',
		'output' => array(
			array(
				'element' => ':root',
				'property' => '--xt-woofc-sp-arrow-hover-color',
			)
		),
		'active_callback' => array(
			array(
				'setting' => 'suggested_products_enabled',
				'operator' => '==',
				'value' => '1',
			),
            array(
                'setting'  => 'suggested_products_display_type',
                'operator' => '==',
                'value'    => 'slider',
            )
		)
	);

} else {

	$fields[] = array(
		'id'      => 'sp_features',
		'section' => 'sp',
		'type'    => 'xt-premium',
		'default' => array(
			'type'  => 'image',
			'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/sp.png',
			'link'  => $this->core->plugin_upgrade_url()
		)
	);
}