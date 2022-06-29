<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BDB_INVITEREGISTERUSER
 * @package Uncanny_Automator_Pro
 */
class BDB_INVITEREGISTERUSER {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'BDB';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'BDBINVITEREGISTERUSER';
		$this->trigger_meta = 'BDBUSERS';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/buddyboss/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			/* translators: Logged-in trigger - BuddyBoss */
			'sentence'            => esc_attr__( 'A user registers a new account via an email invitation', 'uncanny-automator-pro' ),
			/* translators: Logged-in trigger - BuddyBoss */
			'select_option_name'  => esc_attr__( 'A user registers a new account via an email invitation', 'uncanny-automator-pro' ),
			'action'              => 'bp_invites_member_invite_mark_register_user',
			'priority'            => 10,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'bp_invite_mark_register_user' ),
			'options'             => [],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 *  Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $key
	 * @param $user
	 */
	public function bp_invite_mark_register_user( $user_id, $inviter_id, $post_id ) {

		global $uncanny_automator;

		$args = [
			'code'           => $this->trigger_code,
			'meta'           => $this->trigger_meta,
			'user_id'        => $user_id,
			'is_signed_in'   => true,
			'ignore_post_id' => true,
		];

		$user_data = get_userdata( $user_id );
		$args      = $uncanny_automator->maybe_add_trigger_entry( $args, false );

		// Save trigger meta
		if ( $args ) {
			foreach ( $args as $result ) {
				if ( true === $result['result'] && $result['args']['trigger_id'] && $result['args']['get_trigger_id'] ) {

					$run_number = $uncanny_automator->get->trigger_run_number( $result['args']['trigger_id'], $result['args']['get_trigger_id'], $user_id );
					$save_meta  = [
						'user_id'        => $user_id,
						'trigger_id'     => $result['args']['trigger_id'],
						'run_number'     => $run_number, //get run number
						'trigger_log_id' => $result['args']['get_trigger_id'],
						'ignore_user_id' => true,
					];

					$save_meta['meta_key']   = 'first_name';
					$save_meta['meta_value'] = $user_data->first_name;
					$uncanny_automator->insert_trigger_meta( $save_meta );

					$save_meta['meta_key']   = 'last_name';
					$save_meta['meta_value'] = $user_data->last_name;
					$uncanny_automator->insert_trigger_meta( $save_meta );

					$save_meta['meta_key']   = 'useremail';
					$save_meta['meta_value'] = $user_data->user_email;
					$uncanny_automator->insert_trigger_meta( $save_meta );

					$save_meta['meta_key']   = 'username';
					$save_meta['meta_value'] = $user_data->user_login;
					$uncanny_automator->insert_trigger_meta( $save_meta );

					$save_meta['meta_key']   = 'user_id';
					$save_meta['meta_value'] = $user_data->ID;
					$uncanny_automator->insert_trigger_meta( $save_meta );

					$uncanny_automator->maybe_trigger_complete( $result['args'] );
				}
			}
		}
	}
}
