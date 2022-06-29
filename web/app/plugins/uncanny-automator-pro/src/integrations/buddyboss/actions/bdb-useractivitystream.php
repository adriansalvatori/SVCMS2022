<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BDB_USERACTIVITYSTREAM
 *
 * @package Uncanny_Automator_Pro
 */
class BDB_USERACTIVITYSTREAM {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'BDB';

	private $action_code;
	private $action_meta;

	/**
	 * Set Triggers constructor.
	 */
	public function __construct() {
		$this->action_code = 'BDBUSERACTIVITYSTREAM';
		$this->action_meta = 'BDBACTION';

		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object.
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/buddyboss/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - BuddyBoss */
			'sentence'           => sprintf( esc_attr__( "Add a post to the user's {{activity:%1\$s}} stream", 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - BuddyBoss */
			'select_option_name' => esc_attr__( "Add a post to the user's {{activity}} stream", 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'add_post_stream' ),
			'options_group'      => array(
				$this->action_meta => array(
					$uncanny_automator->helpers->recipe->field->text_field( $this->action_meta, esc_attr__( 'Activity action', 'uncanny-automator-pro' ), true, 'text', '', false ),
					$uncanny_automator->helpers->recipe->buddyboss->all_buddyboss_users(
						__( 'Author', 'uncanny-automator-pro' ),
						'BDBAUTHOR',
						array(
							'uo_include_any' => true,
							'uo_any_label'   => esc_attr__( 'User that completes the triggers', 'uncanny-automator-pro' ),
						)
					),
					$uncanny_automator->helpers->recipe->field->text_field( 'BDBACTIONLINK', esc_attr__( 'Activity action link', 'uncanny-automator-pro' ), true, 'url', '', false ),
					$uncanny_automator->helpers->recipe->field->text_field( 'BDBCONTENT', esc_attr__( 'Activity content', 'uncanny-automator-pro' ), true, 'textarea' ),
				),
			),
		);

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
	public function add_post_stream( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;
		$action         = $uncanny_automator->parse->text( $action_data['meta']['BDBACTION'], $recipe_id, $user_id, $args );
		$action         = do_shortcode( $action );
		$action_link    = $uncanny_automator->parse->text( $action_data['meta']['BDBACTIONLINK'], $recipe_id, $user_id, $args );
		$action_link    = do_shortcode( $action_link );
		$action_content = $action_data['meta']['BDBCONTENT'];
		$action_content = $uncanny_automator->parse->text( $action_content, $recipe_id, $user_id, $args );
		$action_content = do_shortcode( $action_content );
		$action_author  = $user_id;

		if ( isset( $action_data['meta']['BDBAUTHOR'] ) ) {
			$action_author = $uncanny_automator->parse->text( $action_data['meta']['BDBAUTHOR'], $recipe_id, $user_id, $args );

			if ( '-1' === $action_author ) {
				$action_author = $user_id;
			}
		}

		$activity = bp_activity_add(
			array(
				'action'        => $action,
				'content'       => $action_content,
				'primary_link'  => $action_link,
				'component'     => 'activity',
				'type'          => 'activity_update',
				'user_id'       => $action_author,
				'hide_sitewide' => true,
			)
		);

		// Failed to add activity.
		if ( false === $activity ) {

			$error_message = esc_html__( 'There is an error on posting stream.', 'uncanny-automator' );

			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		// Activity returned a WordPress error.
		if ( is_wp_error( $activity ) ) {

			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $activity->get_error_message() );

			return;
		}

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );

	}
}
