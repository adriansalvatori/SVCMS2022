<?php
/**
 * Edit user's birthday field view.
 *
 * @package AutomateWoo\Birthdays
 */

defined( 'ABSPATH' ) || exit;

/**
 * View args:
 *
 * @var array $current_birthday
 */

global $wp_locale;

?>

<h2><?php esc_html_e( 'AutomateWoo customer birthday', 'automatewoo-birthdays' ); ?></h2>

<table class="form-table" id="automatewoo-customer-birthday">
	<tr>
		<th>
			<label><?php esc_html_e( 'Birthday', 'automatewoo-birthdays' ); ?></label>
		</th>
		<td>
			<?php if ( AW_Birthdays()->options()->require_year_of_birth() ) : ?>
				<select name="automatewoo_birthday_year" id="automatewoo_birthday_year"
					class="automatewoo-select automatewoo-birthday-field__select automatewoo-birthday-field__select--year"
				>
					<option value="">- <?php esc_attr_e( 'Year', 'automatewoo-birthdays' ); ?> -</option>
					<?php $this_year = (int) date( 'Y' ); ?>
					<?php for ( $i = $this_year; $i >= ( $this_year - 120 ); $i-- ) : ?>
						<option value="<?php echo esc_attr( $i ); ?>" <?php $current_birthday ? selected( $current_birthday['year'], $i ) : false; ?>><?php echo esc_html( $i ); ?></option>
					<?php endfor; ?>
				</select>
			<?php endif; ?>

			<select name="automatewoo_birthday_month" id="automatewoo_birthday_month"
				class="automatewoo-select automatewoo-birthday-field__select automatewoo-birthday-field__select--month"
			>
				<option value="">- <?php esc_attr_e( 'Month', 'automatewoo-birthdays' ); ?> -</option>
				<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
					<?php $month = str_pad( $i, 2, '0', STR_PAD_LEFT ); ?>
					<option value="<?php echo esc_attr( $month ); ?>" <?php $current_birthday ? selected( $current_birthday['month'], $month ) : false; ?>><?php echo esc_html( $wp_locale->month[ $month ] ); ?></option>
				<?php endfor; ?>
			</select>

			<select name="automatewoo_birthday_day" id="automatewoo_birthday_day"
				class="automatewoo-select automatewoo-birthday-field__select automatewoo-birthday-field__select--day"
			>
				<option value="">- <?php esc_html_e( 'Day', 'automatewoo-birthdays' ); ?> -</option>
				<?php for ( $i = 1; $i <= 31; $i++ ) : ?>
					<?php $day = str_pad( $i, 2, '0', STR_PAD_LEFT ); ?>
					<option value="<?php echo esc_attr( $day ); ?>" <?php $current_birthday ? selected( $current_birthday['day'], $day ) : false; ?>><?php echo esc_html( $i ); ?></option>
				<?php endfor; ?>
			</select>

			<br/>
		</td>
	</tr>
</table>
