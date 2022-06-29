<?php

/**
 * Project Email notifications meta box
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * PH_Meta_Box_Project_Emails Class
 *
 * @since 1.0
 */
class PH_Meta_Box_Project_Email_Notifications
{

	/**
	 * Output the metabox
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public static function output($post)
	{
?>

		<div id="project_emails_notifications_container" class="ph_meta_box">
			<div class="ph-field">
				<div id="ph-manual-email-send"></div>
			</div>
			<p><strong><?php _e('Email Settings', 'project-huddle'); ?></strong></p>
			<div class="ph-field">
				<a href="<?php echo esc_url(admin_url('profile.php#ph-emails')); ?>" class="el-button el-button--default el-button--mini">
					<?php _e('Edit Global Email Settings', 'project-huddle'); ?> &rarr;
				</a>
			</div>
		</div>
<?php
	}
}
