<?php

namespace Uncanny_Automator_Pro;

/**
 * @package Uncanny_Automator_Pro
 * Class LF_LEAVEGROUP
 */ 
class LF_LEAVEGROUP {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'LF';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'LFLEAVESGROUP';
		$this->trigger_meta = 'LFGROUPS';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/lifterlms/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'is_pro'              => true,
			/* translators: Logged-in trigger - LifterLMS */
			'sentence'            => sprintf( esc_attr__( 'A user leaves {{a group:%1$s}} {{a number of:%2$s}} time(s)', 'uncanny-automator-pro' ), $this->trigger_meta, 'NUMTIMES' ),
			/* translators: Logged-in trigger - LifterLMS */
			'select_option_name'  => esc_attr__( 'A user leaves {{a group}}', 'uncanny-automator-pro' ),
			'action'              => array( 'llms_user_removed_from_group', 'llms_user_enrollment_deleted' ),
			'priority'            => 20,
			'accepted_args'       => 4,
			'validation_function' => array( $this, 'lf_leaves_group' ),
			'options'             => [
				Automator()->helpers->recipe->lifterlms->options->pro->all_lf_groups( esc_attr__( 'Group', 'uncanny-automator-pro' ), $this->trigger_meta ),
				Automator()->helpers->recipe->options->number_of_times(),
			],
		);

		Automator()->register->trigger( $trigger );

		return;
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $group_id
	 * @param $trigger
	 * @param $new_status
	 */
	public function lf_leaves_group( $user_id, $group_id, $trigger = null, $new_status = null ) {
		if ( empty( $user_id ) ) {
			return;
		}

		$args = [
			'code'    => $this->trigger_code,
			'meta'    => $this->trigger_meta,
			'post_id' => $group_id,
			'user_id' => $user_id,
		];

		Automator()->maybe_add_trigger_entry( $args );
	}

}