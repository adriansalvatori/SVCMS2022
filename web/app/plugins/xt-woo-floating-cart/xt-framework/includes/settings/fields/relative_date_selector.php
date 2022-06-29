<?php
/**
 * Template for displaying the relative date selector field
 *
 * @var array $field The field.
 *
 * @package XT_Framework_Settings\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$periods      = array(
	'days'   => __( 'Day(s)', 'xt-framework' ),
	'weeks'  => __( 'Week(s)', 'xt-framework' ),
	'months' => __( 'Month(s)', 'xt-framework' ),
	'years'  => __( 'Year(s)', 'xt-framework' ),
);
$option_value = xtfw_parse_relative_date_option( $this->get_option( $field['name'], $field['default'] ) );
?>
<tr>
    <th scope="row" class="titledesc">
        <?php $this->render_field_label($field); ?>
    </th>
    <td class="forminp">
        <?php $this->render_field_before( $field ); // WPCS: XSS ok. ?>
        <input
                name="<?php echo esc_attr( $field['name'] ); ?>[number]"
                id="<?php echo esc_attr( $field['id'] ); ?>"
                type="number"
                style="width: 80px;"
                value="<?php echo esc_attr( $option_value['number'] ); ?>"
                class="<?php echo esc_attr( $field['class'] ); ?>"
                placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
                step="1"
                min="1"
            <?php $this->render_input_attributes($field); ?>
        />&nbsp;
        <select name="<?php echo esc_attr( $field['name'] ); ?>[unit]" style="width: auto;">
            <?php
            foreach ( $periods as $field => $label ) {
                echo '<option value="' . esc_attr( $field ) . '" ' . selected( $option_value['unit'], $field, false ) . '>' . esc_html( $label ) . '</option>';
            }
            ?>
        </select>
        <?php $this->render_field_description( $field ); // WPCS: XSS ok. ?>
        <?php $this->render_field_after( $field ); // WPCS: XSS ok. ?>
    </td>
</tr>