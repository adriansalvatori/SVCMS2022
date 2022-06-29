<?php
$fields[] = array(
    'id'        => 'bg_color',
    'section'   => 'colors',
    'label'     => esc_html__( 'Bg Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '#ffffff',
    'transport' => 'postMessage',
    'js_vars' => array(
        array(
            'element' => 'body',
            'function' => 'dark_light_color_class',
            'prefix' => 'xt_woofc-'
        ),
        array(
            'element'  => ':root',
            'property' => '--xt-woofc-bg-color',
        )
    ),
    'output'    => array(
        array(
            'element'  => ':root',
            'property' => '--xt-woofc-bg-color',
        )
    )
);

$fields[] = array(
    'id'        => 'text_color',
    'section'   => 'colors',
    'label'     => esc_html__( 'Text Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '#666666',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'  => ':root',
            'property' => '--xt-woofc-color',
        )
    )
);

$fields[] = array(
    'id'        => 'primary_color',
    'section'   => 'colors',
    'label'     => esc_html__( 'Primary Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '#263646',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'  => ':root',
            'property' => '--xt-woofc-primary-color',
        )
    )
);

$fields[] = array(
    'id'        => 'accent_color',
    'section'   => 'colors',
    'label'     => esc_html__( 'Accent Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '#2c97de',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'  => ':root',
            'property' => '--xt-woofc-accent-color',
        )
    )
);

$fields[] = array(
    'id'        => 'link_color',
    'section'   => 'colors',
    'label'     => esc_html__( 'Link Color', 'woo-floating-cart' ),
    'type' => 'color',
    'priority'  => 10,
    'default'   => '#263646',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'  => ':root',
            'property' => '--xt-woofc-link-color',
        )
    )
);

$fields[] = array(
    'id'        => 'link_hover_color',
    'section'   => 'colors',
    'label'     => esc_html__( 'Link Hover Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '#2c97de',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'  => ':root',
            'property' => '--xt-woofc-link-hover-color',
        )
    )
);

$fields[] = array(
    'id'        => 'border_color',
    'section'   => 'colors',
    'label'     => esc_html__( 'Border Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'choices' => array(
        'alpha' => true,
    ),
    'priority'  => 10,
    'default'   => '#e6e6e6',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'  => ':root',
            'property' => '--xt-woofc-border-color'
        )
    )
);

$fields[] = array(
    'id'        => 'error_color',
    'section'   => 'colors',
    'label'     => esc_html__( 'Error Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '#dd3333',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-error-color',
        )
    )
);

$fields[] = array(
    'id'        => 'success_color',
    'section'   => 'colors',
    'label'     => esc_html__( 'Success Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'priority'  => 10,
    'default'   => '#4b9b12',
    'transport' => 'auto',
    'output'    => array(
        array(
            'element'       => ':root',
            'property' => '--xt-woofc-success-color',
        )
    )
);
