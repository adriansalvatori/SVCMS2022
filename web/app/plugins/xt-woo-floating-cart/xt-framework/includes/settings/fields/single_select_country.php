<?php
/**
 * Template for displaying the single select country field
 *
 * @var array $field The field.
 *
 * @package XT_Framework_Settings\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$country_setting = (string) $this->get_option( $field['name'], $field['default'] );

if ( strstr( $country_setting, ':' ) ) {
	$country_setting = explode( ':', $country_setting );
	$country         = current( $country_setting );
	$state           = end( $country_setting );
} else {
	$country = $country_setting;
	$state   = '*';
}
?>
<tr>
    <th scope="row" class="titledesc">
        <?php $this->render_field_label($field); ?>
    </th>
    <td class="forminp">
        <?php $this->render_field_before( $field ); // WPCS: XSS ok. ?>
        <select name="<?php echo esc_attr( $field['name'] ); ?>" style="<?php echo esc_attr( $field['css'] ); ?>" data-placeholder="<?php esc_attr_e( 'Choose a country&hellip;', 'xt-framework' ); ?>" aria-label="<?php esc_attr_e( 'Country', 'xt-framework' ); ?>" class="wc-enhanced-select">
            <?php WC()->countries->country_dropdown_options( $country, $state ); ?>
        </select>
        <?php $this->render_field_description( $field ); // WPCS: XSS ok. ?>
        <?php $this->render_field_after( $field ); // WPCS: XSS ok. ?>
    </td>
</tr>