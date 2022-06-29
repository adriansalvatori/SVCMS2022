<?php
if ( $this->core->access_manager()->can_use_premium_code__premium_only() ) {

    $fields[] = array(
        'id' => 'cart_totals',
        'section'   => 'totals',
        'label'     => esc_html__( 'Cart Totals', 'woo-floating-cart' ),
        'type' => 'custom'
    );

	$fields[] = array(
		'id'              => 'enable_totals',
		'section'         => 'totals',
		'label'           => esc_html__( 'Enable Totals', 'woo-floating-cart' ),
		'type'            => 'radio-buttonset',
		'choices'         => array(
			'0' => esc_html__( 'No', 'woo-floating-cart' ),
			'1' => esc_html__( 'Yes', 'woo-floating-cart' )
		),
		'default'         => '0'
	);

    $fields[] = array(
        'id'        => 'cart_totals_font_size',
        'section'   => 'totals',
        'label'     => esc_html__( 'Totals Font Size', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '9',
            'max'  => '18',
            'step' => '1',
            'suffix' => 'px'
        ),
        'default'   => '13',
        'transport' => 'auto',
        'output' => array(
            array(
                'element' => ':root',
                'property' => '--xt-woofc-totals-font-size',
                'value_pattern' => '$px'
            )
        ),
        'active_callback' => array(
            array(
                'setting' => 'enable_totals',
                'operator' => '==',
                'value' => '1',
            ),
        )
    );

    $fields[] = array(
        'id'              => 'shipping_methods_display',
        'section'         => 'totals',
        'label'           => esc_html__( 'Shipping Methods Display', 'woo-floating-cart' ),
        'type'            => 'radio-buttonset',
        'choices'         => array(
            'radio' => esc_html__( 'Radios', 'woo-floating-cart' ),
            'dropdown' => esc_html__( 'Dropdown', 'woo-floating-cart' )
        ),
        'default'         => 'dropdown'
    );

	$fields[] = array(
		'id'              => 'enable_total_savings',
		'section'         => 'totals',
		'label'           => esc_html__( 'Enable Total Savings', 'woo-floating-cart' ),
		'description'     => sprintf( esc_html__( 'Savings will only appear if there is any discounted items or applied coupons within the cart', 'woo-floating-cart' ), '<strong>', '</strong>' ),
		'type'            => 'radio-buttonset',
		'choices'         => array(
			'0' => esc_html__( 'No', 'woo-floating-cart' ),
			'1' => esc_html__( 'Yes', 'woo-floating-cart' )
		),
        'default'         => '0'
	);

	$fields[] = array(
		'id'      => 'total_savings_color',
		'section' => 'totals',
		'label' => esc_html__( 'Total Savings Text Color', 'woo-floating-cart' ),
		'type' => 'color',
		'priority' => 10,
		'default' => '',
		'transport' => 'auto',
		'output' => array(
			array(
				'element' => ':root',
				'property' => '--xt-woofc-totals-savings-color',
			)
		),
		'active_callback' => array(
			array(
				'setting' => 'enable_total_savings',
				'operator' => '==',
				'value' => '1',
			),
		)
	);

} else {

	$fields[] = array(
		'id'      => 'totals_features',
		'section' => 'totals',
		'type'    => 'xt-premium',
		'default' => array(
			'type'  => 'image',
			'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/totals.png',
			'link'  => $this->core->plugin_upgrade_url()
		)
	);
}