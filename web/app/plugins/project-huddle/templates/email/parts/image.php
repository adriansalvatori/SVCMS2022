<?php

/**
 * Template for an individual comment
 */

$comment_style = 'padding-top: 10px; padding-bottom: 10px;';
?>

<table cellpadding="0" cellspacing="0" width="100%" style="
	padding: 2px 0;
	margin-bottom: 5px;
	background: #fff;
	">
	<tr>
		<?php if ($item_url) : ?>
			<td valign="middle" style="<?php echo esc_attr($comment_style); ?> width: 50px; padding-right: 10px;">
				<a href="<?php echo esc_url($item_link); ?>">
					<img src="<?php echo esc_url($item_url); ?>" width="50" alt="<?php esc_attr($item_title); ?>" style="margin-top:2px;height:auto;line-height:100%;outline:none;text-decoration:none; border: 1px solid #dcdcdc; border-radius: 3px; box-shadow: 0 0 20px rgba(0,0,0,0.1);" />
				</a>
			</td>
		<?php endif; ?>
		<td valign="middle" style="color: #4C4C4C; border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0;table-layout:fixed;font-family:Helvetica Neue,Arial;font-size:14px;line-height:1.3;letter-spacing:0;text-align:left;max-width:100%;word-wrap:break-word;text-align:left;width:100%;font-size:12px;">
			<a href="<?php echo esc_url($item_link); ?>" style="text-decoration: none; color: #4C4C4C; margin-bottom: 6px; display: inline-block;">
				<span style="font-size: 13px; color: #5cb85c;">
					<?php if (!$item_url && $item_title) : ?>
						<strong style="color: #86949e"><?php echo wp_kses_post($item_title); ?></strong> <br />
					<?php endif; ?>
					<?php echo sprintf(__('Approved by %1s %2s', 'project-huddle'), sanitize_text_field($author), sanitize_text_field($date)); ?>
				</span>
			</a>
		</td>
	</tr>
</table>