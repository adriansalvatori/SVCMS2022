<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WPF_ADDEDTOPIC
 * @package Uncanny_Automator
 */
class WPF_USERREPLIESTOTOPIC {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WPFORO';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {

		$this->trigger_code = 'USERREPLIESTOTOPIC';
		$this->trigger_meta = 'FOROFORUM';
		$this->define_trigger();
		add_action( 'wp_ajax_select_topic_from_forum_wpForo', array( $this, 'topic_from_forum_func' ), 15 );
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$forums = WPF()->forum->get_forums( [ 'type' => 'forum' ] );

		$forum_options = [ - 1 => 'Any Forum' ];
		foreach ( $forums as $forum ) {
			$forum_options[ $forum['forumid'] ] = $forum['title'];
		}

		$forum_relevant_tokens = [
			'WPFORO_FORUM'     => __( 'Forum title', 'uncanny-automator' ),
			'WPFORO_FORUM_ID'  => __( 'Forum ID', 'uncanny-automator' ),
			'WPFORO_FORUM_URL' => __( 'Forum URL', 'uncanny-automator' ),
		];

		$topic_relevant_tokens = [
			'WPFORO_TOPIC'               => __( 'Topic title', 'uncanny-automator' ),
			'WPFORO_TOPIC_ID'            => __( 'Topic ID', 'uncanny-automator' ),
			'WPFORO_TOPIC_URL'           => __( 'Topic URL', 'uncanny-automator' ),
			'WPFORO_TOPIC_CONTENT'       => __( 'Topic content', 'uncanny-automator' ),
			'WPFORO_TOPIC_REPLY_ID'      => __( 'Reply ID', 'uncanny-automator' ),
			'WPFORO_TOPIC_REPLY_URL'     => __( 'Reply URL', 'uncanny-automator' ),
			'WPFORO_TOPIC_REPLY_CONTENT' => __( 'Reply content', 'uncanny-automator' ),
		];

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/wpforo/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - wpForo */
			'sentence'            => sprintf( __( 'A user replies to {{a topic:%1$s}}', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - wpForo */
			'select_option_name'  => __( 'A user replies to {{a topic}}', 'uncanny-automator-pro' ),
			'action'              => 'wpforo_after_add_post',
			'priority'            => 5,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'after_add_post' ),
			'options_group'       => [
				$this->trigger_meta => [
					$uncanny_automator->helpers->recipe->field->select_field_ajax(
						'FOROFORUM',
						__( 'Forum', 'uncanny-automator' ),
						$forum_options,
						'',
						'',
						false,
						true,
						[
							'target_field' => 'FOROTOPIC',
							'endpoint'     => 'select_topic_from_forum_wpForo',
						],
						$forum_relevant_tokens
					),
					$uncanny_automator->helpers->recipe->field->select_field( 'FOROTOPIC', __( 'Topic', 'uncanny-automator' ), [], false, false, false, $topic_relevant_tokens ),
				],
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * @param $post
	 * @param $topic
	 */
	public function after_add_post( $post, $topic ) {

		if ( ! isset( $topic['topicid'] ) || ! isset( $topic['forumid'] ) ) {
			return;
		}

		if ( ! absint( $topic['topicid'] ) || ! absint( $topic['forumid'] ) ) {
			return;
		}

		global $uncanny_automator;


		$forum_id = absint( $topic['forumid'] );
		$topic_id = absint( $topic['topicid'] );

		// Get all recipes that have the "$this->trigger_code = 'USERREPLIESTOTOPIC'" trigger
		$recipes = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		// Get the specific WPFFORUMID meta data from the recipes
		$recipe_trigger_forum_meta_data = $uncanny_automator->get->meta_from_recipes( $recipes, 'FOROFORUM' );
		$recipe_trigger_topic_meta_data = $uncanny_automator->get->meta_from_recipes( $recipes, 'FOROTOPIC' );
		$matched_recipe_ids             = [];

		// Loop through recipe
		foreach ( $recipe_trigger_forum_meta_data as $recipe_id => $trigger_meta ) {
			// Loop through recipe WPFFORUMID trigger meta data
			foreach ( $trigger_meta as $trigger_id => $required_forum_id ) {

				// get the topic ID or bail
				if ( isset( $recipe_trigger_topic_meta_data[ $recipe_id ] ) && isset( $recipe_trigger_topic_meta_data[ $recipe_id ][ $trigger_id ] ) ) {
					$required_topic_id = $recipe_trigger_topic_meta_data[ $recipe_id ][ $trigger_id ];
				} else {
					continue;
				}

				$required_forum_id = ( - 1 == $required_forum_id ) ? 0 : absint( $required_forum_id );
				$required_topic_id = ( - 1 == $required_topic_id ) ? 0 : absint( $required_topic_id );

				$match = false;

				if ( 0 === $required_forum_id && 0 === $required_topic_id ) {
					// Any forum and any topic
					$match = true;
				} elseif ( $forum_id === absint( $required_forum_id ) && 0 === $required_topic_id ) {
					// Specific forum and any topic
					$match = true;
				} elseif ( $forum_id === $required_forum_id && $topic_id === $required_topic_id ) {
					// Any forum and any topic
					$match = true;
				}

				if ( $match ) {
					$matched_recipe_ids[] = [
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					];
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$pass_args = [
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => get_current_user_id(),
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
				];

				$args = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {

							$trigger_meta = [
								'user_id'        => get_current_user_id(),
								'trigger_id'     => $result['args']['trigger_id'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							];

							$trigger_meta['meta_key']   = 'WPFORO_TOPIC_REPLY_ID';
							$trigger_meta['meta_value'] = $post['postid'];
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPFORO_TOPIC_ID';
							$trigger_meta['meta_value'] = $topic_id;

							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = 'WPFORO_TOPIC_FORUM_ID';
							$trigger_meta['meta_value'] = $forum_id;

							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$uncanny_automator->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}

	/**
	 * Return all the specific fields of a form ID provided in ajax call
	 */
	public function topic_from_forum_func() {

		global $uncanny_automator;

		// Nonce and post object validation
		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$fields = array();

		$fields[] = [
			'value' => - 1,
			'text'  => __( 'Any topic', 'uncanny-automator' ),
		];

		if ( isset( $_POST ) ) {

			$trigger_id = absint( $_POST['item_id'] );

			if ( $trigger_id ) {

				if ( isset( $_POST['values'] ) && isset( $_POST['values']['FOROFORUM'] ) ) {
					$forum_id = absint( $_POST['values']['FOROFORUM'] );
				} else {
					$forum_id = 0;
				}

				$topics = WPF()->topic->get_topics( [ 'forumid' => $forum_id ] );

				foreach ( $topics as $topic ) {
					$fields[] = array(
						'value' => $topic['topicid'],
						'text'  => $topic['title'],
					);
				}
			}
		}

		echo wp_json_encode( $fields );
		die();
	}
}
