<?php
if ( $this->core->access_manager()->can_use_premium_code__premium_only() ) {

	$fields[] = array(
		'id'       => 'cart_menu_enabled',
		'section'  => 'menu-item',
		'label'       => esc_html__( 'Enable Cart Menu Item', 'woo-floating-cart' ),
		'type'        => 'radio-buttonset',
		'choices'     => array(
			'0' => esc_html__( 'No', 'woo-floating-cart' ),
			'1' => esc_html__( 'Yes', 'woo-floating-cart' )
		),
		'default'     => '0',
		'priority'    => 10
	);

	$fields[] = array(
		'id'       => 'cart_menu_menus',
		'section'  => 'menu-item',
		'label'       => esc_html__( 'Select Menu(s)', 'woo-floating-cart' ),
		'description' => esc_html__( 'Select the menu(s) in which you want to display the Menu Cart', 'woo-floating-cart' ),
		'type'        => 'select',
		'choices'     => XT_Framework_Customizer_Options::get_menu_options(),
		'multiple'    => 999,
		'default'     => array(),
		'priority'    => 10,
		'screen'      => 'desktop',
		'active_callback' => array(
			array(
				'setting'  => 'cart_menu_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		)
	);

    $fields[] = array(
        'id'       => 'cart_menu_menus_tablet',
        'section'  => 'menu-item',
        'label'       => esc_html__( 'Select Menu(s)', 'woo-floating-cart' ),
        'description' => esc_html__( 'Select the menu(s) in which you want to display the Menu Cart', 'woo-floating-cart' ),
        'type'        => 'select',
        'choices'     => XT_Framework_Customizer_Options::get_menu_options(),
        'multiple'    => 999,
        'default'     => array(),
        'priority'    => 10,
        'screen'      => 'tablet',
        'active_callback' => array(
            array(
                'setting'  => 'cart_menu_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
        'id'       => 'cart_menu_menus_mobile',
        'section'  => 'menu-item',
        'label'       => esc_html__( 'Select Menu(s)', 'woo-floating-cart' ),
        'description' => esc_html__( 'Select the menu(s) in which you want to display the Menu Cart', 'woo-floating-cart' ),
        'type'        => 'select',
        'choices'     => XT_Framework_Customizer_Options::get_menu_options(),
        'multiple'    => 999,
        'default'     => array(),
        'priority'    => 10,
        'screen'      => 'mobile',
        'active_callback' => array(
            array(
                'setting'  => 'cart_menu_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        )
    );

	if( $this->core->is_theme(array('Storefront','Divi'))) {

		$fields[] = array(
			'id'       => 'cart_menu_hide_theme_menu_cart',
			'section'  => 'menu-item',
			'label'       => esc_html__( 'Hide default theme menu item', 'woo-floating-cart' ),
			'type'        => 'radio-buttonset',
			'choices'     => array(
				'0' => esc_html__( 'No', 'woo-floating-cart' ),
				'1' => esc_html__( 'Yes', 'woo-floating-cart' )
			),
			'default'     => '1',
			'priority'    => 10,
			'active_callback' => array(
				array(
					'setting'  => 'cart_menu_enabled',
					'operator' => '==',
					'value'    => '1',
				),
			)
		);
	}

	$fields[] = array(
		'id'       => 'cart_menu_display_empty',
		'section'  => 'menu-item',
		'label' => esc_html__( 'Always Visible', 'woo-floating-cart' ),
		'description' => esc_html__( 'Always display cart menu item, even if it\'s empty', 'woo-floating-cart' ),
		'type'        => 'radio-buttonset',
		'choices'     => array(
			'0' => esc_html__( 'No', 'woo-floating-cart' ),
			'1' => esc_html__( 'Yes', 'woo-floating-cart' )
		),
		'default'     => '1',
		'priority'    => 10,
		'transport'   => 'postMessage',
		'js_vars'         => array(
			array(
				'element'  => '.xt_woofc-menu',
				'function' => 'toggleClass',
				'class' => 'xt_woofc-menu-hide-empty',
				'value' => '0'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_menu_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		)
	);

	$fields[] = array(
		'id'       => 'cart_menu_position',
		'section'  => 'menu-item',
		'label'       => esc_html__( 'Position', 'woo-floating-cart' ),
		'description' => esc_html__( 'Select the position that looks best with your menu.', 'woo-floating-cart' ),
		'type'        => 'radio-buttonset',
		'choices'     => array(
			'first'	=> esc_html__( 'First Menu Item' , 'woo-floating-cart' ),
			'last'	=> esc_html__( 'Last Menu Item' , 'woo-floating-cart' ),
		),
		'input_attrs' => array(
			'data-col' => '3'
		),
		'default'     => 'last',
		'priority'    => 10,
		'active_callback' => array(
			array(
				'setting'  => 'cart_menu_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		)
	);

	$fields[] = array(
		'id'       => 'cart_menu_alignment',
		'section'  => 'menu-item',
		'label'       => esc_html__( 'Alignment', 'woo-floating-cart' ),
		'description' => esc_html__( 'Select the alignment that looks best with your menu. This might have no effect at all.', 'woo-floating-cart' ),
		'type'        => 'radio-buttonset',
		'choices'     => array(
			'inherit'		=> esc_html__( 'Inherit' , 'woo-floating-cart' ),
			'left'			=> esc_html__( 'Left' , 'woo-floating-cart' ),
			'right'			=> esc_html__( 'Right' , 'woo-floating-cart' ),
		),
		'input_attrs' => array(
			'data-col' => '3'
		),
		'default'     => 'inherit',
		'priority'    => 10,
		'screen'      => 'desktop',
		'transport'   => 'postMessage',
		'js_vars'     => array(
			array(
				'element'  => '.xt_woofc-is-desktop .xt_woofc-menu',
				'function' => 'class',
				'prefix' => 'xt_woofc-menu-desktop-align-'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_menu_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		)
	);

	$fields[] = array(
		'id'       => 'cart_menu_alignment_tablet',
		'section'  => 'menu-item',
		'label'       => esc_html__( 'Alignment', 'woo-floating-cart' ),
		'description' => esc_html__( 'Select the alignment that looks best with your menu. This might have no effect at all.', 'woo-floating-cart' ),
		'type'        => 'radio-buttonset',
		'choices'     => array(
			'inherit'		=> esc_html__( 'Inherit' , 'woo-floating-cart' ),
			'left'			=> esc_html__( 'Left' , 'woo-floating-cart' ),
			'right'			=> esc_html__( 'Right' , 'woo-floating-cart' ),
		),
		'input_attrs' => array(
			'data-col' => '3'
		),
		'default'     => 'inherit',
		'priority'    => 10,
		'screen'      => 'tablet',
		'transport'   => 'postMessage',
		'js_vars'     => array(
			array(
				'element'  => '.xt_woofc-is-tablet .xt_woofc-menu',
				'function' => 'class',
				'prefix' => 'xt_woofc-menu-tablet-align-'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_menu_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		)
	);

	$fields[] = array(
		'id'       => 'cart_menu_alignment_mobile',
		'section'  => 'menu-item',
		'label'       => esc_html__( 'Alignment', 'woo-floating-cart' ),
		'description' => esc_html__( 'Select the alignment that looks best with your menu. This might have no effect at all.', 'woo-floating-cart' ),
		'type'        => 'radio-buttonset',
		'choices'     => array(
			'inherit'		=> esc_html__( 'Inherit' , 'woo-floating-cart' ),
			'left'			=> esc_html__( 'Left' , 'woo-floating-cart' ),
			'right'			=> esc_html__( 'Right' , 'woo-floating-cart' ),
		),
		'input_attrs' => array(
			'data-col' => '3'
		),
		'default'     => 'inherit',
		'priority'    => 10,
		'screen'      => 'mobile',
		'transport'   => 'postMessage',
		'js_vars'     => array(
			array(
				'element'  => '.xt_woofc-is-mobile .xt_woofc-menu',
				'function' => 'class',
				'prefix' => 'xt_woofc-menu-mobile-align-'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_menu_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		)
	);

	$fields[] = array(
		'id'       => 'cart_menu_display',
		'section'  => 'menu-item',
		'label'       => esc_html__( 'Display Mode', 'woo-floating-cart' ),
		'description' => esc_html__( 'What would you like to display in the menu?', 'woo-floating-cart' ),
		'type'        => 'radio-buttonset',
		'choices'     => array(
			'icon'	        => esc_html__( 'Icon Only' , 'woo-floating-cart' ),
			'items'	        => esc_html__( 'Counter' , 'woo-floating-cart' ),
			'price'	        => esc_html__( 'Price' , 'woo-floating-cart' ),
			'both'	        => esc_html__( 'Both' , 'woo-floating-cart'),
		),
		'input_attrs' => array(
			'data-col' => '2'
		),
		'default'     => 'items',
		'priority'    => 10,
        'screen'      => 'desktop',
		'transport'   => 'postMessage',
		'partial_refresh'    => [
			'cart_menu_display' => [
				'selector'        => '.xt_woofc-menu-desktop a.xt_woofc-menu-link',
				'render_callback' => function() {
					return $this->core->frontend()->menu->cart_menu_link('desktop');
				},
			]
		],
		'active_callback' => array(
			array(
				'setting'  => 'cart_menu_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		)
	);

    $fields[] = array(
        'id'       => 'cart_menu_display_tablet',
        'section'  => 'menu-item',
        'label'       => esc_html__( 'Display Mode', 'woo-floating-cart' ),
        'description' => esc_html__( 'What would you like to display in the menu?', 'woo-floating-cart' ),
        'type'        => 'radio-buttonset',
        'choices'     => array(
            'icon'	        => esc_html__( 'Icon Only' , 'woo-floating-cart' ),
            'items'	        => esc_html__( 'Counter' , 'woo-floating-cart' ),
            'price'	        => esc_html__( 'Price' , 'woo-floating-cart' ),
            'both'	        => esc_html__( 'Both' , 'woo-floating-cart'),
        ),
        'input_attrs' => array(
            'data-col' => '2'
        ),
        'default'     => 'items',
        'priority'    => 10,
        'screen'      => 'tablet',
        'transport'   => 'postMessage',
        'partial_refresh'    => [
            'cart_menu_display_tablet' => [
                'selector'        => '.xt_woofc-menu-tablet a.xt_woofc-menu-link',
                'render_callback' => function() {
                    return $this->core->frontend()->menu->cart_menu_link('tablet');
                },
            ]
        ],
        'active_callback' => array(
            array(
                'setting'  => 'cart_menu_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
        'id'       => 'cart_menu_display_mobile',
        'section'  => 'menu-item',
        'label'       => esc_html__( 'Display Mode', 'woo-floating-cart' ),
        'description' => esc_html__( 'What would you like to display in the menu?', 'woo-floating-cart' ),
        'type'        => 'radio-buttonset',
        'choices'     => array(
            'icon'	        => esc_html__( 'Icon Only' , 'woo-floating-cart' ),
            'items'	        => esc_html__( 'Counter' , 'woo-floating-cart' ),
            'price'	        => esc_html__( 'Price' , 'woo-floating-cart' ),
            'both'	        => esc_html__( 'Both' , 'woo-floating-cart'),
        ),
        'input_attrs' => array(
            'data-col' => '2'
        ),
        'default'     => 'items',
        'priority'    => 10,
        'screen'      => 'mobile',
        'transport'   => 'postMessage',
        'partial_refresh'    => [
            'cart_menu_display_mobile' => [
                'selector'        => '.xt_woofc-menu-mobile a.xt_woofc-menu-link',
                'render_callback' => function() {
                    return $this->core->frontend()->menu->cart_menu_link('mobile');
                },
            ]
        ],
        'active_callback' => array(
            array(
                'setting'  => 'cart_menu_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
        )
    );

    $fields[] = array(
        'id'       => 'cart_menu_icon_only_on_empty',
        'section'  => 'menu-item',
        'label'       => esc_html__( 'If cart is empty, show icon only.', 'woo-floating-cart' ),
        'type'        => 'radio-buttonset',
        'choices'     => array(
            '0'	        => esc_html__( 'No' , 'woo-floating-cart' ),
            '1'	        => esc_html__( 'Yes' , 'woo-floating-cart' ),
        ),
        'default'     => '0',
        'priority'    => 10,
        'transport'   => 'postMessage',
        'partial_refresh'    => [
            'cart_menu_icon_only_on_empty' => [
                'selector'        => '.xt_woofc-menu-desktop a.xt_woofc-menu-link',
                'render_callback' => function() {
                    return $this->core->frontend()->menu->cart_menu_link('desktop');
                },
            ],
            'cart_menu_icon_only_on_empty_tablet' => [
                'selector'        => '.xt_woofc-menu-tablet a.xt_woofc-menu-link',
                'render_callback' => function() {
                    return $this->core->frontend()->menu->cart_menu_link('tablet');
                },
            ],
            'cart_menu_icon_only_on_empty_mobile' => [
                'selector'        => '.xt_woofc-menu-mobile a.xt_woofc-menu-link',
                'render_callback' => function() {
                    return $this->core->frontend()->menu->cart_menu_link('mobile');
                },
            ]
        ],
        'active_callback' => array(
            array(
                'setting'  => 'cart_menu_enabled',
                'operator' => '==',
                'value'    => '1',
            )
        )
    );

	$fields[] = array(
		'id'       => 'cart_menu_counter_type',
		'section'  => 'menu-item',
		'label'       => esc_html__( 'Counter Type', 'woo-floating-cart' ),
		'description' => esc_html__( 'What would you like to display the counter?', 'woo-floating-cart' ),
		'type'        => 'radio-buttonset',
		'choices'     => array(
			'text'	        => esc_html__( 'Text' , 'woo-floating-cart' ),
			'badge'	        => esc_html__( 'Badge' , 'woo-floating-cart' ),
		),
		'input_attrs' => array(
			'data-col' => '2'
		),
		'default'     => 'text',
		'priority'    => 10,
		'screen'      => 'desktop',
		'transport'   => 'postMessage',
		'partial_refresh'    => [
			'cart_menu_counter_type' => [
				'selector'        => '.xt_woofc-menu-desktop a.xt_woofc-menu-link',
				'render_callback' => function() {
					return $this->core->frontend()->menu->cart_menu_link('desktop');
				},
			]
		],
		'active_callback' => array(
			array(
				'setting'  => 'cart_menu_enabled',
				'operator' => '==',
				'value'    => '1',
			),
			array(
				'setting'  => 'cart_menu_display',
				'operator' => 'in',
				'value'    => array('items', 'both'),
			)
		)
	);

    $fields[] = array(
        'id'       => 'cart_menu_counter_type_tablet',
        'section'  => 'menu-item',
        'label'       => esc_html__( 'Counter Type', 'woo-floating-cart' ),
        'description' => esc_html__( 'What would you like to display the counter?', 'woo-floating-cart' ),
        'type'        => 'radio-buttonset',
        'choices'     => array(
            'text'	        => esc_html__( 'Text' , 'woo-floating-cart' ),
            'badge'	        => esc_html__( 'Badge' , 'woo-floating-cart' ),
        ),
        'input_attrs' => array(
            'data-col' => '2'
        ),
        'default'     => 'text',
        'priority'    => 10,
        'screen'      => 'tablet',
        'transport'   => 'postMessage',
        'partial_refresh'    => [
            'cart_menu_counter_type_tablet' => [
                'selector'        => '.xt_woofc-menu-tablet a.xt_woofc-menu-link',
                'render_callback' => function() {
                    return $this->core->frontend()->menu->cart_menu_link('tablet');
                },
            ]
        ],
        'active_callback' => array(
            array(
                'setting'  => 'cart_menu_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
            array(
                'setting'  => 'cart_menu_display_tablet',
                'operator' => 'in',
                'value'    => array('items', 'both'),
            )
        )
    );

    $fields[] = array(
        'id'       => 'cart_menu_counter_type_mobile',
        'section'  => 'menu-item',
        'label'       => esc_html__( 'Counter Type', 'woo-floating-cart' ),
        'description' => esc_html__( 'What would you like to display the counter?', 'woo-floating-cart' ),
        'type'        => 'radio-buttonset',
        'choices'     => array(
            'text'	        => esc_html__( 'Text' , 'woo-floating-cart' ),
            'badge'	        => esc_html__( 'Badge' , 'woo-floating-cart' ),
        ),
        'input_attrs' => array(
            'data-col' => '2'
        ),
        'default'     => 'text',
        'priority'    => 10,
        'screen'      => 'mobile',
        'transport'   => 'postMessage',
        'partial_refresh'    => [
            'cart_menu_counter_type_mobile' => [
                'selector'        => '.xt_woofc-menu-mobile a.xt_woofc-menu-link',
                'render_callback' => function() {
                    return $this->core->frontend()->menu->cart_menu_link('mobile');
                },
            ]
        ],
        'active_callback' => array(
            array(
                'setting'  => 'cart_menu_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
            array(
                'setting'  => 'cart_menu_display_mobile',
                'operator' => 'in',
                'value'    => array('items', 'both'),
            )
        )
    );

    $fields[] = array(
		'id'       => 'cart_menu_counter_badge_position',
		'section'  => 'menu-item',
		'label'    => esc_html__( 'Counter Badge Position', 'woo-floating-cart' ),
		'type'     => 'radio-buttonset',
		'choices'     => array(
			'above'	    => esc_html__( 'Above' , 'woo-floating-cart' ),
			'inline'	=> esc_html__( 'Inline' , 'woo-floating-cart' ),
		),
		'input_attrs' => array(
			'data-col' => '2'
		),
		'default'     => 'above',
		'priority'    => 10,
		'screen'      => 'desktop',
		'transport'   => 'postMessage',
		'js_vars'     => array(
			array(
				'element'  => '.xt_woofc-menu-desktop .xt_woofc-menu-count',
				'function' => 'class',
				'prefix' => 'xt_woofc-counter-position-'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_menu_enabled',
				'operator' => '==',
				'value'    => '1',
			),
			array(
				'setting'  => 'cart_menu_display',
				'operator' => 'in',
				'value'    => array('items', 'both'),
			),
			array(
				'setting'  => 'cart_menu_counter_type',
				'operator' => '==',
				'value'    => 'badge',
			),
		)
	);

    $fields[] = array(
        'id'       => 'cart_menu_counter_badge_position_tablet',
        'section'  => 'menu-item',
        'label'    => esc_html__( 'Counter Badge Position', 'woo-floating-cart' ),
        'type'     => 'radio-buttonset',
        'choices'     => array(
            'above'	    => esc_html__( 'Above' , 'woo-floating-cart' ),
            'inline'	=> esc_html__( 'Inline' , 'woo-floating-cart' ),
        ),
        'input_attrs' => array(
            'data-col' => '2'
        ),
        'default'     => 'above',
        'priority'    => 10,
        'screen'      => 'tablet',
        'transport'   => 'postMessage',
        'js_vars'     => array(
            array(
                'element'  => '.xt_woofc-menu-tablet .xt_woofc-menu-count',
                'function' => 'class',
                'prefix' => 'xt_woofc-counter-position-'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_menu_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
            array(
                'setting'  => 'cart_menu_display_tablet',
                'operator' => 'in',
                'value'    => array('items', 'both'),
            ),
            array(
                'setting'  => 'cart_menu_counter_type_tablet',
                'operator' => '==',
                'value'    => 'badge',
            ),
        )
    );

    $fields[] = array(
        'id'       => 'cart_menu_counter_badge_position_mobile',
        'section'  => 'menu-item',
        'label'    => esc_html__( 'Counter Badge Position', 'woo-floating-cart' ),
        'type'     => 'radio-buttonset',
        'choices'     => array(
            'above'	    => esc_html__( 'Above' , 'woo-floating-cart' ),
            'inline'	=> esc_html__( 'Inline' , 'woo-floating-cart' ),
        ),
        'input_attrs' => array(
            'data-col' => '2'
        ),
        'default'     => 'above',
        'priority'    => 10,
        'screen'      => 'mobile',
        'transport'   => 'postMessage',
        'js_vars'     => array(
            array(
                'element'  => '.xt_woofc-menu-mobile .xt_woofc-menu-count',
                'function' => 'class',
                'prefix' => 'xt_woofc-counter-position-'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_menu_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
            array(
                'setting'  => 'cart_menu_display_mobile',
                'operator' => 'in',
                'value'    => array('items', 'both'),
            ),
            array(
                'setting'  => 'cart_menu_counter_type_mobile',
                'operator' => '==',
                'value'    => 'badge',
            ),
        )
    );

	$fields[] = array(
		'id'       => 'cart_menu_counter_badge_size',
		'section'  => 'menu-item',
		'label'    => esc_html__( 'Counter Badge Size', 'woo-floating-cart' ),
		'type'     => 'slider',
		'choices'  => array(
			'min'  => '0.8',
			'max'  => '1.1',
			'step' => '0.1',
			'suffix' => 'x',
		),
		'priority'        => 10,
		'default'         => '1',
		'screen'          => 'desktop',
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-menu-badge-scale'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_menu_enabled',
				'operator' => '==',
				'value'    => '1',
			),
			array(
				'setting'  => 'cart_menu_display',
				'operator' => 'in',
				'value'    => array('items', 'both'),
			),
			array(
				'setting'  => 'cart_menu_counter_type',
				'operator' => '==',
				'value'    => 'badge',
			),
		)
	);

    $fields[] = array(
        'id'       => 'cart_menu_counter_badge_size_tablet',
        'section'  => 'menu-item',
        'label'    => esc_html__( 'Counter Badge Size', 'woo-floating-cart' ),
        'type'     => 'slider',
        'choices'  => array(
            'min'  => '0.8',
            'max'  => '1.1',
            'step' => '0.1',
            'suffix' => 'x',
        ),
        'priority'        => 10,
        'default'         => '1',
        'screen'          => 'tablet',
        'transport'       => 'auto',
        'output'          => array(
            array(
                'element'  => ':root',
                'property' => '--xt-woofc-menu-badge-scale'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_menu_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
            array(
                'setting'  => 'cart_menu_display_tablet',
                'operator' => 'in',
                'value'    => array('items', 'both'),
            ),
            array(
                'setting'  => 'cart_menu_counter_type_tablet',
                'operator' => '==',
                'value'    => 'badge',
            ),
        )
    );

    $fields[] = array(
        'id'       => 'cart_menu_counter_badge_size_mobile',
        'section'  => 'menu-item',
        'label'    => esc_html__( 'Counter Badge Size', 'woo-floating-cart' ),
        'type'     => 'slider',
        'choices'  => array(
            'min'  => '0.8',
            'max'  => '1.1',
            'step' => '0.1',
            'suffix' => 'x',
        ),
        'priority'        => 10,
        'default'         => '1',
        'screen'          => 'mobile',
        'transport'       => 'auto',
        'output'          => array(
            array(
                'element'  => ':root',
                'property' => '--xt-woofc-menu-badge-scale'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_menu_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
            array(
                'setting'  => 'cart_menu_display_mobile',
                'operator' => 'in',
                'value'    => array('items', 'both'),
            ),
            array(
                'setting'  => 'cart_menu_counter_type_mobile',
                'operator' => '==',
                'value'    => 'badge',
            ),
        )
    );

	$fields[] = array(
		'id'              => 'cart_menu_badge_text_color',
		'section'         => 'menu-item',
		'label'           => esc_html__( 'Counter Badge Color', 'woo-floating-cart' ),
		'type'            => 'color',
		'priority'        => 10,
		'default'         => '#ffffff',
		'screen'          => 'desktop',
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-menu-badge-color'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_menu_enabled',
				'operator' => '==',
				'value'    => '1',
			),
			array(
				'setting'  => 'cart_menu_display',
				'operator' => 'in',
				'value'    => array('items', 'both'),
			),
			array(
				'setting'  => 'cart_menu_counter_type',
				'operator' => '==',
				'value'    => 'badge',
			),
		)
	);

    $fields[] = array(
        'id'              => 'cart_menu_badge_text_color_tablet',
        'section'         => 'menu-item',
        'label'           => esc_html__( 'Counter Badge Color', 'woo-floating-cart' ),
        'type'            => 'color',
        'priority'        => 10,
        'default'         => '#ffffff',
        'screen'          => 'tablet',
        'transport'       => 'auto',
        'output'          => array(
            array(
                'element'  => ':root',
                'property' => '--xt-woofc-menu-badge-color'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_menu_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
            array(
                'setting'  => 'cart_menu_display_tablet',
                'operator' => 'in',
                'value'    => array('items', 'both'),
            ),
            array(
                'setting'  => 'cart_menu_counter_type_tablet',
                'operator' => '==',
                'value'    => 'badge',
            ),
        )
    );

    $fields[] = array(
        'id'              => 'cart_menu_badge_text_color_mobile',
        'section'         => 'menu-item',
        'label'           => esc_html__( 'Counter Badge Color', 'woo-floating-cart' ),
        'type'            => 'color',
        'priority'        => 10,
        'default'         => '#ffffff',
        'screen'          => 'mobile',
        'transport'       => 'auto',
        'output'          => array(
            array(
                'element'  => ':root',
                'property' => '--xt-woofc-menu-badge-color'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_menu_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
            array(
                'setting'  => 'cart_menu_display_mobile',
                'operator' => 'in',
                'value'    => array('items', 'both'),
            ),
            array(
                'setting'  => 'cart_menu_counter_type_mobile',
                'operator' => '==',
                'value'    => 'badge',
            ),
        )
    );

	$fields[] = array(
		'id'              => 'cart_menu_badge_bg_color',
		'section'         => 'menu-item',
		'label'           => esc_html__( 'Counter Badge Bg Color', 'woo-floating-cart' ),
		'type'            => 'color',
		'priority'        => 10,
		'default'         => '#e94b35',
		'screen'          => 'desktop',
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-menu-badge-bg-color'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_menu_enabled',
				'operator' => '==',
				'value'    => '1',
			),
			array(
				'setting'  => 'cart_menu_display',
				'operator' => 'in',
				'value'    => array('items', 'both'),
			),
			array(
				'setting'  => 'cart_menu_counter_type',
				'operator' => '==',
				'value'    => 'badge',
			),
		)
	);

    $fields[] = array(
        'id'              => 'cart_menu_badge_bg_color_tablet',
        'section'         => 'menu-item',
        'label'           => esc_html__( 'Counter Badge Bg Color', 'woo-floating-cart' ),
        'type'            => 'color',
        'priority'        => 10,
        'default'         => '#e94b35',
        'screen'          => 'tablet',
        'transport'       => 'auto',
        'output'          => array(
            array(
                'element'  => ':root',
                'property' => '--xt-woofc-menu-badge-bg-color'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_menu_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
            array(
                'setting'  => 'cart_menu_display_tablet',
                'operator' => 'in',
                'value'    => array('items', 'both'),
            ),
            array(
                'setting'  => 'cart_menu_counter_type_tablet',
                'operator' => '==',
                'value'    => 'badge',
            ),
        )
    );

    $fields[] = array(
        'id'              => 'cart_menu_badge_bg_color_mobile',
        'section'         => 'menu-item',
        'label'           => esc_html__( 'Counter Badge Bg Color', 'woo-floating-cart' ),
        'type'            => 'color',
        'priority'        => 10,
        'default'         => '#e94b35',
        'screen'          => 'mobile',
        'transport'       => 'auto',
        'output'          => array(
            array(
                'element'  => ':root',
                'property' => '--xt-woofc-menu-badge-bg-color'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_menu_enabled',
                'operator' => '==',
                'value'    => '1',
            ),
            array(
                'setting'  => 'cart_menu_display_mobile',
                'operator' => 'in',
                'value'    => array('items', 'both'),
            ),
            array(
                'setting'  => 'cart_menu_counter_type_mobile',
                'operator' => '==',
                'value'    => 'badge',
            ),
        )
    );

	$fields[] = array(
		'id'       => 'cart_menu_click_action',
		'section'  => 'menu-item',
		'label'       => esc_html__( 'Click Action', 'woo-floating-cart' ),
		'type'        => 'radio-buttonset',
		'choices'     => array(
			'toggle' => esc_html__( 'Toggle Cart', 'woo-floating-cart' ),
			'cart' => esc_html__( 'Go To Cart', 'woo-floating-cart' ),
			'checkout' => esc_html__( 'Go To Checkout', 'woo-floating-cart' )
		),
		'default'     => 'toggle',
		'priority'    => 10,
		'partial_refresh'    => [
			'cart_menu_click_action' => [
                'selector'        => '.xt_woofc-menu-desktop a.xt_woofc-menu-link',
                'render_callback' => function() {
                    return $this->core->frontend()->menu->cart_menu_link('desktop');
                },
			],
            'cart_menu_click_action_tablet' => [
                'selector'        => '.xt_woofc-menu-tablet a.xt_woofc-menu-link',
                'render_callback' => function() {
                    return $this->core->frontend()->menu->cart_menu_link('tablet');
                },
            ],
            'cart_menu_click_action_mobile' => [
                'selector'        => '.xt_woofc-menu-mobile a.xt_woofc-menu-link',
                'render_callback' => function() {
                    return $this->core->frontend()->menu->cart_menu_link('mobile');
                },
            ]
		],
		'active_callback' => array(
			array(
				'setting'  => 'cart_menu_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		)
	);

	$fields[] = array(
		'id'              => 'cart_menu_icon',
		'section'         => 'menu-item',
		'label'           => esc_html__( 'Select Icon', 'woo-floating-cart' ),
		'type'            => 'xticons',
		'choices'         => array( 'types' => array( 'cart' ) ),
		'priority'        => 10,
		'default'         => 'xt_woofcicon-shop',
		'transport'       => 'postMessage',
		'js_vars'         => array(
			array(
				'element'  => '.xt_woofc-menu-icon',
				'function' => 'class'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_menu_enabled',
				'operator' => '==',
				'value'    => '1',
			)
		)
	);

	$fields[] = array(
		'id'              => 'cart_menu_icon_size',
		'section'         => 'menu-item',
		'label'           => esc_html__( 'Icon Size', 'woo-floating-cart' ),
		'type'            => 'slider',
		'choices'         => array(
			'min'  => '1',
			'max'  => '1.5',
			'step' => '0.1',
			'suffix' => 'x',
		),
		'priority'        => 10,
		'default'         => '1.2',
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-menu-icon-scale'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_menu_enabled',
				'operator' => '==',
				'value'    => '1',
			)
		)
	);

	$fields[] = array(
		'id'              => 'cart_menu_icon_color',
		'section'         => 'menu-item',
		'label'           => esc_html__( 'Icon Color', 'woo-floating-cart' ),
		'type'            => 'color',
		'priority'        => 10,
		'default'         => '',
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-menu-icon-color'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_menu_enabled',
				'operator' => '==',
				'value'    => '1',
			)
		)
	);

	$fields[] = array(
		'id'              => 'cart_menu_text_color',
		'section'         => 'menu-item',
		'label'           => esc_html__( 'Text Color', 'woo-floating-cart' ),
		'type'            => 'color',
		'priority'        => 10,
		'default'         => '',
		'transport'       => 'auto',
		'output'          => array(
			array(
				'element'  => ':root',
				'property' => '--xt-woofc-menu-text-color'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'cart_menu_enabled',
				'operator' => '==',
				'value'    => '1',
			)
		)
	);

} else {

	$fields[] = array(
		'id'      => 'cart_menu_features',
		'section' => 'menu-item',
		'type'    => 'xt-premium',
		'default' => array(
			'type'  => 'image',
			'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/menu-item.png',
			'link'  => $this->core->plugin_upgrade_url()
		)
	);
}