<?php

namespace Uncanny_Automator_Pro;

/**
 * Class UOA_CALL_FUNCTION
 */
class UOA_CALL_FUNCTION {
	use \Uncanny_Automator\Recipe\Actions;

	/**
	 * UOA_CALL_FUNCTION constructor.
	 */
	public function __construct() {
		$this->wpautop = false;
		$this->setup_action();
	}

	/**
	 *
	 */
	protected function setup_action() {
		$this->set_integration( 'UOA' );
		$this->set_is_pro( true );
		$this->set_wpautop( false );
		$this->set_is_deprecated( true );
		$this->set_action_meta( 'UOA_CALL_FUNC_META' );
		$this->set_action_code( 'UOA_CALL_FUNC_CODE' );
		/* translators: Action - WordPress */
		$this->set_sentence( sprintf( esc_attr__( 'Call {{a custom function/method:%1$s}}', 'uncanny-automator-pro' ), $this->get_action_meta() ) );
		/* translators: Action - WordPress */
		$this->set_readable_sentence( esc_attr__( 'Call {{a custom function/method}}', 'uncanny-automator-pro' ) );
		$options_group = array(
			$this->get_action_meta() => array(
				array(
					'input_type'      => 'text',
					'option_code'     => $this->get_action_meta(),
					'required'        => true,
					'supports_tokens' => false,
					'label'           => esc_attr__( 'Function name', 'uncanny-automator-pro' ),
					'description'     => esc_attr__( 'The function must be available or registered before this Automator action. Pass the arguments by value in the "Pass variables" field below.', 'uncanny-automator-pro' ),
					'placeholder'     => esc_attr__( 'my_custom_function', 'uncanny-automator-pro' ),
				),
				array(
					'input_type'        => 'repeater',
					'option_code'       => 'FUNCTION_ARGS',
					'label'             => esc_attr__( 'Pass variables', 'uncanny-automator-pro' ),
					'description'       => __( '<strong>Arrays</strong> and <strong>Objects</strong> are not <strong>supported</strong> and will be treated as strings. Variables will be passed to the function in this exact order. Variables like <strong>null</strong>, <strong>[]</strong> and <strong>array()</strong> will be passed as null and empty arrays.', 'uncanny-automator' ),
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
	 * @param int $user_id
	 * @param array $action_data
	 * @param int $recipe_id
	 * @param array $args
	 * @param $parsed
	 */
	protected function process_action( $user_id, $action_data, $recipe_id, $args, $parsed ) {

		$function_name = isset( $parsed[ $this->get_action_meta() ] ) ? $parsed[ $this->get_action_meta() ] : '';
		$function_args = (array) isset( $parsed['FUNCTION_ARGS'] ) ? json_decode( $parsed['FUNCTION_ARGS'] ) : '';

		$args = array();

		// Check if the function exists.
		if ( function_exists( $function_name ) ) {

			foreach ( $function_args as $function_arg ) {
				$args[] = $this->parse( $function_arg->VALUE );
			}

			try {
				call_user_func_array( $function_name, $args );
				Automator()->complete->action( $user_id, $action_data, $recipe_id );
			} catch ( \Exception $e ) {
				$action_data['complete_with_errors'] = true;
				Automator()->complete->action( $user_id, $action_data, $recipe_id, $e->getMessage() );
			}
		} else {
			// Log the error if the function does not exists.
			$action_data['complete_with_errors'] = true;
			$error                               = sprintf(
				esc_html__(
					'
                    The function/method (%s) you are trying to call is not found or not yet registered.',
					'uncanny-automator-pro'
				),
				$function_name
			);
			Automator()->complete->action( $user_id, $action_data, $recipe_id, $error );
		}

	}

	/**
	 * parse
	 *
	 * This function will replace null and empty arrays into real values.
	 *
	 * @param  mixed $value
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
}
