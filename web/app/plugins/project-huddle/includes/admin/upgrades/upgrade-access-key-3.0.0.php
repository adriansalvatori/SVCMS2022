<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PH_Upgrade_Access_Key_3_0_0 extends PH_Upgrade {
	/**
	 * Unique Identifier for upgrade routine
	 * @var string
	 */
	public $name = 'access-key-3-0-0';

	/**
	 * User-Facing identifier for upgrade routine
	 * @var string
	 */
	public $nice_name = '3.0.0 Access Key Upgrade';

	/**
	 * The priority determines the oder in which the upgrades are run.
	 * Use a version for this one.
	 *
	 * @var string
	 */
	public $priority = '3.0.0.2';

	/**
	 * Upgrade user-facing description
	 * @var string
	 */
	public $description = 'An update is necessary for data changes to the access key in version 3.0.0.';

	/**
	 * Store meta keys for upgrade
	 *
	 * @var array
	 */
	public $meta_keys = array();

	/**
	 * The loading method is used to setup the upgrade and is called by the Upgrade Handler.
	 * @return array
	 */
	public function loading() {
		$this->args['sites'] = array();

		// handle multisite
		if ( is_multisite() ) {
			$sites = get_sites( array(
				'number' => 100,
				'fields' => 'ids'
			) );

			if ( ! empty( $sites ) ) {
				$x = 1;
				foreach ( $sites as $site_id ) {
					switch_to_blog( $site_id );
					$mockups = $this->get_all_mockups();
					restore_current_blog();
					$this->args['sites'][$x] = array(
						'site_id' => $mockups
					);
					$x++;
				}
			}

			if( empty( $this->total_steps ) || $this->total_steps <= 1 ) {
				$this->total_steps = count($sites);
			}
		} else {
			$mockups = $this->get_all_mockups();

			$x = 1;
			if ( is_array( $mockups ) ) {
				foreach ( $mockups as $mockup ) {
					$this->args['mockups'][$x] = $mockup;
					$x++;
				}
			}

			if( empty( $this->total_steps ) || $this->total_steps <= 1 ) {
				$this->total_steps = count($mockups);
			}
		}

		$args = array(
			'total_steps' 	=> $this->total_steps,
			'step' 			=> 1,
		);

		return $args;
	}

	public function _beforeStep( $step ) {}

	public function step( $step ) {
		if ( isset( $this->args['sites'] ) ) {
			$site = $this->args['sites'][$step];

			$site_id = key($site);
			$mockups = $site[$site_id];

			switch_to_blog( $site_id );
			if ( ! empty($mockups) ) {
				foreach( $mockups as $mockup_id ) {
					$this->update_project_access($mockup_id);
				}
			}
			restore_current_blog();
		} else if ( isset( $this->args['mockups'] ) ) {
			// get our mockup id
			$project_id = $this->args['mockups'][ $step ];
			ph_log('updating project access for ' .  $project_id);
			$this->update_project_access( $project_id );
		}
	}

	/**
	 * Update project access for project
	 * @param int $project_id
	 */
	public function update_project_access( $project_id = 0 ) {
		if ( ! $project_id ) {
			return;
		}

		// if project access is set, update to new key
		if ( $project_access = get_post_meta( $project_id, 'ph_project_access', true ) ) {
			update_post_meta( $project_id, 'project_access', $project_access );
		}
	}

	public function complete() {
		update_site_option( 'ph_db_version', PH_VERSION );
		ph_log( '3.0.0 Access Key Update Completed' );
	}

	public function isComplete() {
		if ( version_compare( get_site_option( 'ph_db_version' ), '3.0.0', '<' ) ) {
			return false;
		}
		return true;
	}

	public function get_all_mockups() {
		$mockups = new WP_Query(
			array(
				'post_type'      => 'ph-project',
				'posts_per_page' => - 1,
				'fields'         => 'ids',
			)
		);

		return (array) $mockups->posts;
	}
}

function ph_register_upgrade_access_key_3_0_0( $upgrades ) {
	$upgrades[] = new PH_Upgrade_Access_Key_3_0_0();

	return $upgrades;
}

add_action( 'ph_upgrade_handler_register', 'ph_register_upgrade_access_key_3_0_0' );