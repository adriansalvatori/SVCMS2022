<?php
/**
 * Template for displaying the radio / radio-buttons field
 *
 * @var array $field The field.
 *
 * @package XT_Framework_Settings\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$is_radio_buttons = $field['type'] === 'radio-buttons';
$option_value = $this->get_option( $field['name'], $field['default'] );
$cols = $is_radio_buttons && !empty($field['cols']) ? $field['cols'] : '';

if($is_radio_buttons) {
	$field['class'] .= ' switch-input screen-reader-text';
}
?>
<tr>
    <th scope="row" class="titledesc">
        <?php $this->render_field_label($field); ?>
    </th>
    <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $field['type'] ) ); ?>">

        <?php $this->render_field_before( $field ); // WPCS: XSS ok. ?>
        <fieldset>
            <ul>
                <?php
                foreach ( $field['options'] as $key => $val ) {
                    ?>
                    <li data-col="<?php echo esc_attr($cols); ?>">
                        <input
                                name="<?php echo esc_attr( $field['name'] ); ?>"
                                value="<?php echo esc_attr( $key ); ?>"
                                type="radio"
                                style="<?php echo esc_attr( $field['css'] ); ?>"
                                class="<?php echo esc_attr( $field['class'] ); ?>"
                            <?php $this->render_input_attributes($field); ?>
                            <?php checked( $key, $option_value ); ?>
                        />
                        <label><?php echo esc_html( $val ); ?></label>
                    </li>
                    <?php
                }
                ?>
            </ul>
            <?php $this->render_field_description( $field ); // WPCS: XSS ok. ?>
            <?php $this->render_field_after( $field ); // WPCS: XSS ok. ?>
        </fieldset>
    </td>
</tr>