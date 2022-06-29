<?php
/**
 * Handle licenses for updates and activations
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load Updater
 */
if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	include PH_PDF_PLUGIN_DIR . 'includes/updates/EDD_SL_Plugin_Updater.php';
}

/**
 * Get License Key
 */
$license_key = trim( get_option( 'ph_license_key' ) );

 /**
 * Maybe clear out file uploads license data if key changes
 *
 * @param string $old_value
 * @param string $value
 * @return void
 */
function ph_pdf_maybe_clear_license( $old_value, $value ) {
	if ( $old_value && $old_value !== $value ) {
		delete_option( 'ph_pdf_license_data' );
		delete_option( 'ph_pdf_license_status' );
	}
}
add_action( 'update_option_ph_license_key', 'ph_pdf_maybe_clear_license', 10, 2 );

/**
 * setup the updater
 */
$edd_updater = new EDD_SL_Plugin_Updater(
	PH_PDF_SL_STORE_URL,
	PH_PDF_PLUGIN_FILE,
	array(
		'version' => PH_PDF_PLUGIN_VERSION,        // current version number
		'license' => $license_key,    // license key from options
		'item_id' => PH_PDF_SL_ITEM_ID,
		'author'  => 'Project Huddle',
		'beta'    => get_option( 'ph_pdf_beta_version', '' ) == 'on' ? true : false,
	)
);

function ph_pdf_activate_license( $license_data, $license = '' ) {
	if ( ! $license_data || false === $license_data->success || ! $license ) {
		return;
	}

	// data to send in our API request using the id
	$api_params = array(
		'edd_action' => 'activate_license',
		'license'    => $license,
		'item_id'    => PH_PDF_SL_ITEM_ID,
		'url'        => home_url(),
	);

	// Call the custom API.
	$response = wp_remote_post(
		PH_PDF_SL_STORE_URL,
		array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params,
		)
	);

	// make sure the response came back okay
	if ( is_wp_error( $response ) ) {
		return false;
	}

	// decode the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	// store all data
	update_option( 'ph_pdf_license_data', $license_data );

	// $license_data->license will be either "active" or "inactive"
	update_option( 'ph_pdf_license_status', $license_data->license );
}
add_action( 'ph_license_activated', 'ph_pdf_activate_license', 10, 2 );
