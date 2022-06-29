<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BP_SETUSERPROFILETYPE
 * @package Uncanny_Automator_Pro
 */
class BP_SETUSERPROFILETYPE {

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
		$this->action_code = 'BPSETUSERPROFILETYPE';
		$this->action_meta = 'BPPROFILETYPE';

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
			'sentence'           => sprintf( __( "Set the user's member type to {{a specific type:%1\$s}}", 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - BuddyPress */
			'select_option_name' => __( "Set the user's member type to {{a specific type}}", 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'update_user_profile_type' ],
			'options'            => [
				$uncanny_automator->helpers->recipe->buddypress->pro->get_profile_types( __( 'Member type', 'uncanny-automator' ), $this->action_meta ),
			],
		];

		$uncanny_automator->register->action( $action );
	}

	/**
	 * Update user profile type
	 *
	 * @param string $user_id
	 * @param array $action_data
	 * @param string $recipe_id
	 *
	 * @return void
	 *
	 * @since 1.1
	 */
	public function update_user_profile_type( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;
		$member_type = $action_data['meta'][ $this->action_meta ];
		if ( ! empty( $member_type ) ) {
			if ( ! empty( $member_type ) && ! bp_get_member_type_object( $member_type ) ) {
				return;
			}

			/*
			 * If an invalid member type is passed, someone's doing something
			 * fishy with the POST request, so we can fail silently.
			 */
			if ( bp_set_member_type( $user_id, $member_type ) ) {
				// @todo Success messages can't be posted because other stuff happens on the page load.
			}
		}

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
