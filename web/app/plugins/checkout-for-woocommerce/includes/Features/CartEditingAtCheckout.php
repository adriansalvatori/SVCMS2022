<?php

namespace Objectiv\Plugins\Checkout\Features;

use Objectiv\Plugins\Checkout\Admin\Pages\PageAbstract;
use Objectiv\Plugins\Checkout\Interfaces\ItemInterface;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 */
class CartEditingAtCheckout extends FeaturesAbstract {
	protected function run_if_cfw_is_enabled() {
		add_action( 'cfw_update_checkout_after_customer_save', array( $this, 'handle_update_checkout' ), 10, 1 );
		add_action( 'cfw_cart_item_after_data', array( $this, 'output_cart_edit_item_quantity_control' ), 10, 3 );
	}

	public function init() {
		parent::init();

		add_action( 'cfw_do_plugin_activation', array( $this, 'run_on_plugin_activation' ) );
		add_action( 'cfw_cart_summary_before_admin_page_controls', array( $this, 'output_admin_fields' ), 10, 1 );
	}

	/**
	 * @param array $post_data
	 */
	public function handle_update_checkout( array $post_data ) {
		if ( ! isset( $post_data['cart'] ) || 'true' !== $post_data['cfw_update_cart'] ) {
			return;
		}

		cfw_update_cart( $post_data['cart'] );

		// Check cart has contents.
		if ( WC()->cart->is_empty() && ! is_customize_preview() && apply_filters( 'woocommerce_checkout_redirect_empty_cart', true ) ) {
			/**
			 * Filters whether to suppress checkout is not available message
			 * when editing cart results in empty cart
			 *
			 * @since 3.14.0
			 *
			 * @param bool $supress_notice Whether to suppress the message
			 */
			if ( false === apply_filters( 'cfw_cart_edit_redirect_suppress_notice', false ) ) {
				wc_add_notice( cfw__( 'Checkout is not available whilst your cart is empty.', 'woocommerce' ), 'notice' );
			}

			// Allow shortcodes to be used in empty cart redirect URL field
			// This is necessary so that WPML (etc) can swap in a locale specific URL
			$cart_editing_redirect_url = do_shortcode( $this->settings_getter->get_setting( 'cart_edit_empty_cart_redirect' ) );

			$redirect = empty( $cart_editing_redirect_url ) ? wc_get_cart_url() : $cart_editing_redirect_url;

			add_filter(
				'cfw_update_checkout_redirect',
				function() use ( $redirect ) {
					return $redirect;
				}
			);
		}
	}

	public function output_cart_edit_item_quantity_control( array $cart_item, string $cart_item_key, ItemInterface $item ) {
		echo cfw_get_cart_item_quantity_control( $cart_item, $cart_item_key, $item->get_product() );
	}

	/**
	 * @param PageAbstract $cart_summary_admin_page
	 */
	public function output_admin_fields( PageAbstract $cart_summary_admin_page ) {
		if ( ! $this->available ) {
			$notice = $cart_summary_admin_page->get_upgrade_required_notice( $this->required_plans_list );
		}

		$cart_summary_admin_page->output_checkbox_row(
			'enable_cart_editing',
			cfw__( 'Enable Cart Editing At Checkout', 'checkout-wc' ),
			cfw__( 'Enable or disable Cart Editing. Allows customer to remove or adjust quantity of cart items at checkout.', 'checkout-wc' ),
			array(
				'enabled' => $this->available,
				'notice'  => $notice ?? '',
			)
		);

		$cart_summary_admin_page->output_text_input_row(
			'cart_edit_empty_cart_redirect',
			cfw__( 'Cart Editing Empty Cart Redirect', 'checkout-wc' ),
			cfw__( 'URL to redirect to when customer empties cart from checkout page.', 'checkout-wc' ) . '<br/>' . cfw__( 'If left blank, customer will be redirected to the cart page.', 'checkout-wc' ),
			array( 'nested' => true )
		);
	}

	public function run_on_plugin_activation() {
		SettingsManager::instance()->add_setting( 'enable_cart_editing', 'no' );
		SettingsManager::instance()->add_setting( 'cart_edit_empty_cart_redirect', '' );
	}
}
