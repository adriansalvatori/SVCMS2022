<?php
if ( $this->core->access_manager()->can_use_premium_code__premium_only() ) {

    $fields[] = array(
        'id' => 'checkout_form',
        'section'   => 'checkout',
        'label' => esc_html__( 'Checkout Form', 'woo-floating-cart' ),
        'type' => 'custom'
    );

    $fields[] = array(
        'id'          => 'cart_checkout_form',
        'section'     => 'checkout',
        'label'       => esc_html__( 'Enable Checkout Form', 'woo-floating-cart' ),
        'description' => sprintf( esc_html__( 'Once the checkout button is clicked, the checkout form will load within the cart and the %1$sCheckout%2$s button label will become %1$sPlace Order%2$s.', 'woo-floating-cart' ), '<strong>', '</strong>' ),
        'type'        => 'radio-buttonset',
        'choices'     => array(
            '1' => esc_attr__( 'Enable', 'woo-floating-cart' ),
            '0' => esc_attr__( 'Disable', 'woo-floating-cart' )
        ),
        'default'     => '0'
    );

    $fields[] = array(
        'id'          => 'cart_checkout_form_font_size',
        'section'     => 'checkout',
        'label'       => esc_html__( 'Checkout Form Overall Font Size', 'woo-floating-cart' ),
        'type'      => 'slider',
        'choices'   => array(
            'min'  => '60',
            'max'  => '150',
            'step' => '1',
            'suffix' => '%'
        ),
        'default'   => '90',
        'transport' => 'auto',
        'output' => array(
            array(
                'element' => ':root',
                'property' => '--xt-woofc-checkout-form-font-size',
                'value_pattern' => '$%'
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'cart_checkout_form',
                'operator' => '==',
                'value'    => '1',
            ),
        ),
    );

    $fields[] = array(
        'id'          => 'cart_checkout_complete_action',
        'section'     => 'checkout',
        'label'       => esc_html__( 'Checkout Complete Action', 'woo-floating-cart' ),
        'type'        => 'radio-buttonset',
        'choices'     => array(
            'redirect' => esc_attr__( 'Redirect to order', 'woo-floating-cart' ),
            'button' => esc_attr__( 'Show View Order Button', 'woo-floating-cart' )
        ),
        'default'   => 'redirect',
        'active_callback' => array(
            array(
                'setting'  => 'cart_checkout_form',
                'operator' => '==',
                'value'    => '1',
            ),
        ),
    );

} else {

	$fields[] = array(
		'id'      => 'checkout_features',
		'section' => 'checkout',
		'type'    => 'xt-premium',
		'default' => array(
			'type'  => 'image',
			'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/checkout.png',
			'link'  => $this->core->plugin_upgrade_url()
		)
	);
}