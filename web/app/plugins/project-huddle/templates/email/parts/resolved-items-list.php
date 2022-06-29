<?php

/**
 * Template part for displaying a list of comments in an email
 */
$color         = esc_html(get_option('ph_highlight_color', '#4353ff'));
$comment_style = 'padding-top: 10px; padding-bottom: 10px; padding-left: 10px; padding-right: 10px;';
?>

<?php if ($comments) : ?>
	<?php $i = 0; ?>

	<?php if ($total) : ?>
		<table cellpadding="0" cellspacing="0" width="100%" style="margin-top: 20px;">
			<tr>
				<td colspan="5" style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0;table-layout:fixed;color:{$color};font-family:Helvetica Neue,Arial;font-size:14px;line-height:1.3;letter-spacing:0;text-align:left;max-width:100%;word-wrap:break-word;text-align:left;width:100%;font-size:12px;;border: none; padding-bottom:8px;padding-right:8px;padding-top:8px;">
					<p style="color:#5cb85c; font-size:14px;margin-top: 0;margin-bottom: 0px; font-weight: bold;">
						<?php if ($type == 'Website') { ?>
							<?php echo sprintf(_n('%1d page was approved.', '%1d pages were approved', $approved, 'project-huddle'), number_format_i18n($approved)); ?>
						<?php } else { ?>
							<?php echo sprintf(_n('%1d image was approved.', '%1d images were approved', $approved, 'project-huddle'), number_format_i18n($approved)); ?>
						<?php  } ?>
					</p>
				</td>
			</tr>
		</table>
	<?php endif; ?>

	<?php foreach ($comments as $comment) : ?>
		<?php
		$thumb_id = (int) get_post_thumbnail_id($comment->comment_post_ID);
		$type = get_post_mime_type($thumb_id);
		$url = '';
		if (in_array($type, array('image/jpeg', 'image/jpg', 'image/png', 'image/gif'))) {
			$url = get_the_post_thumbnail_url($comment->comment_post_ID, 'thumbnail');
		}

		ph_get_template(
			'email/parts/image.php',
			array(
				'comment' => $comment,
				"item_url" => esc_url($url),
				'item_link' => get_the_permalink($comment->comment_post_ID),
				'item_title' => ph_get_the_title($comment->comment_post_ID),
				'content' => sanitize_text_field($comment->comment_content),
				'author'  => $comment->comment_author,
				// translators: Time ago
				'date'    => sprintf(__('%s ago', 'project-huddle'), human_time_diff(strtotime($comment->comment_date), current_time('timestamp'))),
				'avatar'  => esc_url(PH_PLUGIN_URL . '/assets/img/check.png'),
			)
		);
		?>

		<?php if (++$i == apply_filters('ph_weekly_comment_truncate', 6)) : ?>
			<?php $project_link = get_the_permalink($project_id); ?>

			<table cellpadding="0" cellspacing="0" width="100%" style="margin-top: 15px;">
				<tr>
					<td colspan="5" style="padding-top:15px; text-align:center; max-width: 100%; width: 9999px;">
						<?php if ($total - $i > 0) : ?>
							<a href="<?php echo esc_url($project_link); ?>" style="color:#5cb85c; font-size:14px; text-decoration:none;">
								<?php echo sprintf(esc_html__('+ %1d More', 'project-huddle'), $total - $i); ?>
							</a>
						<?php endif; ?>
					</td>
				</tr>
			</table>

			<?php break; ?>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>