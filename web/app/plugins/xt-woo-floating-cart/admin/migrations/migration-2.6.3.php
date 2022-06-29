<?php
$customizer = xt_woo_floating_cart()->customizer();

$value = $customizer->get_option('woo_info_notice_hide', 'flex');
$value = $value === 'flex' ? '0' : '1';
$customizer->update_option('woo_info_notice_hide', $value);

$value = $customizer->get_option('woo_success_notice_hide', 'flex');
$value = $value === 'flex' ? '0' : '1';
$customizer->update_option('woo_success_notice_hide', $value);
