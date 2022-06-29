<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class CartFlows extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'CARTFLOWS_FILE' );
	}

	public function run() {
		global $post;

		// Maybe prevent CheckoutWC template from being loaded
		if ( _is_wcf_checkout_type() ) {
			$checkout_id = $post->ID;
		} else {
			$checkout_id = _get_wcf_checkout_id_from_shortcode( $post->post_content );
		}

		$use_checkoutwc_template = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-cfw-use-template' );

		if ( 'no' === $use_checkoutwc_template ) {
			remove_action( 'wp', 'cfw_frontend', 1 ); // disable templates
		}
	}

	public function admin_init() {
		// Legacy
		add_action( 'cartflows_checkout_style_tab_content', array( $this, 'admin_setting' ), 10, 1 );
		add_filter( 'cartflows_checkout_meta_options', array( $this, 'admin_add_option' ) );

		// New
		add_filter( 'cartflows_react_checkout_design_fields', array( $this, 'add_new_admin_setting' ), 10, 2 );
	}

	public function add_new_admin_setting( $settings, $options ) {
		$settings['settings']['checkout-design']['fields'][] = array(
			'type'  => 'checkbox',
			'label' => 'Use CheckoutWC template?',
			'name'  => 'wcf-cfw-use-template',
			'value' => $options['wcf-cfw-use-template'],
		);

		return $settings;
	}

	public function admin_add_option( $options ) {
		$options['wcf-cfw-use-template'] = array(
			'default'  => '',
			'sanitize' => 'FILTER_DEFAULT',
		);

		return $options;
	}

	public function admin_setting( $options ) {
		echo wcf()->meta->get_checkbox_field(
			array(
				'label' => __( 'CheckoutWC', 'cartflows' ),
				'name'  => 'wcf-cfw-use-template',
				'value' => $options['wcf-cfw-use-template'],
				'after' => 'Use CheckoutWC template?',
			)
		);
	}
}
