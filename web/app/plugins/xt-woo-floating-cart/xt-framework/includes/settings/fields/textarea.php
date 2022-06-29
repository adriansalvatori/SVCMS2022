<?php
/**
 * Template for displaying the color field
 *
 * @var array $field The field.
 *
 * @package XT_Framework_Settings\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$option_value = $this->get_option( $field['name'], $field['default'] );
?>
<tr>
    <th scope="row" class="titledesc">
        <?php $this->render_field_label($field); ?>
    </th>
    <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $field['type'] ) ); ?>">
        <?php $this->render_field_before( $field ); // WPCS: XSS ok. ?>
        <textarea
            name="<?php echo esc_attr( $field['name'] ); ?>"
            id="<?php echo esc_attr( $field['id'] ); ?>"
            style="<?php echo esc_attr( $field['css'] ); ?>"
            class="<?php echo esc_attr( $field['class'] ); ?>"
            placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
            <?php $this->render_input_attributes($field); ?>
        ><?php echo esc_textarea( $option_value ); ?></textarea>
        <?php $this->render_field_description( $field ); // WPCS: XSS ok. ?>
        <?php $this->render_field_after( $field ); // WPCS: XSS ok. ?>
    </td>
</tr>