<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\AUTONAMI_TOKENS;

/**
 * Autonami Tokens file
 */
class AUTONAMI_PRO_TOKENS {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'automator_autonami_list_triggers', array( $this, 'add_list_triggers' ), 10, 1 );
		add_filter( 'automator_autonami_tag_triggers', array( $this, 'add_tag_triggers' ), 10, 1 );
		add_filter( 'automator_autonami_save_tokens', array( $this, 'save_token_data' ), 10, 5 );

	}

	/**
	 * get_pro_list_triggers
	 *
	 * @return void
	 */
	public function get_pro_list_triggers() {
		return array(
			'CONTACT_REMOVED_FROM_LIST',
			'USER_REMOVED_FROM_LIST',
		);
	}

	/**
	 * get_pro_tag_triggers
	 *
	 * @return void
	 */
	public function get_pro_tag_triggers() {
		return array(
			'CONTACT_TAG_REMOVED',
			'USER_TAG_REMOVED',
		);
	}

	/**
	 * add_list_triggers
	 *
	 * @param  array $triggers
	 * @return array
	 */
	public function add_list_triggers( $triggers ) {
		return array_merge( $triggers, $this->get_pro_list_triggers() );
	}

	/**
	 * add_tag_triggers
	 *
	 * @param  array $triggers
	 * @return array
	 */
	public function add_tag_triggers( $triggers ) {
		return array_merge( $triggers, $this->get_pro_tag_triggers() );
	}

	/**
	 * Method save_token_data
	 *
	 * @param  mixed $args
	 * @param  mixed $trigger
	 * @return void
	 */
	public function save_token_data( $data, $bwfcrm_contact, $trigger_code, $log_entry ) {

		if ( in_array( $trigger_code, $this->get_pro_list_triggers(), true ) ) {
			$this->save_list_tokens( $data, $log_entry );
		} elseif ( in_array( $trigger_code, $this->get_pro_tag_triggers(), true ) ) {
			$this->save_tag_tokens( $data, $log_entry );
		}

	}

	/**
	 * Method save_list_tokens
	 *
	 * @param  mixed $token_base
	 * @param  mixed $list
	 * @param  mixed $entry
	 * @return void
	 */
	public function save_list_tokens( $list, $entry ) {

		$bwfcrm_list = \BWFCRM_Lists::get_lists( array( $list ) );

		$bwfcrm_list = array_shift( $bwfcrm_list );

		if ( empty( $bwfcrm_list ) ) {
			return;
		}

		Automator()->db->token->save( 'LIST', $bwfcrm_list['name'], $entry );
		Automator()->db->token->save( 'LIST_ID', $bwfcrm_list['ID'], $entry );

	}

	/**
	 * Method save_tag_tokens
	 *
	 * @param  mixed $token_base
	 * @param  mixed $list
	 * @param  mixed $entry
	 * @return void
	 */
	public function save_tag_tokens( $tag, $entry ) {

		$bwfcrm_tag = \BWFCRM_Tag::get_tags( array( $tag ) );

		$bwfcrm_tag = array_shift( $bwfcrm_tag );

		if ( empty( $bwfcrm_tag ) ) {
			return;
		}

		Automator()->db->token->save( 'TAG', $bwfcrm_tag['name'], $entry );
		Automator()->db->token->save( 'TAG_ID', $bwfcrm_tag['ID'], $entry );

	}

}
