<?php
if ( $this->core->access_manager()->can_use_premium_code__premium_only() ) {

    $fields[] = array(
        'id' => 'express_payment_buttons',
        'section'   => 'extras',
        'label'     => esc_html__( 'Express Payment buttons', 'woo-floating-cart' ),
        'type' => 'custom'
    );

    $fields[] = array(
        'id'              => 'paypal_express_checkout',
        'section'         => 'extras',
        'label'           => esc_html__( 'Paypal Express Checkout', 'woo-floating-cart' ),
        'description'     => '<p><a href="https://wordpress.org/plugins/woocommerce-paypal-payments/" target="_blank">'.esc_html__( 'Install and configure paypal plugin.', 'woo-floating-cart').'</a><br><br>'.esc_html__('Make sure to enable the Mini Cart button.', 'woo-floating-cart').' <a target="_blank" href="https://d.pr/i/PejMWp">https://d.pr/i/PejMWp</a></p>',
        'type'            => 'radio-buttonset',
        'choices'         => array(
            '0' => esc_attr__( 'Disable', 'woo-floating-cart' ),
            '1'     => esc_attr__( 'Enable', 'woo-floating-cart' )
        ),
        'default'     => '0'
    );

} else {

	$fields[] = array(
		'id'      => 'extras_features',
		'section' => 'extras',
		'type'    => 'xt-premium',
		'default' => array(
			'type'  => 'image',
			'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/extras.png',
			'link'  => $this->core->plugin_upgrade_url()
		)
	);
}