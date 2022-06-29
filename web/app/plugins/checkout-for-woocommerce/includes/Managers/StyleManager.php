<?php

namespace Objectiv\Plugins\Checkout\Managers;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Managers
 */
class StyleManager {
	static function output_google_font_includes() {
		$settings_manager = SettingsManager::instance();
		$body_font        = $settings_manager->get_setting( 'body_font' );
		$heading_font     = $settings_manager->get_setting( 'heading_font' );

		if ( ! empty( $body_font ) && 'System Font Stack' !== $body_font ) {
			echo '<link href="https://fonts.googleapis.com/css?family=' . urlencode( $body_font ) . '&display=swap" rel="stylesheet">';
		}

		if ( ! empty( $heading_font ) && 'System Font Stack' !== $heading_font ) {
			echo '<link href="https://fonts.googleapis.com/css?family=' . urlencode( $heading_font ) . '&display=swap" rel="stylesheet">';
		}
	}
	static function output_css_custom_property_overrides() {
		$settings_manager                  = SettingsManager::instance();
		$active_template                   = cfw_get_active_template();
		$active_theme                      = $active_template->get_slug();
		$body_background_color             = $settings_manager->get_setting( 'body_background_color', array( $active_theme ) );
		$body_text_color                   = $settings_manager->get_setting( 'body_text_color', array( $active_theme ) );
		$body_font                         = $settings_manager->get_setting( 'body_font' );
		$heading_font                      = $settings_manager->get_setting( 'heading_font' );
		$header_background_color           = $settings_manager->get_setting( 'header_background_color', array( $active_theme ) );
		$footer_background_color           = $settings_manager->get_setting( 'footer_background_color', array( $active_theme ) );
		$summary_bg_color                  = $settings_manager->get_setting( 'summary_background_color', array( $active_theme ) );
		$summary_mobile_bg_color           = $settings_manager->get_setting( 'summary_mobile_background_color', array( $active_theme ) );
		$summary_text_color                = $settings_manager->get_setting( 'summary_text_color', array( $active_theme ) );
		$summary_link_color                = $settings_manager->get_setting( 'summary_link_color', array( $active_theme ) );
		$header_text_color                 = $settings_manager->get_setting( 'header_text_color', array( $active_theme ) );
		$footer_text_color                 = $settings_manager->get_setting( 'footer_color', array( $active_theme ) );
		$body_link_color                   = $settings_manager->get_setting( 'link_color', array( $active_theme ) );
		$primary_button_bg_color           = $settings_manager->get_setting( 'button_color', array( $active_theme ) );
		$primary_button_text_color         = $settings_manager->get_setting( 'button_text_color', array( $active_theme ) );
		$primary_button_hover_bg_color     = $settings_manager->get_setting( 'button_hover_color', array( $active_theme ) );
		$primary_button_hover_text_color   = $settings_manager->get_setting( 'button_text_hover_color', array( $active_theme ) );
		$secondary_button_bg_color         = $settings_manager->get_setting( 'secondary_button_color', array( $active_theme ) );
		$secondary_button_text_color       = $settings_manager->get_setting( 'secondary_button_text_color', array( $active_theme ) );
		$secondary_button_hover_bg_color   = $settings_manager->get_setting( 'secondary_button_hover_color', array( $active_theme ) );
		$secondary_button_hover_text_color = $settings_manager->get_setting( 'secondary_button_text_hover_color', array( $active_theme ) );
		$cart_item_background_color        = $settings_manager->get_setting( 'cart_item_quantity_color', array( $active_theme ) );
		$cart_item_text_color              = $settings_manager->get_setting( 'cart_item_quantity_text_color', array( $active_theme ) );
		$breadcrumb_completed_text_color   = $settings_manager->get_setting( 'breadcrumb_completed_text_color', array( $active_theme ) );
		$breadcrumb_current_text_color     = $settings_manager->get_setting( 'breadcrumb_current_text_color', array( $active_theme ) );
		$breadcrumb_next_text_color        = $settings_manager->get_setting( 'breadcrumb_next_text_color', array( $active_theme ) );
		$breadcrumb_completed_accent_color = $settings_manager->get_setting( 'breadcrumb_completed_accent_color', array( $active_theme ) );
		$breadcrumb_current_accent_color   = $settings_manager->get_setting( 'breadcrumb_current_accent_color', array( $active_theme ) );
		$breadcrumb_next_accent_color      = $settings_manager->get_setting( 'breadcrumb_next_accent_color', array( $active_theme ) );
		$logo_url                          = cfw_get_logo_url();

		$custom_properties = apply_filters(
			'cfw_custom_css_properties',
			array(
				'--cfw-body-background-color'              => $body_background_color,
				'--cfw-body-text-color'                    => $body_text_color,
				'--cfw-body-font-family'                   => ! empty( $body_font ) && 'System Font Stack' !== $body_font ? $body_font . ',-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif' : false,
				'--cfw-heading-font-family'                => ! empty( $heading_font ) && 'System Font Stack' !== $heading_font ? $heading_font . ',-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif' : false,
				'--cfw-header-background-color'            => $active_template->supports( 'header-background' ) ? $header_background_color : false,
				'--cfw-header-bottom-margin'               => strtolower( $header_background_color ) !== strtolower( $body_background_color ) ? '2em' : false,
				'--cfw-footer-background-color'            => $active_template->supports( 'footer-background' ) ? $footer_background_color : false,
				'--cfw-footer-top-margin'                  => '#ffffff' !== strtolower( $footer_background_color ) ? '2em' : false,
				'--cfw-cart-summary-background-color'      => $active_template->supports( 'summary-background' ) ? $summary_bg_color : false,
				'--cfw-cart-summary-mobile-background-color' => $summary_mobile_bg_color,
				'--cfw-cart-summary-text-color'            => $active_template->supports( 'summary-background' ) ? $summary_text_color : false,
				'--cfw-cart-summary-link-color'            => $summary_link_color,
				'--cfw-header-text-color'                  => $header_text_color,
				'--cfw-footer-text-color'                  => $footer_text_color,
				'--cfw-body-link-color'                    => $body_link_color,
				'--cfw-buttons-primary-background-color'   => $primary_button_bg_color,
				'--cfw-buttons-primary-text-color'         => $primary_button_text_color,
				'--cfw-buttons-primary-hover-background-color' => $primary_button_hover_bg_color,
				'--cfw-buttons-primary-hover-text-color'   => $primary_button_hover_text_color,
				'--cfw-buttons-secondary-background-color' => $secondary_button_bg_color,
				'--cfw-buttons-secondary-text-color'       => $secondary_button_text_color,
				'--cfw-buttons-secondary-hover-background-color' => $secondary_button_hover_bg_color,
				'--cfw-buttons-secondary-hover-text-color' => $secondary_button_hover_text_color,
				'--cfw-cart-summary-item-quantity-background-color' => $cart_item_background_color,
				'--cfw-cart-summary-item-quantity-text-color' => $cart_item_text_color,
				'--cfw-breadcrumb-completed-text-color'    => $breadcrumb_completed_text_color,
				'--cfw-breadcrumb-current-text-color'      => $breadcrumb_current_text_color,
				'--cfw-breadcrumb-next-text-color'         => $breadcrumb_next_text_color,
				'--cfw-breadcrumb-completed-accent-color'  => $breadcrumb_completed_accent_color,
				'--cfw-breadcrumb-current-accent-color'    => $breadcrumb_current_accent_color,
				'--cfw-breadcrumb-next-accent-color'       => $breadcrumb_next_accent_color,
				'--cfw-logo-url'                           => "url({$logo_url})",
			)
		);

		echo 'body { ' . PHP_EOL;

		foreach ( $custom_properties as $custom_property => $value ) {
			if ( empty( $value ) ) {
				continue;
			}

			echo "	{$custom_property}: {$value};" . PHP_EOL;
		}

		echo ' }' . PHP_EOL;
		do_action( 'cfw_after_custom_css_property_overrides' );
	}

	public static function output_custom_css() {
		$settings_manager  = SettingsManager::instance();
		$active_template   = cfw_get_active_template();
		$custom_css        = $settings_manager->get_setting( 'custom_css', array( $active_template->get_slug() ) );
		$show_mobile_logos = $settings_manager->get_setting( 'show_logos_mobile' );

		?>
		html {
			background: var(--cfw-body-background-color) !important;
		}
		<?php
		if ( 'yes' === $show_mobile_logos ) :
			?>
			@media(max-width: 900px) {
				form #cfw-billing-methods .payment_method_icons {
					display: flex !important;
				}
			}
			<?php
		endif;

		if ( ! empty( $custom_css ) ) {
			echo $custom_css;
		}
	}

	static function output_styles() {
		wp_print_styles();
		StyleManager::output_google_font_includes();

		echo '<style>' . PHP_EOL;
		StyleManager::output_css_custom_property_overrides();
		StyleManager::output_custom_css();
		echo '</style>' . PHP_EOL;
	}
}
