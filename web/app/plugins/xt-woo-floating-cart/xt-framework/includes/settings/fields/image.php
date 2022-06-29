<?php
/**
 * Template for displaying the image field
 *
 * @var array $field The field.
 *
 * @package XT_Framework_Settings\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! empty( $field['image'] ) ) {
?>
    <tr>
        <th scope="row" colspan="2">

			<?php if(!empty($field['link'])): ?>
            <a href="<?php echo esc_url($field['link']);?>">
				<?php endif; ?>

                <img alt="" width="100%" class="xtfw-settings-image" src="<?php echo esc_url( $field['image'] );?>" />

				<?php if(!empty($field['image_mobile'])): ?>
                    <img alt="" width="100%" class="xtfw-settings-image-mobile" src="<?php echo esc_url( $field['image_mobile'] );?>" />
				<?php endif; ?>

				<?php if(!empty($field['link'])): ?>
            </a>
		    <?php endif; ?>

        </th>
    </tr>
<?php
}