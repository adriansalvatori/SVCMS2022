<?php
/**
 * Template for displaying the checkbox field
 *
 * @var array $field The field.
 *
 * @package XT_Framework_Settings\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$option_value     = $this->get_option( $field['name'], $field['default'] );
$visibility_class = array();

if ( ! isset( $field['hide_if_checked'] ) ) {
    $field['hide_if_checked'] = false;
}
if ( ! isset( $field['show_if_checked'] ) ) {
    $field['show_if_checked'] = false;
}
if ( 'yes' === $field['hide_if_checked'] || 'yes' === $field['show_if_checked'] ) {
    $visibility_class[] = 'hidden_option';
}
if ( 'option' === $field['hide_if_checked'] ) {
    $visibility_class[] = 'hide_options_if_checked';
}
if ( 'option' === $field['show_if_checked'] ) {
    $visibility_class[] = 'show_options_if_checked';
}

if ( ! isset( $field['checkboxgroup'] ) || 'start' === $field['checkboxgroup'] ) {
    ?>
        <tr class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
            <th scope="row" class="titledesc">
                <?php $this->render_field_label($field); ?>
            </th>
            <td class="forminp forminp-checkbox">
                <?php $this->render_field_before( $field ); // WPCS: XSS ok. ?>
                <fieldset>
    <?php
} else {
    ?>
        <?php $this->render_field_before( $field ); // WPCS: XSS ok. ?>
        <fieldset class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
    <?php
}

if ( ! empty( $field['title'] ) ) {
    ?>
        <legend class="screen-reader-text"><span><?php echo esc_html( $field['title'] ); ?></span></legend>
    <?php
}
?>
<input
    name="<?php echo esc_attr( $field['name'] ); ?>"
    id="<?php echo esc_attr( $field['id'] ); ?>"
    type="checkbox"
    class="xtfw-switch <?php echo esc_attr( isset( $field['class'] ) ? $field['class'] : '' ); ?>"
    value="1"
    <?php checked( $option_value, 'yes' ); ?>
    <?php $this->render_input_attributes($field); ?>
/>
<label class="xtfw-switch-label<?php echo (!empty($field['desc_inline']) ? ' inline': '')?>" for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html__('Toggle', 'xt-framework'); ?></label>
<?php $this->render_field_description( $field ); // WPCS: XSS ok.  ?>
<?php

if ( ! isset( $field['checkboxgroup'] ) || 'end' === $field['checkboxgroup'] ) {
                ?>
                </fieldset>
                <?php $this->render_field_after( $field ); // WPCS: XSS ok. ?>
            </td>
        </tr>
    <?php
} else {
    ?>
        </fieldset>
        <?php $this->render_field_after( $field ); // WPCS: XSS ok. ?>
    <?php
}