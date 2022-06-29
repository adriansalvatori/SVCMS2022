<?php

namespace Uncanny_Automator_Pro;

/**
 * @package Uncanny_Automator_Pro
 * class BP_SUBSCRIBEFORUM
 */
class BP_SUBSCRIBEFORUM {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'BP';

	private $action_code;
	/**
	 * @var string
	 */
	private $action_meta;

	/**
	 * Set Triggers constructor.
	 */
	public function __construct() {
		$this->action_code = 'BPSUBSCRIBEFORUM';
		$this->action_meta = 'BPSUBSCRIBE';
//		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object.
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/buddypress/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - BuddyPress */
			'sentence'           => sprintf( esc_attr__( 'Subscribe the user to {{a forum:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - BuddyPress */
			'select_option_name' => esc_attr__( 'Subscribe the user to {{a forum}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'subscribe_to_a_forum' ),
			'options'            => array(
				$uncanny_automator->helpers->recipe->buddypress->options->pro->list_buddypress_forums( esc_attr__( 'Forum', 'uncanny-automator-pro' ), $this->action_meta, [ 'uo_include_any' => false ] ),
			),
		);

		$uncanny_automator->register->action( $action );
	}


	/**
	 * Subscribe to BuddyBoss Forum
	 *
	 * @param string $user_id
	 * @param array $action_data
	 * @param string $recipe_id
	 *
	 * @return void
	 */
	public function subscribe_to_a_forum( $user_id, $action_data, $recipe_id, $args ) {
		if ( bbp_is_subscriptions_active() === false ) {
			return;
		}

		global $uncanny_automator;
		$forum_id        = $uncanny_automator->parse->text( $action_data['meta'][ $this->action_meta ], $recipe_id, $user_id, $args );
		$is_subscription = bbp_is_user_subscribed( $user_id, $forum_id );
		$success         = false;

		if ( true === $is_subscription ) {
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( 'The user is already subscribed to the specified forum.', 'uncanny-automator-pro' ) );

			return;
		} else {
			$success = bbp_add_user_subscription( $user_id, $forum_id );
			// Do additional subscriptions actions
			do_action( 'bbp_subscriptions_handler', $success, $user_id, $forum_id, 'bbp_subscribe' );
		}

		if ( $success === false && $is_subscription === false ) {
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( 'There was a problem subscribing to that forum!', 'uncanny-automator-pro' ) );

			return;
		}

		$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id );
	}

}
