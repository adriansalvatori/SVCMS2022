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
global $ph_login_page;
$ph_login_page = true;
// get custom header
ph_get_template_part('header', 'project');
?>

<div class="ph-min-h-screen ph-flex ph-items-center ph-justify-center ph-bg-gray-100 ph-py-12 ph-px-4 sm:ph-px-6 lg:ph-px-8">
	<div class="ph-max-w-md ph-w-full">
		<div class="ph-text-3xl ph-mb-6 ph-text-center ph-leading-tight ph-text-gray-900 ph-tracking-tight">
			<?php _e('Please Login', 'project-huddle'); ?>
		</div>

		<?php if (isset($_GET['login']) && 'failed' === $_GET['login']) : ?>
			<div class="ph-rounded-md ph-bg-red-100 ph-p-4 ph-mb-4">
				<div class="ph-flex">
					<div class="ph-flex-shrink-0">
						<svg class="ph-h-5 ph-w-5 ph-text-red-400" fill="currentColor" viewBox="0 0 20 20">
							<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
						</svg>
					</div>
					<div class="ph-ml-3">
						<div class="ph-leading-5 ph-text-red-700">
							<?php _e('Incorrect username or password. Please try again.', 'project-huddle'); ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php
		/**
		 * Filters content to display at the top of the login form.
		 *
		 * The filter evaluates just following the opening form tag element.
		 *
		 * @since 3.0.0
		 *
		 * @param string $content Content to display. Default empty.
		 * @param array  $args    Array of login form arguments.
		 */
		$login_form_top = apply_filters('login_form_top', '', []);

		/**
		 * Filters content to display in the middle of the login form.
		 *
		 * The filter evaluates just following the location where the 'login-password'
		 * field is displayed.
		 *
		 * @since 3.0.0
		 *
		 * @param string $content Content to display. Default empty.
		 * @param array  $args    Array of login form arguments.
		 */
		$login_form_middle = apply_filters('login_form_middle', '', []);

		/**
		 * Filters content to display at the bottom of the login form.
		 *
		 * The filter evaluates just preceding the closing form tag element.
		 *
		 * @since 3.0.0
		 *
		 * @param string $content Content to display. Default empty.
		 * @param array  $args    Array of login form arguments.
		 */
		$login_form_bottom = apply_filters('login_form_bottom', '', []);

		$redirect = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		?>

		<form data-cy="login-form" name="loginform" class="ph-space-y-4" id="loginform" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post">
			<?php echo $login_form_top; ?>
			<p class="login-username">
				<input data-cy="username" type="text" class="ph-outline-none ph-placeholder-gray-800 ph-w-full ph-bg-gray-200 ph-border-none ph-block ph-p-5 ph-mb-3" name="log" id="user_login" class="input" value="" size="20" placeholder="<?php _e('Username or Email Address', 'project-huddle'); ?>">
			</p>
			<p class="login-password">
				<input data-cy="password" type="password" class="ph-outline-none ph-placeholder-gray-800 ph-w-full ph-bg-gray-200 ph-border-none ph-block ph-p-5 ph-mb-3" name="pwd" id="user_pass" class="input" value="" size="20" placeholder="<?php _e('Password', 'project-huddle'); ?>">
			</p>
			<?php echo $login_form_middle; ?>
			<p class="login-submit ph-flex ph-flex-wrap ph-items-center">
				<input data-cy="submit" type="submit" name="wp-submit" id="wp-submit" class="ph-bg-primary ph-text-white ph-px-6 ph-py-5 focus:ph-outline-none ph-leading-none ph-rounded-sm ph-transition ph-duration-200 ph-uppercase ph-tracking-widest ph-text-xs ph-font-bold" value="Log In">
				<input type="hidden" name="redirect_to" value="<?php echo esc_url($redirect); ?>">
				<a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="ph-ml-auto hover:ph-text-gray-700 ph-text-gray-600 ph-bg-transparentph-px-6 ph-py-5 focus:ph-outline-none ph-leading-none ph-rounded-sm ph-transition ph-duration-200 ph-uppercase ph-tracking-widest ph-text-xs ph-font-bold" :text="true" type="button" @click.native="resetDialog"><?php _e('Forgot your password?', 'project-huddle') ?></a>
			</p>
			<?php echo $login_form_bottom; ?>
		</form>
	</div>
</div>