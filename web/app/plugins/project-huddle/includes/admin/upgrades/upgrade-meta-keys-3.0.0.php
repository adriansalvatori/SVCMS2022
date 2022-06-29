<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PH_Upgrade_Meta_Keys_3_0_0 extends PH_Upgrade {
	/**
	 * Unique Identifier for upgrade routine
	 * @var string
	 */
	public $name = 'metakeys-3-0-0';

	/**
	 * User-Facing identifier for upgrade routine
	 * @var string
	 */
	public $nice_name = '3.0.0 Meta Keys Upgrade';

	/**
	 * The priority determines the oder in which the upgrades are run.
	 * Use a version for this one.
	 *
	 * @var string
	 */
	public $priority = '3.0.0';

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
		$this->meta_keys = array(
			'ph_image_alignment'                 => 'alignment',
			'ph_image_size'                      => 'size',
			'ph_image_background_color'          => 'background_color',
			'ph_image_background_image'          => 'background_image',
			'ph_image_background_image_position' => 'background_image_position',
			'ph_project_comments'                => 'project_comments',
			'ph_project_sharing'                 => 'project_sharing',
			'ph_retina'                          => 'retina',
			'ph_project_download'                => 'project_download',
			'ph_zoom'                            => 'zoom',
			'ph_project_approval'                => 'project_approval',
			'ph_project_unapproval'              => 'project_unapproval'
		);

		$x = 1;
		foreach ( $this->meta_keys as $old => $new ) {
			$this->args['meta_keys'][$x] = array(
				$old => $new
			);
			$x++;
		}

		$this->total_steps = count($this->meta_keys);

		$args = array(
			'total_steps' => $this->total_steps,
			'step'        => 1,
		);

		return $args;
	}

	public function _beforeStep( $step ) {}

	public function step( $step ) {
		// Get our form ID
		$values = $this->args['meta_keys'][ $step ];

		$old = key($values);
		$new = $values[$old];

		$this->ph_update_meta_key( key($values), $new );
	}

	public function complete() {
		ph_log( '3.0.0 Meta Keys Update Completed' );
	}

	public function isComplete() {
		// if newer than 2.6.0, it's complete
		if ( version_compare( get_site_option( 'ph_db_version' ), '3.0.0', '<' ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Rename meta keys
	 * Usage: update_meta_key( 'old_key', 'new_key');
	 *
	 * @param string $old_key Old key.
	 * @param string $new_key New key.
	 *
	 * @return array|null|object changed rows
	 */
	public function ph_update_meta_key( $old_key = null, $new_key = null ) {
		global $wpdb;

		// multisite
		$query = 'UPDATE ' . $wpdb->base_prefix . "postmeta SET meta_key = '" . $new_key . "' WHERE meta_key = '" . $old_key . "'";
		// phpcs:ignore
		$results = $wpdb->get_results( $query, ARRAY_A );

		// regular
		$query = 'UPDATE ' . $wpdb->prefix . "postmeta SET meta_key = '" . $new_key . "' WHERE meta_key = '" . $old_key . "'";
		// phpcs:ignore
		$results = $wpdb->get_results( $query, ARRAY_A );

		return $results;
	}
}

function ph_register_upgrade_meta_keys_3_0_0( $upgrades ) {
	$upgrades[] = new PH_Upgrade_Meta_Keys_3_0_0();

	return $upgrades;
}

add_action( 'ph_upgrade_handler_register', 'ph_register_upgrade_meta_keys_3_0_0' );