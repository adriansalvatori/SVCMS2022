<?php

$customizer = xt_woo_floating_cart()->customizer();
$module = xt_woo_floating_cart()->modules()->get('add-to-cart');

if(!empty($module)) {

	$module_customizer = $module->customizer();

	$move_options = array(
		'trigger_hide_view_cart' => 'hide_view_cart_button'
	);

	$options = $customizer->get_options();
	$module_options = $module_customizer->get_options();

	foreach ( $move_options as $key => $new_key ) {

		$module_options[$new_key] = isset($options[$key]) ? $options[$key] : '';

		if(isset($options[$key])) {
			$module_options[$new_key] = $options[$key];
			unset($options[$key]);
		}
	}

	$module_options['single_ajax_add_to_cart'] = '1';

	$module_customizer->update_options($module_options);
	$customizer->update_options($options);
}
