<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Admin\Pages
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class CartSummary extends PageAbstract {
	public function __construct() {
		parent::__construct( cfw__( 'Cart Summary', 'checkout-wc' ), 'manage_options', 'cart-summary' );
	}
	public function output() {
		$this->output_form_open();
		?>
		<div class="space-y-6">
			<?php
			cfw_admin_page_section(
				'Cart Summary',
				'Control the Cart Summary at checkout.',
				$this->get_settings()
			);

			/**
			 * Fires at the top of the cart summary admin page settings table inside <tbody>
			 *
			 * @since 7.0.0
			 *
			 * @param CartSummary $cart_summary_admin_page The cart summary admin page
			 */
			do_action( 'cfw_cart_summary_after_admin_page_settings', $this );
			?>
		</div>
		<?php
		$this->output_form_close();
	}

	/**
	 * @return string
	 */
	protected function get_settings() : string {
		ob_start();

		/**
		 * Fires at the top of the cart summary admin page settings table inside <tbody>
		 *
		 * @since 5.0.0
		 *
		 * @param CartSummary $cart_summary_admin_page The cart summary admin page
		 */
		do_action( 'cfw_cart_summary_before_admin_page_controls', $this );

		$this->output_checkbox_row(
			'show_cart_item_discount',
			cfw__( 'Enable Sale Prices', 'checkout-wc' ),
			cfw__( 'Enable sale price under on cart item labels at checkout. Example: <s>$10.00</s> $5.00', 'checkout-wc' )
		);

		$this->output_radio_group_row(
			'cart_item_link',
			cfw__( 'Cart Item Links', 'checkout-wc' ),
			cfw__( 'Choose whether or not cart items link to the single product page.', 'checkout-wc' ),
			'disabled',
			array(
				'disabled' => cfw__( 'Disabled (Recommended)', 'checkout-wc' ),
				'enabled'  => cfw__( 'Enabled', 'checkout-wc' ),
			),
			array(
				'disabled' => cfw__( 'Do not link cart items to single product page. (Recommended)', 'checkout-wc' ),
				'enabled'  => cfw__( 'Link each cart item to product page.', 'checkout-wc' ),
			)
		);

		$this->output_radio_group_row(
			'cart_item_data_display',
			cfw__( 'Cart Item Data Display', 'checkout-wc' ),
			cfw__( 'Choose how to display cart item data.', 'checkout-wc' ),
			'short',
			array(
				'short'       => cfw__( 'Short (Recommended)', 'checkout-wc' ),
				'woocommerce' => cfw__( 'WooCommerce Default', 'checkout-wc' ),
			),
			array(
				'short'       => cfw__( 'Display only variation values. For example, Size: XL, Color: Red is displayed as XL / Red. (Recommended)', 'checkout-wc' ),
				'woocommerce' => cfw__( 'Each variation is displayed on a separate line using this format: Label: Value', 'checkout-wc' ),
			)
		);

		/**
		 * Fires at the top of the cart summary admin page settings table inside <tbody>
		 *
		 * @since 5.0.0
		 *
		 * @param CartSummary $cart_summary_admin_page The cart summary admin page
		 */
		do_action( 'cfw_cart_summary_after_admin_page_controls', $this );

		return ob_get_clean();
	}
}
