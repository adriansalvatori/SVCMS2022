<?php
/**
 * This template can be overridden by copying it to yourtheme/automatewoo/birthdays/birthday-field-month.php
 *
 * @see     https://docs.woothemes.com/document/template-structure/
 * @package AutomateWoo/Birthdays/Templates
 */

defined( 'ABSPATH' ) || exit;

global $wp_locale;

/**
 * Template args:
 *
 * @var array $current_birthday
 */
?>

<select name="automatewoo_birthday_month" id="automatewoo_birthday_month"
	class="woocommerce-Select automatewoo-select automatewoo-birthday-field__select automatewoo-birthday-field__select--month"
	<?php echo $current_birthday ? 'disabled' : ''; ?>
>
	<option value=""><?php esc_attr_e( 'Month', 'automatewoo-birthdays' ); ?></option>
	<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
		<?php $month = str_pad( $i, 2, '0', STR_PAD_LEFT ); ?>
		<option value="<?php echo esc_attr( $month ); ?>" <?php $current_birthday ? selected( $current_birthday['month'], $month ) : false; ?>><?php echo esc_html( $wp_locale->month[ $month ] ); ?></option>
	<?php endfor; ?>
</select>
