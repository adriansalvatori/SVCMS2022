<?php
/**
 * This template can be overridden by copying it to yourtheme/automatewoo/birthdays/birthday-section-checkout.php
 *
 * @see     https://docs.woothemes.com/document/template-structure/
 * @package AutomateWoo/Birthdays/Templates
 */

namespace AutomateWoo\Birthdays;

defined( 'ABSPATH' ) || exit;

/**
 * Template args:
 *
 * @var array  $context
 * @var string $field_description
 * @var array  $current_birthday
 */
?>

<div class="automatewoo-birthday-section automatewoo-birthday-section--checkout form-row form-row-wide"
	<?php echo ! is_user_logged_in() && ! WC()->checkout()->is_registration_required() ? 'style="display: none;"' : ''; ?>
>
	<label><?php esc_html_e( 'Birthday (optional)', 'automatewoo-birthdays' ); ?></label>
	<?php Frontend::output_template( 'birthday-field.php', $context ); ?>
	<span class="automatewoo-birthday-section__description"><?php echo esc_html( $field_description ); ?></span>
</div>
