<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BB_SUBSCRIBEFORUM
 * @package Uncanny_Automator_Pro
 */
class BB_SUBSCRIBEFORUM {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'BB';

	private $action_code;
	private $action_meta;

	/**
	 * Set Triggers constructor.
	 */
	public function __construct() {
		$this->action_code = 'BBSUBSCRIBEFORUM';
		$this->action_meta = 'BBFORUM';

		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object.
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = [
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/bbpress/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - bbPress */
			'sentence'           => sprintf( esc_attr__( 'Subscribe the user to {{a forum:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - bbPress */
			'select_option_name' => esc_attr__( 'Subscribe the user to {{a forum}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'subscribe_a_forum' ],
			'options'            => [
				$uncanny_automator->helpers->recipe->bbpress->options->pro->list_bbpress_forums( esc_attr__( 'Forum', 'uncanny-automator-pro' ), $this->action_meta, false, true )
			],
		];
		$uncanny_automator->register->action( $action );
	}

	/**
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 * @param $args
	 */
	public function subscribe_a_forum( $user_id, $action_data, $recipe_id, $args ) {
		if ( bbp_is_subscriptions_active() === false ) {
			return;
		}

		global $uncanny_automator;
		$forum_ids = array_map( 'intval', json_decode( $action_data['meta'][ $this->action_meta ] ) );

		if ( ! empty( $forum_ids ) ) {
			foreach ( $forum_ids as $forum_id ) {
				$is_subscription = bbp_is_user_subscribed( $user_id, $forum_id );
				$success         = false;

				if ( true === $is_subscription ) {
					$action_data['complete_with_errors'] = true;
					$action_data['do-nothing']           = true;
					$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( 'The user is already subscribed to the specified forum.', 'uncanny-automator-pro' ) );

					return;
				} else {
					$success = bbp_add_user_subscription( $user_id, $forum_id );
					// Do additional subscriptions actions
					do_action( 'bbp_subscriptions_handler', $success, $user_id, $forum_id, 'bbp_subscribe' );
				}

				if ( $success === false && $is_subscription === false ) {
					$action_data['complete_with_errors'] = true;
					$action_data['do-nothing']           = true;
					$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( 'There was a problem subscribing to that forum!', 'uncanny-automator-pro' ) );

					return;
				}

				$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id );

			}
		}
	}
}
