<?php
/**
 * Template for displaying the select / multiselect fields
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
        <select
            name="<?php echo esc_attr( $field['name'] ); ?><?php echo ( 'multiselect' === $field['type'] ) ? '[]' : ''; ?>"
            id="<?php echo esc_attr( $field['id'] ); ?>"
            style="<?php echo esc_attr( $field['css'] ); ?>"
            class="xtfw-select <?php echo esc_attr( $field['class'] ); ?>"
            <?php $this->render_input_attributes($field); ?>
            <?php echo 'multiselect' === $field['type'] ? 'multiple="multiple"' : ''; ?>
        >
            <?php
            if(isset($field['options']) && is_array($field['options'])) {
                foreach ( $field['options'] as $key => $val ) {
                    ?>
                    <option value="<?php echo esc_attr( $key ); ?>"
                        <?php

                        if ( is_array( $option_value ) ) {
                            selected( in_array( (string) $key, $option_value, true ));
                        } else {
                            selected( $option_value, (string) $key );
                        }

                        ?>
                    ><?php echo esc_html( $val ); ?></option>
                    <?php
                }
            }
            ?>
        </select>
        <?php $this->render_field_description( $field ); // WPCS: XSS ok. ?>
        <?php $this->render_field_after( $field ); // WPCS: XSS ok. ?>
    </td>
</tr>