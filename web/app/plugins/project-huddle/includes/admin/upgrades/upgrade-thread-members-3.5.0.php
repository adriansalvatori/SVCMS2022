<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PH_Upgrade_Thread_Members_3_5_0 extends PH_Upgrade {
	/**
	 * Unique Identifier for upgrade routine
	 * @var string
	 */
	public $name = 'data-threads-members-3-5-0';

	/**
	 * Get 100 threads at a time
	 * @var int
	 */
	public $offset = 100;

	/**
	 * User-Facing identifier for upgrade routine
	 * @var string
	 */
	public $nice_name = '3.5.0 Email Notification Data Upgrade';

	/**
	 * The priority determines the oder in which the upgrades are run.
	 * Use a version for this one.
	 *
	 * @var string
	 */
	public $priority = '3.5.0';

	/**
	 * Upgrade user-facing description
	 * @var string
	 */
	public $description = 'An update is necessary for comment thread data changes to version 3.5.0. This may take a while if you have a lot of threads. Please don\'t navigate away from this page.';

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
		ph_thread_members_create_table();

		$this->args['sites'] = array();

		// handle multisite
		if ( is_multisite() ) {
			$sites = get_sites(
				array(
					'number' => 100,
					'fields' => 'ids',
				)
			);

			if ( ! empty( $sites ) ) {
				$x = 1;
				foreach ( $sites as $site_id ) {
					switch_to_blog( $site_id );
					$threads = $this->get_all_threads();
					restore_current_blog();
					$this->args['sites'][ $x ] = array(
						'site_id' => $threads,
					);
					$x ++;
				}
			}

			if ( empty( $this->total_steps ) || $this->total_steps <= 1 ) {
				$this->total_steps = count( $sites );
			}
		} else {
			$threads               = $this->get_all_threads();
			$this->args['threads'] = true;

			if ( empty( $this->total_steps ) || $this->total_steps <= 1 ) {
				$this->total_steps = ( count( $threads ) / $this->offset ) + 1;
			}
		}

		$args = array(
			'total_steps' => $this->total_steps,
			'step'        => $this->getLastStep(),
		);

		return $args;
	}

	public function _beforeStep( $step ) {
	}

	public function step( $step ) {
		if ( isset( $this->args['sites'] ) ) {
			$site = $this->args['sites'][ $step ];

			$site_id = key( $site );
			$threads = $site[ $site_id ];

			switch_to_blog( $site_id );
			if ( ! empty( $threads ) ) {
				foreach ( $threads as $thread_id ) {
					if ( $thread_id ) {
						$this->upgrade_data( $thread_id );
					}
				}
			}
			restore_current_blog();
		} elseif ( isset( $this->args['threads'] ) ) {
			// get thread chunk based on step offset
			$threads = $this->get_threads_chunk( $step );
			foreach ( $threads as $thread ) {
				$this->upgrade_data( $thread );
			}
		}
	}

	/**
	 * Upgrade comment data
	 *
	 * @param $id
	 */
	public function upgrade_data( $thread_id ) {
		// get author of thread (the reporter)
		$thread    = get_post( $thread_id );
		$author_id = $thread->post_author;
		$members   = array();

		// add author id
		if ( $author_id ) {
			$members[] = (int) $author_id;
		}

		// add project author
		$parents = ph_get_parents_ids( $thread_id );
		if ( $parents['project'] ) {
			$project = get_post( $parents['project'] );
			if ( $project->post_author ) {
				$members[] = $project->post_author;
			} else {
				ph_log( 'no author' );
			}
		} else {
			ph_log( 'no parent' );
		}

		// unique
		$members = array_unique( $members );

		// insert each as a row in the database table
		foreach ( $members as $member ) {
			ph_add_member_to_thread(
				array(
					'user_id' => $member,
					'post_id' => $thread->ID,
				)
			);
		}
	}

	public function complete() {
		ph_log( '3.5.0 Thread Members Data Update Completed' );
		update_site_option( 'ph_data_upgrade_version', PH_VERSION );
	}

	public function isComplete() {
		// if there is not data upgrade version yet, it's a new install
		if ( ! get_site_option( 'ph_data_upgrade_version' ) ) {
			return true;
		}

		// if newer than 2.6.0, it's complete
		if ( version_compare( get_site_option( 'ph_data_upgrade_version' ), '3.5.0-beta1', '<' ) ) {
			return false;
		}

		return true;
	}

	public function get_all_threads() {
		$threads = new WP_Query(
			array(
				'post_type'      => array( 'ph_comment_location', 'phw_comment_loc' ),
				'posts_per_page' => - 1,
				'fields'         => 'ids',
			)
		);

		return (array) $threads->posts;
	}

	public function get_threads_chunk( $step ) {
		$threads = new WP_Query(
			array(
				'post_type'      => array( 'ph_comment_location', 'phw_comment_loc' ),
				'offset'         => ( $this->offset * ( $step - 1 ) ),
				'posts_per_page' => $this->offset,
			)
		);

		return (array) $threads->posts;
	}
}

function ph_register_upgrade_thread_members_3_5_0( $upgrades ) {
	$upgrades[] = new PH_Upgrade_Thread_Members_3_5_0();

	return $upgrades;
}

add_action( 'ph_upgrade_handler_register', 'ph_register_upgrade_thread_members_3_5_0' );
