<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WPUM_UPDATEACCINFO
 * @package Uncanny_Automator_Pro
 */
class WPUM_UPDATEACCINFO {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WPUSERMANAGER';

	/**
	 * @var string
	 */
	private $trigger_code;
	/**
	 * @var string
	 */
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'WPUMUSERACCUPDATE';
		$this->trigger_meta = 'WPUMACCINFO';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;
		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/wp-user-manager/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'is_pro'              => true,
			/* translators: Logged-in trigger - WP User Manager */
			'sentence'            => __( 'A user updates their account information', 'uncanny-automator-pro' ),
			/* translators: Logged-in trigger - WP User Manager */
			'select_option_name'  => __( 'A user updates their account information', 'uncanny-automator-pro' ),
			'action'              => 'wpum_after_user_update',
			'priority'            => 99,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'wpum_account_update' ),
			'options'             => [],
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @param $obj
	 * @param $values
	 * @param $updated_id
	 */
	public function wpum_account_update( $obj, $values, $updated_id ) {
		global $uncanny_automator;

		if ( 0 === absint( $updated_id ) ) {
			// Its a logged in recipe and
			// user ID is 0. Skip process
			return;
		}
		$pass_args = [
			'code'           => $this->trigger_code,
			'meta'           => $this->trigger_meta,
			'user_id'        => $updated_id,
			'ignore_post_id' => true,
		];

		$args = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );

		if ( $args ) {
			foreach ( $args as $result ) {
				if ( true === $result['result'] ) {
					$trigger_meta = [
						'user_id'        => $updated_id,
						'trigger_id'     => $result['args']['trigger_id'],
						'trigger_log_id' => $result['args']['get_trigger_id'],
						'run_number'     => $result['args']['run_number'],
					];

					foreach ( $values['account'] as $key => $value ) {
						$trigger_meta['meta_key']   = $key;
						$trigger_meta['meta_value'] = maybe_serialize( $value );
						$uncanny_automator->insert_trigger_meta( $trigger_meta );
					}

					$uncanny_automator->maybe_trigger_complete( $result['args'] );
				}
			}
		}
	}
}