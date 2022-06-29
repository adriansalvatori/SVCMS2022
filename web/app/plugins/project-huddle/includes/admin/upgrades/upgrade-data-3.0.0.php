<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PH_Upgrade_Data_3_0_0 extends PH_Upgrade {
	/**
	 * Unique Identifier for upgrade routine
	 * @var string
	 */
	public $name = 'data-3-0-0';

	/**
	 * User-Facing identifier for upgrade routine
	 * @var string
	 */
	public $nice_name = '3.0.0 Data Upgrade';

	/**
	 * The priority determines the oder in which the upgrades are run.
	 * Use a version for this one.
	 *
	 * @var string
	 */
	public $priority = '3.0.0.1';

	/**
	 * Upgrade user-facing description
	 * @var string
	 */
	public $description = 'An update is necessary for data changes to version 3.0.0.';

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
					$this->args['sites'][ $x ] = array(
						'site_id' => $mockups
					);
					$x ++;
				}
			}

			if ( empty( $this->total_steps ) || $this->total_steps <= 1 ) {
				$this->total_steps = count( $sites );
			}
		} else {
			$mockups = $this->get_all_mockups();

			$x = 1;
			if ( is_array( $mockups ) ) {
				foreach ( $mockups as $mockup ) {
					$this->args['mockups'][ $x ] = $mockup;
					$x ++;
				}
			}

			if ( empty( $this->total_steps ) || $this->total_steps <= 1 ) {
				$this->total_steps = count( $mockups );
			}
		}

		$args = array(
			'total_steps' => $this->total_steps,
			'step'        => 1,
		);

		return $args;
	}

	public function _beforeStep( $step ) {
	}

	public function step( $step ) {
		if ( isset( $this->args['sites'] ) ) {
			$site = $this->args['sites'][ $step ];

			$site_id = key( $site );
			$mockups = $site[ $site_id ];

			switch_to_blog( $site_id );
			if ( ! empty( $mockups ) ) {
				foreach ( $mockups as $mockup_id ) {
					$this->upgrade_data( $mockup_id );
				}
			}
			restore_current_blog();
		} else if ( isset( $this->args['mockups'] ) ) {
			// get our mockup id
			$project_id = $this->args['mockups'][ $step ];
			$this->upgrade_data( $project_id );
		}
	}

	public function upgrade_data( $id ) {
		// project members.
		$emails  = (array) get_post_meta( (int) $id, 'ph_project_emails_enable', true );
		$emails  = array_unique( $emails );
		$members = array();

		// Store user ids in ids array.
		foreach ( $emails as $key => $email ) {
			if ( ! $email ) {
				continue;
			}

			// Get user by email.
			$user = get_user_by( 'email', $email );

			// users only.
			if ( $user && is_a( $user, 'WP_User' ) ) {
				// store user in array.
				$members[] = $user;
			}
		}

		// update in new post meta.
		if ( ! empty( $members ) ) {
			$user_ids = array();
			foreach ( $members as $member ) {
				if ( is_a( $member, 'WP_User' ) ) {
					$user_ids[] = $member->ID;
				}
			}

			$ids = (array) get_post_meta( $id, 'project_members', true );
			$ids = array_unique( array_merge( $user_ids, $ids ) );
			update_post_meta( $id, 'project_members', $ids );
		}

		// images.
		$image_ids  = get_post_meta( $id, 'ph_project_images', true );
		$menu_order = 0;

		if ( ! empty( $image_ids ) ) {
			foreach ( $image_ids as $image_id ) {
				wp_update_post(
					array(
						'ID'         => $image_id,
						'menu_order' => $menu_order ++, // store order.
						'meta_input' => array(
							'parent_id' => $id,
						),
					)
				);

				// get approval data.
				$approval = (array) get_post_meta( (int) $image_id, 'ph_approved', true );

				// get last approval.
				if ( is_array( $approval ) ) {
					end( $approval );         // move the internal pointer to the end of the array.
					$key = key( $approval );  // fetches the key of the element pointed to by the internal pointer.
				}

				// store new data format.
				if ( isset( $key ) && isset( $approval[ $key ] ) && isset( $approval[ $key ]['approval'] ) ) {
					$value  = (bool) $approval[ $key ]['approval'];
					$status = $value ? ph_approval_completed_status() : ph_get_default_approval_status();
					wp_set_object_terms( $image_id, $status, 'ph_approval', true );
				}

				// get the array of comment location ids for the image.
				$comment_locations = get_post_meta( $image_id, 'comment_locations', true );

				if ( ! empty( $comment_locations ) ) {
					foreach ( $comment_locations as $comment_id ) {
						// store image id in comment.
						update_post_meta( $comment_id, 'parent_id', $image_id );
					}
				}
			}
		}
	}

	public function complete() {
		ph_log( '3.0.0 Data Update Completed' );
	}

	public function isComplete() {
		// if newer than 2.6.0, it's complete
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

function ph_register_upgrade_data_3_0_0( $upgrades ) {
	$upgrades[] = new PH_Upgrade_Data_3_0_0();

	return $upgrades;
}

add_action( 'ph_upgrade_handler_register', 'ph_register_upgrade_data_3_0_0' );