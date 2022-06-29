<?php

namespace Uncanny_Automator_Pro;

/**
 * Abstract class Action_Condition
 *
 * See integrations/wp/wp-token-meets-condition.php for an example how to extend it
 *
 * @package Uncanny_Automator_Pro
 */
abstract class Action_Condition {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public $integration = 'GEN';
	/**
	 * @var string
	 */
	public $code = '';
	/**
	 * @var string
	 */
	public $name = '';
	/**
	 * @var
	 */
	public $dynamic_name;
	/**
	 * @var bool
	 */
	public $is_pro = true;
	/**
	 * @var bool
	 */
	public $requires_user = false;
	/**
	 * @var bool
	 */
	public $deprecated = false;
	/**
	 * @var bool
	 */
	public $active = true;

	/**
	 * Method define_condition
	 *
	 * @return void
	 */
	abstract public function define_condition();

	/**
	 * Method fields
	 *
	 * @return void
	 */
	abstract public function fields();

	/**
	 * Method evaluate_condition
	 *
	 * If the conditions fails, use the $this->condition_failed( $log_message ) inside this method
	 *
	 * @param mixed $result
	 * @param mixed $action
	 * @param mixed $condition
	 *
	 * @return void
	 */
	abstract public function evaluate_condition();

	/**
	 * Set up Automator trigger constructor.
	 */
	final public function __construct() {

		$this->define_condition();

		if ( ! $this->active ) {
			return;
		}

		$this->parse = Automator()->parse;
		$this->field = Automator()->helpers->recipe->field;

		add_filter( 'automator_pro_actions_conditions_list', array( $this, 'register' ) );
		add_filter( 'automator_pro_evaluate_actions_conditions', array( $this, 'maybe_evaluate_condition' ), 10, 2 );
		add_filter( 'automator_pro_actions_conditions_fields', array( $this, 'maybe_send_fields' ), 10, 3 );
	}

	/**
	 * Method register
	 *
	 * @param mixed $actions_conditions
	 *
	 * @return void
	 */
	final public function register( $actions_conditions ) {

		if ( empty( $this->name ) ) {
			throw new \Exception( 'Condition name is required' );
		}

		if ( empty( $this->dynamic_name ) ) {
			throw new \Exception( 'Condition dynamic is required' );
		}

		if ( empty( $this->fields() ) ) {
			throw new \Exception( 'Condition fields are required' );
		}

		$add_condition = array(
			'name'          => $this->name,
			'dynamic_name'  => $this->dynamic_name,
			'is_pro'        => $this->is_pro,
			'requires_user' => $this->requires_user,
			'deprecated'    => $this->deprecated,
		);

		$actions_conditions[ $this->integration ][ $this->code ] = $add_condition;

		return $actions_conditions;
	}

	/**
	 * Method maybe_evaluate_condition
	 *
	 * @param mixed $result
	 * @param mixed $action
	 * @param mixed $condition
	 *
	 * @return void
	 */
	final public function maybe_evaluate_condition( $action, $condition ) {

		try {
			$this->hydrate( $action, $condition );
			$this->evaluate_condition();
		} catch ( \Exception $e ) {
			automator_log( $e->getMessage() );
		}

		return $this->action;
	}

	/**
	 * Method hydrate
	 *
	 * @param array $action
	 * @param array $condition
	 *
	 * @return void
	 */
	final public function hydrate( $action, $condition ) {

		$this->condition = $condition;
		$this->action    = $action;

		if ( ! isset( $this->condition['condition'] ) ) {
			throw new \Exception( 'Missing condition' );
		}

		if ( $this->condition['condition'] !== $this->code ) {
			throw new \Exception( "Condition code doesn't match" );
		}

		if ( ! isset( $this->condition['integration'] ) ) {
			throw new \Exception( 'Missing integration code' );
		}

		if ( $this->condition['integration'] !== $this->integration ) {
			throw new \Exception( "Integration code doesn't match" );
		}

		if ( ! isset( $this->condition['fields'] ) ) {
			throw new \Exception( 'Condition options are missing' );
		}

		$this->options     = $condition['fields'];
		$this->action_data = $action['action_data'];
		$this->recipe_id   = $action['recipe_id'];
		$this->user_id     = empty( $action['user_id'] ) ? get_current_user_id() : $action['user_id'];
		$this->args        = $action['args'];

		// The condition is met by default
		$this->action['process_further'] = true;
	}

	/**
	 * Method condition_failed
	 *
	 * Use this method if the condition was not met
	 *
	 * @param mixed $action
	 * @param mixed $log_message
	 */
	public function condition_failed( $log_message = '' ) {

		$log_message = apply_filters( 'automator_pro_actions_conditions_log_message', $log_message, $this );

		if ( empty( $log_message ) ) {
			$log_message = $this->name . __( ' failed', 'uncanny-automator-pro' );
		}

		$this->action['process_further']                          = false;
		$this->action['action_data']['failed_actions_conditions'] = true;
		$this->action['action_data']['actions_conditions_log'][]  = sanitize_text_field( $log_message );
	}

	/**
	 * Method get_option
	 *
	 * Get the input value of an option
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function get_option( $name ) {

		if ( ! isset( $this->options[ $name ] ) ) {
			throw new \Exception( $name . ' option is missing' );
		}

		return $this->options[ $name ];
	}

	/**
	 * Method get_parsed_option
	 *
	 * Get the input value of an option with tokens parsed
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function get_parsed_option( $name ) {

		$value = $this->get_option( $name );

		return $this->parse->text( $value, $this->recipe_id, $this->user_id, $this->args );
	}

	/**
	 * Method maybe_send_fields
	 *
	 * Send the condtion's fields to the UI
	 *
	 * @param array $fields
	 * @param string $integration
	 * @param string $code
	 *
	 * @return array
	 */
	public function maybe_send_fields( $fields, $integration, $code ) {

		if ( $this->integration !== $integration ) {
			return $fields;
		}

		if ( $this->code !== $code ) {
			return $fields;
		}

		return $this->fields();
	}
}
