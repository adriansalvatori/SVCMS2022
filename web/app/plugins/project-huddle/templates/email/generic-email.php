<?php
/**
 * New comment email
 */
ph_get_template( 'email/default-header.php' ); ?>
    <td align="right" valign="top" width="40" style="padding:20px 20px 0 0;">
        {{ avatar }}
    </td>
    <td align="left" valign="top">
		<?php
		$message = '<p style="color: #999;">';
		$message .= '<strong style="color: #000;">{{commenter}}</strong>';
		$message .= '</p>';
		$message .= '{{content}}';
		$message .= '<p style="text-align:center">{{link}}</p>';
		echo wp_kses_post( wpautop( apply_filters( 'ph_share_post_email', $message ) ) );
		?>
    </td>
<?php ph_get_template( 'email/default-footer.php' );
