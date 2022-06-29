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
					<p style="color:#5cb85c; font-size:14px;margin-top: 0;margin-bottom: 10px; font-weight: bold;">
						<?php echo sprintf(_n('%1d task was resolved.', '%1d tasks were resolved', $total, 'project-huddle'), number_format_i18n($total)); ?>
					</p>
				</td>
			</tr>
		</table>
	<?php endif; ?>

	<?php foreach ($comments as $comment) : ?>
		<?php
		$item_id = get_post_meta($comment->comment_post_ID, 'parent_id', true);
		$thread = get_post($comment->comment_post_ID);
		$content = get_post_field('post_content', $thread);

		$comments = ph_get_comments(
			array(
				'post_id'  => $comment->comment_post_ID,
				'order_by' => 'comment_date',
				'order'    => 'asc',
				'type__in' => array(
					'ph_comment',
				),
				'number'   => 1,
			)
		);

		if (!empty($comments)) {
			$content = $comments[0]->comment_content;
		}

		ph_get_template(
			'email/parts/comment.php',
			array(
				'comment' => $comment,
				'link'    => get_comment_link($comment->comment_ID),
				'content' => sanitize_text_field($content),
				'author'  => $thread->post_author ? get_userdata($thread->post_author)->display_name : '',
				'byline' => ph_get_the_title($item_id),
				'approval' => sprintf(__('By %1s %2s', 'project-huddle'), $comment->comment_author, sprintf(__('%s ago', 'project-huddle'), human_time_diff(strtotime($comment->comment_date), current_time('timestamp')))),
				// translators: Time ago
				'date'    => sprintf(__('%s ago', 'project-huddle'), human_time_diff(strtotime($thread->post_date), current_time('timestamp'))),
				'avatar'  => esc_url(PH_PLUGIN_URL . '/assets/img/check.png'),
			)
		);
		?>

		<?php if (++$i == apply_filters('ph_resolved_comment_truncate', 8)) : ?>
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