<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PH_Upgrade_Fix_Missing_Pages_2_6_0_5 extends PH_Upgrade {
	/**
	 * Unique Identifier for upgrade routine
	 * @var string
	 */
	public $name = 'metadata-2-6-0-5';

	/**
	 * User-Facing identifier for upgrade routine
	 * @var string
	 */
	public $nice_name = '2.6.0.5 Page Data Upgrade';

	/**
	 * The priority determines the oder in which the upgrades are run.
	 * Use a version for this one.
	 *
	 * @var string
	 */
	public $priority = '2.6.0.5';

	/**
	 * Upgrade user-facing description
	 * @var string
	 */
	public $description = 'This update fixes missing page data from versions after 2.6.0 and prior to 2.6.0.5';

	/**
	 * The loading method is used to setup the upgrade and is called by the Upgrade Handler.
	 * @return array
	 */
	public function loading() {
		// Get all our forms
		$threads = $this->get_threads();

		$x = 1;
		if ( is_array( $threads ) ) {
			foreach ( $threads as $thread ) {
				$this->args['threads'][$x] = $thread;
				$x++;
			}
		}

		if( empty( $this->total_steps ) || $this->total_steps <= 1 ) {
			$this->total_steps = count($threads);
		}

		$args = array(
			'total_steps' 	=> $this->total_steps,
			'step' 			=> 1,
		);

		return $args;
	}

	public function _beforeStep( $step ) {}

	public function step( $step ) {
		// Get our form ID
		$website_id = $this->args['sites'][ $step ];

		// remove prefix.
		$website_url = get_post_meta( $website_id, 'ph_website_url', true );
		update_post_meta( $website_id, 'website_url', $website_url );

		$pages = get_post_meta( $website_id, 'ph_webpages', true );

		// we're going to use parents instead.
		if ( ! empty( $pages ) ) {
			foreach ( $pages as $page_id => $page_url ) {

				// store generic parent id.
				update_post_meta( $page_id, 'parent_id', $website_id );

				$locations = (array) get_post_meta( $page_id, 'comment_locations', true );

				// update locations parent.
				foreach ( $locations as $location ) {
					// use parent instead.
					update_post_meta( $location, 'parent_id', $page_id );
				}

				// use page path for just path of page.
				update_post_meta( $page_id, 'page_path', $page_url );

				// have absolute url.
				update_post_meta( $page_id, 'page_url', trailingslashit( $website_url ) . ltrim( $page_url, '/' ) );
			}
		}
	}

	public function complete() {
		ph_log( 'ph_fix_missing_pages_2_6_0_5 Update Completed' );
		update_site_option( 'ph_fix_missing_pages_2_6_0_5', true );
	}

	public function isComplete() {
		// if newer than 2.6.0.5, it's complete
		if ( version_compare( get_site_option( 'ph_db_version' ), '2.6.0.5', '>' ) ) {
			return true;
		}

		// otherwise we should upgrade
		return get_site_option( 'ph_fix_missing_pages_2_6_0_5', false );
	}

	public function get_threads() {
		// phpcs:ignore
		$threads = get_posts(
			array(
				'post_type'      => 'phw_comment_loc',
				// phpcs:ignore
				'posts_per_page' => - 1,
				// phpcs:ignore
				'meta_query'     => array(
					array(
						'key'   => 'parent_id',
						'value' => 0,
					),
				),
			)
		);

		return $threads;
	}
}

function ph_register_upgrade_fix_missing_pages_2_6_0_5( $upgrades ) {
	$upgrades[] = new PH_Upgrade_Fix_Missing_Pages_2_6_0_5();

	return $upgrades;
}

add_action( 'ph_upgrade_handler_register', 'ph_register_upgrade_fix_missing_pages_2_6_0_5' );