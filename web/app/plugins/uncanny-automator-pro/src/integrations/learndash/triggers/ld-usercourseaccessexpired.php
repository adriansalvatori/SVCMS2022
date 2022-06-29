<?php

namespace Uncanny_Automator_Pro;

/**
 * Class LD_USERCOURSEACCESSEXPIRED
 *
 * @package Uncanny_Automator_Pro
 */
class LD_USERCOURSEACCESSEXPIRED {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'LD';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'LD_COURSEACCESSEXPIRED';
		$this->trigger_meta = 'LDCOURSE';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/learndash/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - LearnDash */
			'sentence'            => sprintf( esc_attr__( "A user's access to {{a course:%1\$s}} expires", 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - LearnDash */
			'select_option_name'  => esc_attr__( "A user's access to {{a course}} expires", 'uncanny-automator-pro' ),
			'action'              => 'learndash_user_course_access_expired',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'user_course_access_expired' ),
			'options'             => array(
				Automator()->helpers->recipe->learndash->options->all_ld_courses( null, $this->trigger_meta ),
			),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $course_id
	 */
	public function user_course_access_expired( $user_id, $course_id ) {
		$args = array(
			'code'         => $this->trigger_code,
			'meta'         => $this->trigger_meta,
			'post_id'      => $course_id,
			'user_id'      => $user_id,
			'is_signed_in' => true,
		);

		Automator()->maybe_add_trigger_entry( $args );
	}

}
