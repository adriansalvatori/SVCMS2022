<?php

/**
 * Template part for displaying project section name
 */
$color          = esc_html(get_option('ph_highlight_color', '#4353ff'));
$approval_color = $approved ? '#5cb85c' : '#6f7c8a';
?>

<table cellpadding="0" cellspacing="0" width="100%" style="   
	border-bottom: 1px solid #E7E7E7;">
	<tr>
		<td style="border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0;table-layout:fixed;color:#4C4C4C;font-family:Helvetica Neue, Arial;font-size:14px;line-height:1.3;letter-spacing:0;text-align:left;max-width:100%;word-wrap:break-word;text-align:left;width:999px;font-size:12px;padding-bottom:4px;padding-right:0px;padding-left:0px;padding-top:40px;">
			<h2 style="font-size: 20px; margin:0; margin-top: 5px; margin-bottom: 5px; color: #4C4C4C;  mso-line-height-rule:exactly;"><a href="<?php echo esc_url(get_permalink($project_id)); ?>" style="color: #4C4C4C; text-decoration: none;">
					<?php echo wp_kses_post($name); ?>
					<?php if ('ph-project' == get_post_type($project_id)) : ?>
						<?php if ($approved) : ?>
							<small style="font-weight: normal;
									color: #5cb85c;
									background: #EDFAED;
									padding: 6px;
									line-height: 1;
									font-size: 12px;
									margin: 0 5px;">
								<?php esc_html_e('Approved', 'project-huddle'); ?>
							</small>
						<?php elseif ($item_approval) : ?>
							<small style="font-weight: normal;
								color: #87949e;
								line-height: 1;
								font-size: 12px;
								margin: 0 5px;
								padding: 6px;
								background: #f0f3f5;
								border-radius: 3px;">
								<?php echo sprintf(__('%1d of %2d approved', 'project-huddle'), $item_approval['approved'], $item_approval['total']); ?>
							</small>
						<?php endif; ?>
						</small>
					<?php endif; ?>
				</a>
			</h2>
		</td>
	</tr>
</table>