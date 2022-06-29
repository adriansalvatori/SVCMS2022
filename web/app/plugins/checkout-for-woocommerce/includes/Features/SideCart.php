<?php

namespace Objectiv\Plugins\Checkout\Features;
use Objectiv\Plugins\Checkout\FormFieldAugmenter;
use Objectiv\Plugins\Checkout\Interfaces\ItemInterface;
use Objectiv\Plugins\Checkout\Interfaces\SettingsGetterInterface;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;
use WC_Frontend_Scripts;
use WC_Shipping_Free_Shipping;
use WC_Shipping_Zones;

class SideCart extends FeaturesAbstract {
	protected $item_just_added_to_cart = false;
	protected $order_bumps_controller  = false;

	public function __construct( bool $enabled, bool $available, string $required_plans_list, SettingsGetterInterface $settings_getter, OrderBumps $order_bumps_controller ) {
		$this->enabled                = $enabled;
		$this->available              = $available;
		$this->required_plans_list    = $required_plans_list;
		$this->settings_getter        = $settings_getter;
		$this->order_bumps_controller = $order_bumps_controller;
	}

	public function init() {
		parent::init();

		add_action( 'cfw_do_plugin_activation', array( $this, 'run_on_plugin_activation' ) );
	}

	protected function run_if_cfw_is_enabled() {
		if ( apply_filters( 'cfw_disable_side_cart', false ) ) {
			return;
		}

		// Prevent redirecting after add to cart when side cart is on
		add_filter(
			'pre_option_woocommerce_cart_redirect_after_add',
			function() {
				return 'no';
			}
		);
		add_filter( 'woocommerce_add_to_cart_redirect', '__return_false' );

		add_action( 'woocommerce_add_to_cart', array( $this, 'detect_item_just_added_to_cart' ) );
		add_action( 'wp', array( $this, 'run_sidecart' ) );
	}

	public function run_sidecart() {
		add_filter( 'cfw_breadcrumbs', array( $this, 'remove_cart_breadcrumb' ) );
		add_filter( 'cfw_show_return_to_cart_link', '__return_false' );
		add_shortcode( 'checkoutwc_cart', array( $this, 'render_shortcode' ) );
		add_action( 'cfw_side_cart_item_after_data', array( $this, 'output_cart_edit_item_quantity_control' ), 10, 3 );

		if ( SettingsManager::instance()->get_setting( 'enable_order_bumps_on_side_cart' ) === 'yes' ) {
			add_action( 'cfw_after_side_cart_items_table', array( $this->order_bumps_controller, 'output_cart_summary_bumps' ) );
		}

		if ( SettingsManager::instance()->get_setting( 'enable_free_shipping_progress_bar' ) === 'yes' ) {
			add_action( 'cfw_after_side_cart_header', array( $this, 'maybe_output_shipping_progress_bar' ) );
		}

		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'add_side_cart_to_fragments' ) );
		add_filter( 'cfw_event_data', array( $this, 'add_localized_settings' ) );
		add_action( 'cfw_custom_css_properties', array( $this, 'add_custom_css_property' ) );

		if ( SettingsManager::instance()->get_setting( 'enable_order_bumps_on_side_cart' ) === 'yes' ) {
			add_action( 'cfw_before_update_side_cart_action', array( $this->order_bumps_controller, 'handle_adding_order_bump_to_cart' ) );
		}

		// Turn off empty cart notice
		remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 );
		add_action( 'woocommerce_cart_is_empty', 'cfw_output_empty_cart_message', 1 );

		// Turn off "Item removed. Undo?" notices
		add_filter( 'woocommerce_cart_item_removed_notice_type', '__return_null' );

		add_filter(
			'pre_option_woocommerce_enable_ajax_add_to_cart',
			function( $result ) {
				if ( SettingsManager::instance()->get_setting( 'enable_side_cart' ) === 'yes' && SettingsManager::instance()->get_setting( 'enable_ajax_add_to_cart' ) === 'yes' ) {
					$result = 'yes';
				}

				return $result;
			},
			10,
			1
		);

		// Output custom styles
		add_action(
			'wp_head',
			function() {
				do_action( 'cfw_custom_styles' );
			}
		);

		if ( ! is_cfw_page() ) {
			/**
			 * Compatibility Nightmare Avoidance
			 */
			add_action( 'wp_enqueue_scripts', array( $this, 'make_sure_cart_fragments_script_is_enqueued' ), 100 * 1000 );
			add_action( 'wp_print_scripts', array( WC_Frontend_Scripts::class, 'localize_printed_scripts' ), 5 );
			add_action( 'wp_print_footer_scripts', array( WC_Frontend_Scripts::class, 'localize_printed_scripts' ), 5 );

			add_action( 'cfw_before_cart_item_subtotal', array( $this, 'add_delete_button' ), 10, 1 );
			add_action( 'wp_footer', array( $this, 'output_side_cart_and_overlay_markup' ), 10000 );
		}
	}

	public function detect_item_just_added_to_cart() {
		$this->item_just_added_to_cart = true;
	}

	public function make_sure_cart_fragments_script_is_enqueued() {
		WC_Frontend_Scripts::load_scripts();
		wp_enqueue_script( 'wc-cart-fragments' );
	}

	public function add_delete_button( ItemInterface $item ) {
		if ( defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
			return;
		}

		$_product = $item->get_product();

		echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'woocommerce_cart_item_remove_link',
			sprintf(
				'<a href="%s" class="cfw-remove-item-button" aria-label="%s" data-product_id="%s" data-product_sku="%s">&cross;</a>',
				esc_url( wc_get_cart_remove_url( $item->get_item_key() ) ),
				cfw_esc_html__( 'Remove this item', 'woocommerce' ),
				esc_attr( $_product->get_id() ),
				esc_attr( $_product->get_sku() )
			),
			$item->get_item_key()
		);
	}

	public function output_side_cart_and_overlay_markup() {
		?>
		<div id="cfw-side-cart-overlay"></div>
		<div class="checkoutwc" id="cfw-side-cart">
			<?php
			if ( WC()->cart->is_empty() ) {
				echo $this->get_side_cart_fragment();
			} else {
				echo '<form class="uninitialized checkoutwc" id="cfw-side-cart-form"></form>';
			}
			?>
		</div>

		<?php if ( SettingsManager::instance()->get_setting( 'enable_floating_cart_button' ) === 'yes' && ( ! WC()->cart->is_empty() || SettingsManager::instance()->get_setting( 'hide_floating_cart_button_empty_cart' ) !== 'yes' ) ) : ?>
		<a class="cfw-side-cart-floating-button cfw-side-cart-open-trigger" aria-label="<?php echo esc_attr( cfw__( 'View cart', 'woocommerce' ) ); ?>">
			<?php echo self::get_cart_icon(); ?>
		</a>
		<?php endif; ?>
		<?php
	}

	public function get_side_cart_fragment() {
		ob_start();
		?>
		<form id="cfw-side-cart-form" class="checkoutwc">
			<div class="cfw-side-cart-contents-header">
				<span class="cfw-side-cart-close-btn">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M17 8L21 12M21 12L17 16M21 12L3 12" stroke="#111827" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</span>

				<?php echo self::get_cart_icon(); ?>
				<?php do_action( 'cfw_after_side_cart_header' ); ?>
			</div>

			<div class="cfw-side-cart-contents">
				<?php
				if ( WC()->cart->is_empty() ) {
					do_action( 'woocommerce_cart_is_empty' );
					do_action( 'checkoutwc_empty_side_cart_content' );
				} else {
					echo cfw_get_side_cart_item_summary_table();
					do_action( 'cfw_after_side_cart_items_table' );
				}
				?>
			</div>

			<div class="cfw-side-cart-contents-footer">
				<div class="cfw-side-cart-contents-footer-border-shim"></div>
				<?php do_action( 'cfw_side_cart_footer_start' ); ?>

				<?php if ( ! WC()->cart->is_empty() ) : ?>
					<?php
					if ( wc_coupons_enabled() && SettingsManager::instance()->get_setting( 'enable_promo_codes_on_side_cart' ) === 'yes' ) :
						/**
						 * Filters promo code button label
						 *
						 * @param string $promo_code_button_label Promo code button label
						 * @since 3.0.0
						 */
						$promo_code_button_label = apply_filters( 'cfw_promo_code_apply_button_label', esc_attr__( 'Apply', 'checkout-wc' ) );
						?>

						<div class="cfw-side-cart-coupon-wrap">
							<div class="row">
								<div class="col-lg-12 no-gutters">
									<a class="cfw-show-coupons-module" href="javascript:">
										<?php
										/**
										 * Filters promo code toggle link text
										 *
										 * @param string $promo_code_toggle_link_text Filters promo code toggle link text
										 * @since 3.0.0
										 */
										echo apply_filters( 'cfw_promo_code_toggle_link_text', __( 'Have a promo code? Click here.', 'checkout-wc' ) );
										?>
									</a>
								</div>
							</div>
							<div class="cfw-promo-wrap cfw-hidden">
								<div class="row cfw-promo-row cfw-input-wrap-row">
									<?php
									FormFieldAugmenter::instance()->add_hooks();
									$output = woocommerce_form_field(
										'cfw-promo-code',
										array(
											'type'        => 'text',
											'required'    => false,

											/**
											 * Filters promo code label
											 *
											 * @param string $promo_code_label Promo code label
											 * @since 3.0.0
											 */
											'label'       => apply_filters( 'cfw_promo_code_label', __( 'Promo Code', 'checkout-wc' ) ),

											/**
											 * Filters promo code placeholder
											 *
											 * @param string $promo_code_placeholder Promo code placeholder
											 * @since 3.0.0
											 */
											'placeholder' => apply_filters( 'cfw_promo_code_placeholder', __( 'Enter Promo Code', 'checkout-wc' ) ),

											'label_class' => 'cfw-input-label',
											'class'       => array( 'no-gutters' ),
											'start'       => false,
											'end'         => false,
											'columns'     => 8,
											'return'      => true,
										)
									);

									$output = str_replace( '(' . cfw_esc_html__( 'optional', 'woocommerce' ) . ')', '', $output );

									echo $output;

									FormFieldAugmenter::instance()->remove_hooks();
									?>
									<div class="col-lg-4">
										<div class="cfw-input-wrap cfw-button-input">
											<input type="button" name="cfw-promo-code-btn" id="cfw-promo-code-btn" class="cfw-secondary-btn" value="<?php echo $promo_code_button_label; ?>" />
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php
					endif;
					cfw_wc_print_notices();
					?>
					<div class="cfw-side-cart-totals">
						<?php do_action( 'cfw_before_side_cart_totals' ); ?>
						<table class="cfw-module">
							<tr class="cart-subtotal">
								<th><?php cfw_e( 'Subtotal', 'woocommerce' ); ?></th>
								<td><?php wc_cart_totals_subtotal_html(); ?></td>
							</tr>
							<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
								<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
									<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
									<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
								</tr>
							<?php endforeach; ?>
						</table>
					</div>

					<div class="wc-proceed-to-checkout">
						<?php wc_get_pay_buttons(); ?>

						<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="cfw-primary-btn cfw-side-cart-checkout-btn">
							<?php cfw_esc_html_e( 'Proceed to checkout', 'woocommerce' ); ?>
						</a>

						<?php
						/**
						 * Filters whether to show continue shopping button in side cart
						 *
						 * @param bool $enable_button Whether to show continue shopping button in side cart
						 * @since 7.0.5
						 */
						if ( apply_filters( 'cfw_side_cart_enable_continue_shopping_button', SettingsManager::instance()->get_setting( 'enable_side_cart_continue_shopping_button' ) === 'yes' ) ) :
							?>
							<a href="#" class="cfw-side-cart-close-trigger cfw-secondary-btn">
								<?php cfw_esc_html_e( 'Continue shopping', 'woocommerce' ); ?>
							</a>
						<?php endif; ?>

						<?php
						/**
						 * Fires after checkout proceed to cart (and maybe continue shopping) buttons
						 * @since 7.0.6
						 */
						do_action( 'cfw_after_side_cart_proceed_to_checkout_button' );
						?>
					</div>
				<?php endif; ?>
			</div>
		</form>
		<?php
		return ob_get_clean();
	}

	/**
	 * @param array|null $data
	 * @return bool
	 */
	public function does_cart_qualifies_for_free_shipping( array $data = null ): bool {
		$data = $data ?? $this->get_free_shipping_data();

		if ( empty( $data ) ) {
			return false;
		}

		return (bool) $data['has_free_shipping'];
	}

	/**
	 * @param array|null $data
	 * @return float|null
	 */
	public function get_remaining_amount_to_qualify_for_free_shipping( array $data = null ): ?float {
		$data = $data ?? $this->get_free_shipping_data();

		if ( empty( $data ) ) {
			return null;
		}

		return floatval( $data['amount_remaining'] );
	}

	/**
	 * @param array|null $data
	 * @return int
	 */
	public function get_fill_percentage( array $data = null ): int {
		$data = $data ?? $this->get_free_shipping_data();

		if ( empty( $data ) ) {
			return 0;
		}

		return intval( $data['fill_percentage'] );
	}

	/**
	 * @return array
	 */
	public function get_free_shipping_data(): array {
		$data = array();

		$has_free_shipping        = false;
		$threshold                = SettingsManager::instance()->get_setting( 'side_cart_free_shipping_threshold' );
		$amount_remaining         = null;
		$fill_percentage          = null;
		$subtotal                 = WC()->cart->get_displayed_subtotal();
		$has_free_shipping_coupon = false;

		foreach ( WC()->cart->get_applied_coupons() as $coupon_code ) {
			$coupon = new \WC_Coupon( $coupon_code );

			if ( $coupon->get_free_shipping() ) {
				$has_free_shipping_coupon = true;
				break;
			}
		}

		if ( $has_free_shipping_coupon ) {
			return array(
				'has_free_shipping' => true,
				'amount_remaining'  => 0,
				'fill_percentage'   => 100,
			);
		}

		if ( ! empty( $threshold ) && is_numeric( $threshold ) ) {
			return array(
				'has_free_shipping' => ( $subtotal >= $threshold ) || $has_free_shipping_coupon,
				'amount_remaining'  => $subtotal >= $threshold ? 0 : $threshold - $subtotal,
				'fill_percentage'   => min( ceil( ( $subtotal / $threshold ) * 100 ), 100 ),
			);
		}

		WC()->cart->calculate_shipping();

		$packages = WC()->shipping()->get_packages();

		if ( empty( $packages ) ) {
			return $data;
		}

		// Only look at first package for this feature
		$available_methods = $packages[0]['rates'];

		foreach ( $available_methods as $available_method ) {
			if ( $available_method instanceof WC_Shipping_Free_Shipping ) {
				$has_free_shipping = true;
				break;
			}
		}

		if ( ! $has_free_shipping ) {
			$shipping_zone    = WC_Shipping_Zones::get_zone_matching_package( $packages[0] );
			$shipping_methods = $shipping_zone->get_shipping_methods( true );

			foreach ( $shipping_methods as $shipping_method ) {
				if ( $shipping_method instanceof WC_Shipping_Free_Shipping && ( 'min_amount' === $shipping_method->requires || 'either' === $shipping_method->requires ) ) {

					if ( 'no' === $shipping_method->ignore_discounts && ! empty( WC()->cart->get_coupon_discount_totals() ) ) {
						foreach ( WC()->cart->get_coupon_discount_totals() as $coupon_value ) {
							$subtotal -= $coupon_value;
						}
					}

					if ( $subtotal >= $shipping_method->min_amount ) {
						$has_free_shipping = true;
					} else {
						$amount_remaining = $shipping_method->min_amount - $subtotal;
						$fill_percentage  = ceil( ( $subtotal / $shipping_method->min_amount ) * 100 );
					}
					break;
				}
			}
		}

		if ( ! $has_free_shipping && is_null( $amount_remaining ) ) {
			return $data;
		}

		$data = array(
			'has_free_shipping' => $has_free_shipping,
			'amount_remaining'  => $amount_remaining,
			'fill_percentage'   => $has_free_shipping ? 100 : $fill_percentage,
		);

		return apply_filters( 'cfw_shipping_bar_data', $data );
	}

	public function maybe_output_shipping_progress_bar() {
		$quantity          = array_sum( wp_list_pluck( WC()->cart->get_cart(), 'quantity' ) );
		$data              = $this->get_free_shipping_data();
		$fill_percent      = $this->get_fill_percentage( $data );
		$has_free_shipping = $this->does_cart_qualifies_for_free_shipping( $data );
		$amount_remaining  = $this->get_remaining_amount_to_qualify_for_free_shipping( $data );

		$free_shipping_message    = SettingsManager::instance()->get_setting( 'side_cart_free_shipping_message' );
		$amount_remaining_message = SettingsManager::instance()->get_setting( 'side_cart_amount_remaining_message' );

		if ( empty( $free_shipping_message ) ) {
			$free_shipping_message = __( 'Congrats! You get free standard shipping.', 'checkout-wc' );
		}

		if ( empty( $amount_remaining_message ) ) {
			// translators: %s is the amount remaining for free shipping
			$amount_remaining_message = __( 'You\'re %s away from free shipping!', 'checkout-wc' );
		}

		$amount_remaining = '<strong>' . wc_price( $amount_remaining, array( 'decimals' => 0.0 === fmod( $amount_remaining, 1 ) ? 0 : 2 ) ) . '</strong>';

		$amount_remaining_message = sprintf( $amount_remaining_message, $amount_remaining );

		if ( ! empty( $data ) && 0 < $quantity ) :
			?>
			<div class="cfw-side-cart-free-shipping-progress-wrap">
				<p class="cfw-xtra-small">
					<?php if ( $has_free_shipping ) : ?>
						<?php echo $free_shipping_message; ?>
					<?php else : ?>
						<?php echo $amount_remaining_message; ?>
					<?php endif; ?>
				</p>

				<div class="cfw-side-cart-free-shipping-progress">
					<div class="cfw-side-cart-free-shipping-progress-indicator" style="width: <?php echo $fill_percent; ?>%;"></div>
				</div>
			</div>
			<?php
		endif;
	}

	public function add_custom_css_property( $properties ) {
		$properties['--cfw-side-cart-free-shipping-progress-indicator']  = SettingsManager::instance()->get_setting( 'side_cart_free_shipping_progress_indicator_color' );
		$properties['--cfw-side-cart-free-shipping-progress-background'] = SettingsManager::instance()->get_setting( 'side_cart_free_shipping_progress_bg_color' );
		$properties['--cfw-side-cart-button-bottom-position']            = SettingsManager::instance()->get_setting( 'floating_cart_button_bottom_position' ) . 'px';
		$properties['--cfw-side-cart-button-right-position']             = SettingsManager::instance()->get_setting( 'floating_cart_button_right_position' ) . 'px';
		$properties['--cfw-side-cart-icon-color']                        = SettingsManager::instance()->get_setting( 'side_cart_icon_color' );
		$properties['--cfw-side-cart-icon-width']                        = SettingsManager::instance()->get_setting( 'side_cart_icon_width' ) . 'px';

		return $properties;
	}

	public function add_side_cart_to_fragments( array $fragments ): array {
		$fragments['#cfw-side-cart-form']     = $this->get_side_cart_fragment();
		$fragments['.cfw-side-cart-quantity'] = self::get_quantity();

		return $fragments;
	}

	public function add_localized_settings( array $event_data ) : array {
		$event_data['settings']['enable_ajax_add_to_cart'] = SettingsManager::instance()->get_setting( 'enable_ajax_add_to_cart' ) === 'yes';
		$event_data['runtime_params']['openCart']          = $this->item_just_added_to_cart;

		return $event_data;
	}

	public function remove_cart_breadcrumb( $breadcrumbs ) {
		unset( $breadcrumbs['cart'] );

		return $breadcrumbs;
	}

	public function render_shortcode( $attributes ): string {
		$attributes = shortcode_atts(
			array(
				'color'      => SettingsManager::instance()->get_setting( 'side_cart_icon_color' ),
				'width'      => SettingsManager::instance()->get_setting( 'side_cart_icon_width' ) . 'px',
				'text_color' => '#222',
			),
			$attributes,
			'checkoutwc_cart'
		);

		$output  = "<style>.cfw_cart_icon_shortcode { --cfw-side-cart-icon-color: {$attributes['color']}; --cfw-side-cart-icon-width: {$attributes['width']}; --cfw-side-cart-icon-text-color: {$attributes['text_color']}; }</style>";
		$output .= self::get_cart_icon( 'cfw_cart_icon_shortcode cfw-side-cart-open-trigger' );

		return $output;
	}

	public function run_on_plugin_activation() {
		SettingsManager::instance()->add_setting( 'enable_side_cart', 'no' );
		SettingsManager::instance()->add_setting( 'enable_ajax_add_to_cart', 'no' );
		SettingsManager::instance()->add_setting( 'enable_free_shipping_progress_bar', 'no' );
		SettingsManager::instance()->add_setting( 'side_cart_free_shipping_threshold', '' );
		SettingsManager::instance()->add_setting( 'side_cart_amount_remaining_message', '' );
		SettingsManager::instance()->add_setting( 'side_cart_free_shipping_message', '' );
		SettingsManager::instance()->add_setting( 'side_cart_free_shipping_threshold', '' );
		SettingsManager::instance()->add_setting( 'side_cart_free_shipping_progress_indicator_color', cfw_get_active_template()->get_default_setting( 'button_color' ) );
		SettingsManager::instance()->add_setting( 'enable_floating_cart_button', 'yes' );
		SettingsManager::instance()->add_setting( 'floating_cart_button_bottom_position', '20' );
		SettingsManager::instance()->add_setting( 'floating_cart_button_right_position', '20' );
		SettingsManager::instance()->add_setting( 'enable_order_bumps_on_side_cart', 'no' );
		SettingsManager::instance()->add_setting( 'side_cart_icon_color', '#222222' );
		SettingsManager::instance()->add_setting( 'side_cart_icon_width', '34' );
		SettingsManager::instance()->add_setting( 'side_cart_icon', 'cart-outline.svg' );
		SettingsManager::instance()->add_setting( 'show_side_cart_item_discount', 'yes' );
	}

	public static function get_cart_icon( string $additional_class = '' ): string {
		ob_start();
		?>
		<div class="cfw-side-cart-quantity-wrap <?php echo $additional_class; ?>">
			<?php echo self::get_cart_icon_file_contents(); ?>

			<?php echo self::get_quantity(); ?>
		</div>
		<?php

		return ob_get_clean();
	}

	public static function get_quantity(): string {
		$quantity = array_sum( wp_list_pluck( WC()->cart->get_cart(), 'quantity' ) );

		ob_start();
		?>
		<div class="cfw-side-cart-quantity <?php echo 0 === $quantity ? 'cfw-hidden' : ''; ?>">
			<?php echo $quantity; ?>
		</div>
		<?php

		return ob_get_clean();
	}

	public static function get_cart_icon_file_contents(): string {
		$filename = SettingsManager::instance()->get_setting( 'side_cart_icon' );
		$path     = CFW_PATH . '/assets/images/cart-icons/' . $filename;

		if ( ! file_exists( $path ) ) {
			return '';
		}

		return file_get_contents( $path );
	}

	public function output_cart_edit_item_quantity_control( array $cart_item, string $cart_item_key, ItemInterface $item ) {
		echo cfw_get_cart_item_quantity_control( $cart_item, $cart_item_key, $item->get_product() );
	}
}
