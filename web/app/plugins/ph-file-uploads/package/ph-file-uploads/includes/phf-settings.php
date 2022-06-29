<?php
/**
 * Maybe clear out file uploads license data if key changes
 *
 * @param string $old_value
 * @param string $value
 * @return void
 */
function phf_maybe_clear_license( $old_value, $value ) {
	if ( $old_value && $old_value !== $value ) {
		delete_option( 'phf_license_data' );
	}
}
add_action( 'update_option_ph_license_key', 'phf_maybe_clear_license', 10, 2 );

/**
 * Plugin settings
 */
function phf_updates_settings( $settings ) {
	$settings['fields']['files_divider'] = array(
		'id'          => 'phf_divider',
		'label'       => __( 'File Uploads', 'project-huddle' ),
		'description' => '',
		'type'        => 'divider',
	);

	$license_data = get_option( 'phf_license_data', false );
	$expires      = $license_data->expires !== 'lifetime' ? 'Expires ' . date( get_option( 'date_format' ), strtotime( $license_data->expires ) ) : 'Never Expires';

	if ( ! empty( $license_data ) && $license_data->success ) {
		$settings['fields']['files_activate_license'] = array(
			'id'          => 'files_activate_license',
			'label'       => __( 'Activate License', 'project-huddle' ),
			'description' => esc_html( $expires ),
			'type'        => 'custom',
			'html'        => '<div style="color:green;">Active</div> ',
		);
	} else {
		$html  = '';
		$html .= wp_nonce_field( 'ph_license_nonce', 'ph_license_nonce', true, false );
		$html .= '<input type="submit" class="button-secondary" name="ph_license_activate" value="' . __( 'Activate License', 'project-huddle' ) . '"/>';

		$settings['fields']['files_activate_license'] = array(
			'id'          => 'files_activate_license',
			'label'       => __( 'Activate License', 'project-huddle' ),
			'description' => __( 'Activate your license with the File Uploads Addon', 'project-huddle' ),
			'type'        => 'custom',
			'html'        => $html,
		);
	}

	$settings['fields']['files_beta_version'] = array(
		'id'          => 'files_beta_version',
		'label'       => __( 'Pre-Release Versions for the File Uploads addon', 'project-huddle' ),
		'description' => __( 'Get updates for pre-release versions of the File Uploads Addon.', 'project-huddle' ),
		'type'        => 'checkbox',
		'default'     => '',
	);

	return $settings;
}

add_filter( 'ph_settings_updates', 'phf_updates_settings' );
