<?php

global $ph_members_db_version;
$ph_members_db_version = '1.0';

global $ph_thread_members_db_version;
$ph_thread_members_db_version = '1.0';

/**
 * Store license data in database if not stored yet.
 *
 * @return void
 */
function ph_store_license_data()
{
	if (!get_site_option('ph_license_data', false)) {

		// store license key data in database
		// data to send in our API request using the id.
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => get_site_option('ph_license_key'),
			'item_id'    => PH_SL_ITEM_ID,
			'url'        => home_url(),
		);

		// Call the custom API.
		$response = wp_remote_post(
			PH_SL_STORE_URL,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		// make sure the response came back okay.
		if (is_wp_error($response)) {
			return;
		}
		// decode the license data.
		$license_data = json_decode(wp_remote_retrieve_body($response));

		// store all data.
		update_site_option('ph_license_data', $license_data);
	}
}

/**
 * Create an associative table for the many-to-many relationship between users and projects
 *
 * @return bool|void
 */
function ph_update_members()
{
	// create table
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE `{$wpdb->prefix}ph_members` (
		  user_id bigint(20) UNSIGNED NOT NULL,
          project_id bigint(20) UNSIGNED NOT NULL,
          PRIMARY KEY  (user_id, project_id),
          KEY IX_project_id (project_id)
          )$charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta($sql);

	// update site option
	update_site_option('ph_members_db_version', '1.0');

	return true;
}

/**
 * We need to do this no matter what
 * There's minimal risk as we're not deleting post meta for now.
 */
function ph_update_members_database_check()
{
	global $ph_members_db_version;
	if (get_option('ph_members_db_version') != $ph_members_db_version) {
		ph_update_members();
	}
}

add_action('admin_init', 'ph_update_members_database_check');

/**
 * Create an associative table for the many-to-many relationship between users and threads
 *
 * @return bool|void
 */
function ph_thread_members_create_table()
{

	// create table
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$charset_collate = $wpdb->get_charset_collate();

	dbDelta("CREATE TABLE `{$wpdb->prefix}ph_thread_members` (
		user_id bigint(20) UNSIGNED NOT NULL,
		post_id bigint(20) UNSIGNED NOT NULL,
		PRIMARY KEY  (user_id, post_id),
		KEY IX_post_id (post_id)
		)$charset_collate;");

	// update site option
	update_site_option('ph_thread_members_db_version', '1.0');

	// success
	return true;
}

/**
 * We need to do this no matter what
 * There's minimal risk as we're not deleting post meta for now.
 */
function ph_update_thread_members_database_check()
{
	global $ph_thread_members_db_version;
	if (get_option('ph_thread_members_db_version') != $ph_thread_members_db_version) {
		ph_thread_members_create_table();
	}
}

add_action('admin_init', 'ph_update_thread_members_database_check');


/**
 * General update stuff on every update.
 *
 * @return void
 */
function ph_general_update()
{
	$roles = new \PH_Roles();
	$roles->remove_caps();
	$roles->add_roles();
	$roles->add_caps();

	// store license data.
	ph_store_license_data();

	/* Restore original Post Data */
	wp_reset_postdata();

	// flush rewrite rules.
	add_action('shutdown', function () {
		flush_rewrite_rules();
	});

	ph_log('Roles and permalinks flushed as part of ' . PH_VERSION . ' update.');
}

add_action('ph_all_upgrades_complete', 'ph_general_update');


function ph_run_update_functions()
{
	// flush roles if updating from older than 4.0.18
	if (version_compare(get_option('ph_db_version'), '4.0.18', '<=')) {
		$roles = new PH_Roles();
		$roles->add_caps();
	}

	update_option('ph_db_version', PH_VERSION);
}
add_action('init', 'ph_run_update_functions');
