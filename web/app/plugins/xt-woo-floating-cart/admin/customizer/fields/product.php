<?php
if ( $this->core->access_manager()->can_use_premium_code__premium_only() ) {

	// GENERAL

	$fields[] = array(
		'id' => 'product_general',
		'section' => 'product',
		'label'   => esc_html__( 'General', 'woo-floating-cart' ),
		'type' => 'custom'
	);

	$fields[] = array(
		'id'        => 'cart_product_show_sku',
		'section'   => 'product',
		'label'     => esc_html__( 'Show Product Sku', 'woo-floating-cart' ),
		'type'      => 'toggle',
		'default'   => '0',
		'priority'  => 10,
		'transport' => 'postMessage',
		'partial_refresh'    => [
			'cart_product_show_sku' => [
				'selector'        => '.xt_woofc-list-wrap',
				'render_callback' => function() {
					return $this->core->get_template( 'parts/cart/list', array('no_container' => true), true );
				},
			]
		]
	);

	$fields[] = array(
		'id'        => 'cart_product_show_attributes',
		'section'   => 'product',
		'label'     => esc_html__( 'Show Product Attributes', 'woo-floating-cart' ),
		'type'      => 'toggle',
		'default'   => '0',
		'priority'  => 10,
		'transport' => 'postMessage',
		'partial_refresh'    => [
			'cart_product_show_attributes' => [
				'selector'        => '.xt_woofc-list-wrap',
				'render_callback' => function() {
					return $this->core->get_template( 'parts/cart/list', array('no_container' => true), true );
				},
			]
		]
	);

	$fields[] = array(
		'id'        => 'cart_product_show_bundled_products',
		'section'   => 'product',
		'label'     => esc_html__( 'Show Bundled Products Items', 'woo-floating-cart' ),
		'type'      => 'toggle',
		'default'   => '1',
		'priority'  => 10,
		'transport' => 'postMessage',
		'partial_refresh'    => [
			'cart_product_show_bundled_products' => [
				'selector'        => '.xt_woofc-list-wrap',
				'render_callback' => function() {
					return $this->core->get_template( 'parts/cart/list', array('no_container' => true), true );
				},
			]
		]
	);

	$fields[] = array(
		'id'        => 'cart_product_show_composite_products',
		'section'   => 'product',
		'label'     => esc_html__( 'Show Composite Products Items', 'woo-floating-cart' ),
		'type'      => 'toggle',
		'default'   => '1',
		'priority'  => 10,
		'transport' => 'postMessage',
		'partial_refresh'    => [
			'cart_product_show_composite_products' => [
				'selector'        => '.xt_woofc-list-wrap',
				'render_callback' => function() {
					return $this->core->get_template( 'parts/cart/list', array('no_container' => true), true );
				},
			]
		]
	);

	$fields[] = array(
		'id'        => 'cart_product_link_to_single',
		'section'   => 'product',
		'label'     => esc_html__( 'Link Product to Single Page', 'woo-floating-cart' ),
		'type'      => 'radio-buttonset',
		'choices'   => array(
			'0' => esc_attr__( 'No', 'woo-floating-cart' ),
			'1' => esc_attr__( 'Yes', 'woo-floating-cart' ),
		),
		'default'   => '1',
		'priority'  => 10,
		'transport' => 'postMessage',
		'partial_refresh'    => [
			'cart_product_link_to_single' => [
				'selector'        => '.xt_woofc-list-wrap',
				'render_callback' => function() {
					return $this->core->get_template( 'parts/cart/list', array('no_container' => true), true );
				},
			]
		]
	);

	// THUMBNAIL

	$fields[] = array(
		'id' => 'product_thumbnail',
		'section'   => 'product',
		'label'     => esc_html__( 'Thumbnail', 'woo-floating-cart' ),
		'type' => 'custom'
	);

	$fields[] = array(
		'id'        => 'cart_product_hide_thumb',
		'section'   => 'product',
		'label'     => esc_html__( 'Hide Product Thumbnail', 'woo-floating-cart' ),
		'type'      => 'radio-buttonset',
		'choices'   => array(
			'show-thumbs' => esc_attr__( 'No', 'woo-floating-cart' ),
			'hide-thumbs' => esc_attr__( 'Yes', 'woo-floating-cart' ),
		),
		'default'   => 'show-thumbs',
		'priority'  => 10,
		'transport' => 'postMessage',
		'js_vars'   => array(
			array(
				'element'  => '.xt_woofc',
				'function' => 'class',
				'prefix'   => 'xt_woofc-'
			)
		)
	);

	$fields[] = array(
		'id'        => 'cart_product_squared_thumb',
		'section'   => 'product',
		'label'     => esc_html__( 'Force Squared Thumbnail', 'woo-floating-cart' ),
		'description' => esc_html__( 'If enabled, the thumbnail container will be cropped to make the thumbnail squared', 'woo-floating-cart' ),
		'type'      => 'radio-buttonset',
		'choices'   => array(
			'0' => esc_attr__( 'No', 'woo-floating-cart' ),
			'1' => esc_attr__( 'Yes', 'woo-floating-cart' ),
		),
		'default'   => '1',
		'priority'  => 10,
		'transport' => 'postMessage',
		'js_vars'   => array(
			array(
				'element'  => '.xt_woofc',
				'function' => 'toggleClass',
				'class'    => 'xt_woofc-squared-thumbnail' ,
				'value'   => '1'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_product_hide_thumb',
				'value'    => 'show-thumbs',
			),
		)
	);


    $fields[] = array(
        'id'        => 'cart_product_thumb_width_desktop',
        'section'   => 'product',
        'label'     => esc_html__( 'Product Thumbnail Width', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '50',
            'max'  => '120',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '80',
        'priority'  => 10,
        'transport' => 'auto',
        'screen' => 'desktop',
        'output'    => array(
            array(
                'element' => ':root',
                'property' => '--xt-woofc-product-image-width',
                'value_pattern' => '$px'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_product_hide_thumb',
                'value'    => 'show-thumbs',
            ),
        )
    );

    $fields[] = array(
        'id'        => 'cart_product_thumb_width_tablet',
        'section'   => 'product',
        'label'     => esc_html__( 'Product Thumbnail Width', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '50',
            'max'  => '120',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '80',
        'priority'  => 10,
        'transport' => 'auto',
        'screen' => 'tablet',
        'output'    => array(
            array(
                'element' => ':root',
                'property' => '--xt-woofc-product-image-width',
                'value_pattern' => '$px'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_product_hide_thumb',
                'value'    => 'show-thumbs',
            ),
        )
    );

    $fields[] = array(
        'id'        => 'cart_product_thumb_width_mobile',
        'section'   => 'product',
        'label'     => esc_html__( 'Product Thumbnail Width', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '50',
            'max'  => '120',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '80',
        'priority'  => 10,
        'transport' => 'auto',
        'screen' => 'mobile',
        'output'    => array(
            array(
                'element' => ':root',
                'property' => '--xt-woofc-product-image-width',
                'value_pattern' => '$px'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_product_hide_thumb',
                'value'    => 'show-thumbs',
            ),
        )
    );

    $fields[] = array(
        'id'        => 'cart_product_thumb_border_radius',
        'section'   => 'product',
        'label'     => esc_html__( 'Thumbnail Border Radius', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '0',
            'max'  => '100',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '0',
        'priority'  => 10,
        'transport' => 'auto',
        'output'    => array(
            array(
                'element' => ':root',
                'property' => '--xt-woofc-product-image-border-radius',
                'value_pattern' => '$px'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_product_hide_thumb',
                'value'    => 'show-thumbs',
            ),
        )
    );

    $fields[] = array(
        'id'        => 'cart_product_thumb_padding',
        'section'   => 'product',
        'label'     => esc_html__( 'Product Thumbnail Padding', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '0',
            'max'  => '5',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '0',
        'priority'  => 10,
        'transport' => 'auto',
        'output'    => array(
            array(
                'element' => ':root',
                'property' => '--xt-woofc-product-image-padding',
                'value_pattern' => '$px'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_product_hide_thumb',
                'value'    => 'show-thumbs',
            ),
        )
    );

    $fields[] = array(
        'id'        => 'cart_product_thumb_border_width',
        'section'   => 'product',
        'label'     => esc_html__( 'Thumbnail Border Width', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '0',
            'max'  => '5',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '0',
        'priority'  => 10,
        'transport' => 'auto',
        'output'    => array(
            array(
                'element' => ':root',
                'property' => '--xt-woofc-product-image-border-width',
                'value_pattern' => '$px'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_product_hide_thumb',
                'value'    => 'show-thumbs',
            ),
        )
    );

    $fields[] = array(
        'id'        => 'cart_product_thumb_border_color',
        'section'   => 'product',
        'label'     => esc_html__( 'Thumbnail Border Color', 'woo-floating-cart' ),
        'type'      => 'color',
        'priority'  => 10,
        'default'   => '',
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'  => ':root',
                'property' => '--xt-woofc-product-image-border-color',
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_product_hide_thumb',
                'value'    => 'show-thumbs',
            ),
        )
    );

	// TITLE

	$fields[] = array(
		'id' => 'product_title',
		'section' => 'product',
		'label'   => esc_html__( 'Title', 'woo-floating-cart' ),
		'type' => 'custom'
	);

	$fields[] = array(
		'id'        => 'cart_product_title_truncate',
		'section'   => 'product',
		'label'     => esc_html__( 'Cart Product Title Truncate', 'woo-floating-cart' ),
		'description'     => esc_html__( 'Truncate will keep the title on 1 line. If the title overflows, an ellipsis (...) will be shown', 'woo-floating-cart' ),
		'type'      => 'radio-buttonset',
		'choices'   => array(
			'nowrap'   => esc_attr__( 'Yes', 'woo-floating-cart' ),
			'normal' => esc_attr__( 'No', 'woo-floating-cart' )
		),
		'default'   => 'nowrap',
		'priority'  => 10,
		'transport' => 'auto',
		'output'    => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-product-title-wrap',
			)
		)
	);

	$fields[] = array(
		'id'        => 'cart_product_title_color',
		'section'   => 'product',
		'label'     => esc_html__( 'Cart Product Title Color', 'woo-floating-cart' ),
		'type'      => 'color',
		'priority'  => 10,
		'default'   => '',
		'transport' => 'auto',
		'output'    => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-product-title-color',
			)
		)
	);

	$fields[] = array(
		'id'        => 'cart_product_title_hover_color',
		'section'   => 'product',
		'label'     => esc_html__( 'Cart Product Title Hover Color', 'woo-floating-cart' ),
		'type'      => 'color',
		'priority'  => 10,
		'default'   => '',
		'transport' => 'auto',
		'output'    => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-product-title-hover-color',
			)
		)
	);

	// PRICE

	$fields[] = array(
		'id' => 'product_price',
		'section'   => 'product',
		'label'     => esc_html__( 'Price', 'woo-floating-cart' ),
		'type' => 'custom'
	);

	$fields[] = array(
		'id'        => 'cart_product_price_color',
		'section'   => 'product',
		'label'     => esc_html__( 'Cart Product Price Color', 'woo-floating-cart' ),
		'type'      => 'color',
		'priority'  => 10,
		'default'   => '',
		'transport' => 'auto',
		'output'    => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-product-price-color',
			)
		)
	);

	$fields[] = array(
		'id'        => 'cart_product_price_display',
		'section'   => 'product',
		'label'     => esc_html__( 'Cart Product Price Display', 'woo-floating-cart' ),
		'type'        => 'radio-buttonset',
		'choices'     => array(
			'subtotal' => esc_attr__( 'Subtotal', 'woo-floating-cart' ),
			'item_price' => esc_attr__( 'Item Price', 'woo-floating-cart' )
		),
		'transport' => 'postMessage',
		'priority'  => 10,
		'default'   => 'subtotal',
		'partial_refresh'    => [
			'cart_product_price_display' => [
				'selector'        => '.xt_woofc-list-wrap',
				'render_callback' => function() {
					return $this->core->get_template( 'parts/cart/list', array('no_container' => true), true );
				},
			]
		],
	);

	// ATTRIBUTES

	$fields[] = array(
		'id' => 'product_attributes',
		'section'   => 'product',
		'label'     => esc_html__( 'Attributes', 'woo-floating-cart' ),
		'type' => 'custom',
		'active_callback' => array(
			array(
				'setting'  => 'cart_product_show_attributes',
				'operator' => '==',
				'value'    => '1',
			),
		),
	);

	$fields[] = array(
		'id'              => 'cart_product_attributes_display',
		'section'         => 'product',
		'label'           => esc_html__( 'Product Attributes Display Type', 'woo-floating-cart' ),
		'type'            => 'radio-buttonset',
		'choices'         => array(
			'list'   => esc_attr__( 'List', 'woo-floating-cart' ),
			'inline' => esc_attr__( 'Inline', 'woo-floating-cart' )
		),
		'default'         => 'list',
		'priority'        => 10,
		'transport'       => 'postMessage',
		'js_vars'         => array(
			array(
				'element'  => '.xt_woofc-variation',
				'function' => 'class',
				'prefix'   => 'xt_woofc-variation-'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_product_show_attributes',
				'operator' => '==',
				'value'    => '1',
			),
		),
	);

	$fields[] = array(
		'id'              => 'cart_product_attributes_hide_label',
		'section'         => 'product',
		'label'           => esc_html__( 'Hide attribute labels, show values only', 'woo-floating-cart' ),
		'type'            => 'radio-buttonset',
		'choices'         => array(
			'0' => esc_attr__( 'No', 'woo-floating-cart' ),
			'1' => esc_attr__( 'Yes', 'woo-floating-cart' ),
		),
		'default'         => '0',
		'priority'        => 10,
		'transport'       => 'postMessage',
		'partial_refresh'    => [
			'cart_product_attributes_hide_label' => [
				'selector'        => '.xt_woofc-list-wrap',
				'render_callback' => function() {
					return $this->core->get_template( 'parts/cart/list', array('no_container' => true), true );
				},
			]
		],
		'active_callback' => array(
			array(
				'setting'  => 'cart_product_show_attributes',
				'operator' => '==',
				'value'    => '1',
			),
		),
	);

	$fields[] = array(
		'id'              => 'cart_product_attributes_color',
		'section'         => 'product',
		'label'           => esc_html__( 'Cart Product Attributes Color', 'woo-floating-cart' ),
		'type'            => 'color',
		'priority'        => 10,
		'default'         => '',
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-product-attributes-color',
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_product_show_attributes',
				'operator' => '==',
				'value'    => '1',
			),
		)
	);

	// QUANTITY

	$fields[] = array(
		'id' => 'product_quantity',
		'section'   => 'product',
		'label'     => esc_html__( 'Quantity Input', 'woo-floating-cart' ),
		'type' => 'custom'
	);

	$fields[] = array(
		'id'        => 'cart_product_qty_enabled',
		'section'   => 'product',
		'label'     => esc_html__( 'Cart Product Qty Input Enabled', 'woo-floating-cart' ),
		'type'        => 'radio-buttonset',
		'choices'     => array(
			'0' => esc_attr__( 'Disabled', 'woo-floating-cart' ),
			'1' => esc_attr__( 'Enabled', 'woo-floating-cart' )
		),
		'priority'  => 10,
		'default'   => '1',
		'partial_refresh'    => [
			'cart_product_qty_enabled' => [
				'selector'        => '.xt_woofc-list-wrap',
				'render_callback' => function() {
					return $this->core->get_template( 'parts/cart/list', array('no_container' => true), true );
				},
			]
		]
	);

	$fields[] = array(
		'id'        => 'cart_product_qty_template',
		'section'   => 'product',
		'label'     => esc_html__( 'Cart Product Qty Template', 'woo-floating-cart' ),
		'type'      => 'sortable',
		'choices'     => array(
			'input' => esc_html__( 'Quantity Input', 'woo-floating-cart' ),
			'minus' => esc_html__( 'Minus Icon', 'woo-floating-cart' ),
			'plus' => esc_html__( 'Plus Icon', 'woo-floating-cart' )
		),
		'default'     => array(
			'input',
			'minus',
			'plus'
		),
		'priority'  => 10,
		'transport' => 'postMessage',
		'partial_refresh'    => [
			'cart_product_qty_template' => [
				'selector'        => '.xt_woofc-list-wrap',
				'render_callback' => function() {
					$this->core->get_template( 'parts/cart/list', array('no_container' => true), true );
				},
			]
		],
		'active_callback' => array(
			array(
				'setting'  => 'cart_product_qty_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		),
	);

	$fields[] = array(
		'id'        => 'cart_product_qty_plus_minus_color',
		'section'   => 'product',
		'label'     => esc_html__( 'Cart Product Qty Plus Minus Color', 'woo-floating-cart' ),
		'type'      => 'color',
		'priority'  => 10,
		'default'   => '',
		'transport' => 'auto',
		'output'    => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-product-qty-icon-color',
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_product_qty_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		),
	);
	$fields[] = array(
		'id'        => 'cart_product_qty_plus_minus_hover_color',
		'section'   => 'product',
		'label'     => esc_html__( 'Cart Product Qty Plus Minus Hover Color', 'woo-floating-cart' ),
		'type'      => 'color',
		'priority'  => 10,
		'default'   => '',
		'transport' => 'auto',
		'output'    => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-product-qty-icon-hover-color',
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_product_qty_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		),
	);

	$fields[] = array(
		'id'        => 'cart_product_qty_input_color',
		'section'   => 'product',
		'label'     => esc_html__( 'Cart Product Qty Input Color', 'woo-floating-cart' ),
		'type'      => 'color',
		'priority'  => 10,
		'default'   => '',
		'transport' => 'auto',
		'output'    => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-product-qty-input-color',
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_product_qty_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		),
	);

	$fields[] = array(
		'id'        => 'cart_product_qty_plus_minus_size',
		'section'   => 'product',
		'label'     => esc_html__( 'Cart Product Qty Plus Minus Size', 'woo-floating-cart' ),
		'type'      => 'slider',
		'choices'   => array(
			'min'  => '10',
			'max'  => '18',
			'step' => '1',
			'suffix' => 'px',
		),
		'default'   => '10',
		'priority'  => 10,
		'transport' => 'auto',
		'output'    => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-product-qty-icon-size',
				'value_pattern' => '$px'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_product_qty_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		),
	);

	$fields[] = array(
		'id'        => 'cart_product_qty_input_size',
		'section'   => 'product',
		'label'     => esc_html__( 'Cart Product Qty Input Size', 'woo-floating-cart' ),
		'type'      => 'slider',
		'choices'   => array(
			'min'  => '10',
			'max'  => '18',
			'step' => '1',
			'suffix' => 'px',
		),
		'default'   => '16',
		'priority'  => 10,
		'transport' => 'auto',
		'output'    => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-product-qty-input-size',
				'value_pattern' => '$px'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_product_qty_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		),
	);

    $fields[] = array(
        'id'        => 'cart_product_qty_input_radius',
        'section'   => 'product',
        'label'     => esc_html__( 'Cart Product Qty Input Radius', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '0',
            'max'  => '25',
            'step' => '1',
            'suffix' => 'px',
        ),
        'default'   => '0',
        'priority'  => 10,
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'  => ':root',
                'property' => '--xt-woofc-product-qty-input-radius',
                'value_pattern' => '$px'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_product_qty_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        ),
    );

	// REMOVE

	$fields[] = array(
		'id' => 'product_remove',
		'section'   => 'product',
		'label'     => esc_html__( 'Remove Link', 'woo-floating-cart' ),
		'type' => 'custom'
	);

	$fields[] = array(
		'id'        => 'cart_product_delete_icon_enabled',
		'section'   => 'product',
		'label'     => esc_html__( 'Cart Product Remove Icon Enabled', 'woo-floating-cart' ),
		'type'        => 'radio-buttonset',
		'choices'     => array(
			'0' => esc_attr__( 'No', 'woo-floating-cart' ),
			'1' => esc_attr__( 'Yes', 'woo-floating-cart' )
		),
		'priority'  => 10,
		'default'   => '0',
		'transport' => 'postMessage',
		'js_vars'   => array(
			array(
				'element'  => '.xt_woofc',
				'function' => 'toggleClass',
				'class' => 'xt_woofc-icon-actions',
				'value' => '1'
			)
		),
		'output'    => array(
			array(
				'element'  => '.xt_woofc.xt_woofc-icon-actions .xt_woofc-actions a span',
				'property' => 'display',
				'value_pattern' => 'none'
			),
			array(
				'element'  => '.xt_woofc:not(.xt_woofc-icon-actions) .xt_woofc-actions a i',
				'property' => 'display',
				'value_pattern' => 'none'
			)
		)
	);

	$fields[] = array(
		'id'        => 'cart_product_delete_icon',
		'section'   => 'product',
		'label'     => esc_html__( 'Cart Product Remove Icon', 'woo-floating-cart' ),
		'type'      => 'xticons',
		'choices'   => array( 'types' => array( 'trash' ) ),
		'priority'  => 10,
		'default'   => 'xt_icon-trash-o',
		'transport' => 'postMessage',
		'js_vars'   => array(
			array(
				'element'  => '.xt_woofc-inner .xt_woofc-delete-icon',
				'function' => 'class'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_product_delete_icon_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		)
	);

	$fields[] = array(
		'id'        => 'cart_product_delete_color',
		'section'   => 'product',
		'label'     => esc_html__( 'Cart Product Remove Color', 'woo-floating-cart' ),
		'type'      => 'color',
		'priority'  => 10,
		'default'   => '',
		'transport' => 'auto',
		'output'    => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-product-remove-color'
			)
		)
	);

	$fields[] = array(
		'id'        => 'cart_product_delete_hover_color',
		'section'   => 'product',
		'label'     => esc_html__( 'Cart Product Remove Hover Color', 'woo-floating-cart' ),
		'type'      => 'color',
		'priority'  => 10,
		'default'   => '',
		'transport' => 'auto',
		'output'    => array(

			array(
				'element'  => ':root',
				'property' => '--xt-woofc-product-remove-hover-color'
			)
		)
	);

} else {

	$fields[] = array(
		'id'      => 'list_features',
		'section' => 'product',
		'type'    => 'xt-premium',
		'default' => array(
			'type'  => 'image',
			'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/product.png',
			'link'  => $this->core->plugin_upgrade_url()
		)
	);
}