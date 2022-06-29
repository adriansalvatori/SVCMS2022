<?php
/**
 * Reminder email
 */
ph_get_template( 'email/default-header.php' ); ?>
<td align="left" valign="top"> 
	<?php
        echo wpautop(apply_filters(
            'ph_reminder_email_text',
            '<p style="color: #999;">Hi {{username}},</p>
                <p style="color: #999;">Here is the conversation waiting for your action: <strong style="color: #000;">{{post_title}}</strong>.</p>
                <p style="text-align:center">
                {{link}}
                </p>'
        ));
    ?>
</td>
