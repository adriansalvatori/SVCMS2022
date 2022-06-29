<?php
/* @var $customizer XT_Framework_Customizer */

if ( $this->core->access_manager()->can_use_premium_code__premium_only() ) {

    $default_font = 'Source Sans Pro';

	$fields[] = array(
		'id'        => 'typo_counter',
		'section'   => 'typography',
		'label'     => esc_attr__( 'Product Counter Typography', 'woo-floating-cart' ),
		'type'      => 'typography',
		'default'   => array(
			'font-family' => $default_font,
			'variant'     => '700',
			'subsets'     => array( 'latin-ext' )
		),
		'priority'  => 10,
		'transport' => 'auto',
		'output'    => array(
			array(
				'element' => '.xt_woofc-inner .xt_woofc-count',
			),
		)
	);

	$fields[] = array(
		'id'        => 'typo_header_title',
		'section'   => 'typography',
		'label'     => esc_attr__( 'Header Title Typography', 'woo-floating-cart' ),
		'type'      => 'typography',
		'default'   => array(
			'font-family'    => $default_font,
			'variant'        => '700',
			'font-size'      => '16px',
			'letter-spacing' => '1.4px',
			'text-transform' => 'uppercase',
			'subsets'        => array( 'latin-ext' ),
		),
		'priority'  => 10,
		'transport' => 'auto',
		'output'    => array(
			array(
				'element' => '.xt_woofc-inner .xt_woofc-title',
			),
		)
	);

	$fields[] = array(
		'id'        => 'typo_header_msg',
		'section'   => 'typography',
		'label'     => esc_attr__( 'Header Info Notice Typography', 'woo-floating-cart' ),
		'type'      => 'typography',
		'default'   => array(
			'font-family'    => $default_font,
			'variant'        => '700',
			'font-size'      => '10px',
			'letter-spacing' => '1.4',
			'subsets'        => array( 'latin-ext' ),
			'text-transform' => 'uppercase'
		),
		'priority'  => 10,
		'transport' => 'auto',
		'output'    => array(
			array(
				'element' => array(
					'.xt_woofc-inner .xt_woofc-notice',
					'.xt_woofc-inner .xt_woofc-coupon'
				),
			),
		)
	);

	$fields[] = array(
		'id'        => 'typo_header_error_msg',
		'section'   => 'typography',
		'label'     => esc_attr__( 'Header Error Notice Typography', 'woo-floating-cart' ),
		'type'      => 'typography',
		'default'   => array(
			'font-family'    => $default_font,
			'variant'        => '700',
			'font-size'      => '10px',
			'letter-spacing' => '1.4',
			'subsets'        => array( 'latin-ext' ),
			'text-transform' => 'uppercase'
		),
		'priority'  => 10,
		'transport' => 'auto',
		'output'    => array(
			array(
				'element' => '.xt_woofc-inner .xt_woofc-notice-error',
			),
		)
	);

	$fields[] = array(
		'id'        => 'typo_header_message',
		'section'   => 'typography',
		'label'     => esc_attr__( 'Header Message Typography', 'woo-floating-cart' ),
		'type'      => 'typography',
		'default'   => array(
			'font-family'    => $default_font,
			'variant'        => '600',
			'font-size'      => '16px',
			'letter-spacing' => '1',
			'text-align'     => 'center',
			'subsets'        => array( 'latin-ext' ),
			'text-transform' => 'none'
		),
		'priority'  => 10,
		'transport' => 'auto',
		'output'    => array(
			array(
				'element' => '.xt_woofc-inner .xt_woofc-header-message',
			),
		)
	);

	$fields[] = array(
		'id'        => 'typo_header_no_products_msg',
		'section'   => 'typography',
		'label'     => esc_attr__( 'No Products Message Typography', 'woo-floating-cart' ),
		'type'      => 'typography',
		'default'   => array(
			'font-family'    => $default_font,
			'variant'        => 'regular',
			'font-size'      => '12px',
			'letter-spacing' => '1.4',
			'text-align'     => 'left',
			'subsets'        => array( 'latin-ext' ),
			'text-transform' => 'none'
		),
		'priority'  => 10,
		'transport' => 'auto',
		'output'    => array(
			array(
				'element' => '.xt_woofc-empty .xt_woofc-inner .xt_woofc-no-product',
			),
		)
	);

	$fields[] = array(
		'id'        => 'typo_product_title',
		'section'   => 'typography',
		'label'     => esc_attr__( 'Product Title / Price Typography', 'woo-floating-cart' ),
		'type'      => 'typography',
		'default'   => array(
			'font-family'    => $default_font,
			'variant'        => '700',
			'font-size'      => '16px',
			'letter-spacing' => '0',
			'subsets'        => array( 'latin-ext' ),
			'text-transform' => 'capitalize'
		),
		'priority'  => 10,
		'transport' => 'auto',
		'output'    => array(
			array(
				'element' => array(
					'.xt_woofc-inner .xt_woofc-product-title',
					'.xt_woofc-inner .xt_woofc-price',
					'.xt_woofc-inner .xt_woofc-price del',
					'.xt_woofc-inner .xt_woofc-price ins'
				),
			),
            array(
                'element' => '.xt_woofc-inner .xt_woofc-subscription .xt_woofc-price',
                'property'      => 'font-size',
                'choice'        => 'font-size',
                'value_pattern' => 'calc($ * 0.8)'
            )
		)
	);

	$fields[] = array(
		'id'        => 'typo_product_attributes_labels',
		'section'   => 'typography',
		'label'     => esc_attr__( 'Product Attributes Label Typography', 'woo-floating-cart' ),
		'type'      => 'typography',
		'default'   => array(
			'font-family'    => $default_font,
			'variant'        => '600',
			'font-size'      => '10px',
			'letter-spacing' => '0',
			'subsets'        => array( 'latin-ext' ),
			'text-transform' => 'capitalize'
		),
		'priority'  => 10,
		'transport' => 'auto',
		'output'    => array(
			array(
				'element' => array(
					'.xt_woofc-inner .xt_woofc-product-variations dl dt',
					'.xt_woofc-inner .xt_woofc-product-attributes dl dt',
					'.xt_woofc-inner .xt_woofc-sku dl dt'
				),
			),
		)
	);

	$fields[] = array(
		'id'        => 'typo_product_attributes_values',
		'section'   => 'typography',
		'label'     => esc_attr__( 'Product Attributes Values Typography', 'woo-floating-cart' ),
		'type'      => 'typography',
		'default'   => array(
			'font-family'    => $default_font,
			'variant'        => 'regular',
			'font-size'      => '10px',
			'letter-spacing' => '0',
			'subsets'        => array( 'latin-ext' ),
			'text-transform' => 'capitalize'
		),
		'priority'  => 10,
		'transport' => 'auto',
		'output'    => array(
			array(
				'element' => array(
					'.xt_woofc-inner .xt_woofc-product-variations dl dd',
					'.xt_woofc-inner .xt_woofc-product-attributes dl dd',
					'.xt_woofc-inner .xt_woofc-sku dl dd'
				),
			),
		)
	);

	$fields[] = array(
		'id'        => 'typo_product_action_link',
		'section'   => 'typography',
		'label'     => esc_attr__( 'Product Remove Link Typography', 'woo-floating-cart' ),
		'type'      => 'typography',
		'default'   => array(
			'font-family'    => $default_font,
			'variant'        => 'regular',
			'font-size'      => '14px',
			'letter-spacing' => '0',
			'subsets'        => array( 'latin-ext' ),
			'text-transform' => 'capitalize'
		),
		'priority'  => 10,
		'transport' => 'auto',
		'output'    => array(
			array(
				'element' => '.xt_woofc-inner .xt_woofc-actions',
			),
			array(
				'element'       => '.xt_woofc-inner .xt_woofc-actions',
				'media_query'   => $customizer->media_query('mobile', 'max'),
				'property'      => 'font-size',
				'choice'        => 'font-size',
				'value_pattern' => 'calc($ * 0.85)',
			),
		)
	);


	$fields[] = array(
		'id'        => 'typo_product_quantity_input',
		'section'   => 'typography',
		'label'     => esc_attr__( 'Product Quantity Input Typography', 'woo-floating-cart' ),
		'type'      => 'typography',
		'default'   => array(
			'font-family'    => $default_font,
			'variant'        => 'regular',
			'font-size'      => '14px',
			'letter-spacing' => '1.2px',
			'subsets'        => array( 'latin-ext' )
		),
		'priority'  => 10,
		'transport' => 'auto',
		'output'    => array(
			array(
				'element' => '.xt_woofc-inner .xt_woofc-quantity input',
			),
			array(
				'element'       => '.xt_woofc-inner .xt_woofc-quantity input',
				'media_query'   => $customizer->media_query('mobile', 'max'),
				'property'      => 'font-size',
				'choice'        => 'font-size',
				'value_pattern' => 'calc($ * 0.85)',
			),
		)
	);

	$fields[] = array(
		'id'        => 'typo_footer_checkout_button',
		'section'   => 'typography',
		'label'     => esc_attr__( 'Footer Checkout Button Typography', 'woo-floating-cart' ),
		'type'      => 'typography',
		'default'   => array(
			'font-family'    => $default_font,
			'variant'        => '600italic',
			'font-size'      => '24px',
			'letter-spacing' => '0',
			'subsets'        => array( 'latin-ext' ),
			'text-transform' => 'none'
		),
		'priority'  => 10,
		'transport' => 'auto',
		'output'    => array(
			array(
				'element' => '.xt_woofc-inner a.xt_woofc-checkout',
			),
			array(
				'element'       => '.xt_woofc-inner a.xt_woofc-checkout',
				'media_query'   => $customizer->media_query('mobile', 'max'),
				'property'      => 'font-size',
				'choice'        => 'font-size',
				'value_pattern' => 'calc($ * 0.75)',
			),
		)
	);

} else {

	$fields[] = array(
		'id'      => 'typography_features',
		'section' => 'typography',
		'type'    => 'xt-premium',
		'default' => array(
			'type'  => 'image',
			'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/typography.png',
			'link'  => $this->core->plugin_upgrade_url()
		)
	);
}