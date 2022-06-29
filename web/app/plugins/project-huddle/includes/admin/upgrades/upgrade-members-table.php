<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PH_Test_Upgrade extends PH_Upgrade {
	/**
	 * Unique Identifier for upgrade routine
	 * @var string
	 */
	public $name = 'members';

	/**
	 * User-Facing identifier for upgrade routine
	 * @var string
	 */
	public $nice_name = 'Project Members';

	/**
	 * The priority determines the oder in which the upgrades are run.
	 * Use a version for this one.
	 *
	 * @var string
	 */
	public $priority = '3.0.12';

	/**
	 * Upgrade user-facing description
	 * @var string
	 */
	public $description = 'An update is necessary for project membership reliability. This also more easily lets you query user\'s projects and vice versa.';

	/**
	 * The loading method is used to setup the upgrade and is called by the Upgrade Handler.
	 * @return array
	 */
	public function loading() {
		$this->total_steps = 1;

		$args = array(
			'total_steps' => $this->total_steps,
			'step'        => 1,
		);

		return $args;
	}

	public function _beforeStep( $step ) {
	}

	public function step( $step ) {
		$this->create_table();

		// get all project ids
		$projects_query = new WP_Query(
			array(
				'post_type'      => array( 'ph-website', 'ph-project' ),
				'posts_per_page' => - 1,
				'fields'         => 'ids',
			)
		);

		// get project posts
		$projects = $projects_query->posts;

		// insert each as a row in the database table
		foreach ( $projects as $project_id ) {
			if ( $members = get_post_meta( $project_id, 'project_members', true ) ) {
				foreach ( $members as $user_id ) {
					ph_add_member_to_project( array(
						'user_id'    => $user_id,
						'project_id' => $project_id,
					) );
				}
			}
		}

		// update site option
		update_site_option( 'ph_members_db_version', '1.0' );

		ph_log( 'Added all members in table via update script.' );

	}

	public function complete() {
		update_site_option( 'ph_members_db_version', '1.0' );
	}

	public function isComplete() {
		if ( version_compare( get_site_option( 'ph_members_db_version' ), '1.0.0', '<' ) ) {
			return true;
		}
		return false;
	}

	public function create_table() {
		ph_log( 'Creating members table' );

		// create table
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE `{$wpdb->prefix}ph_members` (
		  user_id bigint(20) UNSIGNED NOT NULL,
          project_id bigint(20) UNSIGNED NOT NULL,
          PRIMARY KEY  (user_id, project_id),
          KEY IX_project_id (project_id)
          )$charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		$success = empty( $wpdb->last_error );

		if ( ! $success ) {
			ph_log( $wpdb->last_error );
			$this->errors[] = $wpdb->last_error;

			return;
		}

		ph_log( 'Created members table' );
	}
}

function ph_register_test_upgrade( $upgrades ) {
	$upgrades[] = new PH_Test_Upgrade();

	return $upgrades;
}

add_action( 'ph_upgrade_handler_register', 'ph_register_test_upgrade' );