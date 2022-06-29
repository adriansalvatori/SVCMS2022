<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Recipe;

/**
 * Class Add_Autonami_Integration
 *
 * @package Uncanny_Automator_Pro
 */
class Add_Autonami_Integration {

	use Recipe\Integrations;

	/**
	 * Add_Integration constructor.
	 */
	public function __construct() {
		$this->setup();
	}

	/**
	 * Integration Set-up.
	 */
	protected function setup() {
		$this->set_integration( 'AUTONAMI' );

		// By default, bwfan_contact_removed_from_lists action passes a single list or an array of lists.
		// We need to create a custom hook that fires for each list separately to make sure our tokens work.
		add_action( 'bwfan_contact_removed_from_lists', array( $this, 'contact_removed_from_lists' ), 10, 2 );

		// By default, bwfan_tags_added_to_contact action passes a single tag or an array of tags.
		// We need to create a custom hook that fires for each tag separately to make sure our tokens work.
		add_action( 'bwfan_tags_removed_from_contact', array( $this, 'tag_removed_from_contact' ), 10, 2 );
	}

	/**
	 * Method contact_added_to_lists
	 *
	 * @param  mixed $lists
	 * @param  mixed $bwfcrm_contact_object
	 * @return void
	 */
	public function contact_removed_from_lists( $lists, $bwfcrm_contact_object ) {

		if ( ! is_array( $lists ) ) {
			do_action( 'automator_bwfan_contact_removed_from_list', $lists, $bwfcrm_contact_object );
			return;
		}

		foreach ( $lists as $list ) {
			do_action( 'automator_bwfan_contact_removed_from_list', $list, $bwfcrm_contact_object );
		}

	}

	/**
	 * Method tag_removed_from_contact
	 *
	 * @param  mixed $lists
	 * @param  mixed $bwfcrm_contact_object
	 * @return void
	 */
	public function tag_removed_from_contact( $tags, $bwfcrm_contact_object ) {

		if ( ! is_array( $tags ) ) {
			do_action( 'automator_bwfan_tag_removed_from_contact', $tags, $bwfcrm_contact_object );
			return;
		}

		foreach ( $tags as $tag ) {
			do_action( 'automator_bwfan_tag_removed_from_contact', $tag, $bwfcrm_contact_object );
		}

	}

	/**
	 * automator_free_requirement_met
	 *
	 * @return bool
	 */
	public function automator_free_requirement_met() {

		if ( ! defined( 'AUTOMATOR_PLUGIN_VERSION' ) || version_compare( AUTOMATOR_PLUGIN_VERSION, '4.1', '<' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Only load this integration and its triggers and actions if the related plugin is active
	 *
	 * @param $status
	 * @param $plugin
	 *
	 * @return bool
	 */
	public function plugin_active() {
		return class_exists( 'BWFCRM_Contact' ) && $this->automator_free_requirement_met();
	}

}
