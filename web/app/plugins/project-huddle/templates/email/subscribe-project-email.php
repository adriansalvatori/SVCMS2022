<?php
/**
 * Mockup subscribe project email
 */
ph_get_template( 'email/default-header.php' ); ?>
    <td align="right" valign="top" width="40" style="padding:20px 20px 0 0;">
        {{ avatar }}
    </td>
    <td align="left" valign="top">
		<?php
		// translatable message
		$message = '<p style="color: #999;">';
		$message .= sprintf( __( '%1s added you to %2s.', 'project-huddle' ), '<strong style="color: #000;">{{commenter}}</strong>', '<strong style="color: #000;">{{project_name}}</strong>' );
		$message .= '</p>';
		$message .= '<p style="text-align:center">{{link}}</p>';

		// echo output
		echo wp_kses_post( wpautop( apply_filters( 'ph_mockup_subscribe_project_email', $message ) ) );
		?>
    </td>
<?php ph_get_template( 'email/default-footer.php' );
