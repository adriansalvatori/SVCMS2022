<?php
/**
 * This template can be overridden by copying it to yourtheme/automatewoo/birthdays/birthday-field-day.php
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

<select name="automatewoo_birthday_day" id="automatewoo_birthday_day"
	class="woocommerce-Select automatewoo-select automatewoo-birthday-field__select automatewoo-birthday-field__select--day"
	<?php echo $current_birthday ? 'disabled' : ''; ?>
>
	<option value=""><?php esc_html_e( 'Day', 'automatewoo-birthdays' ); ?></option>
	<?php for ( $i = 1; $i <= 31; $i++ ) : ?>
		<?php $day = str_pad( $i, 2, '0', STR_PAD_LEFT ); ?>
		<option value="<?php echo esc_attr( $day ); ?>" <?php $current_birthday ? selected( $current_birthday['day'], $day ) : false; ?>><?php echo esc_html( $i ); ?></option>
	<?php endfor; ?>
</select>
