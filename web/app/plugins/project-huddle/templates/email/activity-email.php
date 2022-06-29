<?php

/**
 * Resolved Thread Email
 */
ph_get_template('email/default-header.php'); ?>

<td>
	<table cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td valign="top">
				<h1 style="font-size: 18px; text-align: center; margin:0; mso-line-height-rule:exactly;">{{title}}</h1>
				{{message}}
				{{avatar}}
			</td>
		</tr>
	</table>

	{{sections}}

</td>
<?php
ph_get_template('email/default-footer.php');
