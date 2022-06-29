<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BP_ENDFRIENDSHIP
 * @package Uncanny_Automator_Pro
 */
class BP_ENDFRIENDSHIP {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'BP';

	private $action_code;
	private $action_meta;

	/**
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		$this->action_code = 'BPREMOVEACONNECTION';
		$this->action_meta = 'BPUSERS';

		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object.
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = [
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/buddypress/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - BuddyPress */
			'sentence'           => sprintf( __( 'End friendship with {{a user:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - BuddyPress */
			'select_option_name' => __( 'End friendship with {{a user}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'bp_end_friendship' ],
			'options'            => [
				$uncanny_automator->helpers->recipe->buddypress->options->all_buddypress_users(),
			],
		];

		$uncanny_automator->register->action( $action );
	}


	/**
	 * Remove from BP Remove Friend
	 *
	 * @param string $user_id
	 * @param array $action_data
	 * @param string $recipe_id
	 *
	 * @return void
	 *
	 * @since 1.1
	 */
	public function bp_end_friendship( $user_id, $action_data, $recipe_id, $args ) {
		global $uncanny_automator;
		$remove_friend = $action_data['meta'][ $this->action_meta ];
		friends_remove_friend( $user_id, $remove_friend );
		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}

}
