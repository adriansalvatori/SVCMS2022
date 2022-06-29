<?php
/**
 * Plugin settings
 */

function ph_pdf_updates_settings( $settings ) {
	$settings['fields']['pdf_divider'] = array(
		'id'          => 'pdf_divider',
		'label'       => __( 'PDF Mockups', 'project-huddle' ),
		'description' => '',
		'type'        => 'divider',
	);

	$license_data = get_option( 'ph_pdf_license_data', false );
	$expires      = $license_data->expires !== 'lifetime' ? 'Expires ' . date( get_option( 'date_format' ), strtotime( $license_data->expires ) ) : 'Never Expires';

	if ( ! empty( $license_data ) && $license_data->success ) {
		$settings['fields']['pdf_activate_license'] = array(
			'id'          => 'pdf_activate_license',
			'label'       => __( 'Activate License', 'project-huddle' ),
			'description' => esc_html( $expires ),
			'type'        => 'custom',
			'html'        => '<div style="color:green;">Active</div> ',
		);
	} else {
		$html  = '';
		$html .= wp_nonce_field( 'ph_license_nonce', 'ph_license_nonce', true, false );
		$html .= '<input type="submit" class="button-secondary" name="ph_license_activate" value="' . __( 'Activate License', 'project-huddle' ) . '"/>';

		$settings['fields']['ph_pdf_activate_license'] = array(
			'id'          => 'ph_pdf_activate_license',
			'label'       => __( 'Activate License', 'project-huddle' ),
			'description' => __( 'Activate your license with the PDF Mockups Addon', 'project-huddle' ),
			'type'        => 'custom',
			'html'        => $html,
		);
	}

	$settings['fields']['ph_pdf_beta_version'] = array(
		'id'          => 'ph_pdf_beta_version',
		'label'       => __( 'Pre-Release Versions for the PDF Mockups addon', 'project-huddle' ),
		'description' => __( 'Get updates for pre-release versions of the PDF Mockups Addon.', 'project-huddle' ),
		'type'        => 'checkbox',
		'default'     => '',
	);

	return $settings;
}

add_filter( 'ph_settings_updates', 'ph_pdf_updates_settings' );
