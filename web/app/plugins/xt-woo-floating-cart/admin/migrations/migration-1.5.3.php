<?php

$customizer = xt_woo_floating_cart()->customizer();

$convert_bool_to_string = array(
	'cart_product_show_sku',
	'active_cart_body_lock_scroll',
	'flytocart_animation',
	'trigger_hide_view_cart',
	'visible_on_empty'
);

$sync_desktop_values_to_other = array(
	'cart_width' => array('cart_width_tablet', 'cart_width_mobile'),
	'cart_height' => array('cart_height_tablet', 'cart_height_mobile'),
	'border_radius' => array('border_radius_tablet', 'border_radius_mobile')
);

$options = $customizer->get_options();

foreach ( $convert_bool_to_string as $key ) {

	if(isset($options[$key])) {

		if ( is_bool($options[$key]) ) {

			$options[$key] = (string) $options[$key];
		}
	}
}

foreach ( $sync_desktop_values_to_other as $key => $new_keys) {

	if(isset($options[$key])) {

		$original_value = $options[$key];

		// Sync values with new keys
		foreach($new_keys as $new_key) {

			$options[$new_key] = $original_value;
		}
	}
}

$customizer->update_options( $options );