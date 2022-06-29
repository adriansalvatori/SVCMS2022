<?php

namespace Uncanny_Automator_Pro;

use PeepSo;
use PeepSoActivity;

/**
 * Class PeepSo_USERPUBLISHESANACTIVITYPOST
 *
 * @package Uncanny_Automator_Pro
 */
class PeepSo_USERPUBLISHESANACTIVITYPOST {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'PP';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'PPUSERPUBLISHESANACTIVITYPOST';
		$this->trigger_meta = 'USERPUBLISHESANACTIVITYPOST';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/peepso/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			/* translators: Logged-in trigger - WordPress Core */
			'sentence'            => __( 'A user publishes an activity post', 'uncanny-automator-pro' ),
			/* translators: Logged-in trigger - WordPress Core */
			'select_option_name'  => __( 'A user publishes an activity post', 'uncanny-automator-pro' ),
			'action'              => 'peepso_activity_after_add_post',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array(
				$this,
				'peepso_activity_after_add_post',
			),
			'options'             => array(),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 */
	public function peepso_activity_after_add_post( $external_act_id, $act_id ) {

		global $user_ID;

		$args = array(
			'code'           => $this->trigger_code,
			'meta'           => $this->trigger_meta,
			'post_id'        => - 1,
			'ignore_post_id' => true,
			'user_id'        => $user_ID,
			'is_signed_in'   => true,
		);

		$args = Automator()->maybe_add_trigger_entry( $args, false );

		// Save trigger meta
		if ( $args ) {
			foreach ( $args as $result ) {
				if ( true === $result['result'] && $result['args']['trigger_id'] && $result['args']['get_trigger_id'] ) {

					$run_number = Automator()->get->trigger_run_number( $result['args']['trigger_id'], $result['args']['get_trigger_id'], $result['args']['user_id'] );
					$save_meta  = array(
						'user_id'        => $result['args']['user_id'],
						'trigger_id'     => $result['args']['trigger_id'],
						'run_number'     => $run_number, //get run number
						'trigger_log_id' => $result['args']['get_trigger_id'],
						'ignore_user_id' => true,
					);

					$peepActivity  = new PeepSoActivity();
					$activity_data = $peepActivity->get_activity( $act_id );
					$main_post     = get_post( $external_act_id );

					$save_meta['meta_key']   = 'USERID';
					$save_meta['meta_value'] = $result['args']['user_id'];
					Automator()->insert_trigger_meta( $save_meta );

					$save_meta['meta_key']   = 'POSTID';
					$save_meta['meta_value'] = absint( $act_id );
					Automator()->insert_trigger_meta( $save_meta );

					$save_meta['meta_key']   = 'POSTBODY';
					$save_meta['meta_value'] = $main_post->post_content;
					Automator()->insert_trigger_meta( $save_meta );

					$save_meta['meta_key']   = 'POSTURL';
					$save_meta['meta_value'] = PeepSo::get_page( 'activity_status', false ) . get_the_title( $activity_data->act_external_id );
					Automator()->insert_trigger_meta( $save_meta );

					Automator()->maybe_trigger_complete( $result['args'] );
				}
			}
		}
	}
}
