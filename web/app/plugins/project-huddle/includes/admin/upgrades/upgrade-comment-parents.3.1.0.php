<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PH_Upgrade_Comment_Meta_3_2_0 extends PH_Upgrade {
	/**
	 * Unique Identifier for upgrade routine
	 * @var string
	 */
	public $name = 'data-comments-3-2-0';

	/**
	 * Get 100 comments at a time
	 * @var int
	 */
	public $offset = 100;

	/**
	 * User-Facing identifier for upgrade routine
	 * @var string
	 */
	public $nice_name = '3.2.0 Comment Data Upgrade';

	/**
	 * The priority determines the oder in which the upgrades are run.
	 * Use a version for this one.
	 *
	 * @var string
	 */
	public $priority = '3.2.0';

	/**
	 * Upgrade user-facing description
	 * @var string
	 */
	public $description = 'An update is necessary for data changes to version 3.2. This may take a while if you have a lot of comments. Please don\'t navigate away from this page.';

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

		// handle multisite, each step is a site and not a comment chunk
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
				foreach ( $comments as $comment_id ) {
					if ( $comment_id ) {
						$this->upgrade_data( $comment_id );
					}
				}
			}
			restore_current_blog();
		} elseif ( isset( $this->args['comments'] ) ) {
			$comments = $this->get_comments_chunk( $step );
			foreach ( $comments as $comment ) {
				$this->upgrade_data( $comment );
			}
		}
	}

	/**
	 * Upgrade comment data
	 *
	 * @param $id
	 */
	public function upgrade_data( $comment ) {
		// get parent ids
		$parents = ph_get_parents_ids( $comment, 'comment' );

		// set item id
		if ( $parents['item'] ) {
			// update meta
			update_comment_meta( $comment->comment_ID, 'item_id', (int) $parents['item'] );
		}

		// set project id
		if ( $parents['project'] ) {
			// update meta
			update_comment_meta( $comment->comment_ID, 'project_id', (int) $parents['project'] );
		}
	}

	public function complete() {
		ph_log( '3.2.0 Comment Meta Data Update Completed' );
	}

	public function isComplete() {
		// if there is not data upgrade version yet, it's a new install
		if ( ! get_site_option( 'ph_data_upgrade_version' ) ) {
			return true;
		}

		// if newer than 3.1.0, it's complete
		if ( version_compare( get_site_option( 'ph_data_upgrade_version' ), '3.1.99', '<' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get all comments
	 *
	 * @return array
	 */
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

function ph_register_upgrade_comment_meta_3_2_0( $upgrades ) {
	$upgrades[] = new PH_Upgrade_Comment_Meta_3_2_0();

	return $upgrades;
}

add_action( 'ph_upgrade_handler_register', 'ph_register_upgrade_comment_meta_3_2_0' );
