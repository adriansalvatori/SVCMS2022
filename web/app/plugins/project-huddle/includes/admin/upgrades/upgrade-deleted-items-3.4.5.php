<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PH_Upgrade_Deleted_Items_3_4_5 extends PH_Upgrade {
	/**
	 * Unique Identifier for upgrade routine
	 * @var string
	 */
	public $name = 'deleted-items-3-4-5';

	/**
	 * User-Facing identifier for upgrade routine
	 * @var string
	 */
	public $nice_name = '3.4.5 Deleted Items Upgrade';

	/**
	 * The priority determines the oder in which the upgrades are run.
	 * Use a version for this one.
	 *
	 * @var string
	 */
	public $priority = '3.4.5';

	/**
	 * Upgrade user-facing description
	 * @var string
	 */
	public $description = 'An update is necessary to fix a bug with deleted projects and items.';

	/**
	 * Get 100 comments at a time
	 * @var int
	 */
	public $offset = 100;

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
					$comments = $this->get_all_comments();
					restore_current_blog();
					$this->args['sites'][ $x ] = array(
						'site_id' => $comments,
					);
					$x ++;
				}
			}

			if ( empty( $this->total_steps ) || $this->total_steps <= 1 ) {
				$this->total_steps = count( $sites );
			}
		} else {
			$comments               = $this->get_all_comments();
			$this->args['comments'] = true;

			if ( empty( $this->total_steps ) || $this->total_steps <= 1 ) {
				$this->total_steps = ( count( $comments ) / $this->offset ) + 1;
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

			$site_id  = key( $site );
			$comments = $site[ $site_id ];

			switch_to_blog( $site_id );
			if ( ! empty( $comments ) ) {
				foreach ( $comments as $thread_id ) {
					if ( $thread_id ) {
						$this->upgrade_data( $thread_id );
					}
				}
			}
			restore_current_blog();
		} elseif ( isset( $this->args['comments'] ) ) {
			// get thread chunk based on step offset
			$comments = $this->get_comments_chunk( $step );
			foreach ( $comments as $thread ) {
				$this->upgrade_data( $thread );
			}
		}
	}

	/**
	 * Upgrade comment data
	 *
	 * @param WP_Comment $comment
	 */
	public function upgrade_data( $comment ) {
		$thread_id = $comment->comment_post_ID;

		if ( ! $thread_id ) {
			return;
		}

		// check if has item
		if ( $item_id = get_post_meta( $thread_id, 'parent_id', true ) ) {
			$item_status = get_post_status( $item_id );

			// check if item is trashed
			if ( ! $item_status || 'trash' === $item_status ) {
				wp_trash_post( $thread_id );
				return; // bail as we cannot get the project id
			}

			// check if item has project
			if ( $project_id = get_post_meta( $item_id, 'parent_id', true ) ) {
				$project_status = get_post_status( $project_id );

				// check if item is trashed
				if ( ! $project_status || 'trash' === $project_status ) {
					wp_trash_post( $item_id );
					return; // bail as we cannot get the project id
				}
			}
		}
	}

	public function complete() {
		update_site_option( 'ph_data_upgrade_version', PH_VERSION );
		ph_log( '3.4.5 Deleted Items Update Completed' );
	}

	public function isComplete() {
		// if there is not data upgrade version yet, it's a new install
		if ( ! get_site_option( 'ph_data_upgrade_version' ) ) {
			return true;
		}

		if ( version_compare( get_site_option( 'ph_data_upgrade_version' ), '3.4.5', '<' ) ) {
			return false;
		}
		return true;
	}


	public function get_all_comments() {
		$comments = ph_get_comments(
			array(
				'type__in' => ph_get_comment_types(),
				'fields'   => 'ids',
			)
		);

		return (array) $comments;
	}

	public function get_comments_chunk( $step ) {
		$comments = ph_get_comments(
			array(
				'type__in' => ph_get_comment_types(),
				'offset'   => ( $this->offset * ( $step - 1 ) ),
				'number'   => $this->offset,
			)
		);

		return (array) $comments;
	}
}

function ph_register_upgrade_deleted_items_3_4_5( $upgrades ) {
	$upgrades[] = new PH_Upgrade_Deleted_Items_3_4_5();

	return $upgrades;
}

add_action( 'ph_upgrade_handler_register', 'ph_register_upgrade_deleted_items_3_4_5' );
