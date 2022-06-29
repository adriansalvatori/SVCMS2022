<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BDB_SETUSERPROFILEDATA
 * @package Uncanny_Automator_Pro
 */
class BDB_SETUSERPROFILEDATA {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'BDB';

	private $action_code;
	private $action_meta;

	/**
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		$this->action_code = 'BDBSETUSERPROFILEDATA';
		$this->action_meta = 'BDBPROFILE';

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
			'sentence'           => sprintf( __( "Set the user's {{Xprofile data:%1\$s}}", 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - BuddyBoss */
			'select_option_name' => __( "Set the user's {{Xprofile data}}", 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'update_user_profile_data' ],
			'options_group'      => [
				$this->action_meta => [
					[
						'input_type'        => 'repeater',
						'option_code'       => 'BDBPROFILEDATA',
						'label'             => '',
						'required'          => true,
						'fields'            => [
							$uncanny_automator->helpers->recipe->buddyboss->options->pro->list_all_profile_fields( esc_attr__( 'Field', 'uncanny-automator-pro' ), 'BDBUSERFIELD', [ 'is_repeater' => true ] ),
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
					],
				],
			],
		];

		$uncanny_automator->register->action( $action );
	}

	/**
	 * Update user profile type
	 *
	 * @param string $user_id
	 * @param array $action_data
	 * @param string $recipe_id
	 *
	 * @return void
	 *
	 * @since 1.1
	 */
	public function update_user_profile_data( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;
		$user_fields_data = $action_data['meta']['BDBPROFILEDATA'];

		// Adding other users
		if ( ! empty( $user_fields_data ) ) {
			$user_selectors = json_decode( $user_fields_data, true );
			if ( ! empty( $user_selectors ) ) {
				foreach ( $user_selectors as $user_selector ) {
					$field_id = $user_selector['BDBUSERFIELD'];
					if ( ! empty( $user_selector['VALUE'] ) ) {
						$value = $uncanny_automator->parse->text( $user_selector['VALUE'], $recipe_id, $user_id, $args );
						if ( function_exists( 'xprofile_set_field_data' ) ) {
							xprofile_set_field_data( $field_id, $user_id, $value );
						}
					}
				}
			}
		}
		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
