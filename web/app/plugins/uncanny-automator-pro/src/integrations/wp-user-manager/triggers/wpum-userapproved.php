<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WPUM_USERAPPROVED
 * @package Uncanny_Automator_Pro
 */
class WPUM_USERAPPROVED {

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
		$this->trigger_code = 'WPUMUSERAPPROVED';
		$this->trigger_meta = 'WPUMUVAPPROVED';
		if ( class_exists( 'WPUM_User_Verification' ) ) {
			$this->define_trigger();
		}
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
			'sentence'            => __( 'A user is approved', 'uncanny-automator-pro' ),
			/* translators: Logged-in trigger - WP User Manager */
			'select_option_name'  => __( 'A user is approved', 'uncanny-automator-pro' ),
			'action'              => 'wpumuv_after_user_approval',
			'priority'            => 99,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'wpum_user_approved' ),
			'options'             => [],
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @param $user_id
	 */
	public function wpum_user_approved( $user_id ) {
		global $uncanny_automator;

		if ( 0 === absint( $user_id ) ) {
			// Its a logged in recipe and
			// user ID is 0. Skip process
			return;
		}

		$pass_args = [
			'code'           => $this->trigger_code,
			'meta'           => $this->trigger_meta,
			'user_id'        => $user_id,
			'ignore_post_id' => true,
		];

		$uncanny_automator->maybe_add_trigger_entry( $pass_args );

	}

}