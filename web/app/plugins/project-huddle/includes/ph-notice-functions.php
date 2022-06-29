<?php

/**
 * Functions necessary for dismissing notices
 */

/**
 * Output javascript necessary for dismissing notices
 */
function ph_dismiss_js()
{ ?>
	<script>
		jQuery(function($) {
			$(document).on('click', '.ph-notice .notice-dismiss', function() {
				// Read the "data-notice" information to track which notice
				var type = $(this).closest('.ph-notice').data('notice');
				// Since WP 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				$.ajax(ajaxurl, {
					type: 'POST',
					data: {
						action: 'ph_dismissed_notice_handler',
						type: type
					}
				});
			});
		});
	</script>
	<?php }

/**
 * Stores notice dismissing in options table
 */
function ph_ajax_notice_handler()
{
	$type = isset($_POST['type']) ? $_POST['type'] : false;
	// Store it in the options table
	if ($type) {
		update_option("dismissed-$type", true);
		update_site_option("dismissed-$type", true);
	}
}

add_action('wp_ajax_ph_dismissed_notice_handler', 'ph_ajax_notice_handler');

// front end
function ph_add_message($message, $type = '')
{
	// success is the default
	if (empty($type))
		$type = 'success';

	// Send the values to the cookie for page reload display
	@setcookie('wp-message',      $message, time() + 60 * 60 * 24, COOKIEPATH);
	@setcookie('wp-message-type', $type,    time() + 60 * 60 * 24, COOKIEPATH);
}

function ph_setup_message()
{
	if (isset($_COOKIE['wp-message']))
		$template_message = stripslashes($_COOKIE['wp-message']);

	if (isset($_COOKIE['wp-message-type']))
		$template_message_type = stripslashes($_COOKIE['wp-message-type']);

	add_action('ph_template_notices', 'ph_render_message');

	if (isset($_COOKIE['wp-message']))
		@setcookie('wp-message', false, time() - 1000, COOKIEPATH);
	if (isset($_COOKIE['wp-message-type']))
		@setcookie('wp-message-type', false, time() - 1000, COOKIEPATH);
}
add_action('wp', 'ph_setup_message', 5);


function ph_render_message()
{
	if (isset($_COOKIE['wp-message'])) {
		$template_message = stripslashes($_COOKIE['wp-message']);

		if (!empty($template_message)) :
			$type    = ('success' == stripslashes($_COOKIE['wp-message-type'])) ? 'updated' : 'error';
			$content = apply_filters('ph_render_message_content', stripslashes($_COOKIE['wp-message']), $type);

			switch ($type) {
				case 'error':
					$class = 'toast-error';
					$icon = 'icon-stop';
					break;
				default:
					$class = 'toast-primary';
					$icon = 'icon-check';
					break;
			}
	?>

			<div id="ph-message" data-cy="message" class="toast <?php echo esc_attr($class); ?> ph-template-message">
				<i class="icon <?php echo esc_attr($icon); ?>"></i>
				<?php echo $content; ?>
			</div>

<?php
			do_action('render_message');
		endif;
	}
}
