<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Recipe;

/**
 * Class BDB_CREATEFRIENDSHIP
 *
 * @package Uncanny_Automator_Pro
 */
class BDB_CREATEFRIENDSHIP {

	use Recipe\Actions;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->setup_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	protected function setup_action() {
		$this->set_integration( 'BDB' );
		$this->set_action_code( 'CREATEFRIENDSHIP' );
		$this->set_action_meta( 'BDBUSERS' );
		$this->set_support_link( $this->get_action_code(), 'integration/buddyboss/' );
		$this->set_is_pro( true );
		/* translators: Action - BuddyBoss */
		$this->set_sentence( sprintf( esc_attr__( 'Create a friendship between {{a user:%1$s}} and {{another user:%2$s}}', 'uncanny-automator-pro' ), $this->get_action_meta(), 'INITIATINGUSER' ) );
		/* translators: Action - BuddyBoss */
		$this->set_readable_sentence( esc_attr__( 'Create a friendship between {{a user}} and {{another user}}', 'uncanny-automator-pro' ) );
		$this->set_options(
			array(
				Automator()->helpers->recipe->buddyboss->options->all_buddyboss_users( null, $this->get_action_meta() ),
				Automator()->helpers->recipe->buddyboss->options->all_buddyboss_users( __( 'Initiating user', 'uncanny-automator-pro' ), 'INITIATINGUSER' ),
			)
		);
		$this->register_action();
	}

	/**
	 * Process the action.
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 * @param $args
	 * @param $parsed
	 *
	 * @return void.
	 */
	protected function process_action( $user_id, $action_data, $recipe_id, $args, $parsed ) {
		$initiating_user_id = Automator()->parse->text( $action_data['meta']['INITIATINGUSER'], $recipe_id, $user_id, $args );
		$friend_user_id     = Automator()->parse->text( $action_data['meta'][ $this->get_action_meta() ], $recipe_id, $user_id, $args );

		if ( ! function_exists( 'friends_add_friend' ) ) {
			$action_data['complete_with_errors'] = true;
			$action_data['do-nothing']           = true;
			Automator()->complete->action( $user_id, $action_data, $recipe_id, __( 'BuddyBoss connection module is not active.', 'uncanny-automator-pro' ) );

			return;
		}
		$send = friends_add_friend( $initiating_user_id, $friend_user_id, true );
		if ( false === $send ) {
			$action_data['complete_with_errors'] = true;
			$action_data['do-nothing']           = true;
			Automator()->complete->action( $user_id, $action_data, $recipe_id, __( 'We are unable to send friendship request to selected user.', 'uncanny-automator-pro' ) );

			return;
		}
		Automator()->complete->action( $user_id, $action_data, $recipe_id );

	}

}
