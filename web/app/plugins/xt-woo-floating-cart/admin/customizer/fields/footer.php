<?php

$fields[] = array(
	'id'        => 'cart_checkout_button_bg_color',
	'section'   => 'footer',
	'label'     => esc_html__( 'Cart Checkout Button Bg Color', 'woo-floating-cart' ),
	'type' => 'color',
	'choices' => array(
	    'alpha' => true,
	),
	'default'   => '',
	'transport' => 'auto',
	'output'    => array(
		array(
			'element' => ':root',
			'property' => '--xt-woofc-checkout-btn-bg-color'
		)
	)
);

$fields[] = array(
	'id'        => 'cart_checkout_button_bg_hover_color',
	'section'   => 'footer',
	'label'     => esc_html__( 'Cart Checkout Button Bg Hover Color', 'woo-floating-cart' ),
	'type' => 'color',
	'choices' => array(
	    'alpha' => true,
	),
	'default'   => '',
	'transport' => 'auto',
	'output'    => array(
		array(
			'element' => ':root',
			'property' => '--xt-woofc-checkout-btn-bg-hover-color'
		)
	)
);

$fields[] = array(
	'id'        => 'cart_checkout_button_text_color',
	'section'   => 'footer',
	'label'     => esc_html__( 'Cart Checkout Button Text Color', 'woo-floating-cart' ),
	'type'      => 'color',
	'default'   => '',
	'transport' => 'auto',
	'output'    => array(
		array(
			'element' => ':root',
			'property' => '--xt-woofc-checkout-btn-color'
		)
	)
);

$fields[] = array(
	'id'        => 'cart_checkout_button_text_hover_color',
	'section'   => 'footer',
	'label'     => esc_html__( 'Cart Checkout Button Text Hover Color', 'woo-floating-cart' ),
	'type'      => 'color',
	'default'   => '',
	'transport' => 'auto',
	'output'    => array(
		array(
			'element' => ':root',
			'property' => '--xt-woofc-checkout-btn-hover-color'
		)
	)
);


if ( $this->core->access_manager()->can_use_premium_code__premium_only() ) {

	$fields[] = array(
		'id'        => 'checkout_button_height_desktop',
		'section'   => 'footer',
		'label'     => esc_html__( 'Checkout Button Height Size', 'woo-floating-cart' ),
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
				'element' => '.xt_woofc-animation-slide',
				'property' => '--xt-woofc-checkout-btn-height',
				'value_pattern' => '$px'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'animation_type',
				'operator' => '==',
				'value'    => 'slide',
			),
		),
		'screen' => 'desktop'
	);

	$fields[] = array(
		'id'        => 'checkout_button_height_tablet',
		'section'   => 'footer',
		'label'     => esc_html__( 'Checkout Button Height Size', 'woo-floating-cart' ),
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
				'element' => '.xt_woofc-animation-slide',
				'property' => '--xt-woofc-checkout-btn-height',
				'value_pattern' => '$px'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'animation_type',
				'operator' => '==',
				'value'    => 'slide',
			),
		),
		'screen' => 'tablet'
	);

	$fields[] = array(
		'id'        => 'checkout_button_height_mobile',
		'section'   => 'footer',
		'label'     => esc_html__( 'Checkout Button Height Size', 'woo-floating-cart' ),
		'type'      => 'slider',
		'choices'   => array(
			'min'  => '40',
			'max'  => '100',
			'step' => '1',
		),
		'default'   => '72',
		'priority'  => 10,
		'transport' => 'auto',
		'output'    => array(
			array(
				'element' => '.xt_woofc-animation-slide',
				'property' => '--xt-woofc-checkout-btn-height',
				'value_pattern' => '$px'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'animation_type',
				'operator' => '==',
				'value'    => 'slide',
			),
		),
		'screen' => 'mobile'
	);

	$fields[] = array(
		'id'              => 'cart_checkout_link',
		'section'         => 'footer',
		'label'           => esc_html__( 'Cart Checkout Action', 'woo-floating-cart' ),
		'type'            => 'radio-buttonset',
		'choices'         => array(
			'checkout' => esc_attr__( 'Go to Checkout Page', 'woo-floating-cart' ),
			'cart'     => esc_attr__( 'Go to Cart Page', 'woo-floating-cart' )
		),
		'default'         => 'checkout',
		'active_callback' => array(
			array(
				'setting'  => 'cart_checkout_form',
				'operator' => '==',
				'value'    => '0',
			),
		),
	);

} else {

	$fields[] = array(
		'id'      => 'footer_features',
		'section' => 'footer',
		'type'    => 'xt-premium',
		'default' => array(
			'type'  => 'image',
			'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/footer.png',
			'link'  => $this->core->plugin_upgrade_url()
		)
	);
}
