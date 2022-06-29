<?php
/**
 * Template for displaying the color field
 *
 * @var array $field The field.
 *
 * @package XT_Framework_Settings\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$default_value = isset($field['default']) ? $field['default'] : '';
$option_value = $this->get_option( $field['name'], $default_value );
?>
<tr>
    <th scope="row" class="titledesc">
        <?php $this->render_field_label($field); ?>
    </th>
    <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $field['type'] ) ); ?>">
        <?php $this->render_field_before( $field ); // WPCS: XSS ok. ?>
        <input
                name="<?php echo esc_attr( $field['name'] ); ?>"
                id="<?php echo esc_attr( $field['id'] ); ?>"
                type="text"
                dir="ltr"
                style="<?php echo esc_attr( $field['css'] ); ?>"
                value="<?php echo esc_attr( $option_value ); ?>"
                class="xtfw-colorpicker <?php echo esc_attr( $field['class'] ); ?>"
                placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
                data-alpha-enabled="true"
                data-default-color="<?php echo esc_attr($default_value);?>"
            <?php $this->render_input_attributes($field); ?>
        />
        <?php $this->render_field_description( $field ); // WPCS: XSS ok. ?>
        <?php $this->render_field_after( $field ); // WPCS: XSS ok. ?>
    </td>
</tr>
