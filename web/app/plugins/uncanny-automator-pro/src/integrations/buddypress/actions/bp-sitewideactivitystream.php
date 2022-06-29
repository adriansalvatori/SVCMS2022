<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BP_SITEWIDEACTIVITYSTREAM
 * @package Uncanny_Automator_Pro
 */
class BP_SITEWIDEACTIVITYSTREAM {

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
		$this->action_code = 'BPSITEWIDEACTIVITYSTREAM';
		$this->action_meta = 'BPACTION';

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
			'requires_user'      => false,
			/* translators: Action - BuddyPress */
			'sentence'           => sprintf( esc_attr__( 'Add a post to the sitewide {{activity:%1$s}} stream', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - BuddyPress */
			'select_option_name' => esc_attr__( 'Add a post to the sitewide {{activity}} stream', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'add_post_stream' ],
			'options_group'      => [
				$this->action_meta => [
					$uncanny_automator->helpers->recipe->field->text_field( $this->action_meta, esc_attr__( 'Activity action', 'uncanny-automator-pro' ), true, 'text', '', false ),
					$uncanny_automator->helpers->recipe->buddypress->all_buddypress_users( __( 'Author', 'uncanny-automator-pro' ), 'BPAUTHOR', [
						'uo_include_any' => true,
						'uo_any_label'   => esc_attr__( 'User that completes the triggers', 'uncanny-automator-pro' ),
					] ),
					$uncanny_automator->helpers->recipe->field->text_field( "BPACTIONLINK", esc_attr__( 'Activity action link', 'uncanny-automator-pro' ), true, 'url', '', false ),
					$uncanny_automator->helpers->recipe->field->text_field( "BPCONTENT", esc_attr__( 'Activity content', 'uncanny-automator-pro' ), true, 'textarea' ),
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
	public function add_post_stream( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;
		$action         = $uncanny_automator->parse->text( $action_data['meta']['BPACTION'], $recipe_id, $user_id, $args );
		$action         = do_shortcode( $action );
		$action_link    = $uncanny_automator->parse->text( $action_data['meta']['BPACTIONLINK'], $recipe_id, $user_id, $args );
		$action_link    = do_shortcode( $action_link );
		$action_content = $action_data['meta']['BPCONTENT'];
		$action_content = $uncanny_automator->parse->text( $action_content, $recipe_id, $user_id, $args );
		$action_content = do_shortcode( $action_content );
		$action_author  = $user_id;

		if ( isset( $action_data['meta']['BPAUTHOR'] ) ) {
			$action_author = $uncanny_automator->parse->text( $action_data['meta']['BPAUTHOR'], $recipe_id, $user_id, $args );

			if ( $action_author == '-1' ) {
				$action_author = $user_id;
			}
		}

		$activity = bp_activity_add( [
			'action'        => $action,
			'content'       => $action_content,
			'primary_link'  => $action_link,
			'component'     => 'activity',
			'type'          => 'activity_update',
			'user_id'       => $action_author,
			'hide_sitewide' => false,
		] );

		if ( is_wp_error( $activity ) ) {
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( $activity->get_error_message() ) );
		} elseif ( ! $activity ) {
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( 'There is an error on posting stream.' ) );
		} else {
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
		}
	}
}
