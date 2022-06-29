<?php
/**
 * Approve Image Email
 */
ph_get_template( 'email/default-header.php' ); ?>
	<td align="right" valign="top" width="40" style="padding:20px 20px 0 0;">
		{{ avatar }}
	</td>
	<td align="left" valign="top">
		<?php
		// translatable message
		$message = '<p style="color: #999;">';
		$message .= sprintf( __( '%1s marked %2s as %3s in %4s.', 'project-huddle' ), '<strong style="color: #000;">{{commenter}}</strong>', '<strong style="color: #000;">{{item_name}}</strong>', '<strong style="color: #000;">{{approval_status}}</strong>', '<strong style="color: #000;">{{project_name}}</strong>' );
		$message .= '</p>';
		$message .= '<p style="text-align:center">{{link}}</p>';

		echo wp_kses_post( wpautop( apply_filters( 'ph_mockup_approve_image_email', $message ) ) );
		?>
	</td>
<?php ph_get_template( 'email/default-footer.php' );