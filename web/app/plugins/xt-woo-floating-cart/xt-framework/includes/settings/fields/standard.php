<?php
/**
 * Template for displaying standard fields
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
        <input
            name="<?php echo esc_attr( $field['name'] ); ?>"
            id="<?php echo esc_attr( $field['id'] ); ?>"
            type="<?php echo esc_attr( $field['type'] ); ?>"
            style="<?php echo esc_attr( $field['css'] ); ?>"
            value="<?php echo esc_attr( $option_value ); ?>"
            class="<?php echo esc_attr( $field['class'] ); ?>"
            placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
			<?php $this->render_input_attributes($field); ?>
        />
		<?php if($field['type'] == 'range'): ?>
            <output
                    name="<?php echo esc_attr( $field['id'] ); ?>_amount"
                    id="<?php echo esc_attr( $field['id'] ); ?>_amount"
                    for="<?php echo esc_attr( $field['id'] ); ?>"
            ><?php echo esc_html( $field['prefix'] ); ?><?php echo esc_attr( $option_value ); ?><?php echo esc_html( $field['suffix'] ); ?></output>
		<?php endif; ?>
		<?php $this->render_field_description( $field ); // WPCS: XSS ok. ?>
		<?php $this->render_field_after( $field ); // WPCS: XSS ok. ?>
    </td>
</tr>