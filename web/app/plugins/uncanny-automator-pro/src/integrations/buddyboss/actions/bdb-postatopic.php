<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BDB_POSTATOPIC
 * @package Uncanny_Automator_Pro
 */
class BDB_POSTATOPIC {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'BDB';

	private $action_code;
	private $action_meta;

	/**
	 * Set Triggers constructor.
	 */
	public function __construct() {
		$this->action_code = 'BDBPOSTATOPIC';
		$this->action_meta = 'BDBFORUMTOPIC';

		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object.
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = [
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/buddyboss/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - BuddyBoss */
			'sentence'           => sprintf( esc_attr__( 'Post a topic in {{a forum:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - BuddyBoss */
			'select_option_name' => esc_attr__( 'Post a topic in {{a forum}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'post_a_forum_topic' ],
			'options_group'      => [
				$this->action_meta => [
					$uncanny_automator->helpers->recipe->buddyboss->options->list_buddyboss_forums( esc_attr__( 'Forum', 'uncanny-automator-pro' ), $this->action_meta, [ 'uo_include_any' => false ] ),
					$uncanny_automator->helpers->recipe->field->text_field( 'BDBTOPICTITLE', esc_attr__( 'Topic title', 'uncanny-automator-pro' ), true, 'text', '', false ),
					$uncanny_automator->helpers->recipe->field->text_field( 'BDBTOPICCONTENT', esc_attr__( 'Topic content', 'uncanny-automator-pro' ), true, 'textarea' ),
				],
			],
		];

		$uncanny_automator->register->action( $action );
	}


	/**
	 * Remove from BP Groups
	 *
	 * @param string $user_id
	 * @param array $action_data
	 * @param string $recipe_id
	 *
	 * @return void
	 *
	 * @since 1.1
	 */
	public function post_a_forum_topic( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;
		$forum_id       = $uncanny_automator->parse->text( $action_data['meta']['BDBFORUMTOPIC'], $recipe_id, $user_id, $args );
		$topic_title    = $uncanny_automator->parse->text( $action_data['meta']['BDBTOPICTITLE'], $recipe_id, $user_id, $args );
		$topic_title    = do_shortcode( $topic_title );
		$topic_content  = $uncanny_automator->parse->text( $action_data['meta']['BDBTOPICCONTENT'], $recipe_id, $user_id, $args );
		$topic_content  = do_shortcode( $topic_content );
		$topic_author   = $user_id;
		$anonymous_data = 0;

		// Forum exists.
		if ( ! empty( $forum_id ) ) {

			// Forum is a category.
			if ( bbp_is_forum_category( $forum_id ) ) {
				$action_data['complete_with_errors'] = true;
				$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( 'Sorry, This forum is a category. No discussions can be created in this forum.', 'uncanny-automator-pro' ) );

				return;

				// Forum is not a category.
			} else {

				// Forum is closed and user cannot access.
				if ( bbp_is_forum_closed( $forum_id ) && ! current_user_can( 'edit_forum', $forum_id ) ) {
					$action_data['complete_with_errors'] = true;
					$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( 'Sorry, This forum has been closed to new discussions.', 'uncanny-automator-pro' ) );

					return;
				}

				/**
				 * Added logic for group forum
				 * Current user is part of that group or not.
				 * We need to check manually because bbpress updating that caps only on group forum page and
				 * in API those conditional tag will not work.
				 */
				$is_member = false;
				$group_ids = array();
				if ( function_exists( 'bbp_get_forum_group_ids' ) ) {
					$group_ids = bbp_get_forum_group_ids( $forum_id );
					if ( ! empty( $group_ids ) ) {
						foreach ( $group_ids as $group_id ) {
							if ( groups_is_user_member( $topic_author, $group_id ) ) {
								$is_member = true;
								break;
							}
						}
					}
				}

				// Forum is private and user cannot access.
				if ( bbp_is_forum_private( $forum_id ) && ! bbp_is_user_keymaster() ) {
					if (
						( empty( $group_ids ) && ! current_user_can( 'read_private_forums' ) )
						|| ( ! empty( $group_ids ) && ! $is_member )
					) {
						$action_data['complete_with_errors'] = true;
						$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( 'Sorry, This forum is private and you do not have the capability to read or create new discussions in it.', 'uncanny-automator-pro' ) );

						return;
					}

					// Forum is hidden and user cannot access.
				} elseif ( bbp_is_forum_hidden( $forum_id ) && ! bbp_is_user_keymaster() ) {
					if (
						( empty( $group_ids ) && ! current_user_can( 'read_hidden_forums' ) )
						|| ( ! empty( $group_ids ) && ! $is_member )
					) {
						$action_data['complete_with_errors'] = true;
						$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( 'Sorry, This forum is hidden and you do not have the capability to read or create new discussions in it.', 'uncanny-automator-pro' ) );

						return;
					}
				}
			}
		}

		if ( ! bbp_check_for_duplicate(
			array(
				'post_type'      => bbp_get_topic_post_type(),
				'post_author'    => $topic_author,
				'post_content'   => $topic_content,
				'anonymous_data' => $anonymous_data,
			)
		) ) {
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( "Duplicate discussion detected; it looks as though you've already said that!", 'uncanny-automator-pro' ) );

			return;
		}

		/** Topic Blacklist */
		if ( ! bbp_check_for_blacklist( $anonymous_data, $topic_author, $topic_title, $topic_content ) ) {
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( 'Sorry, Your discussion cannot be created at this time.', 'uncanny-automator-pro' ) );

			return;
		}

		/** Topic Status */
		// Maybe put into moderation.
		if ( ! bbp_check_for_moderation( $anonymous_data, $topic_author, $topic_title, $topic_content ) ) {
			$topic_status = bbp_get_pending_status_id();

			// Check a whitelist of possible topic status ID's.
		} elseif ( ! empty( $topic->bbp_topic_status ) && in_array( $topic->bbp_topic_status, array_keys( bbp_get_topic_statuses() ), true ) ) {
			$topic_status = $topic->bbp_topic_status;

			// Default to published if nothing else.
		} else {
			$topic_status = bbp_get_public_status_id();
		}
		/** No Errors */
		// Add the content of the form to $topic_data as an array.
		// Just in time manipulation of topic data before being created.
		$topic_data = apply_filters(
			'bbp_new_topic_pre_insert',
			array(
				'post_author'    => $topic_author,
				'post_title'     => $topic_title,
				'post_content'   => $topic_content,
				'post_status'    => $topic_status,
				'post_parent'    => $forum_id,
				'post_type'      => bbp_get_topic_post_type(),
				'tax_input'      => [],
				'comment_status' => 'closed',
			)
		);

		// Insert topic.
		$topic_id = wp_insert_post( $topic_data );

		if ( empty( $topic_id ) || is_wp_error( $topic_id ) ) {
			$append_error                        = (
			( is_wp_error( $topic_id ) && $topic_id->get_error_message() )
				? __( 'The following problem(s) have been found with your topic: ', 'uncanny-automator-pro' ) . $topic_id->get_error_message()
				: __( 'We are facing a problem to creating a topic.', 'uncanny-automator-pro' )
			);
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $append_error );

			return;

		}

		/** Trash Check */
		// If the forum is trash, or the topic_status is switched to.
		// trash, trash it properly.
		if (
			( bbp_get_trash_status_id() === get_post_field( 'post_status', $forum_id ) )
			|| ( bbp_get_trash_status_id() === $topic_data['post_status'] )
		) {

			// Trash the reply.
			wp_trash_post( $topic_id );
		}

		/** Spam Check */
		// If reply or topic are spam, officially spam this reply.
		if ( bbp_get_spam_status_id() === $topic_data['post_status'] ) {
			add_post_meta( $topic_id, '_bbp_spam_meta_status', bbp_get_public_status_id() );
		}

		/**
		 * Removed notification sent and called additionally.
		 * Due to we have moved all filters on title and content.
		 */
		remove_action( 'bbp_new_topic', 'bbp_notify_forum_subscribers', 11 );

		/** Update counts, etc... */
		do_action( 'bbp_new_topic', $topic_id, $forum_id, $anonymous_data, $topic_author );

		/** Stickies */
		// Sticky check after 'bbp_new_topic' action so forum ID meta is set.
		if ( ! empty( $topic->bbp_stick_topic ) && in_array(
				$topic->bbp_stick_topic,
				array(
					'stick',
					'super',
					'unstick',
				),
				true
			) ) {

			// What's the caps?
			if ( current_user_can( 'moderate' ) ) {

				// What's the haps?
				switch ( $topic->bbp_stick_topic ) {

					// Sticky in this forum.
					case 'stick':
						bbp_stick_topic( $topic_id );
						break;

					// Super sticky in all forums.
					case 'super':
						bbp_stick_topic( $topic_id, true );
						break;

					// We can avoid this as it is a new topic.
					case 'unstick':
					default:
						break;
				}
			}
		}

		// Handle Subscription Checkbox.
		if ( bbp_is_subscriptions_active() ) {
			$author_id = bbp_get_user_id( 0, true, true );
			// Check if subscribed.
			$subscribed = bbp_is_user_subscribed( $author_id, $topic_id );

			// Subscribed and unsubscribing.
			if ( true === $subscribed && empty( $topic->bbp_topic_subscription ) ) {
				bbp_remove_user_subscription( $author_id, $topic_id );

				// Not subscribed and subscribing.
			} elseif ( false === $subscribed && ! empty( $topic->bbp_topic_subscription ) ) {
				bbp_add_user_subscription( $author_id, $topic_id );
			}
		}

		/** Additional Actions (After Save) */
		do_action( 'bbp_new_topic_post_extras', $topic_id );


		if ( function_exists( 'bbp_notify_forum_subscribers' ) ) {
			/**
			 * Sends notification emails for new topics to subscribed forums.
			 */
			bbp_notify_forum_subscribers( $topic_id, $forum_id, $anonymous_data, $topic_author );
		}

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
