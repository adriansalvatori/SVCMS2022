<?php
/**
 * This template can be overridden by copying it to yourtheme/automatewoo/birthdays/birthday-section-account.php
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

<div id="aw-birthday" class="automatewoo-birthday-section automatewoo-birthday-section--account woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
	<label><?php esc_html_e( 'Birthday (optional)', 'automatewoo-birthdays' ); ?></label>
	<?php Frontend::output_template( 'birthday-field.php', $context ); ?>
	<span class="automatewoo-birthday-section__description"><?php echo esc_html( $field_description ); ?></span>
	<span class="automatewoo-birthday-section__already-set-text"><?php esc_html_e( 'Your birthday can not be changed once set.', 'automatewoo-birthdays' ); ?></span>
</div>

