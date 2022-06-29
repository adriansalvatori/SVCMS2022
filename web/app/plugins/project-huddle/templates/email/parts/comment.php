<?php

use PH\Controllers\Utility\Truncator;

/**
 * Template for an individual comment
 */

// if (!$content) {
// 	return;
// }
$comment_style = 'padding-top: 10px; padding-bottom: 10px; padding-right: 10px;';
?>

<table cellpadding="0" cellspacing="0" width="100%" style="
	padding: <?php echo isset($padding) ? (int) $padding : 2; ?>px;
	margin-bottom: 5px;
	<?php echo isset($background) ? 'background: ' . sanitize_hex_color($background) . ';' : ''; ?>
	">
	<tr>
		<td valign="top" style="<?php echo esc_attr($comment_style); ?> width: 1px; max-width: 20px;">
			<img src="<?php echo esc_url($avatar); ?>" width="28" style="border-radius:9999px" alt="" />
		</td>
		<td valign="top" style="<?php echo esc_attr($comment_style); ?> color: #4C4C4C; border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0;table-layout:fixed;font-family:Helvetica Neue,Arial;font-size:14px;line-height:1.3;letter-spacing:0;text-align:left;max-width:100%;word-wrap:break-word;text-align:left;width:100%;font-size:12px;padding-right:10px;padding-left:10px; padding-bottom: 25px;">
			<table cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td>
						<a href="<?php echo esc_url($link); ?>" style="text-decoration: none; color: #4C4C4C; margin-bottom: 6px; display: inline-block;">
							<span style="font-weight: bold; font-size: 14px; color: #51595f;">
								<?php echo sanitize_text_field($author); ?>
							</span>
							<span style="font-size: 12px; color: #b1b1b1;">
								<span style="display: inline-block;"> -
									<?php echo sanitize_text_field($date); ?>
								</span>
							</span>
						</a>
					</td>
				</tr>
				<tr>
					<td style="padding-top: 5px;">
						<a href="<?php echo esc_url($link); ?>" style="text-decoration: none; color: #4c4c4c; display: block; font-size:14px; line-height: 1.4em; margin-bottom: 5px;">
							<?php echo wp_kses_post(Truncator::truncate($content, 20)); ?>
						</a>
					</td>
				</tr>
				<tr>
					<td style="padding: 0;">
						<a href="<?php echo esc_url($link); ?>" style="
					font-size: 12px;
					text-decoration: none;
					display: block;
					line-height: 18px;
					margin: 10px 0 5px;
					color: <?php echo isset($color) ? sanitize_hex_color($color) : '#b4b4b4'; ?>;
					">
							<img src="<?php echo esc_url(PH_PLUGIN_URL . '/assets/img/pin.png'); ?>" width="10" style="width: 10px; display: inline-block; margin-right: 4px" alt="" />
							<?php echo sanitize_text_field($byline); ?><br />
							<?php if (isset($approval)) : ?>
								<img src="<?php echo esc_url(PH_PLUGIN_URL . '/assets/img/user.png'); ?>" width="10" style="width: 10px; display: inline-block; margin-right: 4px" alt="" />
								<?php echo sanitize_text_field($approval); ?>
							<?php endif; ?>
						</a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>