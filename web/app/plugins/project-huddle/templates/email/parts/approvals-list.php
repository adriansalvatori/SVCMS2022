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
					<p style="color:<?php echo sanitize_hex_color($color); ?>; font-size:14px;margin-top: 0;margin-bottom: 10px; font-weight: bold;">
						<?php echo sprintf(_n('%1d new comment was added.', '%1d new comments were added', $total, 'project-huddle'), number_format_i18n($total)); ?>
					</p>
				</td>
			</tr>
		</table>
	<?php endif; ?>

	<?php foreach ($comments as $comment) : ?>
		<?php
				ph_get_template(
					'email/parts/comment.php',
					array(
						'link'    => get_comment_link($comment->comment_ID),
						'content' => $comment->comment_content,
						'author'  => $comment->comment_author,
						'avatar'  => get_avatar_url($comment->comment_author_email, 32),
					)
				);
				?>

		<?php if (++$i == apply_filters('ph_approvals_truncate', 6)) : ?>
			<?php $project_link = get_the_permalink($project_id); ?>

			<table cellpadding="0" cellspacing="0" width="100%" style="margin-top: 15px;">
				<tr>
					<td colspan="5" style="padding-top:15px; text-align:center; max-width: 100%; width: 9999px;">
						<?php if ($total - $i > 0) : ?>
							<a href="<?php echo esc_url($project_link); ?>" style="color:<?php echo sanitize_hex_color($color); ?>; font-size:14px; text-decoration:none;">
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