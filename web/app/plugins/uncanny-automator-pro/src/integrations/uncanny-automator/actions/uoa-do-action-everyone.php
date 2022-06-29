<?php

namespace Uncanny_Automator_Pro;

/**
 * Class UOA_DO_ACTION_EVERYONE
 */
class UOA_DO_ACTION_EVERYONE {
	use \Uncanny_Automator\Recipe\Actions;

	/**
	 * UOA_DO_ACTION_EVERYONE constructor.
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'migrate_existing_actions_to_new' ), 999 );

		$this->wpautop = false;
		$this->setup_action();
	}

	/**
	 * Setups our action.
	 *
	 * @return void
	 */
	protected function setup_action() {
		$this->set_integration( 'UOA' );
		$this->set_is_pro( true );
		$this->set_wpautop( false );
		$this->set_requires_user( false );
		$this->set_action_meta( 'DOACTION_EVERYONE' );
		$this->set_action_code( 'UOADOACTION_EVERYONE' );
		if ( method_exists( '\Uncanny_Automator\Recipe\Actions', 'set_requires_user' ) ) {
			$this->set_requires_user( false );
		}
		$this->set_support_link( 'https://automatorplugin.com/knowledge-base/run-a-wordpress-hook/' );
		/* translators: Action - WordPress */
		$this->set_sentence( sprintf( esc_attr__( 'Run a {{WordPress hook:%1$s}}', 'uncanny-automator' ), $this->get_action_meta() ) );
		/* translators: Action - WordPress */
		$this->set_readable_sentence( esc_attr__( 'Run a {{WordPress hook}}', 'uncanny-automator' ) );
		$options_group = array(
			$this->get_action_meta() => array(
				/* translators: Text field */
				Automator()->helpers->recipe->field->text_field( 'HOOK_NAME', esc_attr__( 'Hook name', 'uncanny-automator' ), false, 'text', null, true, esc_attr__( 'Enter an existing or custom WP hook name here', 'uncanny-automator' ) ),
				/* translators: Repeater field */
				array(
					'input_type'        => 'repeater',

					'option_code'       => 'HOOK_VARS',

					'label'             => esc_attr__( 'Pass variables', 'uncanny-automator' ),
					'description'       => __( 'Variables will be passed to the hook in this exact order. Variables like <strong>null</strong>, <strong>[]</strong> and <strong>array()</strong> will be passed as null and empty arrays.', 'uncanny-automator' ),

					'required'          => false,
					'fields'            => array(
						array(
							'input_type'      => 'text',

							'option_code'     => 'VALUE',
							'label'           => esc_attr__( 'Value', 'uncanny-automator' ),

							'supports_tokens' => true,
							'required'        => false,
						),
					),

					/* translators: Non-personal infinitive verb */
					'add_row_button'    => esc_attr__( 'Add a variable', 'uncanny-automator' ),
					/* translators: Non-personal infinitive verb */
					'remove_row_button' => esc_attr__( 'Remove a variable', 'uncanny-automator' ),
				),
			),
		);

		$this->set_options_group( $options_group );

		$this->register_action();
	}


	/**
	 * Process the action.
	 *
	 * @param int $user_id
	 * @param array $action_data
	 * @param int $recipe_id
	 * @param array $args
	 * @param $parsed
	 */
	protected function process_action( $user_id, $action_data, $recipe_id, $args, $parsed ) {

		$hook_name = isset( $parsed['HOOK_NAME'] ) ? $parsed['HOOK_NAME'] : '';

		$vars = isset( $parsed['HOOK_VARS'] ) ? json_decode( $parsed['HOOK_VARS'] ) : '';

		$args = array();

		foreach ( $vars as $var ) {

			$args[] = $this->parse( $var->VALUE ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		array_unshift( $args, $hook_name );

		try {

			call_user_func_array( 'do_action', $args );

		} catch ( \Exception $e ) {

			$action_data['complete_with_errors'] = true;
			$error_message                       = $e->getMessage();

			Automator()->complete->action( $user_id, $action_data, $recipe_id, $error_message );

		}

		Automator()->complete->action( $user_id, $action_data, $recipe_id );

	}

	/**
	 * Parses the value.
	 *
	 * This function will replace null and empty arrays into real values.
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	protected function parse( $value ) {
		$output = null;
		switch ( $value ) {
			case 'null':
				break;
			case 'array()':
			case '[]':
				$output = array();
				break;
			default:
				$output = $value;
				break;
		}

		return apply_filters( 'automator_do_action_parse_vars', $output, $value );
	}

	/**
	 * Update the post meta from 'UOA_CALL_FUNC_CODE' to 'UOA_CALL_FUNC_EVERYONE_CODE'.
	 *
	 * @return void
	 */
	public function migrate_existing_actions_to_new() {

		$option_key = 'automator_uoa_do_function_everyone_migrate';

		if ( 'yes' === get_option( $option_key, 'no' ) ) {
			return;
		}

		global $wpdb;

		$existing_actions = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT post_id 
				FROM $wpdb->postmeta 
				WHERE meta_key = %s 
				AND meta_value = %s",
				'code',
				'UOADOACTION'
			)
		);

		if ( empty( $existing_actions ) ) {
			update_option( $option_key, 'yes' );
			return;
		}

		foreach ( $existing_actions as $action_id ) {
			update_post_meta( $action_id, 'code', 'UOADOACTION_EVERYONE' );
		}

		update_option( $option_key, 'yes' );

	}
}
