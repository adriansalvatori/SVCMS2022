<?php
/**
 * Assigned comment email
 */
ph_get_template( 'email/default-header.php' ); ?>
    <td align="right" valign="top" width="40" style="padding:20px 20px 0 0;">
        {{ avatar }}
    </td>
    <td align="left" valign="top">
		<?php
		// translatable message
		$message = '<p style="color: #999;">';
		$message .= sprintf( __( '%1s assigned you an issue on %2s in %3s.', 'project-huddle' ), '<strong style="color: #000;">{{commenter}}</strong>', '<strong style="color: #000;">{{item_name}}</strong>', '<strong style="color: #000;">{{project_name}}</strong>' );
		$message .= '</p>';
		$message .= '{{content}}';
		$message .= '<p style="text-align:center">{{link}}</p>';

		echo wp_kses_post( wpautop( apply_filters( 'ph_website_assigned_thread_email', $message ) ) );
		?>
    </td>
<?php ph_get_template( 'email/default-footer.php' );
