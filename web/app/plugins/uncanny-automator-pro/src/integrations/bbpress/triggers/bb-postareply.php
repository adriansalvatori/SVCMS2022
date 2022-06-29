<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BB_POSTAREPLY
 *
 * @package Uncanny_Automator_Pro
 */
class BB_POSTAREPLY {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'BB';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'BBPOSTAREPLY';
		$this->trigger_meta = 'BBTOPIC';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		$bbp_forum_post_type = apply_filters( 'bbp_forum_post_type', 'forum' );
		$args                = array(
			'post_type'      => $bbp_forum_post_type,
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => array( 'publish', 'private' ),
		);

		$options               = Automator()->helpers->recipe->options->wp_query( $args, true, __( 'Any forum', 'uncanny-automator' ) );
		$forum_relevant_tokens = array(
			'BBFORUMS'     => __( 'Forum title', 'uncanny-automator' ),
			'BBFORUMS_ID'  => __( 'Forum ID', 'uncanny-automator' ),
			'BBFORUMS_URL' => __( 'Forum URL', 'uncanny-automator' ),
		);

		$relevant_tokens = array(
			$this->trigger_meta          => __( 'Topic title', 'uncanny-automator' ),
			$this->trigger_meta . '_ID'  => __( 'Topic ID', 'uncanny-automator' ),
			$this->trigger_meta . '_URL' => __( 'Topic URL', 'uncanny-automator' ),
		);

		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/bbpress/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - bbPress */
			'sentence'            => sprintf( esc_attr__( 'A user replies to {{a topic:%1$s}}', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - bbPress */
			'select_option_name'  => esc_attr__( 'A user replies to {{a topic}}', 'uncanny-automator-pro' ),
			'action'              => 'bbp_new_reply',
			'priority'            => 10,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'bbp_insert_reply' ),
			'options_group'       => array(
				$this->trigger_meta => array(
					Automator()->helpers->recipe->field->select_field_ajax(
						'BBFORUMS',
						__( 'Forum', 'uncanny-automator' ),
						$options,
						'',
						'',
						false,
						true,
						array(
							'target_field' => $this->trigger_meta,
							'endpoint'     => 'select_topic_from_forum_TOPICREPLY',
						),
						$forum_relevant_tokens
					),
					Automator()->helpers->recipe->field->select_field( $this->trigger_meta, __( 'Topic', 'uncanny-automator' ), array(), false, false, false, $relevant_tokens ),
				),
			),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 *  Validation function when the trigger action is hit
	 *
	 * @param $reply_id
	 * @param $topic_id
	 * @param $forum_id
	 */
	public function bbp_insert_reply( $reply_id, $topic_id, $forum_id ) {
		$recipes    = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$conditions = $this->match_condition( $topic_id, $forum_id, $recipes, $this->trigger_meta, $this->trigger_code, 'SUBVALUE' );

		if ( ! $conditions ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( ! empty( $conditions ) ) {
			foreach ( $conditions['recipe_ids'] as $recipe_id => $trigger_id ) {
				if ( ! Automator()->is_recipe_completed( $recipe_id, $user_id ) ) {
					$args = array(
						'code'             => $this->trigger_code,
						'meta'             => $this->trigger_meta,
						'recipe_to_match'  => $recipe_id,
						'trigger_to_match' => $trigger_id,
						'post_id'          => $topic_id,
						'user_id'          => $user_id,
					);

					$args = Automator()->maybe_add_trigger_entry( $args, false );

					// Save trigger meta
					if ( $args ) {
						foreach ( $args as $result ) {
							if ( true === $result['result'] && $result['args']['trigger_id'] && $result['args']['get_trigger_id'] ) {
								$reply      = get_post( $reply_id );
								$run_number = Automator()->get->trigger_run_number( $result['args']['trigger_id'], $result['args']['get_trigger_id'], $user_id );

								$save_meta = array(
									'user_id'        => $user_id,
									'trigger_id'     => $result['args']['trigger_id'],
									'run_number'     => $run_number, //get run number
									'trigger_log_id' => $result['args']['get_trigger_id'],
									'ignore_user_id' => true,
								);

								$save_meta['meta_key']   = 'BBFORUMS_ID';
								$save_meta['meta_value'] = maybe_serialize( $forum_id );
								Automator()->insert_trigger_meta( $save_meta );

								$save_meta['meta_key']   = $this->trigger_meta . '_ID';
								$save_meta['meta_value'] = maybe_serialize( $topic_id );
								Automator()->insert_trigger_meta( $save_meta );

								$save_meta['meta_key']   = 'REPLY_ID';
								$save_meta['meta_value'] = maybe_serialize( $reply_id );
								Automator()->insert_trigger_meta( $save_meta );

								$save_meta['meta_key']   = 'REPLY_URL';
								$save_meta['meta_value'] = maybe_serialize( bbp_get_reply_url( $reply_id ) );
								Automator()->insert_trigger_meta( $save_meta );

								$save_meta['meta_key']   = 'REPLY_CONTENT';
								$save_meta['meta_value'] = maybe_serialize( $reply->post_content );
								Automator()->insert_trigger_meta( $save_meta );

								Automator()->maybe_trigger_complete( $result['args'] );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Match condition for form field and value.
	 *
	 * @param int $topic_id .
	 * @param int $forum_id .
	 * @param null|array $recipes .
	 * @param null|string $trigger_meta .
	 * @param null|string $trigger_code .
	 * @param null|string $trigger_second_code .
	 *
	 * @return array|bool
	 */
	public function match_condition( $topic_id, $forum_id, $recipes = null, $trigger_meta = null, $trigger_code = null, $trigger_second_code = null ) {
		if ( null === $recipes ) {
			return false;
		}

		$recipe_ids = array();
		foreach ( $recipes as $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				if ( key_exists( 'BBFORUMS', $trigger['meta'] ) && ( $trigger['meta']['BBFORUMS'] == - 1 || $trigger['meta']['BBFORUMS'] == $forum_id ) ) {
					if ( key_exists( $trigger_meta, $trigger['meta'] ) && ( $trigger['meta'][ $trigger_meta ] == - 1 || $trigger['meta'][ $trigger_meta ] == $topic_id ) ) {
						$recipe_ids[ $recipe['ID'] ] = $trigger['ID'];
					}
				}
			}
		}

		if ( ! empty( $recipe_ids ) ) {
			return array( 'recipe_ids' => $recipe_ids, 'result' => true );
		}

		return false;
	}
}
