<?php

use Objectiv\Plugins\Checkout\Managers\SettingsManager;

add_action( 'cfw_checkout_before_main_container', 'cfw_glass_override_breadcrumb_colors' );

function cfw_glass_override_breadcrumb_colors() {
	$template_slug    = basename( __DIR__ );
	$settings_manager = SettingsManager::instance();
	?>
	<style type="text/css">
		#cfw #cfw-breadcrumb li.tab.active a {
			color: <?php echo $settings_manager->get_setting( 'button_color', array( $template_slug ) ); ?>;
			border-bottom-color: <?php echo $settings_manager->get_setting( 'button_color', array( $template_slug ) ); ?>;
		}

		input[type="checkbox"]:checked {
			box-shadow: 0 0 0 10px <?php echo $settings_manager->get_setting( 'button_color', array( $template_slug ) ); ?> inset !important;
		}

		input[type="radio"]:checked:after {
			background-color: <?php echo $settings_manager->get_setting( 'button_color', array( $template_slug ) ); ?> !important;
		}
	</style>
	<?php
}

add_action( 'cfw_cart_html_table_start', 'cfw_glass_cart_heading', 21 );

function cfw_glass_cart_heading() {
	if ( ! is_cfw_page() ) {
		return;
	}
	?>
	<tr>
		<td colspan="4">
			<h3>
				<?php _e( 'Your Cart', 'checkout-wc' ); ?>
			</h3>
		</td>
	</tr>
	<?php
}

remove_action( 'cfw_checkout_cart_summary', 'cfw_cart_html', 40 );
add_action( 'cfw_checkout_cart_summary', 'cfw_glass_cart_html', 40 );

function cfw_glass_cart_html() {
	?>
	<div class="cfw-glass-cart-wrap">
		<?php cfw_cart_html(); ?>
	</div>
	<?php
}

// Move notices inside container
remove_action( 'cfw_order_pay_main_container_start', 'cfw_wc_print_notices_with_wrap', 10 );
add_action( 'cfw_order_pay_before_order_review', 'cfw_wc_print_notices', 0 );

remove_action( 'cfw_checkout_main_container_start', 'cfw_wc_print_notices_with_wrap', 10 );
add_action( 'cfw_checkout_before_order_review', 'cfw_wc_print_notices', 0 );

add_filter(
	'cfw_active_theme_color_settings',
	function( $color_settings ) {
		$color_settings['accent_color'] = cfw__( 'Accent Color', 'checkout-wc' );

		return $color_settings;
	}
);

add_action(
	'cfw_after_custom_css_property_overrides',
	function() {
		$settings_manager = SettingsManager::instance();
		$active_theme     = cfw_get_active_template()->get_slug();
		$accent_color     = $settings_manager->get_setting( 'accent_color', array( $active_theme ) );

		if ( empty( $accent_color ) ) {
			return;
		}
		?>
		body {
			--cfw-active-theme-colors-accent-color: <?php echo $accent_color; ?> !important;
		}
		<?php
	}
);

add_filter( 'cfw_breadcrumbs', 'cfw_glass_remove_cart_breadcrumb' );

function cfw_glass_remove_cart_breadcrumb( $breadcrumbs ) {
	unset( $breadcrumbs['cart'] );

	return $breadcrumbs;
}