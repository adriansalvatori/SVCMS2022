<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PH_Upgrade_Metadata_2_6_0 extends PH_Upgrade {
	/**
	 * Unique Identifier for upgrade routine
	 * @var string
	 */
	public $name = 'metadata-2-6-0';

	/**
	 * User-Facing identifier for upgrade routine
	 * @var string
	 */
	public $nice_name = '2.6.0 MetaData Upgrade';

	/**
	 * The priority determines the oder in which the upgrades are run.
	 * Use a version for this one.
	 *
	 * @var string
	 */
	public $priority = '2.6.0';

	/**
	 * Upgrade user-facing description
	 * @var string
	 */
	public $description = 'An update is necessary for metadata fixes for upgrades from versions prior to 2.6.0';

	/**
	 * The loading method is used to setup the upgrade and is called by the Upgrade Handler.
	 * @return array
	 */
	public function loading() {
		// Get all our forms
		$website_ids = $this->get_all_websites();

		$x = 1;
		if ( is_array( $website_ids ) ) {
			foreach ( $website_ids as $website_id ) {
				$this->args['sites'][$x] = $website_id;
				$x++;
			}
		}

		if( empty( $this->total_steps ) || $this->total_steps <= 1 ) {
			$this->total_steps = count($website_ids);
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
		ph_log( 'ph_update_metadata_2_6_0 Update Completed' );
		update_site_option( 'ph_update_metadata_2_6_0', true );
	}

	public function isComplete() {
		// if newer than 2.6.0, it's complete
		if ( version_compare( get_site_option( 'ph_db_version' ), '2.6.0', '>' ) ) {
			return true;
		}
		return get_site_option( 'ph_update_metadata_2_6_0', false );
	}

	public function get_all_websites() {
		$sites = new WP_Query(
			array(
				'post_type'      => 'ph-website',
				'posts_per_page' => - 1,
				'fields'         => 'ids',
				// phpcs:ignore
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'   => 'website_url',
						'value' => 0,
					),
					array(
						'key'     => 'website_url',
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);

		$site_ids = (array) $sites->posts;

		return $site_ids;
	}
}

function ph_register_upgrade_metadata_2_6_0( $upgrades ) {
	$upgrades[] = new PH_Upgrade_Metadata_2_6_0();

	return $upgrades;
}

add_action( 'ph_upgrade_handler_register', 'ph_register_upgrade_metadata_2_6_0' );