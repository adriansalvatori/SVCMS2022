<?php
/**
 * Template for displaying the heading field
 *
 * @var array $field The field.
 *
 * @package XT_Framework_Settings\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if(!empty($field['title'])) {
	$heading = 'h'.(!empty($field['heading']) && in_array($field['heading'], array(1, 2, 3, 4, 5, 6)) ? intval($field['heading']) : 2);
	echo "<".esc_attr($heading).">".esc_html($field['title'])."</".esc_attr($heading).">";
}
