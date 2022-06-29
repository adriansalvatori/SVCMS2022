<?php

/**
 * Shows project approval
 */
?>

<table cellpadding="0" cellspacing="0" width="100%" style="margin-top: 30px;
	background: #EDFAED;
	padding: 20px;
	border-radius: 4px;">
	<tr>
		<td valign="top" style="padding-right:10px; width: 1px; max-width: 20px;">
			<img src="<?php echo esc_url($avatar); ?>" width="20" style="border-radius:9999px" alt="" />
		</td>
		<td valign="top" style="padding-left: 5px; padding-right:5px;">
			<p style="color:#5cb85c; font-size:14px;margin-top: 0;margin-bottom: 0px; font-weight: bold; line-height:1;">
				<?php printf(esc_html__('%1$s approved this project %2$s.', 'project-huddle'), $person, $date); ?>
			</p>
		</td>
	</tr>
</table>