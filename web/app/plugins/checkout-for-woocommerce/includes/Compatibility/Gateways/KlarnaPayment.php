<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class KlarnaPayment extends CompatibilityAbstract {

	protected $klarna_payments = null;

	public function is_available(): bool {
		return class_exists( '\\WC_Klarna_Payments' );
	}

	public function pre_init() {
		add_action( 'cfw_payment_gateway_list_klarna_payments_alternate', array( $this, 'klarna_payments_content' ), 10, 1 );
		add_filter( 'cfw_show_gateway_klarna_payments', '__return_false' );
	}

	public function klarna_payments_content( $count ) {
		do_action( 'klarna_payments_template' );

		if ( is_array( WC()->session->get( 'klarna_payments_categories' ) ) ) {
			$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
			$current_gateway    = WC()->session->get( 'chosen_payment_method' );

			foreach ( apply_filters( 'wc_klarna_payments_available_payment_categories', WC()->session->get( 'klarna_payments_categories' ) ) as $payment_category ) {
				$payment_category      = is_array( $payment_category ) ? $this->toObject( $payment_category ) : $payment_category;
				$payment_category_id   = 'klarna_payments_' . $payment_category->identifier;
				$payment_category_name = $payment_category->name;
				$payment_category_icon = $payment_category->asset_urls->standard;
				$kp                    = $available_gateways['klarna_payments'];
				$kp->id                = $payment_category_id;
				$kp->title             = $payment_category_name;
				$kp->icon              = $payment_category_icon;
				$headers               = empty( $kp->icon ) ? false : get_headers( $kp->icon );
				if ( $headers && 'HTTP/1.1 404 Not Found' === $headers[0] ) {
					switch ( $kp->id ) {
						case 'klarna_payments_pay_later':
							$kp->icon = 'https://cdn.klarna.com/1.0/shared/image/generic/badge/sv_se/pay_later/standard/pink.svg';
							break;
						case 'klarna_payments_pay_over_time':
							$kp->icon = 'https://cdn.klarna.com/1.0/shared/image/generic/badge/sv_se/slice_it/standard/pink.svg';
							break;
						case 'klarna_payments_pay_now':
							$kp->icon = 'https://cdn.klarna.com/1.0/shared/image/generic/badge/sv_se/pay_now/standard/pink.svg';
							break;
					}
				}
				?>
				<li class="wc_payment_method payment_method_<?php echo $kp->id; ?> cfw-radio-reveal-li">
					<div class="payment_method_title_wrap cfw-radio-reveal-title-wrap">
						<input id="payment_method_<?php echo $kp->id; ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $kp->id ); ?>" <?php echo ( ( empty( $current_gateway ) && 0 === $count ) || stripos( $current_gateway, 'klarna_payments' ) !== false ) ? 'checked' : ''; ?> data-order_button_text="<?php echo esc_attr( $kp->order_button_text ); ?>" />

						<label class="payment_method_label cfw-radio-reveal-label" for="payment_method_<?php echo $kp->id; ?>">
							<div>
								<span class="payment_method_title cfw-radio-reveal-title"><?php echo $kp->get_title(); ?></span>

								<span class="payment_method_icons">
									<?php echo $kp->get_icon(); ?>
								</span>
							</div>
						</label>
					</div>
					<?php
					/**
					 * Filters whether to show custom klarna payment box HTML
					 *
					 * @since 2.0.0
					 *
					 * @param bool $show Whether to show custom payment box HTML
					 */
					if ( apply_filters( "cfw_payment_gateway_{$kp->id}_content", $kp->has_fields() || $kp->get_description() ) ) :
						?>
						<div class="payment_box payment_method_<?php echo $kp->id; ?> cfw-radio-reveal-content" <?php echo ( ! $kp->chosen ) ? 'style="display:none;"' : ''; ?> >
							<?php
							ob_start();
							$kp->payment_fields();

							$field_html = ob_get_clean();

							/**
							 * Gateway Compatibility Patches
							 */
							// Expiration field fix
							$field_html = str_ireplace( 'js-sv-wc-payment-gateway-credit-card-form-expiry', 'js-sv-wc-payment-gateway-credit-card-form-expiry  wc-credit-card-form-card-expiry', $field_html );
							$field_html = str_ireplace( 'js-sv-wc-payment-gateway-credit-card-form-account-number', 'js-sv-wc-payment-gateway-credit-card-form-account-number  wc-credit-card-form-card-number', $field_html );

							// Credit Card Field Placeholders
							$field_html = str_ireplace( '•••• •••• •••• ••••', 'Card Number', $field_html );
							$field_html = str_ireplace( '&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;', 'Card Number', $field_html );

							/**
							 * Filters klarna payment gateway output
							 *
							 * @since 2.0.0
							 *
							 * @param string $output The gateway output
							 */
							echo apply_filters( "cfw_payment_gateway_field_html_{$kp->id}", $field_html );
							?>
						</div>
					<?php endif; ?>
				</li>
				<?php
			}
		}
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'KlarnaPayments',
			'params' => array(),
		);

		return $compatibility;
	}

	public function toObject( $array ) {
		$obj = new \stdClass();

		foreach ( $array as $key => $val ) {
			$obj->$key = is_array( $val ) ? $this->toObject( $val ) : $val;
		}

		return $obj;
	}
}
