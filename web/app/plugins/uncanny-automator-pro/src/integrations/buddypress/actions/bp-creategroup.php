<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BP_CREATEGROUP
 *
 * @package Uncanny_Automator_Pro
 */
class BP_CREATEGROUP {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'BP';

	private $action_code;
	private $action_meta;

	/**
	 * Set Triggers constructor.
	 */
	public function __construct() {

		$this->action_code = 'BPCREATEGROUP';

		$this->action_meta = 'BPGROUPCREATE';

		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object.
	 */
	public function define_action() {

		$user_selectors = array(
			array(
				'value' => 'ID',
				'text'  => __( 'ID', 'uncanny-automator-pro' ),
			),
			array(
				'value' => 'email',
				'text'  => __( 'Email', 'uncanny-automator-pro' ),
			),
			array(
				'value' => 'username',
				'text'  => __( 'Username', 'uncanny-automator-pro' ),
			),
		);

		$group_status = array(
			'public'  => __( 'Public', 'uncanny-automator-pro' ),
			'private' => __( 'Private', 'uncanny-automator-pro' ),
			'hidden'  => __( 'Hidden', 'uncanny-automator-pro' ),
		);

		$privacy_dropdown = Automator()->helpers->recipe->field->select_field( 'BPGROUPPRIVACY', esc_attr__( 'Group status', 'uncanny-automator-pro' ), $group_status );

		$privacy_dropdown['description'] = __( 'BuddyPress automatically adds the user to the group as group creator.', 'uncanny-automator-pro' );

		$action = array(
			'author'             => Automator()->get_author_name(),
			'support_link'       => Automator()->get_author_support_link( $this->action_code, 'integration/buddypress/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - BuddyPress */
			'sentence'           => sprintf( esc_attr__( 'Create {{a group:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - BuddyPress */
			'select_option_name' => esc_attr__( 'Create {{a group}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'add_post_stream' ),
			'options_group'      => array(
				$this->action_meta => array(
					Automator()->helpers->recipe->field->text_field( 'BPGROUPTITLE', esc_attr__( 'Group name', 'uncanny-automator-pro' ), true, 'text', '', true ),
					$privacy_dropdown,
					array(
						'input_type'        => 'repeater',
						'option_code'       => 'ADDMOREUSERS',
						'label'             => esc_attr__( 'Additional users to add to the group', 'uncanny-automator-pro' ),
						'required'          => true,
						'fields'            => array(
							array(
								'option_code' => 'USER_SELECTOR',
								'label'       => __( 'Select user where', 'uncanny-automator-pro' ),
								'input_type'  => 'select',
								'required'    => false,
								'options'     => $user_selectors,
							),
							array(
								'input_type'      => 'text',
								'option_code'     => 'VALUE',
								'label'           => esc_attr__( 'Value', 'uncanny-automator-pro' ),
								'supports_tokens' => true,
								'required'        => false,
							),
						),

						/* translators: Non-personal infinitive verb */
						'add_row_button'    => esc_attr__( 'Add pair', 'uncanny-automator-pro' ),
						/* translators: Non-personal infinitive verb */
						'remove_row_button' => esc_attr__( 'Remove pair', 'uncanny-automator-pro' ),
					),
				),
			),
		);

		Automator()->register->action( $action );
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

		$title           = Automator()->parse->text( $action_data['meta']['BPGROUPTITLE'], $recipe_id, $user_id, $args );
		$title           = do_shortcode( $title );
		$privacy_options = $action_data['meta']['BPGROUPPRIVACY'];
		$add_other_users = $action_data['meta']['ADDMOREUSERS'];

		// Creating a group
		$group = groups_create_group(
			array(
				'creator_id' => $user_id,
				'name'       => $title,
				'status'     => $privacy_options,
			)
		);

		if ( is_wp_error( $group ) ) {

			$action_data['complete_with_errors'] = true;

			Automator()->complete_action( $user_id, $action_data, $recipe_id, $group->get_error_message() );

		} elseif ( ! $group ) {

			Automator()->complete_action( $user_id, $action_data, $recipe_id, __( 'There is an error on creating group.', 'uncanny-automator-pro' ) );

		} else {

			// Adding other users
			if ( ! empty( $add_other_users ) ) {

				$user_selectors = json_decode( $add_other_users, true );

				if ( ! empty( $user_selectors ) ) {

					foreach ( $user_selectors as $user_selector ) {

						$existing_user_id = false;

						// Parse the value as token.
						$user_selector_value = Automator()->parse->text( $user_selector['VALUE'], $recipe_id, $user_id, $args );

						if ( ! empty( $user_selector_value ) ) {

							if ( 'ID' === $user_selector['USER_SELECTOR'] ) {

								$existing_user_id = intval( $user_selector_value );

							} elseif ( 'email' === $user_selector['USER_SELECTOR'] ) {

								$existing_user = get_user_by( 'email', $user_selector_value );

								if ( $existing_user ) {

									$existing_user_id = $existing_user->ID;

								}
							} elseif ( 'username' === $user_selector['USER_SELECTOR'] ) {

								$existing_user = get_user_by( 'login', $user_selector_value );

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

			Automator()->complete->action( $user_id, $action_data, $recipe_id );
		}
	}
}
