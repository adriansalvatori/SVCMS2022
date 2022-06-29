<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WC_CREATEORDER
 *
 * @package Uncanny_Automator_Pro
 */
class WC_CREATEORDER {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WC';

	/**
	 * Action code var.
	 *
	 * @var string
	 */
	private $action_code;
	/**
	 * Action meta var.
	 *
	 * @var string
	 */
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'WCCREATEORDER';
		$this->action_meta = 'CREATEORDER';
		$this->define_action();

		add_filter(
			'uap_option_all_wc_products',
			array(
				$this,
				'remove_any_product_option',
			)
		);
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		$action = array(
			'author'             => Automator()->get_author_name( $this->action_code ),
			'support_link'       => Automator()->get_author_support_link( $this->action_code, 'integration/woocommerce/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - WooCommerce */
			'sentence'           => sprintf( __( 'Create an order with {{a product:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - WooCommerce */
			'select_option_name' => __( 'Create an order with {{a product}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'create_woocommerce_order' ),
			'options_callback'   => array( $this, 'load_options' ),
		);

		Automator()->register->action( $action );
	}

	/**
	 * Load options.
	 *
	 * @return array
	 */
	public function load_options() {

		$options_array = array(
			'options_group' => array(
				$this->action_meta => Automator()->helpers->recipe->woocommerce->pro->load_options_input( false ),
			),
		);

		return Automator()->utilities->keep_order_of_options( $options_array );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 * @param $args
	 *
	 * @return false|void
	 */
	public function create_woocommerce_order( $user_id, $action_data, $recipe_id, $args ) {

		// First make sure all required functions and classes exist
		if ( ! function_exists( 'wc_create_order' ) ) {
			$error_message                       = __( '`wc_create_order` function is missing.', 'uncanny-automator-pro' );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$order_response = Automator()->helpers->recipe->woocommerce->pro->wc_create_order( $user_id, $action_data, $recipe_id, $args, false );

		if ( false === $order_response ) {
			$error_message                       = __( 'Action execution failed.', 'uncanny-automator-pro' );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		Automator()->complete_action( $user_id, $action_data, $recipe_id );
	}

	/**
	 * Remove Any product option from the drop down
	 *
	 * @param $option
	 */
	public function remove_any_product_option( $option ) {
		unset( $option['options'][- 1] );

		return $option;
	}

}
