<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

namespace Uncanny_Automator_Pro;

/**
 * Class LD_USERREMOVEDGROUP
 * @package Uncanny_Automator_Pro
 */
class LD_USERREMOVEDGROUP {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'LD';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'LD_USERREMOVEDGROUP';
		$this->trigger_meta = 'LDUSERGROUP';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/learndash/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - LearnDash */
			'sentence'            => sprintf( __( 'A user is removed from {{a group:%1$s}}', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - LearnDash */
			'select_option_name'  => __( 'A user is removed from {{a group}}', 'uncanny-automator-pro' ),
			'action'              => 'ld_removed_group_access',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'group_remove_user' ),
			'options'             => array(
				$uncanny_automator->helpers->recipe->learndash->options->all_ld_groups( null, $this->trigger_meta ),
			),
		);

		$uncanny_automator->register->trigger( $trigger );

	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $group_id
	 */
	public function group_remove_user( $user_id, $group_id ) {

		if ( empty( $group_id ) || empty( $user_id ) ) {
			return;
		}

		global $uncanny_automator;

		$args = array(
			'code'         => $this->trigger_code,
			'meta'         => $this->trigger_meta,
			'post_id'      => $group_id,
			'user_id'      => $user_id,
			'is_signed_in' => true,
		);
		$uncanny_automator->maybe_add_trigger_entry( $args );
	}
}
