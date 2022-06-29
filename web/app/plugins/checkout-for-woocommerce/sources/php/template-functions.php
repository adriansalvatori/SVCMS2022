<?php

use Objectiv\Plugins\Checkout\AddressFieldsAugmenter;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * Takes a callable and excutes it, then returns the content inside
 * a row / max width column
 *
* @param callback $callable
*/
function cfw_auto_wrap( callable $callable ) {
	if ( is_callable( $callable ) ) {
		ob_start();

		call_user_func( $callable );

		$func_output = ob_get_clean();

		if ( ! empty( $func_output ) ) {
			$output  = '<div class="row">';
			$output .= '<div class="col-12">';

			$output .= $func_output;

			$output .= '</div>';
			$output .= '</div>';

			echo $output;
		}
	}
}

/**
 * Thank you page section wrap open
 *
 * @param $class
 */
function cfw_thank_you_section_start( $class ) {
	echo "<section class=\"{$class}\">";
}

/**
 * Thank you page section wrapper
 *
* @param callable $callable
* @param $class
* @param array $parameters
*/
function cfw_thank_you_section_auto_wrap( callable $callable, $class, $parameters = array() ) {
	if ( is_callable( $callable ) ) {
		ob_start();

		call_user_func( $callable, ...$parameters );

		$func_output = ob_get_clean();

		if ( ! empty( $func_output ) ) {
			$output  = "<section class=\"{$class}\">";
			$output .= '<div class="inner">';

			$output .= $func_output;

			$output .= '</div>';
			$output .= '</section>';

			echo $output;
		}
	}
}

/**
 * The mobile cart summary header
 *
 * Includes cart total and button to expand the cart summary
 *
 * @param bool $total
 */
function cfw_cart_summary_mobile_header( $total = false ) {
	?>
	<div id="cfw-mobile-cart-header">
		<div class="cfw-display-table cfw-w100">
			<a id="cfw-expand-cart" class="cfw-display-table-row">
				<span class="cfw-cart-icon cfw-display-table-cell">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
				</span>

				<span class="cfw-cart-summary-label-show cfw-small cfw-display-table-cell">
					<span>
						<?php if ( ! empty( $cart_summary_mobile_label = SettingsManager::instance()->get_setting( 'cart_summary_mobile_label' ) ) ) : ?>
							<?php echo $cart_summary_mobile_label; ?>
						<?php else : ?>
							<?php
							/**
							* Filters show order summary link label
							*
							* @param string $show_order_summary_label The show order summary link label
							* @since 2.0.0
							*/
							echo apply_filters( 'cfw_show_order_summary_link_text', esc_html__( 'Show order summary', 'checkout-wc' ) );
							?>
						<?php endif; ?>
					</span>

					<svg width="11" height="6" xmlns="http://www.w3.org/2000/svg" class="cfw-arrow" fill="#000"><path d="M.504 1.813l4.358 3.845.496.438.496-.438 4.642-4.096L9.504.438 4.862 4.534h.992L1.496.69.504 1.812z"></path></svg>
				</span>

				<span class="cfw-cart-summary-label-hide cfw-small cfw-display-table-cell">
					<span>
						<?php
						/**
						 * Filters hide order summary link label
						 *
						 * @param $hide_order_summary_label
						 * @since 3.0.0
						 */
						echo apply_filters( 'cfw_show_order_summary_hide_link_text', esc_html__( 'Hide order summary', 'checkout-wc' ) );
						?>
					</span>

					<svg width="11" height="6" xmlns="http://www.w3.org/2000/svg" class="cfw-arrow" fill="#000"><path d="M.504 1.813l4.358 3.845.496.438.496-.438 4.642-4.096L9.504.438 4.862 4.534h.992L1.496.69.504 1.812z"></path></svg>
				</span>

				<span id="cfw-mobile-total" class="total amount cfw-display-table-cell">
					<?php echo empty( $total ) ? WC()->cart->get_total() : $total; ?>
				</span>
			</a>
		</div>
	</div>
	<?php
}

/**
 * Helper function to output a close div tag
 */
function cfw_close_cart_summary_div() {
	/**
	 * Fires after cart summary before closing </div> tag
	 *
	 * @since 3.0.0
	 */
	do_action( 'cfw_after_cart_summary' );
	?>
	</div>
	<?php
}

/**
 * The opening div tag for the cart summary content
 */
function cfw_cart_summary_content_open_wrap() {
	?>
	<div id="cfw-cart-summary-content">
	<?php
}

/**
 * Handles WooCommerce before order review hooks
 *
 * This hook is in a different place on our checkout so
 * we have to wrap it with an ID and apply styles similar to native
 */
function cfw_cart_summary_before_order_review() {
	?>
	<div id="cfw-checkout-before-order-review">
		<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
	</div>
	<?php
}

/**
 * Handles WooCommerce after order review hooks
 *
 * This hook is in a different place on our checkout so
 * we have to wrap it with an ID and apply styles similar to native
 */
function cfw_cart_summary_after_order_review() {
	?>
	<div id="cfw-checkout-after-order-review">
		<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
	</div>
	<?php
}

/**
 * Print WooCommerce notices with placeholder div for JS behaviors
 */
function cfw_wc_print_notices() {
	$all_notices  = WC()->session->get( 'wc_notices', array() );
	$notice_types = apply_filters( 'woocommerce_notice_types', array( 'error', 'success', 'notice' ) );
	$notices      = array();

	foreach ( $notice_types as $notice_type ) {
		if ( wc_notice_count( $notice_type ) > 0 ) {
			$notices[ $notice_type ] = $all_notices[ $notice_type ];
		}
	}

	$type_class_mapping = array(
		'error'   => 'cfw-alert-error',
		'notice'  => 'cfw-alert-info',
		'success' => 'cfw-alert-success',
	);

	$used_alert_ids = array();

	wc_clear_notices();

	// DO NOT REMOVE PLACEHOLDER BELOW
	// It is a template for new alerts
	?>
	<div id="cfw-alert-placeholder">
		<div class="cfw-alert">
			<div class="message"></div>
		</div>
	</div>

	<div id="cfw-alert-container" class="woocommerce-notices-wrapper">
		<?php if ( ! empty( $notices ) ) : ?>
			<?php foreach ( $notices as $type => $messages ) : ?>
				<?php
				foreach ( $messages as $message ) :
					// In WooCommerce 3.9+, messages can be an array with two properties:
					// - notice
					// - data
					$message  = $message['notice'] ?? $message;
					$alert_id = md5( $message . $type_class_mapping[ $type ] . $type );

					if ( in_array( $alert_id, $used_alert_ids, true ) || empty( $message ) ) {
						continue;
					}
					?>

					<?php $used_alert_ids[] = $alert_id; ?>
					<div class="cfw-alert <?php echo $type_class_mapping[ $type ]; ?> cfw-alert-<?php echo $alert_id; ?>">
						<div class="message">
							<?php echo $message; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Notices with wrap
 */
function cfw_wc_print_notices_with_wrap() {
	cfw_auto_wrap( 'cfw_wc_print_notices' );
}

/**
 * Payment Request buttons (aka Express Checkout)
 */
function cfw_payment_request_buttons() {
	if ( ! has_action( 'cfw_payment_request_buttons' ) ) {
		return;
	}
	?>
	<div id="cfw-payment-request-buttons" style="position: absolute; visibility: hidden">
		<h2><?php _e( 'Express checkout', 'checkout-wc' ); ?></h2>
		<?php
		/**
		 * Hook for adding payment request buttons
		 *
		 * @since 3.0.0
		 */
		do_action( 'cfw_payment_request_buttons' );
		?>
	</div>
	<?php
	/**
	 * Hook for adding payment request buttons separator
	 *
	 * @since 7.0.0
	 */
	do_action( 'cfw_after_payment_request_buttons' );
}

/**
 * Customer information tab heading
 */
function cfw_customer_info_tab_heading() {
	?>
	<h3>
		<?php
		/**
		 * Filters customer info tab heading
		 *
		 * @param $customer_info_heading string Customer info tab heading
		 * @since 2.0.0
		 */
		echo apply_filters( 'cfw_customer_information_heading', __( 'Information', 'checkout-wc' ) );
		?>
	</h3>
	<?php
}

/**
 * Customer information tab heading
 */
function cfw_order_review_tab_heading() {
	?>
	<h3>
		<?php
		/**
		 * Filters order review tab heading
		 *
		 * @param $order_review_tab_heading string Order review tab heading
		 * @since 2.0.0
		 */
		echo apply_filters( 'cfw_order_review_tab_heading', __( 'Order review', 'checkout-wc' ) );
		?>
	</h3>
	<?php
}

function cfw_customer_info_tab_account() {
	?>
	<div id="cfw-account-details" class="cfw-module">
		<?php
		/**
		 * Fires at the start of login module container
		 *
		 * @since 3.0.0
		 */
		do_action_deprecated( 'cfw_before_customer_info_tab_login', array(), '7.0.0', 'cfw_before_customer_info_account_details' );

		/**
		 * Fires before account details on customer info tab
		 *
		 * @since 7.0.0
		 */
		do_action( 'cfw_before_customer_info_account_details' );

		cfw_maybe_show_already_have_an_account_text();
		cfw_maybe_show_email_field();
		cfw_account_password_field_slide();
		cfw_create_account_checkbox();
		cfw_maybe_show_welcome_back_text();

		/**
		 * Fires before account details on customer info tab
		 *
		 * @since 7.0.0
		 */
		do_action( 'cfw_after_customer_info_account_details' );
		?>
	</div>
	<?php
}

function cfw_customer_info_tab_account_fields() {
	cfw_output_account_checkout_fields( WC()->checkout() );
}

function cfw_maybe_show_already_have_an_account_text() {
	if ( ! cfw_is_login_at_checkout_allowed() ) {
		return;
	}

	if ( is_user_logged_in() ) {
		return;
	}
	?>
	<div class="cfw-have-acc-text cfw-small <?php echo WC()->checkout()->is_registration_required() ? 'account-does-not-exist-text' : ''; ?>">
		<?php
		/**
		 * Fires before enhanced login prompt
		 *
		 * @since 3.0.0
		 */
		do_action( 'cfw_before_enhanced_login_prompt' );
		?>

		<span>
			<?php
			/**
			 * Filters already have account text
			 *
			 * @param string $already_have_account_text Already have an account text
			 * @since 2.0.0
			 */
			echo apply_filters( 'cfw_already_have_account_text', esc_html__( 'Already have an account with us?', 'checkout-wc' ) );
			?>
		</span>

		<a id="cfw-login-modal-trigger" href="javascript:">
			<?php
			/**
			 * Filters login faster text
			 *
			 * @param string $login_faster_text Login faster text
			 * @since 2.0.0
			 */
			echo apply_filters( 'cfw_login_faster_text', esc_html__( 'Log in for a faster checkout experience.', 'checkout-wc' ) );
			?>
		</a>

		<?php
		/**
		 * Fires after enhanced login prompt
		 *
		 * @since 2.0.0
		 */
		do_action( 'cfw_after_enhanced_login_prompt' );
		?>
	</div>

	<?php if ( WC()->checkout()->is_registration_required() ) : ?>
	<div class="cfw-have-acc-text cfw-small account-exists-text">
		<span>
			<?php echo apply_filters( 'woocommerce_registration_error_email_exists', cfw__( 'An account is already registered with your email address. <a href="#" class="showlogin">Please log in.</a>', 'woocommerce' ), '' ); ?>
		</span>
	</div>
	<?php endif; ?>
	<?php
}

function cfw_maybe_show_email_field() {
	if ( is_user_logged_in() ) {
		// We aren't using woocommerce_form_field here because WooCommerce Wholesale Lead Capture is evil
		printf( '<input type="hidden" name="billing_email" id="billing_email" value="%s">', wp_get_current_user()->user_email );
		return;
	}

	$billing_fields = WC()->checkout()->get_checkout_fields( 'billing' );
	$email_field    = $billing_fields['billing_email'];
	$value          = WC()->checkout()->get_value( 'billing_email' );

	woocommerce_form_field( 'billing_email', $email_field, $value );

	if ( is_user_logged_in() ) {
		return;
	}

	/**
	 * Fires after email field output
	 *
	 * @since 3.0.0
	 */
	do_action( 'cfw_checkout_after_email' );
}

function cfw_account_password_field_slide() {
	if ( is_user_logged_in() || ! WC()->checkout()->is_registration_enabled() ) {
			return;
	}
	?>
	<div id="cfw-account-password-slide" class="cfw-input-wrap-row">
		<?php
		/**
		 * Filters whether to automatically generate password for new accounts
		 *
		 * @param string $generate_password Automatically generate password for new accounts
		 * @since 2.0.0
		 */
		if ( ! apply_filters( 'cfw_registration_generate_password', SettingsManager::instance()->get_setting( 'registration_style' ) !== 'woocommerce' ) ) :
			woocommerce_form_field(
				'account_password',
				array(
					'type'              => 'password',
					'label'             => cfw__( 'Create account password', 'woocommerce' ),
					'required'          => true,
					'placeholder'       => cfw__( 'Create account password', 'woocommerce' ),
					'custom_attributes' => array(
						'data-parsley-trigger' => 'keyup change focusout',
					),
					'autocomplete'      => 'new-password',
				)
			);
		endif;
		?>
	</div>
	<?php
}

function cfw_create_account_checkbox() {
	if ( is_user_logged_in() || ! WC()->checkout()->is_registration_enabled() ) {
		return;
	}
	?>
	<div class="cfw-input-wrap cfw-check-input">
		<?php if ( ! WC()->checkout()->is_registration_required() && WC()->checkout()->is_registration_enabled() ) : ?>
			<input type="checkbox" id="createaccount" class="cfw-create-account-checkbox" name="createaccount" />
			<label class="cfw-small" for="createaccount">
				<?php
				/**
				 * Filters create account checkbox site name
				 *
				 * @param string $create_account_site_name Create account checkbox site name
				 * @since 2.0.0
				 */
				$create_account_site_name = apply_filters( 'cfw_create_account_site_name', get_bloginfo( 'name' ) );

				/**
				 * Filters create account checkbox label
				 *
				 * @param string $create_account_checkbox_label Create account checkbox label
				 * @since 2.0.0
				 */
				// translators: %s: site name
				printf( apply_filters( 'cfw_create_account_checkbox_label', esc_html__( 'Create %s shopping account.', 'checkout-wc' ) ), $create_account_site_name );
				?>
			</label>
		<?php elseif ( WC()->checkout()->is_registration_required() ) : ?>
			<span class="cfw-small account-does-not-exist-text">
				<?php
				/**
				 * Filters create account statement
				 *
				 * @param string $create_account_statement Create account statement
				 * @since 2.0.0
				 */
				echo apply_filters( 'cfw_account_creation_statement', esc_html__( 'If you do not have an account, we will create one for you.', 'checkout-wc' ) );
				?>
			</span>
		<?php endif; ?>
	</div>
	<?php
}

function cfw_maybe_show_welcome_back_text() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	?>
	<div class="cfw-have-acc-text cfw-small">
		<?php
		/**
		 * Filters welcome back statement customer name
		 *
		 * @param string $welcome_back_name Welcome back statement customer name
		 * @since 2.0.0
		 */
		$welcome_back_name = apply_filters( 'cfw_welcome_back_name', wp_get_current_user()->display_name );

		/**
		 * Filters welcome back statement customer email
		 *
		 * @param string $welcome_back_email Welcome back statement customer email
		 * @since 2.0.0
		 */
		$welcome_back_email = apply_filters( 'cfw_welcome_back_email', wp_get_current_user()->user_email );

		/* translators: %1 is the customer's name, %2 is their email address */
		$welcome_back_text = sprintf( esc_html__( 'Welcome back, %1$s (%2$s).', 'checkout-wc' ), '<strong>' . $welcome_back_name . '</strong>', $welcome_back_email );

		/**
		 * Filters welcome back statement
		 *
		 * @param string $welcome_back_text Welcome back statement
		 * @since 7.1.10
		 */
		echo apply_filters( 'cfw_welcome_back_text', $welcome_back_text, $welcome_back_name, $welcome_back_email );

		/**
		 * Filters whether to show logout link
		 *
		 * @param bool $show_logout_link Show logout link
		 * @since 2.0.0
		 */
		if ( apply_filters( 'cfw_show_logout_link', false ) ) :
			?>
			<a href="<?php echo wp_logout_url( wc_get_checkout_url() ); ?>"><?php _e( 'Log out.', 'checkout-wc' ); ?></a>
		<?php endif; ?>
	</div>
	<?php
}

function cfw_maybe_output_login_modal_container() {
	if ( ! cfw_is_login_at_checkout_allowed() ) {
		return;
	}

	$redirect = wc_get_checkout_url();
	?>
	<div id="cfw_login_modal" style="display: none;">
		<form id="cfw_login_modal_form" class="checkoutwc" method="post">
			<div id="cfw-login-alert-container" class="woocommerce-notices-wrapper"></div>
			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<h3><?php _e( 'Welcome back', 'checkout-wc' ); ?></h3>

			<p class="cfw-mb">
				<span class="account-exists-text">
					<?php esc_html_e( 'It looks like you already have an account. Please enter your login details below.', 'checkout-wc' ); ?>
				</span>
				<span class="account-does-not-exist-text">
					<?php esc_html_e( 'If you have shopped with us before, please enter your login details below.', 'checkout-wc' ); ?>
				</span>
			</p>

			<?php
			woocommerce_form_field(
				'username',
				array(
					'id'                => 'cfw_login_username',
					'label'             => cfw__( 'Username or email address', 'woocommerce' ),
					'placeholder'       => cfw__( 'Username or email address', 'woocommerce' ),
					'type'              => 'text',
					'autocomplete'      => 'username',
					'required'          => true,
					'custom_attributes' => array( 'data-parsley-trigger' => 'change focusout' ),
				),
				WC()->checkout()->get_value( 'username' )
			);

			woocommerce_form_field(
				'password',
				array(
					'id'                => 'cfw_login_password',
					'label'             => cfw__( 'Password', 'woocommerce' ),
					'placeholder'       => cfw__( 'Password', 'woocommerce' ),
					'type'              => 'password',
					'autocomplete'      => 'current-password',
					'required'          => true,
					'custom_attributes' => array( 'data-parsley-trigger' => 'change focusout' ),
				)
			);

			do_action( 'woocommerce_login_form' );

			wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' );
			?>
			<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ); ?>" />

			<div class="cfw-login-modal-footer">
				<p class="form-row">
					<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
						<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php cfw_esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
					</label>
				</p>
				<p class="lost_password">
					<?php echo apply_filters( 'cfw_login_modal_last_password_link', sprintf( '<a id="cfw_lost_password_trigger" href="#cfw_lost_password_form_wrap" class="cfw-small">%s</a>', cfw_esc_html__( 'Lost your password?', 'woocommerce' ) ) ); ?>
				</p>
			</div>

			<div class="cfw-login-modal-navigation">
				<button type="submit" id="cfw-login-btn" class="cfw-primary-btn" name="login" value="<?php cfw_esc_attr_e( 'Login', 'woocommerce' ); ?>"><?php cfw_esc_html_e( 'Login', 'woocommerce' ); ?></button>

				<?php if ( ! WC()->checkout()->is_registration_required() ) : ?>
					<a id="cfw_login_modal_close" href="javascript:"><?php _e( 'Or continue as guest', 'checkout-wc' ); ?></a>
				<?php endif; ?>
			</div>

			<?php do_action( 'woocommerce_login_form_end' ); ?>
		</form>
	</div>
	<?php
}

/**
 * The address displayed on the Customer Info tab
 */
function cfw_customer_info_address() {
	/**
	 * Fires before customer info address module
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_checkout_before_customer_info_address' );
	?>

	<div id="cfw-customer-info-address" class="cfw-module">
		<?php
		if ( WC()->cart->needs_shipping_address() ) {
			/**
			 * Fires before shipping address
			 *
			 * @since 2.0.0
			 */
			do_action( 'cfw_checkout_before_shipping_address' );
		} else {
			/**
			 * Fires before billing address
			 *
			 * @since 2.0.0
			 */
			do_action( 'cfw_checkout_before_billing_address' );
		}
		?>

		<h3>
			<?php if ( wc_ship_to_billing_address_only() && WC()->cart->needs_shipping() ) : ?>
				<?php
				/**
				 * Filters billing and shipping address heading
				 *
				 * @param string $billing_and_shipping_address_heading Billing and shipping address heading
				 * @since 2.0.0
				 */
				echo apply_filters( 'cfw_billing_shipping_address_heading', esc_html__( 'Billing and Shipping address', 'checkout-wc' ) );
				?>
			<?php elseif ( ! WC()->cart->needs_shipping() ) : ?>
				<?php
				/**
				 * Filters billing address heading
				 *
				 * @param string $billing_address_heading Billing address heading
				 * @since 2.0.0
				 */
				echo apply_filters( 'cfw_billing_address_heading', esc_html__( 'Billing address', 'checkout-wc' ) );
				?>
			<?php else : ?>
				<?php
				/**
				 * Filters shipping address heading
				 *
				 * @param string $shipping_address_heading Shipping address heading
				 * @since 2.0.0
				 */
				echo apply_filters( 'cfw_shipping_address_heading', esc_html__( 'Shipping address', 'checkout-wc' ) );
				?>
			<?php endif; ?>
		</h3>

		<?php
		/**
		 * Fires after customer info address heading
		 *
		 * @since 2.0.0
		 */
		do_action( 'cfw_after_customer_info_address_heading' );

		if ( WC()->cart->needs_shipping() ) {
			/**
			 * Fires after customer info address shipping heading
			 *
			 * @since 4.0.4
			 */
			do_action( 'cfw_after_customer_info_shipping_address_heading' );
		} else {
			/**
			 * Fires after customer info address billing heading
			 *
			 * @since 4.0.4
			 */
			do_action( 'cfw_after_customer_info_billing_address_heading' );
		}
		?>

		<div class="cfw-customer-info-address-container cfw-parsley-shipping-details <?php cfw_address_class_wrap( WC()->cart->needs_shipping() ); ?>">
			<?php if ( ! WC()->cart->needs_shipping() || wc_ship_to_billing_address_only() ) : ?>
				<?php
				/**
				 * Fires before billing address inside billing address container
				 *
				 * @since 4.0.4
				 */
				do_action( 'cfw_start_billing_address_container' );

				cfw_output_billing_checkout_fields( WC()->checkout() );

				/**
				 * Fires before billing address inside billing address container
				 *
				 * @since 4.0.4
				 */
				do_action( 'cfw_end_billing_address_container' );
				?>
			<?php else : ?>
				<?php
				/**
				 * Fires before shipping address inside shipping address container
				 *
				 * @since 4.0.4
				 */
				do_action( 'cfw_start_shipping_address_container' );

				cfw_output_shipping_checkout_fields( WC()->checkout() );

				/**
				 * Fires after shipping address inside shipping address container
				 *
				 * @since 4.0.4
				 */
				do_action( 'cfw_end_shipping_address_container' );
				?>
			<?php endif; ?>
		</div>

		<?php
		if ( WC()->cart->needs_shipping() ) {
			/**
			 * Fires after shipping address
			 *
			 * @since 2.0.0
			 */
			do_action( 'cfw_checkout_after_shipping_address' );
		} else {
			/**
			 * Fires after billing address
			 *
			 * @since 2.0.0
			 */
			do_action( 'cfw_checkout_after_billing_address' );
		}
		?>
	</div>

	<?php
	/**
	 * Fires at the bottom of customer info address module after closing </div>
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_checkout_after_customer_info_address' );
}

/**
 * @return bool
 */
function cfw_show_shipping_tab():bool {
	/**
	 * Filters whether to show shipping tab
	 *
	 * @param string $show_shipping_tab Show shipping tab
	 * @since 2.0.0
	 */
	return apply_filters( 'cfw_show_shipping_tab', WC()->cart->needs_shipping() && SettingsManager::instance()->get_setting( 'skip_shipping_step' ) !== 'yes' ) === true;
}

/**
 * @return bool
 */
function cfw_show_shipping_total():bool {
	/**
	 * Filters whether to show shipping total
	 *
	 * @param string $show_shipping_total Show shipping total
	 * @since 2.0.0
	 */
	return apply_filters( 'cfw_show_shipping_total', WC()->cart->needs_shipping() && wc_shipping_enabled() && WC()->cart->get_cart_contents() && count( WC()->shipping()->get_packages() ) > 0 ) === true;
}

/**
 * Customer information tab nav
 *
 * Includes return to cart and next tab buttons
 */
function cfw_customer_info_tab_nav() {
	/**
	 * Fires before customer info tab navigation container
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_checkout_before_customer_info_tab_nav' );
	?>

	<div id="cfw-customer-info-action" class="cfw-bottom-controls">
		<div class="previous-button">
			<?php cfw_return_to_cart_link(); ?>
		</div>

		<?php cfw_continue_to_shipping_button(); ?>
		<?php cfw_continue_to_payment_button(); ?>
	</div>

	<?php
	/**
	 * Fires after customer info tab navigation container
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_checkout_after_customer_info_tab_nav' );
}

/**
 * Customer information tab nav
 *
 * Includes return to cart and next tab buttons
 * @param bool $show_cart_return_link
 */
function cfw_payment_method_tab_review_nav( $show_cart_return_link = false ) {
	/**
	 * Fires before payment method tab navigation container
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_checkout_before_payment_method_tab_nav' );

	$show_customer_information_tab = cfw_show_customer_information_tab();
	?>

	<div id="cfw-payment-method-action" class="cfw-bottom-controls">
		<div class="previous-button">
			<?php if ( $show_cart_return_link ) : ?>
				<?php cfw_return_to_cart_link(); ?>
			<?php elseif ( $show_customer_information_tab ) : ?>
				<?php cfw_return_to_customer_information_link(); ?>
			<?php endif; ?>

			<?php cfw_return_to_shipping_method_link(); ?>
		</div>

		<?php cfw_continue_to_order_review_button(); ?>
	</div>

	<?php
	/**
	 * Fires after payment method tab navigation container
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_checkout_after_payment_method_tab_nav' );
}

/**
 * Shipping method tab address review section
 */
function cfw_shipping_method_address_review_pane() {
	if ( ! wc_ship_to_billing_address_only() ) {
		$ship_to_label = __( 'Ship to', 'checkout-wc' );
	} else {
		$ship_to_label = cfw__( 'Address', 'woocommerce' );
	}

	/**
	 * Filters ship to label in review pane
	 *
	 * @param string $ship_to_label Ship to label
	 * @since 3.0.0
	 */
	$ship_to_label = apply_filters( 'cfw_ship_to_label', $ship_to_label );

	$long_class = '';

	if ( strlen( $ship_to_label ) > 9 ) {
		$long_class = ' shipping-details-label-long';
	}
	?>
	<ul class="cfw-review-pane cfw-module">
		<li>
			<div class="col-10 inner">
				<div role="rowheader" class="cfw-review-pane-label<?php echo $long_class; ?>">
					<?php _e( 'Contact', 'checkout-wc' ); ?>
				</div>

				<div role="cell" class="cfw-review-pane-content cfw-review-pane-contact-value"></div>
			</div>

			<div role="cell" class="col-2 cfw-review-pane-link">
				<?php if ( ! is_user_logged_in() ) : ?>
					<a href="javascript:" data-tab="#cfw-customer-info" class="cfw-tab-link cfw-small"><?php esc_html_e( 'Change', 'checkout-wc' ); ?></a>
				<?php endif; ?>
			</div>
		</li>

		<?php if ( WC()->cart->needs_shipping() ) : ?>
			<li>
				<div class="col-10 inner">
					<div role="rowheader" class="cfw-review-pane-label<?php echo $long_class; ?>">
						<?php echo $ship_to_label; ?>
					</div>

					<div role="cell" class="cfw-review-pane-content cfw-review-pane-shipping-address-value"></div>
				</div>

				<div role="cell" class="col-2 cfw-review-pane-link">
					<a href="javascript:" data-tab="#cfw-customer-info" class="cfw-tab-link cfw-small"><?php esc_html_e( 'Change', 'checkout-wc' ); ?></a>
				</div>
			</li>
		<?php endif; ?>
	</ul>
	<?php
}

/**
 * Payment method tab address review section
 */
function cfw_payment_method_address_review_pane() {
	if ( ! wc_ship_to_billing_address_only() ) {
		$ship_to_label = __( 'Ship to', 'checkout-wc' );
	} else {
		$ship_to_label = cfw__( 'Address', 'woocommerce' );
	}

	/**
	 * Filters ship to label in review pane
	 *
	 * @param string $ship_to_label Ship to label
	 * @since 3.0.0
	 */
	$ship_to_label = apply_filters( 'cfw_ship_to_label', $ship_to_label );

	$long_class = '';

	if ( strlen( $ship_to_label ) > 9 ) {
		$long_class = ' shipping-details-label-long';
	}
	?>
	<ul class="cfw-review-pane cfw-module">
		<li>
			<div class="inner col-10">
				<div role="rowheader" class="cfw-review-pane-label<?php echo $long_class; ?>">
					<?php _e( 'Contact', 'checkout-wc' ); ?>
				</div>

				<div role="cell" class="cfw-review-pane-content cfw-review-pane-contact-value"></div>
			</div>

			<div role="cell" class="col-2 cfw-review-pane-link">
				<?php if ( ! is_user_logged_in() ) : ?>
					<a href="javascript:" data-tab="#cfw-customer-info" class="cfw-tab-link cfw-small"><?php esc_html_e( 'Change', 'checkout-wc' ); ?></a>
				<?php endif; ?>
			</div>
		</li>

		<?php if ( WC()->cart->needs_shipping() ) : ?>
			<li>
				<div class="inner col-10">
					<div role="rowheader" class="cfw-review-pane-label<?php echo $long_class; ?>">
						<?php echo $ship_to_label; ?>
					</div>

					<div role="cell" class="cfw-review-pane-content cfw-review-pane-shipping-address-value"></div>
				</div>

				<div role="cell" class="col-2 cfw-review-pane-link">
					<a href="javascript:" data-tab="#cfw-customer-info" class="cfw-tab-link cfw-small"><?php esc_html_e( 'Change', 'checkout-wc' ); ?></a>
				</div>
			</li>

			<li class="shipping-method">
				<div class="inner col-10">
					<div role="rowheader" class="cfw-review-pane-label<?php echo $long_class; ?>">
						<?php _e( 'Method', 'checkout-wc' ); ?>
					</div>

					<div role="cell" class="cfw-review-pane-content cfw-review-pane-shipping-method-value"></div>
				</div>

				<div role="cell" class="col-2 cfw-review-pane-link">
					<?php if ( cfw_show_shipping_tab() ) : ?>
						<a href="javascript:" data-tab="#cfw-shipping-method" class="cfw-tab-link cfw-small"><?php esc_html_e( 'Change', 'checkout-wc' ); ?></a>
					<?php endif; ?>
				</div>
			</li>
		<?php endif; ?>
	</ul>
	<?php
}

function cfw_order_review_step_review_pane() {
	if ( ! wc_ship_to_billing_address_only() ) {
		$ship_to_label = __( 'Ship to', 'checkout-wc' );
	} else {
		$ship_to_label = cfw__( 'Address', 'woocommerce' );
	}

	/**
	 * Filters ship to label in review pane
	 *
	 * @param string $ship_to_label Ship to label
	 * @since 3.0.0
	 */
	$ship_to_label = apply_filters( 'cfw_ship_to_label', $ship_to_label );

	$long_class = '';

	if ( strlen( $ship_to_label ) > 9 ) {
		$long_class = ' shipping-details-label-long';
	}
	?>
	<ul class="cfw-review-pane cfw-module">
		<li>
			<div class="inner col-10">
				<div role="rowheader" class="cfw-review-pane-label<?php echo $long_class; ?>">
					<?php _e( 'Contact', 'checkout-wc' ); ?>
				</div>

				<div role="cell" class="cfw-review-pane-content cfw-review-pane-contact-value"></div>
			</div>

			<div role="cell" class="col-2 cfw-review-pane-link">
				<?php if ( ! is_user_logged_in() ) : ?>
					<a href="javascript:" data-tab="#cfw-customer-info" class="cfw-tab-link cfw-small"><?php esc_html_e( 'Change', 'checkout-wc' ); ?></a>
				<?php endif; ?>
			</div>
		</li>

		<?php if ( WC()->cart->needs_shipping() ) : ?>
			<li>
				<div class="inner col-10">
					<div role="rowheader" class="cfw-review-pane-label<?php echo $long_class; ?>">
						<?php echo $ship_to_label; ?>
					</div>

					<div role="cell" class="cfw-review-pane-content cfw-review-pane-shipping-address-value"></div>
				</div>

				<div role="cell" class="col-2 cfw-review-pane-link">
					<a href="javascript:" data-tab="#cfw-customer-info" class="cfw-tab-link cfw-small"><?php esc_html_e( 'Change', 'checkout-wc' ); ?></a>
				</div>
			</li>

			<li class="shipping-method">
				<div class="inner col-10">
					<div role="rowheader" class="cfw-review-pane-label<?php echo $long_class; ?>">
						<?php _e( 'Method', 'checkout-wc' ); ?>
					</div>

					<div role="cell" class="cfw-review-pane-content cfw-review-pane-shipping-method-value"></div>
				</div>

				<div role="cell" class="col-2 cfw-review-pane-link">
					<?php if ( cfw_show_shipping_tab() ) : ?>
						<a href="javascript:" data-tab="#cfw-shipping-method" class="cfw-tab-link cfw-small"><?php esc_html_e( 'Change', 'checkout-wc' ); ?></a>
					<?php endif; ?>
				</div>
			</li>
		<?php endif; ?>

		<li class="cfw-review-pane-payment-row">
			<div class="inner col-10">
				<div role="rowheader" class="cfw-review-pane-label<?php echo $long_class; ?>">
					<?php _e( 'Payment', 'checkout-wc' ); ?>
				</div>

				<div role="cell" class="cfw-review-pane-content cfw-review-pane-payment-method-value"></div>
			</div>

			<div role="cell" class="col-2 cfw-review-pane-link">
				<a href="javascript:" data-tab="#cfw-payment-method" class="cfw-tab-link cfw-small"><?php esc_html_e( 'Change', 'checkout-wc' ); ?></a>
			</div>
		</li>
	</ul>
	<?php
}

/**
* @return string
 */
function cfw_get_review_pane_payment_method(): string {
	if ( WC()->cart->needs_payment() ) {
		$available_payment_methods = WC()->payment_gateways()->get_available_payment_gateways();

		$title = $available_payment_methods[ WC()->session->get( 'chosen_payment_method' ) ]->title ?? '';
	} else {
		$title = cfw__( 'Free', 'woocommerce' );
	}

	if ( $title ) {
		$title .= '<p class="cfw-small cfw-padding-top cfw-light-gray">' . cfw_get_review_pane_billing_address( WC()->checkout() ) . '</p>';
	}

	return $title;
}

function cfw_order_review_step_totals_review_pane() {
	?>
	<ul class="cfw-review-pane cfw-module" id="cfw-review-order-totals">
		<li>
			<div class="inner cfw-no-border">
				<div role="rowheader" class="cfw-review-pane-lab">
					<?php cfw_e( 'Subtotal', 'woocommerce' ); ?>
				</div>
			</div>

			<div role="cell" class="cfw-review-pane-content cfw-review-pane-right cfw-no-border">
				<?php wc_cart_totals_subtotal_html(); ?>
			</div>
		</li>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
		<li>
			<div class="inner cfw-no-border">
				<div role="rowheader" class="cfw-review-pane-label">
					<?php wc_cart_totals_coupon_label( $coupon ); ?>
				</div>
			</div>
			<div role="cell" class="cfw-review-pane-content cfw-review-pane-right cfw-no-border">
				<?php wc_cart_totals_coupon_html( $coupon ); ?>
			</div>
		</li>
		<?php endforeach; ?>

		<?php if ( cfw_show_shipping_total() ) : ?>
			<?php cfw_order_review_pane_shipping_totals(); ?>
		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
		<li>
			<div class="inner cfw-no-border">
				<div role="rowheader" class="cfw-review-pane-label">
					<?php echo esc_html( $fee->name ); ?>
				</div>
			</div>
			<div role="cell" class="cfw-review-pane-content cfw-review-pane-right cfw-no-border">
				<?php wc_cart_totals_fee_html( $fee ); ?>
			</div>
		</li>
		<?php endforeach; ?>
		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
					<li>
						<div class="inner">
							<div role="rowheader" class="cfw-review-pane-label">
								<?php echo esc_html( $tax->label ); ?>
							</div>
						</div>
						<div role="cell" class="cfw-review-pane-content cfw-review-pane-right">
							<?php echo wp_kses_post( $tax->formatted_amount ); ?>
						</div>
					</li>
				<?php endforeach; ?>
			<?php else : ?>
				<li>
					<div class="inner">
						<div role="rowheader" class="cfw-review-pane-label">
							<?php echo esc_html( WC()->countries->tax_or_vat() ); ?>
						</div>
					</div>
					<div role="cell" class="cfw-review-pane-content cfw-review-pane-right">
						<?php wc_cart_totals_taxes_total_html(); ?>
					</div>
				</li>
			<?php endif; ?>
		<?php endif; ?>

		<li>
			<div class="inner">
				<div role="rowheader" class="cfw-order-review-total-label cfw-review-pane-label">
					<?php cfw_e( 'Total', 'woocommerce' ); ?>
				</div>
			</div>
			<div role="cell" class="cfw-review-pane-content cfw-review-pane-right cfw-order-review-total">
				<?php wc_cart_totals_order_total_html(); ?>
			</div>
		</li>
	</ul>
	<?php
}

/**
 * Shipping method tab list of shipping methods
 *
 */
function cfw_shipping_methods() {
	/**
	 * Fires before shipping methods heading
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_checkout_before_shipping_methods' );
	?>
	<h3>
		<?php
		/**
		 * Filters shipping method heading
		 *
		 * @param string $shipping_method_heading Shipping method heading
		 * @since 3.0.0
		 */
		echo apply_filters( 'cfw_shipping_method_heading', esc_html__( 'Shipping method', 'checkout-wc' ) );
		?>
	</h3>

	<?php do_action( 'cfw_after_shipping_method_heading' ); ?>

	<div id="cfw-shipping-methods" class="cfw-module">
		<?php cfw_shipping_methods_html(); ?>
	</div>

	<?php
	/**
	 * Fires after shipping methods
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_checkout_after_shipping_methods' );
}

/**
 * Shipping method tab navigation
 *
 * Includes previous and next tab buttons
 */
function cfw_shipping_method_tab_nav() {
	/**
	 * Fires before shipping method tab navigation container
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_checkout_before_shipping_method_tab_nav' );
	?>

	<div id="cfw-shipping-action" class="cfw-bottom-controls">
		<div class="previous-button">
			<?php cfw_return_to_customer_information_link(); ?>
		</div>

		<?php cfw_continue_to_payment_button(); ?>
	</div>

	<?php
	/**
	 * Fires after shipping method tab navigation container
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_checkout_after_shipping_method_tab_nav' );
}

/**
 * Payment method tab payments list
 *
 * Includes payment method tab heading
 *
 * @param bool $available_gateways
 * @param bool $object
 * @param bool $show_title
 */
function cfw_payment_methods( $available_gateways = false, $object = false, $show_title = true ) {
	echo cfw_get_payment_methods( $available_gateways, $object, $show_title );
}

/**
 * Payment method tab billing address radio group
 */
function cfw_payment_tab_content_billing_address() {
	if ( WC()->cart->needs_shipping_address() ) :
		?>
		<h3 class="cfw-billing-address-heading">
			<?php
			/**
			 * Filters billing address heading on payment method tab
			 *
			 * @param string $billing_address_heading Billing address heading on payment method tab
			 * @since 3.0.0
			 */
			echo apply_filters( 'cfw_billing_address_heading', esc_html__( 'Billing address', 'checkout-wc' ) );
			?>
		</h3>

		<?php
		/**
		 * Fires after the billing address heading on the payment tab
		 *
		 * @since 5.3.2
		 */
		do_action( 'cfw_after_payment_information_address_heading' );
		?>

		<h4 class="cfw-billing-address-description cfw-small">
			<?php
			/**
			 * Filters billing address description
			 *
			 * @param string $billing_address_description Billing address description
			 * @since 3.0.0
			 */
			echo apply_filters( 'cfw_billing_address_description', esc_html__( 'Select the address that matches your card or payment method.', 'checkout-wc' ) );
			?>
		</h4>

		<?php cfw_billing_address_radio_group(); ?>
	<?php endif; ?>

	<?php
	/**
	 * Fires after payment method tab billing address
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_checkout_after_payment_tab_billing_address' );
}

/**
 * Payment method tab order notes
 *
 * This also handles any custom fields attached to order notes area
 */
function cfw_payment_tab_content_order_notes() {
	?>
	<div class="cfw-order-notes-container">
		<?php do_action( 'woocommerce_before_order_notes', WC()->checkout() ); ?>

		<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', false ) ) : ?>

			<div class="cfw-order-notes-wrap">
				<?php do_action( 'cfw_output_fieldset', WC()->checkout()->get_checkout_fields( 'order' ) ); ?>
			</div>

		<?php endif; ?>

		<div class="clear"></div>

		<?php do_action( 'woocommerce_after_order_notes', WC()->checkout() ); ?>
	</div>
	<?php
}

/**
 * Payment method tab terms and conditions
 */
function cfw_payment_tab_content_terms_and_conditions() {
	/**
	 * Fires before payment method terms and conditions output
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_checkout_before_payment_method_terms_checkbox' );

	wc_get_template( 'checkout/terms.php' );
}

/**
 * Payment method tab nav
 *
 * Includes previous tab and place order buttons
 *
 * @param bool $show_cart_return_link
 */
function cfw_payment_tab_nav( $show_cart_return_link = false ) {
	/**
	 * Fires before payment method tab navigation container
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_checkout_before_payment_method_tab_nav' );
	do_action( 'woocommerce_review_order_before_submit' );

	$show_customer_information_tab = cfw_show_customer_information_tab();
	?>

	<div id="cfw-payment-action" class="cfw-bottom-controls">
		<div class="previous-button">
			<?php if ( $show_cart_return_link ) : ?>
				<?php cfw_return_to_cart_link(); ?>
			<?php elseif ( $show_customer_information_tab ) : ?>
				<?php cfw_return_to_customer_information_link(); ?>
			<?php endif; ?>

			<?php cfw_return_to_shipping_method_link(); ?>
		</div>

		<div class="cfw-place-order-wrap">
			<?php do_action( 'cfw_payment_nav_place_order_button' ); ?>
		</div>
	</div>

	<?php
	/**
	 * Fires after payment method tab navigation container
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_checkout_after_payment_method_tab_nav' );
}

/**
 * Order review tab nav
 *
 * Includes previous tab and place order buttons
 */
function cfw_order_review_tab_nav() {
	/**
	 * Fires before payment method tab navigation container
	 *
	 * @since 2.0.0
	 */
	do_action( 'woocommerce_review_order_before_submit' );
	?>

	<div id="cfw-payment-action" class="cfw-bottom-controls">
		<div class="previous-button">
			<?php cfw_return_to_payment_method_link(); ?>
		</div>

		<div class="cfw-place-order-wrap">
			<?php do_action( 'cfw_payment_nav_place_order_button' ); ?>
		</div>
	</div>

	<?php
	/**
	 * Fires after payment method tab navigation container
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_checkout_after_payment_method_tab_nav' );
}

/**
 * Payment method tab nav for one page checkout
 *
 * Includes place order button
 */
function cfw_payment_tab_nav_one_page_checkout() {
	cfw_payment_tab_nav( true );
}

/**
 * Cart list
 */
function cfw_cart_html() {
	echo cfw_get_checkout_item_summary_table();

	/**
	 * After cart html table output
	 *
	 * @since 4.3.4
	 */
	do_action( 'cfw_after_cart_html' );
}

/**
 * Coupon module
 *
 * @param bool $mobile
 */
function cfw_coupon_module( $mobile = false ) {
	/**
	 * Fires before coupon module
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_before_coupon_module', $mobile );

	$field_id  = $mobile ? 'cfw-promo-code-mobile' : 'cfw-promo-code';
	$button_id = $mobile ? 'cfw-promo-code-btn-mobile' : 'cfw-promo-code-btn';

	/**
	 * Filters whether to hide promo code until link is clicked
	 *
	 * @param bool $hide_promo_code Hide promo code until link is clicked
	 * @since 3.0.0
	 */
	$hide_promo_code_by_default = apply_filters( 'cfw_hide_promo_code_by_default', SettingsManager::instance()->get_setting( 'enable_coupon_code_link' ) === 'yes' );

	/**
	 * Filters promo code button label
	 *
	 * @param string $promo_code_button_label Promo code button label
	 * @since 3.0.0
	 */
	$promo_code_button_label = apply_filters( 'cfw_promo_code_apply_button_label', esc_attr__( 'Apply', 'checkout-wc' ) );
	?>
	<div id="<?php echo $mobile ? 'cfw-coupons-mobile' : 'cfw-coupons'; ?>" class="cfw-module">
		<?php if ( wc_coupons_enabled() ) : ?>
			<?php if ( $mobile && $hide_promo_code_by_default ) : ?>
				<h3>
					<?php
					/**
					 * Filters promo code mobile heading
					 *
					 * @param string $promo_code_mobile_heading Promo code mobile heading
					 * @since 3.0.0
					 */
					echo apply_filters( 'cfw_promo_code_mobile_heading', __( 'Promo code', 'checkout-wc' ) );
					?>
				</h3>
			<?php endif; ?>

			<?php if ( $hide_promo_code_by_default ) : ?>
				<div class="row">
					<div class="col-lg-12 no-gutters cfw-small">
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
			<?php endif; ?>
			<div class="cfw-promo-wrap <?php echo $hide_promo_code_by_default ? 'cfw-hidden' : ''; ?>">
				<div class="row cfw-promo-row cfw-input-wrap-row">
					<?php
					$output = woocommerce_form_field(
						$field_id,
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
							'return'      => true,
							'columns'     => 8,
						)
					);

					$output = str_replace( '(' . cfw_esc_html__( 'optional', 'woocommerce' ) . ')', '', $output );

					echo $output;
					?>
					<div class="cfw-input-wrap cfw-button-input col-lg-4 cfw-promo-code-button-wrap form-row">
						<input type="button" name="cfw-promo-code-btn" id="<?php echo $button_id; ?>" class="cfw-secondary-btn" value="<?php echo $promo_code_button_label; ?>" />
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php
		/**
		 * Fires at end of coupon module before closing </div> tag
		 *
		 * @since 2.0.0
		 */
		do_action( 'cfw_coupon_module_end', $mobile );
		?>
	</div>
	<?php
	/**
	 * Fires after coupon module
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_after_coupon_module' );
}

function cfw_maybe_show_coupon_module() {
	if ( SettingsManager::instance()->get_setting( 'show_mobile_coupon_field' ) === 'yes' ) {
		cfw_mobile_coupon_module();
	}
}

function cfw_mobile_coupon_module() {
	cfw_coupon_module( true );
}

/**
 * Cart summary totals
 */
function cfw_totals_html() {
	echo cfw_get_totals_html();
}

/**
 * The form attributes
 *
 * @param bool|mixed $id
 * @param bool $row
 * @param bool $action
 */
function cfw_form_attributes( $id = false, bool $row = true, bool $action = true ) {
	$output = '';
	$format = '%s="%s" ';
	$id     = $id ? $id : 'checkout';

	$attributes = array(
		'id'             => $id,
		'name'           => $id,
		'class'          => array( 'cfw-customer-info-active' ),
		'method'         => 'POST',
		'formnovalidate' => '', // this isn't something WooCommerce core adds - maybe we added it for Parsley.js?
		'novalidate'     => 'novalidate',
		'enctype'        => 'multipart/form-data',
	);

	if ( 'order_review' !== $id ) {
		$attributes['class'][]            = 'woocommerce-checkout';
		$attributes['class'][]            = 'checkout';
		$attributes['data-parsley-focus'] = 'first';
	}

	if ( $row ) {
		$attributes['class'][] = 'row';
	}

	if ( $action ) {
		$attributes['action'] = esc_url( wc_get_checkout_url() );
	}

	$attributes = apply_filters( 'cfw_form_attributes', $attributes, $id );

	foreach ( $attributes as $key => $value ) {
		if ( is_array( $value ) ) {
			$value = join( ' ', $value );
		}

		$output .= sprintf( $format, $key, esc_attr( $value ) );
	}

	echo $output;
}

/**
 * Shipping method tab container style attribute
 */
function cfw_shipping_method_tab_style_attribute() {
	?>
	style=""
	<?php
}

function cfw_customer_info_tab_style_attribute() {
	?>
	style="<?php echo ( ! cfw_show_customer_information_tab() ) ? 'display: none' : ''; ?>"
	<?php
}

/**
 * Render title with order number, checkbox graphic, and thank you statement.
 *
* @param WC_Order $order
*/
function cfw_thank_you_title( WC_Order $order ) {
	if ( $order->has_status( 'failed' ) ) :
		?>
		<div class="cfw-mb">
			<?php
			wc_add_notice( cfw__( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ), 'error' );
			cfw_wc_print_notices_with_wrap();
			?>
			<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="cfw-secondary-btn"><?php cfw_esc_html_e( 'Pay', 'woocommerce' ); ?></a>
		</div>
	<?php endif; ?>
	<div class="title">
		<?php
		/**
		 * Filters thank you page heading icon
		 *
		 * @param string $cfw_thank_you_heading_icon Thank you page heading icon output
		 * @since 5.4.0
		 *
		 */
		echo apply_filters( 'cfw_thank_you_heading_icon', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" fill="none" stroke-width="2" class="cfw-checkmark"><path class="checkmark__circle" d="M25 49c13.255 0 24-10.745 24-24S38.255 1 25 1 1 11.745 1 25s10.745 24 24 24z"></path><path class="checkmark__check" d="M15 24.51l7.307 7.308L35.125 19"></path></svg>', $order );
		?>
		<h5>
			<?php
			/**
			 * Filters thank you page heading title
			 *
			 * @since 3.0.0
			 *
			 * @param string $thank_you_title Thank you page heading title
			 */
			// translators: %s is the order number
			echo sprintf( apply_filters( 'cfw_thank_you_title', __( 'Order %s', 'checkout-wc' ) ), $order->get_order_number() );
			?>
		</h5>
		<h4>
			<?php
			/**
			 * Filters thank you page heading subtitle
			 *
			 * @since 3.0.0
			 *
			 * @param string $thank_you_subtitle Thank you page heading subtitle
			 */
			// translators: %s is the billing first name
			echo sprintf( apply_filters( 'cfw_thank_you_subtitle', __( 'Thank you %s!', 'checkout-wc' ) ), $order->get_billing_first_name() );
			?>
		</h4>
	</div>
	<?php
}

/**
 * Thank you page section open
 *
 * @param $class
 */
function cfw_thank_you_section_wrap( $callable, $class ) {
	?>
	<section class="<?php echo $class; ?>>">
	<?php
}

/**
 * Thank you page section close
 */
function cfw_thank_you_section_end() {
	?>
	</section>
	<?php
}

/**
 * Thank you page order status row
 *
 * Shows progression of order through statuses.
 *
* @param WC_Order $order
* @param array $order_statuses
*/
function cfw_thank_you_order_status_row( WC_Order $order, array $order_statuses ) {
	?>
	<div class="inner status-row">
		<?php if ( $order->needs_shipping_address() && function_exists( 'wc_order_status_manager' ) ) : ?>
			<ul class="status-steps">
				<?php $count = 0; ?>
				<?php
				foreach ( $order_statuses as $order_status ) :
					$order_status = new \WC_Order_Status_Manager_Order_Status( $order_status );
					?>
					<li class="status-step <?php echo $order->get_status() === $order_status->get_slug() ? 'status-step-selected' : ''; ?>">
						<i class="<?php echo $order_status->get_icon(); ?>"></i>

						<span class="title">
							<?php echo wc_get_order_status_name( $order_status->get_slug() ); ?>
						</span>

						<span class="date">
							<?php
							$date = cfw_order_status_date( $order->get_id(), wc_get_order_status_name( $order_status->get_slug() ) );

							if ( $date ) {
								echo date_i18n( get_option( 'date_format' ), strtotime( $date ) );
							} elseif ( 0 === $count ) {
								echo date_i18n( get_option( 'date_format' ), strtotime( $order->get_date_created() ) );
							}
							?>
						</span>
					</li>
					<?php $count++; ?>
				<?php endforeach; ?>
			</ul>
		<?php elseif ( $order->needs_shipping_address() ) : ?>
			<ul class="status-steps">
				<?php $count = 0; ?>
				<?php foreach ( $order_statuses as $order_status ) : ?>
					<?php
					/**
					 * Filters thank you status icon class
					 *
					 * @since 3.0.0
					 *
					 * @param string $thank_you_status_icon Thank you status icon class
					 */
					$thank_you_status_icon = apply_filters( 'cfw_thank_you_status_icon_' . $order_status, 'fa fa-chevron-circle-right' );
					?>
					<li class="status-step <?php echo $order->get_status() === $order_status ? 'status-step-selected status-step-current' : ''; ?>">
						<i class="<?php echo $thank_you_status_icon; ?>"></i>

						<span class="title">
							<?php echo wc_get_order_status_name( $order_status ); ?>
						</span>

						<span class="date">
							<?php
							$date = cfw_order_status_date( $order->get_id(), wc_get_order_status_name( $order_status ) );

							if ( $date ) {
								echo date_i18n( get_option( 'date_format' ), strtotime( $date ) );
							} elseif ( 0 === $count ) {
								echo date_i18n( get_option( 'date_format' ), strtotime( $order->get_date_created() ) );
							}
							?>
						</span>
					</li>
					<?php $count++; ?>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<h3><?php _e( 'Order status', 'checkout-wc' ); ?></h3>
			<p><?php echo wc_get_order_status_name( $order->get_status() ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Thank you page map element
 *
* @param WC_Order $order
*/
function cfw_thank_you_map( WC_Order $order ) {
	if ( $order->needs_shipping_address() ) :
		?>
		<?php if ( SettingsManager::instance()->is_premium_feature_enabled( 'enable_map_embed' ) ) : ?>
		<div id="map"></div>
		<?php endif; ?>

		<?php cfw_maybe_output_tracking_numbers( $order ); ?>
		<?php
	endif;
}

/**
 * Thank you page order updates section
 *
* @param WC_Order $order
*/
function cfw_thank_you_order_updates( WC_Order $order ) {
	?>
	<h3><?php _e( 'Order updates', 'checkout-wc' ); ?></h3>
	<?php
	/**
	 * Filters order updates text
	 *
	 * @since 3.0.0
	 *
	 * @param string $order_updates_text Thank you page order updates text
	 */
	echo wpautop( apply_filters( 'cfw_order_updates_text', __( 'You’ll get shipping and delivery updates by email.', 'checkout-wc' ), $order ) );
}

/**
 * @param WC_Order $order
 * @param array $order_statues
 * @param boolean $show_downloads
 * @param array $downloads
 */
function cfw_thank_you_downloads( $order, $order_statues, $show_downloads, $downloads ) {
	?>
	<h3 class="woocommerce-order-downloads__title"><?php cfw_esc_html_e( 'Downloads', 'woocommerce' ); ?></h3>

	<table class="woocommerce-table woocommerce-table--order-downloads shop_table shop_table_responsive order_details">
		<thead>
		<tr>
			<?php foreach ( wc_get_account_downloads_columns() as $column_id => $column_name ) : ?>
				<th class="<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
			<?php endforeach; ?>
		</tr>
		</thead>

		<?php foreach ( $downloads as $download ) : ?>
			<tr>
				<?php foreach ( wc_get_account_downloads_columns() as $column_id => $column_name ) : ?>
					<td class="<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
						<?php
						if ( has_action( 'woocommerce_account_downloads_column_' . $column_id ) ) {
							do_action( 'woocommerce_account_downloads_column_' . $column_id, $download );
						} else {
							switch ( $column_id ) {
								case 'download-product':
									if ( $download['product_url'] ) {
										echo '<a href="' . esc_url( $download['product_url'] ) . '">' . esc_html( $download['product_name'] ) . '</a>';
									} else {
										echo esc_html( $download['product_name'] );
									}
									break;
								case 'download-file':
									echo '<a href="' . esc_url( $download['download_url'] ) . '" class="woocommerce-MyAccount-downloads-file alt">' . esc_html( $download['download_name'] ) . '</a>';
									break;
								case 'download-remaining':
									echo is_numeric( $download['downloads_remaining'] ) ? esc_html( $download['downloads_remaining'] ) : cfw_esc_html__( '&infin;', 'woocommerce' );
									break;
								case 'download-expires':
									if ( ! empty( $download['access_expires'] ) ) {
										echo '<time datetime="' . esc_attr( date( 'Y-m-d', strtotime( $download['access_expires'] ) ) ) . '" title="' . esc_attr( strtotime( $download['access_expires'] ) ) . '">' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $download['access_expires'] ) ) ) . '</time>';
									} else {
										cfw_esc_html_e( 'Never', 'woocommerce' );
									}
									break;
							}
						}
						?>
					</td>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php
}

/**
 * Thank you page customer information
 *
* @param WC_Order $order
*/
function cfw_thank_you_customer_information( WC_Order $order ) {
	?>
	<h3><?php _e( 'Information', 'checkout-wc' ); ?></h3>

	<?php
	/**
	 * Fires before thank you customer information output (after Information heading)
	 *
	 * @since 2.0.0
	 * @param WC_Order $order The order object
	 */
	do_action( 'cfw_before_thank_you_customer_information', $order );
	?>

	<div class="row">
		<div class="col-lg-6">
			<h6><?php _e( 'Contact information', 'checkout-wc' ); ?></h6>
			<p><?php echo $order->get_billing_email(); ?></p>
		</div>
		<div class="col-lg-6">
			<h6><?php _e( 'Payment', 'checkout-wc' ); ?></h6>
			<p><?php echo $order->get_payment_method_title(); ?></p>
		</div>
	</div>

	<div class="row">
		<?php if ( $order->needs_shipping_address() ) : ?>
			<div class="col-lg-6">
				<?php if ( wc_ship_to_billing_address_only() ) : ?>
					<h6>
						<?php
						/** This action is documented earlier in this file */
						echo apply_filters( 'cfw_billing_shipping_address_heading', __( 'Billing and Shipping address', 'checkout-wc' ) );
						?>
					</h6>
				<?php else : ?>
					<h6>
						<?php
						/** This action is documented earlier in this file */
						echo apply_filters( 'cfw_shipping_address_heading', esc_html__( 'Shipping address', 'checkout-wc' ) );
						?>
					</h6>
				<?php endif; ?>

				<address>
					<?php echo wp_kses_post( $order->get_formatted_shipping_address( cfw_esc_html__( 'N/A', 'woocommerce' ) ) ); ?>
				</address>
			</div>
		<?php endif; ?>

		<?php if ( ! wc_ship_to_billing_address_only() ) : ?>
			<div class="col-lg-6">
				<h6>
					<?php
					/** This action is documented earlier in this file */
					echo apply_filters( 'cfw_billing_address_heading', esc_html__( 'Billing address', 'checkout-wc' ) );
					?>
				</h6>
				<address>
					<?php echo wp_kses_post( $order->get_formatted_billing_address( cfw_esc_html__( 'N/A', 'woocommerce' ) ) ); ?>
				</address>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $order->needs_shipping_address() ) : ?>
		<div class="row">
			<div class="col-lg-6">
				<h6><?php _e( 'Shipping', 'checkout-wc' ); ?></h6>
				<p>
					<?php echo $order->get_shipping_method(); ?>
				</p>
			</div>
		</div>
	<?php endif; ?>

	<div class="clear"></div>

	<?php
	do_action( 'woocommerce_order_details_after_customer_details', $order );
	do_action( 'woocommerce_order_details_after_order_table', $order );
}

/**
 * Renders the buttons beneath the order details on the
 * thank you page
 */
function cfw_thank_you_bottom_controls() {
	/**
	 * Filters thank you page continue shopping button text
	 *
	 * @since 3.0.0
	 *
	 * @param string $cfw_thank_you_continue_shopping_text Thank you page continue shopping button text
	 */
	$cfw_thank_you_continue_shopping_text = apply_filters( 'cfw_thank_you_continue_shopping_text', cfw_esc_html__( 'Continue shopping', 'woocommerce' ) );
	?>
	<div id="cfw-thank-you-action" class="cfw-bottom-controls">
		<?php
		$return_to = apply_filters( 'woocommerce_continue_shopping_redirect', wc_get_page_permalink( 'shop' ) );
		$message   = sprintf( '<a href="%s" tabindex="1" class="cfw-primary-btn cfw-next-tab">%s</a>', esc_url( $return_to ), $cfw_thank_you_continue_shopping_text );
		?>
		<!--- Placeholder -->
		<div></div>
		<?php echo $message; ?>
	</div>
	<?php
}

/**
 * Thank you page cart summary content
 *
* @param WC_Order $order
*/
function cfw_thank_you_cart_summary_content( WC_Order $order ) {
	if ( count( $order->get_items() ) > 0 ) {
		echo cfw_get_order_item_summary_table( $order );
	}
}

/**
 * Order pay heading
 */
function cfw_order_pay_heading() {
	?>
	<h3><?php echo cfw__( 'Pay for order', 'woocommerce' ); ?></h3>
	<?php
}

/**
 * Order pay login form
* @param WC_Order $order
*/
function cfw_order_pay_login_form( WC_Order $order ) {
	?>
	<form <?php cfw_form_attributes( 'login_form', false, false ); ?>>
		<?php do_action( 'woocommerce_login_form_start' ); ?>

		<?php
		woocommerce_form_field(
			'username',
			array(
				'label'        => 'Email',
				'type'         => 'text',
				'required'     => true,
				'autocomplete' => 'username',
			)
		);

		woocommerce_form_field(
			'password',
			array(
				'label'        => 'Password',
				'type'         => 'password',
				'required'     => true,
				'autocomplete' => 'current-password',
			)
		);
		?>

		<div class="clear"></div>

		<?php do_action( 'woocommerce_login_form' ); ?>

		<p class="form-row">
			<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
			<input type="hidden" name="redirect" value="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" />

			<button type="submit" class="woocommerce-button button woocommerce-form-login__submit" name="login" value="<?php cfw_esc_attr_e( 'Login', 'woocommerce' ); ?>"><?php esc_html_e( 'Login', 'woocommerce' ); ?></button>

			<span class="login-optional cfw-small">
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php cfw_esc_html_e( 'Lost your password?', 'woocommerce' ); ?></a>
			</span>
		</p>

		<div class="clear"></div>

		<?php do_action( 'woocommerce_login_form_end' ); ?>
	</form>
	<?php
}

/**
 * Order Pay payment form
 *
* @param WC_Order $order
* @param array $available_gateways
* @param $order_button_text
* @param $call_receipt_hook
*/
function cfw_order_pay_payment_form( WC_Order $order, array $available_gateways, $order_button_text, $call_receipt_hook ) {
	?>
	<form <?php cfw_form_attributes( 'order_review', false, false ); ?>>
		<?php
		// Some gateways need this when they use order-pay
		// to take payment right after checkout
		if ( ! empty( $call_receipt_hook ) ) :
			?>
			<?php do_action( 'woocommerce_receipt_' . $order->get_payment_method(), $order->get_id() ); ?>
		<?php else : ?>
			<?php cfw_payment_methods( $available_gateways, $order, false ); ?>

			<?php wc_get_template( 'checkout/terms.php' ); ?>

			<div id="cfw-payment-action" class="cfw-bottom-controls">
				<div class="previous-button"></div>

				<input type="hidden" name="woocommerce_pay" value="1" />

				<div class="place-order" id="cfw-place-order">
					<?php do_action( 'woocommerce_pay_order_before_submit' ); ?>

                    <?php echo apply_filters( 'woocommerce_pay_order_button_html', '<button type="submit" class="cfw-primary-btn cfw-next-tab validate" id="place_order" formnovalidate="formnovalidate" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); // @codingStandardsIgnoreLine ?>

					<?php do_action( 'woocommerce_pay_order_after_submit' ); ?>

					<?php wp_nonce_field( 'woocommerce-pay', 'woocommerce-pay-nonce' ); ?>
				</div>
			</div>
		<?php endif; ?>
	</form>
	<?php
}

/**
* @param WC_Order $order
* @param $call_receipt_hook
* @param $available_gateways
* @param $order_button_text
*/
function cfw_order_pay_form( WC_Order $order, $call_receipt_hook, $available_gateways, $order_button_text ) {
	if ( ! current_user_can( 'pay_for_order', $order->get_id() ) && ! is_user_logged_in() ) {
		cfw_order_pay_login_form( $order );
	} elseif ( $call_receipt_hook ) {
		wc_get_template( 'checkout/order-receipt.php', array( 'order' => $order ) );
	} else {
		cfw_order_pay_payment_form( $order, $available_gateways, $order_button_text, $call_receipt_hook );
	}
}

/**
 * Thank you page cart summary content
 *
* @param WC_Order $order
*/
function cfw_order_pay_cart_summary_content( WC_Order $order ) {
	if ( count( $order->get_items() ) > 0 ) {
		echo cfw_get_order_item_summary_table( $order );
	}
}

function cfw_thank_you_section_start_order_status() {
	cfw_thank_you_section_start( 'cfw-order-status' );
}

/**
* @param WC_Order $order
 */
function cfw_thank_you_order_updates_wrapped( WC_Order $order ) {
	if ( $order->needs_shipping_address() ) {
		cfw_thank_you_section_auto_wrap( 'cfw_thank_you_order_updates', 'cfw-order-updates', array( $order ) );
	}
}

function cfw_thank_you_downloads_wrapped( $order, $order_statues, $show_downloads, $downloads ) {
	if ( $show_downloads ) {
		cfw_thank_you_section_auto_wrap( 'cfw_thank_you_downloads', 'woocommerce-order-downloads', array( $order, $order_statues, $show_downloads, $downloads ) );
	}
}

function cfw_thank_you_customer_information_wrapped( $order ) {
	cfw_thank_you_section_auto_wrap( 'cfw_thank_you_customer_information', 'cfw-customer-information', array( $order ) );
}

function cfw_cart_summary_mobile_header_display( WC_Order $order ) {
	cfw_cart_summary_mobile_header( $order->get_formatted_order_total() );
}

/**
 * Get the checkout tabs
 *
* @return array
 */
function cfw_get_checkout_tabs() : array {
	/**
	 * Filters the checkout tabs
	 *
	 * @since 7.0.0
	 *
	 * @param array $tabs The checkout tabs
	 */
	$tabs = apply_filters(
		'cfw_get_checkout_tabs',
		array(
			'cfw-customer-info'   => array(
				'label'            => apply_filters( 'cfw_breadcrumb_customer_info_label', esc_html__( 'Information', 'checkout-wc' ) ),
				'classes'          => array(),
				'priority'         => 20,
				'enabled'          => cfw_show_customer_information_tab(),
				'display_callback' => function() {

					/**
					 * Outputs customer info tab content
					 *
					 * @since 2.0.0
					 */
					do_action( 'cfw_checkout_customer_info_tab' );
				},
			),
			'cfw-shipping-method' => array(
				'label'            => apply_filters( 'cfw_breadcrumb_shipping_label', esc_html__( 'Shipping', 'checkout-wc' ) ),
				'classes'          => array(),
				'priority'         => 30,
				'enabled'          => cfw_show_shipping_tab(),
				'display_callback' => function() {
					/**
					 * Outputs customer info tab content
					 *
					 * @since 2.0.0
					 */
					do_action( 'cfw_checkout_shipping_method_tab' );
				},
			),
			'cfw-payment-method'  => array(
				'label'            => apply_filters( 'cfw_breadcrumb_payment_label', esc_html__( 'Payment', 'checkout-wc' ) ),
				'classes'          => array( 'woocommerce-checkout-payment' ),
				'priority'         => 40,
				'enabled'          => true,
				'display_callback' => function() {
					/**
					 * Outputs customer info tab content
					 *
					 * @since 2.0.0
					 */
					do_action( 'cfw_checkout_payment_method_tab' );
				},
			),
		)
	);

	uasort( $tabs, 'cfw_uasort_by_priority_comparison' );

	return $tabs;
}
function cfw_output_checkout_tabs() {
	?>
	<?php foreach ( cfw_get_checkout_tabs() as $tab_id => $tab ) : ?>
		<?php
		if ( ! $tab['enabled'] ) {
			$tab['classes'][] = 'cfw-force-hidden';
		}
		?>
		<div id="<?php echo esc_attr( $tab_id ); ?>" class="cfw-panel <?php echo esc_attr( join( ' ', $tab['classes'] ) ); ?>">
			<?php
			cfw_set_current_tab( $tab_id );

			call_user_func( $tab['display_callback'] );
			?>
		</div>
	<?php endforeach; ?>
	<?php
}

function cfw_set_current_tab( string $tab ) {
	global $cfw_current_tab;
	$cfw_current_tab = $tab;
}

function cfw_get_current_tab(): string {
	global $cfw_current_tab;
	return (string) $cfw_current_tab;
}

function cfw_add_order_review_step_tab() {
	?>
	<!-- Order Review Tab -->
	<div id="cfw-order-review" class="cfw-panel">
		<?php
		/**
		 * Outputs order review tab content
		 *
		 * @since 2.0.0
		 */
		do_action( 'cfw_checkout_order_review_tab' );
		?>
	</div>
	<?php
}

/**
* @param $breadcrumbs
* @return array
*/
function cfw_add_order_review_step_breadcrumb( array $breadcrumbs ): array {
	$breadcrumbs['cfw-order-review'] = array(
		'href'     => '#cfw-order-review', // must match tab ID
		'label'    => __( 'Review', 'checkout-wc' ),
		'priority' => 50, // after payment
	);

	return $breadcrumbs;
}

function cfw_lost_password_modal() {
	?>
	<div id="cfw_lost_password_form_wrap" style="display:none;">
		<form method="post" target="_blank" id="cfw_lost_password_form" class="checkoutwc">
			<div id="cfw-lp-alert-placeholder"></div>
			<p style="margin-bottom: 1em">
				<?php echo apply_filters( 'woocommerce_lost_password_message', cfw_esc_html__( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'woocommerce' ) ); ?>
			</p>

			<?php
			woocommerce_form_field(
				'user_login',
				array(
					'type'         => 'email',
					'required'     => true,
					'autocomplete' => 'email',
					'label'        => cfw__( 'Email address', 'woocommerce' ),
					'placeholder'  => cfw__( 'Email address', 'woocommerce' ),
				)
			);
			?>

			<div class="clear"></div>

			<?php do_action( 'woocommerce_lostpassword_form' ); ?>

			<p class="woocommerce-form-row form-row">
				<input type="hidden" name="wc_reset_password" value="true" />
				<button type="submit" class="cfw-primary-btn" value="<?php cfw_esc_attr_e( 'Reset password', 'woocommerce' ); ?>"><?php cfw_esc_html_e( 'Reset password', 'woocommerce' ); ?></button>
			</p>

			<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>

		</form>
	</div>
	<?php
}

function cfw_maybe_output_footer_nav_menu() {
	$location = 'cfw-footer-menu';

	if ( has_nav_menu( $location ) ) {
		wp_nav_menu(
			array(
				'theme_location' => $location,
			)
		);
	}
}


function cfw_output_empty_cart_message() {
	echo '<h2>' . apply_filters( 'cfw_empty_side_cart_heading', __( 'Your Cart is Empty', 'checkout-wc' ) ) . '</h2>';
}