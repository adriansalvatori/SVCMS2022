<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WooCommerceGiftCards extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'WC_GC' );
	}

	public function run() {
		if ( apply_filters( 'cfw_disable_woocommerce_gift_cards_compatibility', false ) ) {
			return;
		}

		$woocommerce_gift_cards = WC_GC();

		remove_action( 'woocommerce_review_order_before_submit', array( $woocommerce_gift_cards->cart, 'display_form' ), 9 );

		add_action( 'cfw_coupon_module_end', array( $this, 'display_form' ), 9, 1 );
	}

	public function run_on_wp_loaded() {
		add_filter( 'woocommerce_update_order_review_fragments', array( $this, 'add_fragment' ), 10, 1 );
	}

	/**
	 * Display form to add gift card.
	 *
	 * @param bool $mobile
	 *
	 * @return void
	 */
	public function display_form( bool $mobile = false ) {
		if ( $mobile ) {
			return;
		}

		$woocommerce_gift_cards = WC_GC();

		if ( ! wc_gc_is_ui_disabled() ) {
			return;
		}

		if ( $woocommerce_gift_cards->cart->cart_contains_gift_card() ) {
			return;
		}

		$get_private_notices_callback = function() {
			return $this->notices;
		};

		$get_private_notices = $get_private_notices_callback->bindTo( $woocommerce_gift_cards->cart, '\\WC_GC_Cart' );

		$notices = $get_private_notices();

		$woocommerce_gift_cards_field_label       = apply_filters( 'cfw_compatibility_woocommerce_gift_cards_field_label', cfw_esc_attr__( 'Enter your code&hellip;', 'woocommerce-gift-cards' ) );
		$woocommerce_gift_cards_field_placeholder = apply_filters( 'cfw_compatibility_woocommerce_gift_cards_field_placeholder', cfw_esc_attr__( 'Enter your code&hellip;', 'woocommerce-gift-cards' ) );
		?>
		<div class="add_gift_card_form">
			<?php
			if ( ! empty( $notices ) ) {
				foreach ( $notices as $notice ) {
					if ( empty( $notice['type'] ) ) {
						$notice['type'] = 'message';
					}
					echo '<div class="woocommerce-' . esc_attr( $notice['type'] ) . '">' . esc_html( $notice['text'] ) . '</div>';
				}
			}
			?>
			<h4><?php echo apply_filters( 'cfw_compatibility_woocommerce_gift_cards_heading_text', cfw_esc_html__( 'Have a gift card?', 'woocommerce-gift-cards' ) ); ?></h4>
			<div id="wc_gc_cart_redeem_form" class="row cfw-input-wrap-row">
				<div class="col-lg-8 no-gutters">
					<div class="col-lg-12" id="wc_gc_cart_code_field" data-priority="10">
						<?php
						woocommerce_form_field(
							'wc_gc_cart_code',
							array(
								'id'                       => 'wc_gc_cart_code',
								'type'                     => 'text',
								'required'                 => false,
								'label'                    => $woocommerce_gift_cards_field_label,
								'placeholder'              => $woocommerce_gift_cards_field_placeholder,
								'suppress_optional_suffix' => true,
									'custom_attributes'    => array(
									'data-storage'          => 'false',
									'autocomplete'          => 'off',
									'data-parsley-required' => 'false',
									'style'                 => 'width: 100%',
								)
							)
						);
						?>
					</div>
				</div>
				<div class="col-lg-4">
					<div class="cfw-input-wrap form-row cfw-button-input">
						<button type="button" class="cfw-secondary-btn" name="wc_gc_cart_redeem_send" id="wc_gc_cart_redeem_send"><?php cfw_esc_html_e( 'Apply', 'woocommerce-gift-cards' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'WooCommerceGiftCards',
			'params' => array(),
		);

		return $compatibility;
	}

	public function add_fragment( $fragments ) {
		ob_start();
		$this->display_form();

		$fragments['.add_gift_card_form'] = ob_get_clean();

		return $fragments;
	}
}
