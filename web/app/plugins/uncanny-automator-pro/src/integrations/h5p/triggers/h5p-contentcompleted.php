<?php

namespace Uncanny_Automator_Pro;


/**
 * Class H5P_CONTENTCOMPLETED
 * @package Uncanny_Automator_Pro
 */
class H5P_CONTENTCOMPLETED {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'H5P';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'H5PCONTENTCOMPLETED';
		$this->trigger_meta = 'H5P_CONTENT';
		$this->define_trigger();
	}

	/**
	 *
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/h5p/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - H5P */
			'sentence'            => sprintf( __( 'A user completes {{H5P content:%1$s}} {{a number of:%2$s}} times', 'uncanny-automator-pro' ), $this->trigger_meta, 'NUMTIMES' ),
			/* translators: Logged-in trigger - H5P */
			'select_option_name'  => __( 'A user completes {{H5P content}}', 'uncanny-automator-pro' ),
			'action'              => 'h5p_alter_user_result',
			'priority'            => 20,
			'accepted_args'       => 4,
			'validation_function' => array( $this, 'h5p_content_completed' ),
			'options'             => [
				$uncanny_automator->helpers->recipe->h5p->options->pro->all_h5p_contents(),
				$uncanny_automator->helpers->recipe->options->number_of_times(),
			]
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * @param object    &$data Has the following properties score,max_score,opened,finished,time
	 * @param int $result_id Only set if updating result
	 * @param int $content_id Identifier of the H5P Content
	 * @param int $user_id Identifier of the User
	 */
	public function h5p_content_completed( $data, $result_id, $content_id, $user_id ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( empty ( $user_id ) ) {
			return;
		}

		global $uncanny_automator;

		$args = [
			'code'    => $this->trigger_code,
			'meta'    => $this->trigger_meta,
			'post_id' => intval( $content_id ),
			'user_id' => $user_id,
		];

		$uncanny_automator->maybe_add_trigger_entry( $args );
	}
}