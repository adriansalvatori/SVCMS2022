<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BP_GROUPACTIVITYSTREAM
 * @package Uncanny_Automator_Pro
 */
class BP_GROUPACTIVITYSTREAM {

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
		$this->action_code = 'BPGROUPACTIVITYSTREAM';
		$this->action_meta = 'BPGROUPS';

		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object.
	 */
	public function define_action() {

		global $uncanny_automator;

		$groups_argu = [
			'uo_include_any' => true,
			'uo_any_label'   => esc_attr__( 'All groups', 'uncanny-automator' ),
			'status'         => [ 'public', 'private', 'hidden' ],
		];
		$action      = [
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/buddypress/' ),
			'is_pro'             => true,
			'requires_user'      => false,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - BuddyPress */
			'sentence'           => sprintf( esc_attr__( 'Add a post to the activity stream of {{a group:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - BuddyPress */
			'select_option_name' => esc_attr__( 'Add a post to the activity stream of {{a group}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'add_post_stream' ],
			'options_group'      => [
				$this->action_meta => [
					$uncanny_automator->helpers->recipe->buddypress->all_buddypress_groups( null, $this->action_meta, $groups_argu ),
					$uncanny_automator->helpers->recipe->field->text_field( 'BPACTION', esc_attr__( 'Activity action', 'uncanny-automator-pro' ), true, 'text', '', false ),
					$uncanny_automator->helpers->recipe->buddypress->all_buddypress_users( __( 'Author', 'uncanny-automator-pro' ), 'BPAUTHOR', [
						'uo_include_any' => true,
						'uo_any_label'   => esc_attr__( 'User that completes the triggers', 'uncanny-automator-pro' ),
					] ),
					$uncanny_automator->helpers->recipe->field->text_field( 'BPACTIONLINK', esc_attr__( 'Activity action link', 'uncanny-automator-pro' ), true, 'url', '', false ),
					$uncanny_automator->helpers->recipe->field->text_field( 'BPCONTENT', esc_attr__( 'Activity content', 'uncanny-automator-pro' ), true, 'textarea' ),
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
		$group_id       = $action_data['meta']['BPGROUPS'];
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

		if ( empty( $group_id ) ) {
			return false;
		}
		$activity = false;
		if ( '-1' === $group_id ) {
			global $wpdb;
			$statuses   = [ 'public', 'private', 'hidden' ];
			$in_str_arr = array_fill( 0, count( $statuses ), '%s' );
			$in_str     = join( ',', $in_str_arr );
			$group_qry  = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}bp_groups WHERE status IN ($in_str)", $statuses );
			$results    = $wpdb->get_results( $group_qry );
			if ( $results ) {
				foreach ( $results as $result ) {
					$hide_sitewide = false;
					if ( in_array( $result->status, [ 'private', 'hidden' ], true ) ) {
						$hide_sitewide = true;
					}
					$activity = bp_activity_add( [
						'action'        => $action,
						'content'       => $action_content,
						'primary_link'  => $action_link,
						'component'     => 'groups',
						'item_id'       => $result->id,
						'type'          => 'activity_update',
						'user_id'       => $action_author,
						'hide_sitewide' => $hide_sitewide,
					] );
					if ( is_wp_error( $activity ) ) {
						break;
					}
					if ( ! $activity ) {
						break;
					}
				}
			}
		} else {
			global $wpdb;
			$group_qry = "SELECT * FROM {$wpdb->prefix}bp_groups WHERE id = {$group_id}";
			$results   = $wpdb->get_results( $group_qry );
			if ( $results ) {
				foreach ( $results as $result ) {
					$hide_sitewide = false;
					if ( in_array( $result->status, [ 'private', 'hidden' ], true ) ) {
						$hide_sitewide = true;
					}
					$activity = bp_activity_add( [
						'action'        => $action,
						'content'       => $action_content,
						'primary_link'  => $action_link,
						'component'     => 'groups',
						'item_id'       => $result->id,
						'type'          => 'activity_update',
						'user_id'       => $action_author,
						'hide_sitewide' => $hide_sitewide,
					] );
					if ( is_wp_error( $activity ) ) {
						break;
					}
					if ( ! $activity ) {
						break;
					}
				}
			}
		}

		if ( is_wp_error( $activity ) ) {
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( $activity->get_error_message() ) );
		} elseif ( ! $activity ) {
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( 'There is an error on posting stream.' ) );
		} else {
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
		}
	}
}
