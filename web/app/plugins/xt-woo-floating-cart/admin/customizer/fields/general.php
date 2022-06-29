<?php
/* @var $customizer XT_Framework_Customizer */

$fields[] = array(
	'id'          => 'ajax_init',
	'section'     => 'general',
	'label'       => esc_html__( 'Force Ajax Initialization', 'woo-floating-cart' ),
	'description' => esc_html__( 'Enable only if encountering caching issues / conflicts with your theme', 'woo-floating-cart' ),
	'type'        => 'radio-buttonset',
	'choices'     => array(
		'0' => esc_html__( 'No', 'woo-floating-cart' ),
		'1' => esc_html__( 'Yes', 'woo-floating-cart' )
	),
	'default'     => '0',
	'priority'    => 10,
	'transport'   => 'postMessage'
);

$fields[] = array(
	'id'          => 'active_cart_body_lock_scroll',
	'section'     => 'general',
	'label'       => esc_html__( 'Lock page scroll when active', 'woo-floating-cart' ),
	'description' => esc_html__( 'When the floating cart is open, lock main site body scroll', 'woo-floating-cart' ),
	'type'        => 'radio-buttonset',
	'choices'     => array(
		'0' => esc_html__( 'No', 'woo-floating-cart' ),
		'1' => esc_html__( 'Yes', 'woo-floating-cart' )
	),
	'default'     => '1',
	'priority'    => 10
);

$fields[] = array(
    'id'        => 'loading_spinner',
    'section'   => 'general',
    'label'     => esc_html__( 'Cart Loading Spinner', 'woo-floating-cart' ),
    'type'      => 'radio-buttonset',
    'input_attrs' => array(
        'data-col' => '2'
    ),
    'priority'  => 10,
    'choices'   => array(
        '0'                 => esc_html__( 'No Spinner', 'woo-floating-cart' ),
        '1-rotating-plane'  => esc_html__( 'Rotating Plane', 'woo-floating-cart' ),
        '2-double-bounce'   => esc_html__( 'Double Bounce', 'woo-floating-cart' ),
        '3-wave'            => esc_html__( 'Wave', 'woo-floating-cart' ),
        '4-wandering-cubes' => esc_html__( 'Wandering Cubes', 'woo-floating-cart' ),
        '5-pulse'           => esc_html__( 'Pulse', 'woo-floating-cart' ),
        '6-chasing-dots'    => esc_html__( 'Chasing Dots', 'woo-floating-cart' ),
        '7-three-bounce'    => esc_html__( 'Three Bounce', 'woo-floating-cart' ),
        '8-circle'          => esc_html__( 'Circle', 'woo-floating-cart' ),
        '9-cube-grid'       => esc_html__( 'Cube Grid', 'woo-floating-cart' ),
        '10-fading-circle'  => esc_html__( 'Fading Circle', 'woo-floating-cart' ),
        '11-folding-cube'   => esc_html__( 'Folding Cube', 'woo-floating-cart' ),
        'loading-text'      => esc_html__( 'Boring Loading Text', 'woo-floating-cart' )
    ),
    'transport' => 'postMessage',
    'partial_refresh'    => [
        'loading_spinner' => [
            'selector'        => '.xt_woofc-spinner-wrap',
            'render_callback' => function() {
                xt_woofc_spinner_html(false, false);
            },
        ]
    ],
    'default'   => '7-three-bounce'
);

$fields[] = array(
    'id'        => 'loading_spinner_color',
    'section'   => 'general',
    'label'     => esc_html__( 'Cart Loading Spinner Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-spinner-color',
        )
    )
);

$fields[] = array(
    'id'        => 'loading_overlay_color',
    'section'   => 'general',
    'label'     => esc_html__( 'Cart Loading Overlay Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'choices'   => array(
        'alpha' => true
    ),
    'priority'  => 10,
    'default'   => 'rgba(255,255,255,0.5)',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-spinner-overlay-color',
        )
    )
);

$fields[] = array(
    'id'        => 'loading_timeout',
    'section'   => 'general',
    'label'     => esc_html__( 'Cart Loading Extended Duration', 'woo-floating-cart' ),
    'type'      => 'slider',
    'choices'   => array(
        'min'  => '0',
        'max'  => '2000',
        'step' => '10',
        'suffix' => 'ms',
    ),
    'priority'  => 10,
    'default'   => 300,
    'transport' => 'postMessage',
    'js_vars'   => array(
        array(
            'element'  => '.xt_woofc',
            'function' => 'html',
            'attr'     => 'data-loadingtimeout'
        )
    )
);

if ( $this->core->access_manager()->can_use_premium_code__premium_only() ) {

    $fields[] = array(
		'id'       => 'flytocart_animation',
		'section'  => 'general',
		'label'    => esc_html__( 'Enable Fly To Cart animation', 'woo-floating-cart' ),
        'description' => esc_html__( 'Cart Trigger needs to be enabled for this to work', 'woo-floating-cart' ),
        'type'        => 'radio-buttonset',
		'choices'     => array(
			'0' => esc_html__( 'No', 'woo-floating-cart' ),
			'1' => esc_html__( 'Yes', 'woo-floating-cart' )
		),
		'default'  => '1',
		'priority' => 10
	);

	$fields[] = array(
		'id'              => 'flytocart_animation_duration',
		'section'         => 'general',
		'label'           => esc_html__( 'Fly To Cart animation Duration', 'woo-floating-cart' ),
		'type'            => 'slider',
		'choices'         => array(
			'min'  => '300',
			'max'  => '2000',
			'step' => '10',
            'suffix' => 'ms',
		),
		'priority'        => 10,
		'default'         => 650,
		'transport'       => 'postMessage',
		'js_vars'         => array(
			array(
				'element'  => '.xt_woofc',
				'function' => 'html',
				'attr'     => 'data-flyduration'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'flytocart_animation',
				'operator' => '==',
				'value'    => '1',
			),
		)
	);

    $fields[] = array(
        'id'        => 'open_cart_on_product_add',
        'section'   => 'general',
        'label'     => esc_html__( 'Open cart after adding products', 'woo-floating-cart' ),
        'type'      => 'radio-buttonset',
        'choices'   => array(
            '0' => esc_html__( 'No', 'woo-floating-cart' ),
            '1' => esc_html__( 'Yes', 'woo-floating-cart' )
        ),
        'default'   => '0',
        'priority'  => 10,
        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'  => '.xt_woofc',
                'function' => 'html',
                'attr'     => 'data-opencart-onadd'
            )
        )
    );

	$fields[] = array(
		'id'        => 'shake_trigger',
		'section'   => 'general',
		'label'     => esc_html__( 'Shake cart after adding products', 'woo-floating-cart' ),
		'type'      => 'radio-buttonset',
        'input_attrs' => array(
            'data-col' => '2'
        ),
		'priority'  => 10,
		'choices'   => array(
			'horizontal' => esc_html__( 'Horizontal Shake', 'woo-floating-cart' ),
			'vertical'   => esc_html__( 'Vertical Shake', 'woo-floating-cart' ),
            ''           => esc_html__( 'No Shake', 'woo-floating-cart' ),
        ),
		'default'   => 'vertical',
		'transport' => 'postMessage',
		'js_vars'   => array(
			array(
				'element'  => '.xt_woofc',
				'function' => 'html',
				'attr'     => 'data-shaketrigger'
			)
		),
		'active_callback' => array(
			array(
				'setting'  => 'open_cart_on_product_add',
				'operator' => '!=',
				'value'    => '1',
			),
		)
	);

} else {

	$fields[] = array(
		'id'      => 'general_features',
		'section' => 'general',
		'type'    => 'xt-premium',
		'default' => array(
			'type'  => 'image',
			'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/general.png',
			'link'  => $this->core->plugin_upgrade_url()
		)
	);
}