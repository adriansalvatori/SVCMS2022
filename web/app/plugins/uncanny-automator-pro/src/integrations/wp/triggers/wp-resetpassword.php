<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_RESETPASSWORD
 * @package Uncanny_Automator_Pro
 */
class WP_RESETPASSWORD {
	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WP';

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
		$this->trigger_code = 'WPRESETPASSWORD';
		$this->trigger_meta = 'RESETPASSWORD';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/wordpress-core/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			/* translators: Logged-in trigger - WordPress Core */
			'sentence'            => __( 'A user resets their password', 'uncanny-automator-pro' ),
			/* translators: Logged-in trigger - WordPress Core */
			'select_option_name'  => __( 'A user resets their password', 'uncanny-automator-pro' ),
			'action'              => array(
				'after_password_reset',
				'woocommerce_customer_reset_password',
			),
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'user_reset_password' ),
			'options'             => array(),
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user
	 * @param $new_password
	 */
	public function user_reset_password( $user, $new_password = null ) {
		global $uncanny_automator;

		$user_id = $user->ID;

		$args = array(
			'code'           => $this->trigger_code,
			'meta'           => $this->trigger_meta,
			'user_id'        => $user_id,
			'ignore_post_id' => true,
			'is_signed_in'   => true,
		);

		$res = $uncanny_automator->maybe_add_trigger_entry( $args, false );
		if ( $res ) {
			foreach ( $res as $result ) {
				if ( true === $result['result'] ) {
					$uncanny_automator->maybe_trigger_complete( $result['args'] );
				}
			}
		}
	}
}
