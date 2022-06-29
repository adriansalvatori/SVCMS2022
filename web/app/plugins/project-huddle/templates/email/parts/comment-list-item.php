<?php

use PH\Controllers\Utility\Truncator;

/**
 * Template for an individual comment
 */

if (!$content) {
	return;
}
$comment_style = 'padding-top: 10px; padding-bottom: 10px; padding-right: 10px;';
?>

<table cellpadding="0" cellspacing="0" width="100%" style="
	padding: 2px 0;
	margin-bottom: 5px;
	background: #fff;
	">
	<tr>
		<td valign="middle" style="<?php echo esc_attr($comment_style); ?> width: 1px; max-width: 20px;">
			<img src="<?php echo esc_url($avatar); ?>" width="20" style="border-radius:9999px;" alt="" />
		</td>
		<td valign="middle" style="<?php echo esc_attr($comment_style); ?> padding-left: 5px; padding-right:5px; white-space: nowrap;">
			<a href="<?php echo esc_url($link); ?>" style="text-decoration: none; color: #4C4C4C; font-weight: bold;">
				<?php echo sanitize_text_field($author); ?>
			</a>
		</td>
		<td valign="middle" style="<?php echo esc_attr($comment_style); ?> color: #4C4C4C; white-space: nowrap; overflow:hidden; border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0;table-layout:fixed;font-family:Helvetica Neue,Arial;font-size:14px;line-height:1.3;letter-spacing:0;text-align:left;max-width:100%;word-wrap:break-word;text-align:left;width:100%;font-size:12px;padding-right:10px;padding-left:10px;">
			<a href="<?php echo esc_url($link); ?>" style="text-decoration: none; color: #4c4c4c; display: block;">
				<?php echo wp_kses_post(Truncator::truncate($content, 20)); ?>
			</a>
		</td>
		<td valign="middle" style="<?php echo esc_attr($comment_style); ?> padding-left: 5px; padding-right:5px; white-space: nowrap;">
			<a href="<?php echo esc_url($link); ?>" style="text-decoration: none; padding: 6px;">
				<img src="<?php echo esc_url(PH_PLUGIN_URL . '/assets/img/chevron-right.png'); ?>" width="6" alt="" />
			</a>
		</td>
	</tr>
</table>