<?php

/**
 * The Template for displaying the login form
 *
 * Override this template by copying it to your theme or child theme
 *
 * @package     ProjectHuddle
 * @subpackage  templates
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0
 */
?>

<?php
// get custom header
ph_get_template_part('header', 'project');
?>

<div class="ph-password-form-wrapper ph-min-h-screen ph-flex ph-items-center ph-justify-center ph-bg-gray-200 ph-py-12 ph-px-4 sm:ph-px-6 lg:ph-px-8">
	<div class="max-w-md w-full">
		<div class="ph-password-form">

			<h1 class="ph-mb-8 ph-text-xl ph-font-medium ph-text-gray-700 ph-flex ph-justify-center">
				<?php if ($logo = apply_filters('ph_login_logo_id', get_option('ph_login_logo'))) : ?>

					<?php
					// get logo image
					$logo_image = wp_get_attachment_image_src($logo, 'full');

					// check retina option
					if (apply_filters('ph_login_logo_retina', get_option('ph_login_logo_retina'))) :
						$logo_image[1] = $logo_image[1] / 2;
						$logo_image[2] = $logo_image[2] / 2;
					endif;
					?>

					<a href="<?php echo home_url(); ?>">
						<img src="<?php echo esc_url($logo_image[0]); ?>" width="<?php echo (float) $logo_image[1]; ?>" height="<?php echo (float) $logo_image[2]; ?>" />
					</a>

				<?php else : ?>

					<?php _e('Please enter the password.', 'project-huddle'); ?>

				<?php endif; ?>
			</h1>

			<?php echo ph_get_the_password_form(); ?>
		</div>
	</div>
</div>