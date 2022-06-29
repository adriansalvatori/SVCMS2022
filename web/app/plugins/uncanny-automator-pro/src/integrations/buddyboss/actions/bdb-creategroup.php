<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BDB_CREATEGROUP
 *
 * @package Uncanny_Automator_Pro
 */
class BDB_CREATEGROUP {

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
		$this->action_code = 'BDBCREATEGROUP';
		$this->action_meta = 'BDBGROUPCREATE';

		if ( is_admin() ) {
			add_action( 'wp_loaded', [ $this, 'plugins_loaded' ], 99 );
		} else {
			$this->define_action();
		}
	}

	public function plugins_loaded() {
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object.
	 */
	public function define_action() {

		global $uncanny_automator;

		$user_selectors = [
			[
				'value' => 'ID',
				'text'  => __( 'ID', 'uncanny-automator-pro' ),
			],
			[
				'value' => 'email',
				'text'  => __( 'Email', 'uncanny-automator-pro' ),
			],
			[
				'value' => 'username',
				'text'  => __( 'Username', 'uncanny-automator-pro' ),
			],
		];

		$group_status = [
			'public'  => __( 'Public', 'uncanny-automator-pro' ),
			'private' => __( 'Private', 'uncanny-automator-pro' ),
			'hidden'  => __( 'Hidden', 'uncanny-automator-pro' ),
		];

		$privacy_dropdown                = $uncanny_automator->helpers->recipe->field->select_field( 'BDBGROUPPRIVACY', esc_attr__( 'Group status', 'uncanny-automator-pro' ), $group_status );
		$privacy_dropdown['description'] = __( 'BuddyBoss automatically adds the user to the group as group creator.', 'uncanny-automator-pro' );

		// Adding option of parent group when its enabled
		$parent_group = false;
		if ( function_exists( 'bp_enable_group_hierarchies' ) ) {
			$parent_group = bp_enable_group_hierarchies();
		}
		$bp_parent_group_args = array(
			'uo_include_any' => true,
			'uo_any_label'   => __( 'No parent', 'uncanny-automator-pro' ),
			'status'         => array( 'public', 'hidden', 'private' ),
		);

		// Adding option of parent group when its enabled
		$group_type = false;
		if ( function_exists( 'bp_disable_group_type_creation' ) ) {
			$group_type = bp_disable_group_type_creation();
		}
		$bp_group_type_args = array(
			'uo_include_any' => true,
			'uo_any_label'   => __( 'No type', 'uncanny-automator-pro' ),
			'status'         => array( 'public' ),
		);

		$action = [
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/buddyboss/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - BuddyBoss */
			'sentence'           => sprintf( esc_attr__( 'Create {{a group:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - BuddyBoss */
			'select_option_name' => esc_attr__( 'Create {{a group}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'add_post_stream' ],
			'options_group'      => [
				$this->action_meta => [
					$uncanny_automator->helpers->recipe->field->text_field( 'BDBGROUPTITLE', esc_attr__( 'Group name', 'uncanny-automator-pro' ), true, 'text', '', true ),
					$privacy_dropdown,
				],
			],
		];

		if ( $parent_group ) {
			$action['options_group'][ $this->action_meta ][] = $uncanny_automator->helpers->recipe->buddyboss->options->all_buddyboss_groups(
				__( 'Parent group', 'uncanny-automator-pro' ),
				'BDBPARENTGROUPS',
				$bp_parent_group_args
			);
		}

		if ( $group_type ) {
			$action['options_group'][ $this->action_meta ][] = $uncanny_automator->helpers->recipe->buddyboss->options->pro->get_groups_types(
				__( 'Group type', 'uncanny-automator-pro' ),
				'BDBGROUPTYPES',
				$bp_group_type_args
			);
		}
		$action['options_group'][ $this->action_meta ][] = [
			'input_type'        => 'repeater',
			'option_code'       => 'ADDMOREUSERS',
			'label'             => esc_attr__( 'Additional users to add to the group', 'uncanny-automator-pro' ),
			'required'          => true,
			'fields'            => [
				[
					'option_code' => 'USER_SELECTOR',
					'label'       => __( 'Select user where', 'uncanny-automator-pro' ),
					'input_type'  => 'select',
					'required'    => false,
					'options'     => $user_selectors,
				],
				[
					'input_type'      => 'text',
					'option_code'     => 'VALUE',
					'label'           => esc_attr__( 'Value', 'uncanny-automator-pro' ),
					'supports_tokens' => true,
					'required'        => false,
				],
			],

			/* translators: Non-personal infinitive verb */
			'add_row_button'    => esc_attr__( 'Add pair', 'uncanny-automator-pro' ),
			/* translators: Non-personal infinitive verb */
			'remove_row_button' => esc_attr__( 'Remove pair', 'uncanny-automator-pro' ),
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
		$title           = $uncanny_automator->parse->text( $action_data['meta']['BDBGROUPTITLE'], $recipe_id, $user_id, $args );
		$title           = do_shortcode( $title );
		$privacy_options = $action_data['meta']['BDBGROUPPRIVACY'];
		$add_other_users = $action_data['meta']['ADDMOREUSERS'];
		if ( isset( $action_data['meta']['BDBPARENTGROUPS'] ) ) {
			$parent_id = $uncanny_automator->parse->text( $action_data['meta']['BDBPARENTGROUPS'], $recipe_id, $user_id, $args );
		}
		if ( isset( $action_data['meta']['BDBGROUPTYPES'] ) ) {
			$group_types = $uncanny_automator->parse->text( $action_data['meta']['BDBGROUPTYPES'], $recipe_id, $user_id, $args );
		}

		// Creating a group
		$group_args = [
			'creator_id' => $user_id,
			'name'       => $title,
			'status'     => $privacy_options,
		];

		if ( isset( $parent_id ) && ! empty( $group_args ) ) {
			$group_args['parent_id'] = $parent_id;
		}

		$group = groups_create_group( $group_args );

		if ( is_wp_error( $group ) ) {
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( $group->get_error_message() ) );
		} elseif ( ! $group ) {
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( 'There is an error on creating group.', 'uncanny-automator-pro' ) );
		} else {
			// set group type
			if ( isset( $group_types ) && ! empty( $group_types ) ) {
				bp_groups_set_group_type( $group, $group_types );
			}
			// Adding other users
			if ( ! empty( $add_other_users ) ) {
				$user_selectors = json_decode( $add_other_users, true );
				if ( ! empty( $user_selectors ) ) {
					foreach ( $user_selectors as $user_selector ) {
						$existing_user_id = false;
						if ( ! empty( $user_selector['VALUE'] ) ) {
							$value = $uncanny_automator->parse->text( $user_selector['VALUE'], $recipe_id, $user_id, $args );
							if ( 'ID' === $user_selector['USER_SELECTOR'] ) {
								$existing_user_id = intval( $value );
							} elseif ( 'email' === $user_selector['USER_SELECTOR'] ) {
								$existing_user = get_user_by( 'email', $value );
								if ( $existing_user ) {
									$existing_user_id = $existing_user->ID;
								}
							} elseif ( 'username' === $user_selector['USER_SELECTOR'] ) {
								$existing_user = get_user_by( 'login', $value );
								if ( $existing_user ) {
									$existing_user_id = $existing_user->ID;
								}
							}
							if ( $existing_user_id ) {
								groups_join_group( $group, $existing_user_id );
							}
						}
					}
				}
			}

			$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id );
		}
	}
}
