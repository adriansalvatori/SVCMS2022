<?php
$fields[] = array(
    'id'          => 'override_woo_notices',
    'section'     => 'notices',
    'label'       => esc_html__( 'Override WooCommerce Notice Colors', 'woo-floating-cart' ),
    'description' => esc_html__( 'This will override WooCommerce Notices that appears within the floating cart only', 'woo-floating-cart' ).'<br><br>',
    'type'        => 'radio-buttonset',
    'choices'     => array(
        '1' => esc_attr__( 'Enable', 'woo-floating-cart' ),
        '0' => esc_attr__( 'Disable', 'woo-floating-cart' )
    ),
    'default'     => '1'
);

$fields[] = array(
    'id'        => 'woo_notice_font_size',
    'section'   => 'notices',
    'label'   => esc_html__( 'Woo Notice Font Size', 'woo-floating-cart' ),
    'type'      => 'slider',
    'choices'   => array(
        'min'  => '9',
        'max'  => '20',
        'step' => '1',
        'suffix' => 'px'
    ),
    'default'   => '13',
    'transport' => 'auto',
    'output' => array(
        array(
            'element' => ':root',
            'property' => '--xt-woofc-notice-font-size',
            'value_pattern' => '$px'
        )
    ),
    'active_callback' => array(
        array(
            'setting' => 'override_woo_notices',
            'operator' => '==',
            'value' => '1',
        ),
    )
);


$fields[] = array(
    'id' => 'woo_error_notice',
    'section' => 'notices',
    'label'   => esc_html__( 'Woo Error Notice', 'woo-floating-cart' ),
    'type' => 'custom',
    'active_callback' => array(
        array(
            'setting'  => 'override_woo_notices',
            'operator' => '==',
            'value'    => '1',
        ),
    ),
);

$fields[] = array(
    'id'        => 'woo_error_color',
    'section'   => 'notices',
    'label'     => esc_html__( 'Error Notice Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-notice-error-color',
        )
    ),
    'active_callback' => array(
        array(
            'setting'  => 'override_woo_notices',
            'operator' => '==',
            'value'    => '1',
        )
    ),
);

$fields[] = array(
    'id'        => 'woo_error_bg_color',
    'section'   => 'notices',
    'label'     => esc_html__( 'Error Notice Bg Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-notice-error-bg-color',
        )
    ),
    'active_callback' => array(
        array(
            'setting'  => 'override_woo_notices',
            'operator' => '==',
            'value'    => '1',
        )
    ),
);

$fields[] = array(
    'id'        => 'woo_error_icon_color',
    'section'   => 'notices',
    'label'     => esc_html__( 'Error Notice Icon Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-notice-error-icon-color',
        )
    ),
    'active_callback' => array(
        array(
            'setting'  => 'override_woo_notices',
            'operator' => '==',
            'value'    => '1',
        )
    ),
);

$fields[] = array(
    'id' => 'woo_success_notice',
    'section' => 'notices',
    'label'   => esc_html__( 'Woo Success Notice', 'woo-floating-cart' ),
    'type' => 'custom',
    'active_callback' => array(
        array(
            'setting'  => 'override_woo_notices',
            'operator' => '==',
            'value'    => '1',
        ),
    ),
);

$fields[] = array(
    'id' => 'woo_success_notice_hide',
    'section' => 'notices',
    'label'   => esc_html__( 'Hide Woo Success Notices', 'woo-floating-cart' ),
    'type'        => 'radio-buttonset',
    'choices'     => array(
        '0' => esc_attr__( 'No', 'woo-floating-cart' ),
        '1' => esc_attr__( 'Yes', 'woo-floating-cart' )
    ),
    'default'     => '0',
    'active_callback' => array(
        array(
            'setting'  => 'override_woo_notices',
            'operator' => '==',
            'value'    => '1',
        ),
    ),
);

$fields[] = array(
    'id'        => 'woo_success_color',
    'section'   => 'notices',
    'label'     => esc_html__( 'Success Notice Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-notice-success-color',
        )
    ),
    'active_callback' => array(
        array(
            'setting'  => 'override_woo_notices',
            'operator' => '==',
            'value'    => '1',
        ),
        array(
            'setting'  => 'woo_success_notice_hide',
            'operator' => '==',
            'value'    => '0',
        ),
    ),
);

$fields[] = array(
    'id'        => 'woo_success_bg_color',
    'section'   => 'notices',
    'label'     => esc_html__( 'Success Notice Bg Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-notice-success-bg-color',
        )
    ),
    'active_callback' => array(
        array(
            'setting'  => 'override_woo_notices',
            'operator' => '==',
            'value'    => '1',
        ),
        array(
            'setting'  => 'woo_success_notice_hide',
            'operator' => '==',
            'value'    => '0',
        ),
    ),
);

$fields[] = array(
    'id'        => 'woo_success_icon_color',
    'section'   => 'notices',
    'label'     => esc_html__( 'Success Notice Icon Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-notice-success-icon-color',
        )
    ),
    'active_callback' => array(
        array(
            'setting'  => 'override_woo_notices',
            'operator' => '==',
            'value'    => '1',
        ),
        array(
            'setting'  => 'woo_success_notice_hide',
            'operator' => '==',
            'value'    => '0',
        ),
    ),
);

$fields[] = array(
    'id' => 'woo_info_notice',
    'section' => 'notices',
    'label'   => esc_html__( 'Woo Info Notice', 'woo-floating-cart' ),
    'type' => 'custom',
    'active_callback' => array(
        array(
            'setting'  => 'override_woo_notices',
            'operator' => '==',
            'value'    => '1',
        ),
    ),
);

$fields[] = array(
    'id' => 'woo_info_notice_hide',
    'section' => 'notices',
    'label'   => esc_html__( 'Hide Woo Info Notices', 'woo-floating-cart' ),
    'type'        => 'radio-buttonset',
    'choices'     => array(
        '0' => esc_attr__( 'No', 'woo-floating-cart' ),
        '1' => esc_attr__( 'Yes', 'woo-floating-cart' )
    ),
    'default'     => '0',
    'active_callback' => array(
        array(
            'setting'  => 'override_woo_notices',
            'operator' => '==',
            'value'    => '1',
        )
    ),
);

$fields[] = array(
    'id'        => 'woo_info_color',
    'section'   => 'notices',
    'label'     => esc_html__( 'Info Notice Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-notice-info-color',
        )
    ),
    'active_callback' => array(
        array(
            'setting'  => 'override_woo_notices',
            'operator' => '==',
            'value'    => '1',
        ),
        array(
            'setting'  => 'woo_info_notice_hide',
            'operator' => '==',
            'value'    => '0',
        ),
    ),
);

$fields[] = array(
    'id'        => 'woo_info_bg_color',
    'section'   => 'notices',
    'label'     => esc_html__( 'Info Notice Bg Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-notice-info-bg-color',
        )
    ),
    'active_callback' => array(
        array(
            'setting'  => 'override_woo_notices',
            'operator' => '==',
            'value'    => '1',
        ),
        array(
            'setting'  => 'woo_info_notice_hide',
            'operator' => '==',
            'value'    => '0',
        ),
    ),
);

$fields[] = array(
    'id'        => 'woo_info_icon_color',
    'section'   => 'notices',
    'label'     => esc_html__( 'Info Notice Icon Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-notice-info-icon-color',
        )
    ),
    'active_callback' => array(
        array(
            'setting'  => 'override_woo_notices',
            'operator' => '==',
            'value'    => '1',
        ),
        array(
            'setting'  => 'woo_info_notice_hide',
            'operator' => '==',
            'value'    => '0',
        ),
    ),
);