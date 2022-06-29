<?php
if ( $this->core->access_manager()->can_use_premium_code__premium_only() ) {

	$fields[] = array(
		'id'       => 'enable_coupon_form',
		'section'  => 'coupons',
		'label'    => esc_html__( 'Enable Coupon Form', 'woo-floating-cart' ),
		'type'     => 'radio-buttonset',
		'choices'  => array(
			'0' => esc_html__( 'No', 'woo-floating-cart' ),
			'1' => esc_html__( 'Yes', 'woo-floating-cart' )
		),
		'default'  => '0',
		'priority' => 10
	);

    $fields[] = array(
        'id'       => 'enable_coupon_list',
        'section'  => 'coupons',
        'label'    => esc_html__( 'Enable Coupon List', 'woo-floating-cart' ),
        'type'     => 'radio-buttonset',
        'choices'  => array(
            '0' => esc_html__( 'No', 'woo-floating-cart' ),
            '1' => esc_html__( 'Yes', 'woo-floating-cart' )
        ),
        'default'  => '0',
        'priority' => 10,
        'active_callback' => array(
            array(
                'setting' => 'enable_coupon_form',
                'operator' => '==',
                'value' => '1',
            ),
        )
    );

    $fields[] = array(
        'id'       => 'coupon_list_type',
        'section'  => 'coupons',
        'label'    => esc_html__( 'Coupon List Display', 'woo-floating-cart' ),
        'type'     => 'radio-buttonset',
        'choices'  => array(
            'all' => esc_html__( 'Show All', 'woo-floating-cart' ),
            'available' => esc_html__( 'Show available only', 'woo-floating-cart' ),
            'selection' => esc_html__( 'Show custom selection', 'woo-floating-cart' )
        ),
        'default'  => 'all',
        'priority' => 10,
        'transport'   => 'postMessage',
        'partial_refresh'    => [
            'coupon_list_type' => [
                'selector'        => '.xt_woofc-coupons',
                'render_callback' => function() {
                    $this->core->frontend()->render_coupon_list(true);
                },
            ]
        ],
        'active_callback' => array(
            array(
                'setting' => 'enable_coupon_form',
                'operator' => '==',
                'value' => '1',
            ),
            array(
                'setting' => 'enable_coupon_list',
                'operator' => '==',
                'value' => '1',
            ),
        )
    );

    $fields[] = array(
        'id'       => 'coupon_list_selection',
        'section'  => 'coupons',
        'label'    => esc_html__( 'Coupon List Selection', 'woo-floating-cart' ),
        'description' => esc_html('Only display these coupons. Enter coupon post IDs separated by comma. Leave empty to list all', 'woo-floating-cart'),
        'type'     => 'textarea',
        'default'  => '',
        'priority' => 10,
        'partial_refresh'    => [
            'coupon_list_selection' => [
                'selector'        => '.xt_woofc-coupons',
                'render_callback' => function() {
                    $this->core->frontend()->render_coupon_list(true);
                },
            ]
        ],
        'active_callback' => array(
            array(
                'setting' => 'enable_coupon_form',
                'operator' => '==',
                'value' => '1',
            ),
            array(
                'setting' => 'enable_coupon_list',
                'operator' => '==',
                'value' => '1',
            ),
            array(
                'setting' => 'coupon_list_type',
                'operator' => '==',
                'value' => 'selection',
            ),
        )
    );

    $fields[] = array(
        'id'       => 'coupon_list_total',
        'section'  => 'coupons',
        'label'    => esc_html__( 'Coupon List Total', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '1',
            'max'  => '50',
            'step' => '1',
        ),
        'default'   => '20',
        'partial_refresh'    => [
            'coupon_list_total' => [
                'selector'        => '.xt_woofc-coupons',
                'render_callback' => function() {
                    $this->core->frontend()->render_coupon_list(true);
                },
            ]
        ],
        'active_callback' => array(
            array(
                'setting' => 'enable_coupon_form',
                'operator' => '==',
                'value' => '1',
            ),
            array(
                'setting' => 'enable_coupon_list',
                'operator' => '==',
                'value' => '1',
            )
        )
    );

    $fields[] = array(
        'id'       => 'coupon_button_bg_color',
        'section'  => 'coupons',
        'label'    => esc_html__( 'Coupon Button Bg Color', 'woo-floating-cart' ),
        'type'      => 'color',
        'priority'  => 10,
        'default'   => '#eeeeee',
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'       => ':root',
                'property' => '--xt-woofc-coupon-button-bg-color',
            )
        ),
        'active_callback' => array(
            array(
                'setting' => 'enable_coupon_form',
                'operator' => '==',
                'value' => '1',
            ),
        )
    );

    $fields[] = array(
        'id'       => 'coupon_button_text_color',
        'section'  => 'coupons',
        'label'    => esc_html__( 'Coupon Button Text Color', 'woo-floating-cart' ),
        'type'      => 'color',
        'priority'  => 10,
        'default'   => '#263646',
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'       => ':root',
                'property' => '--xt-woofc-coupon-button-text-color',
            )
        ),
        'active_callback' => array(
            array(
                'setting' => 'enable_coupon_form',
                'operator' => '==',
                'value' => '1',
            ),
        )
    );

    $fields[] = array(
        'id'       => 'coupon_savings_text_color',
        'section'  => 'coupons',
        'label'    => esc_html__( 'Coupon Savings Text Color', 'woo-floating-cart' ),
        'type'      => 'color',
        'priority'  => 10,
        'default'   => '#008000',
        'transport' => 'auto',
        'output'    => array(
            array(
                'element'       => ':root',
                'property' => '--xt-woofc-coupon-savings-text-color',
            )
        ),
        'active_callback' => array(
            array(
                'setting' => 'enable_coupon_form',
                'operator' => '==',
                'value' => '1',
            ),
        )
    );

} else {

	$fields[] = array(
		'id'      => 'coupons_features',
		'section' => 'coupons',
		'type'    => 'xt-premium',
		'default' => array(
			'type'  => 'image',
			'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/coupons.png',
			'link'  => $this->core->plugin_upgrade_url()
		)
	);
}