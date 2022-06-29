<?php
/**
 * Template for displaying the admin action button
 *
 * @var array $field The field.
 *
 * @package XT_Framework_Settings\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if (isset($field['title']) && isset($field['button_text']) && isset($field['id'])) {

	$confirm_text = !empty($field['confirm_text']) ? $field['confirm_text'] : esc_attr__('Are you sure you want to proceed?', 'xt-framework');

	$classes = $field['class'];
	?>
	<tr>
		<th scope="row" class="titledesc">
			<label for="admin_action">
				<?php echo wp_kses_post($field['title']); ?>
				<?php xtfw_help_tip($field['desc_tip']); ?>
			</label>
		</th>
		<td class="forminp forminp-text">

			<?php $this->render_field_before( $field ); // WPCS: XSS ok. ?>

			<fieldset>
				<a data-confirm="<?php echo esc_attr($confirm_text); ?>"
				   href="<?php echo esc_url(add_query_arg(array('action' => $field['id']))); ?>"
				   class="button <?php echo esc_attr($classes); ?>"
				   id="<?php echo esc_attr($field['id']); ?>"
				>
					<?php echo esc_html($field['button_text']); ?>
					<?php $this->render_spinner(); ?>
				</a>
			</fieldset>

			<?php $this->render_field_description( $field ); // WPCS: XSS ok. ?>
			<?php $this->render_field_after( $field ); // WPCS: XSS ok. ?>

		</td>
	</tr>
	<?php
}
