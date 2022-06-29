<?php
/**
 * This template can be overridden by copying it to yourtheme/automatewoo/birthdays/birthday-field-year.php
 *
 * @see     https://docs.woothemes.com/document/template-structure/
 * @package AutomateWoo/Birthdays/Templates
 */

defined( 'ABSPATH' ) || exit;

/**
 * Template args:
 *
 * @var array $current_birthday
 */
?>

<select name="automatewoo_birthday_year" id="automatewoo_birthday_year"
	class="woocommerce-Select automatewoo-select automatewoo-birthday-field__select automatewoo-birthday-field__select--year"
	<?php echo $current_birthday ? 'disabled' : ''; ?>
>
	<option value=""><?php esc_attr_e( 'Year', 'automatewoo-birthdays' ); ?></option>
	<?php $this_year = (int) date( 'Y' ); ?>
	<?php for ( $i = $this_year; $i >= ( $this_year - 120 ); $i-- ) : ?>
		<option value="<?php echo esc_attr( $i ); ?>" <?php $current_birthday ? selected( $current_birthday['year'], $i ) : false; ?>><?php echo esc_html( $i ); ?></option>
	<?php endfor; ?>
</select>
