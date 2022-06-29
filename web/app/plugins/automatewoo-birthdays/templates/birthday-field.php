<?php
/**
 * This template can be overridden by copying it to yourtheme/automatewoo/birthdays/birthday-field.php
 *
 * @see     https://docs.woothemes.com/document/template-structure/
 * @package AutomateWoo/Birthdays/Templates
 */

namespace AutomateWoo\Birthdays;

defined( 'ABSPATH' ) || exit;

/**
 * Template args:
 *
 * @var array $context
 * @var bool  $require_year
 * @var bool  $use_us_format
 */
?>

<div class="automatewoo-birthday-field automatewoo-birthday-field--<?php echo $require_year ? 'has-year' : 'no-year'; ?> <?php echo $use_us_format ? 'automatewoo-birthday-field--us-format' : ''; ?>">

	<?php if ( $use_us_format ) : ?>
		<?php Frontend::output_template( 'birthday-field-month.php', $context ); ?>
		<?php Frontend::output_template( 'birthday-field-day.php', $context ); ?>
	<?php else : ?>
		<?php Frontend::output_template( 'birthday-field-day.php', $context ); ?>
		<?php Frontend::output_template( 'birthday-field-month.php', $context ); ?>
	<?php endif; ?>

	<?php if ( $require_year ) : ?>
		<?php Frontend::output_template( 'birthday-field-year.php', $context ); ?>
	<?php endif; ?>

	<div class="clear"></div>

</div>
