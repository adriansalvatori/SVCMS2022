<?php

namespace Objectiv\Plugins\Checkout;

use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * This class is a holding tank for settings defaults that have yet to find a home
 *
 * @deprecated
 */
class DefaultSettingsSetter {
	public function __construct() {}

	public function init( $settings_manager ) {
		$settings_manager = SettingsManager::instance();

		$settings_manager->add_setting( 'enable', 'no' );
		$settings_manager->add_setting( 'login_style', 'enhanced' );
		$settings_manager->add_setting( 'registration_style', 'enhanced' );
		$settings_manager->add_setting( 'label_style', 'floating' );
		$settings_manager->add_setting( 'cart_item_link', 'disabled' );
		$settings_manager->add_setting( 'cart_item_data_display', 'short' );
		$settings_manager->add_setting( 'skip_shipping_step', 'no' );
		$settings_manager->add_setting( 'enable_order_notes', 'no' );
		$settings_manager->add_setting( 'active_template', 'default' );
		$settings_manager->add_setting( 'allow_checkout_field_editor_address_modification', 'no' );
		$settings_manager->add_setting( 'enable_elementor_pro_support', 'no' );
		$settings_manager->add_setting( 'enable_beaver_themer_support', 'no' );
		$settings_manager->add_setting( 'template_loader', 'redirect' );
		$settings_manager->add_setting( 'override_view_order_template', 'yes' );
		$settings_manager->add_setting( 'show_logos_mobile', 'no' );
		$settings_manager->add_setting( 'show_mobile_coupon_field', 'no' );
		$settings_manager->add_setting( 'enable_order_pay', 'no' );
		$settings_manager->add_setting( 'enable_thank_you_page', 'no' );
		$settings_manager->add_setting( 'thank_you_order_statuses', 'no' );
		$settings_manager->add_setting( 'enable_map_embed', 'no' );
		$settings_manager->add_setting( 'override_view_order_template', 'no' );
		$settings_manager->add_setting( 'google_places_api_key', '' );
		$settings_manager->add_setting( 'user_matching', 'enabled' );

		foreach ( cfw_get_available_templates() as $template ) {
			$breadcrumb_completed_text_color   = '#7f7f7f';
			$breadcrumb_current_text_color     = '#333333';
			$breadcrumb_next_text_color        = '#7f7f7f';
			$breadcrumb_completed_accent_color = '#333333';
			$breadcrumb_current_accent_color   = '#333333';
			$breadcrumb_next_accent_color      = '#333333';

			if ( $template->get_slug() === 'glass' ) {
				$breadcrumb_current_text_color   = $settings_manager->get_setting( 'button_color', array( 'glass' ) );
				$breadcrumb_current_accent_color = $settings_manager->get_setting( 'button_color', array( 'glass' ) );
				$breadcrumb_next_text_color      = '#dfdcdb';
				$breadcrumb_next_accent_color    = '#dfdcdb';

			} elseif ( $template->get_slug() === 'futurist' ) {
				$breadcrumb_completed_text_color   = '#222222';
				$breadcrumb_current_text_color     = '#222222';
				$breadcrumb_next_text_color        = '#222222';
				$breadcrumb_completed_accent_color = '#222222';
				$breadcrumb_current_accent_color   = '#222222';
				$breadcrumb_next_accent_color      = '#222222';
			}

			$settings_manager->update_setting( 'breadcrumb_completed_text_color', $breadcrumb_completed_text_color, true, array( $template->get_slug() ) );
			$settings_manager->update_setting( 'breadcrumb_current_text_color', $breadcrumb_current_text_color, true, array( $template->get_slug() ) );
			$settings_manager->update_setting( 'breadcrumb_next_text_color', $breadcrumb_next_text_color, true, array( $template->get_slug() ) );
			$settings_manager->update_setting( 'breadcrumb_completed_accent_color', $breadcrumb_completed_accent_color, true, array( $template->get_slug() ) );
			$settings_manager->update_setting( 'breadcrumb_current_accent_color', $breadcrumb_current_accent_color, true, array( $template->get_slug() ) );
			$settings_manager->update_setting( 'breadcrumb_next_accent_color', $breadcrumb_next_accent_color, true, array( $template->get_slug() ) );
		}

		$custom_logo_id = get_theme_mod( 'custom_logo' );

		if ( $custom_logo_id ) {
			$settings_manager->add_setting( 'logo_attachment_id', $custom_logo_id );
		}

		// 7.1.8
		$settings_manager->add_setting( 'show_cart_item_discount', 'yes' );
	}
}
