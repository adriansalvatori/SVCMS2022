<?php

/**
 * Setup Website Meta Box
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2016, Andre Gagnon
 * @since       2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH'))  exit;

/**
 * PH_Meta_Box_Project_Options Class
 *
 * @since 1.0
 */
class PH_Website_Meta_Box_Page_Feedback
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

		// create nonce field
		wp_nonce_field('project_huddle_save_data', 'project_huddle_meta_nonce');

		$permalink = parse_url(get_the_permalink($post->ID));
		$site_post = (int) get_option('ph_site_post');

		// get existing pages on website
		$existing = (array) get_post_meta($site_post, 'ph_webpages', false);

		if (!empty($existing) && is_array($existing[0])) {
			$page_id = array_search($permalink['path'], $existing[0]);
		}

		if (isset($page_id) && $page_id) {
			$threads = PH()->page->get_comment_threads($page_id);

			foreach ($threads as $thread) {
				if ((bool) get_post_meta((int) $thread, 'resolved', true)) {
					continue;
				}
				$comments = PH()->website_thread->comments($thread);
				$total    = count($comments) - 1;

				if (isset($comments[0])) { ?>
					<div class="comment-item">
						<?php echo $comments[0]->avatar; ?>

						<div class="comment-content">
							<h4><?php echo esc_html($comments[0]->comment_author); ?></h4>
							<?php echo wp_kses_post($comments[0]->comment_content); ?>
							<div class="total">
								<span class="ph-comment-count">
									<span class="dashicons dashicons-admin-comments"></span>
									<?php echo (int) $total; ?>
								</span>
								<span class="view">
									<?php _e('View', 'project-huddle'); ?>
									<span class="dashicons dashicons-arrow-right-alt"></span>
								</span>
							</div>
							<a href="<?php echo get_permalink((int) $thread); ?>"><?php _e('View', 'project-huddle'); ?></a>
						</div>
					</div>

		<?php }
			}
		} else {
			echo '<p>' . __('No Comments!', 'project-huddle') . '</p>';
		} ?>
		<div>
			<a href="<?php echo get_edit_post_link($site_post); ?>" class=" button button-primary"><?php _e('Manage Feedback', 'project-huddle'); ?></a>
		</div>
<?php
	}

	/**
	 * Save meta box data
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public static function save($post_id, $post)
	{
	}

	public static function sanitize_field($field)
	{
	}
}
