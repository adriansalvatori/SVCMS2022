<?php
/**
 * Template for displaying the single select page field
 *
 * @var array $field The field.
 *
 * @package XT_Framework_Settings\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$args = array(
	'name'             => $field['name'],
	'id'               => $field['id'],
	'sort_column'      => 'menu_order',
	'sort_order'       => 'ASC',
	'show_option_none' => ' ',
	'class'            => $field['class'],
	'echo'             => false,
	'selected'         => absint( $this->get_option( $field['name'], $field['default'] ) ),
	'post_status'      => 'publish,private,draft',
);

if ( isset( $field['args'] ) ) {
	$args = wp_parse_args( $field['args'], $args );
}

?>
<tr class="single_select_page">
    <th scope="row" class="titledesc">
        <?php $this->render_field_label($field); ?>
    </th>
    <td class="forminp">
        <?php $this->render_field_before( $field ); // WPCS: XSS ok. ?>
        <?php echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'xt-framework' ) . "' style='" . $field['css'] . "' class='" . $field['class'] . "' id=", wp_dropdown_pages( $args ) ); // WPCS: XSS ok. ?>
        <?php $this->render_field_description( $field ); // WPCS: XSS ok. ?>
        <?php $this->render_field_after( $field ); // WPCS: XSS ok. ?>
    </td>
</tr>

