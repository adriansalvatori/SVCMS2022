<?php

namespace Uncanny_Automator_Pro;

use WC_Order;

/**
 * Class WC_SETSTATUSTOORDER
 *
 * @package Uncanny_Automator_Pro
 */
class WC_SETSTATUSTOORDER {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WC';

	/**
	 * @var string
	 */
	private $action_code;
	/**
	 * @var string
	 */
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'WCSETSTATUSTOANORDER';
		$this->action_meta = 'WCORDERID';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		$action = array(
			'author'             => Automator()->get_author_name( $this->action_code ),
			'support_link'       => Automator()->get_author_support_link( $this->action_code, 'integration/woocommerce/' ),
			'is_pro'             => true,
			'requires_user'      => false,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - WooCommerce */
			'sentence'           => sprintf( __( 'Set {{a specific order:%1$s}} to {{a specific status:%2$s}}', 'uncanny-automator-pro' ), $this->action_meta, 'WCSTATUSES' ),
			/* translators: Action - WooCommerce */
			'select_option_name' => __( 'Set {{a specific order}} to {{a specific status}}', 'uncanny-automator-pro' ),
			'priority'           => 1,
			'accepted_args'      => 1,
			'execution_function' => array(
				$this,
				'set_order_status_to_order',
			),
			'options'            => array(
				Automator()->helpers->recipe->field->text(
					array(
						'option_code' => $this->action_meta,
						'label'       => __( 'Order ID', 'uncanny-automator' ),
						'placeholder' => __( 'Order ID', 'uncanny-automator' ),
					)
				),
				Automator()->helpers->recipe->woocommerce->options->wc_order_statuses(
					__( 'Status', 'uncanny-automator' ),
					'WCSTATUSES'
				),
			),
		);

		Automator()->register->action( $action );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 * @param $args
	 */
	public function set_order_status_to_order( $user_id, $action_data, $recipe_id, $args ) {

		$order_id = Automator()->parse->text( $action_data['meta']['WCORDERID'], $recipe_id, $user_id, $args );
		if ( empty( $order_id ) ) {
			$error_message                       = __( 'Order ID is not valid.', 'uncanny-automator-pro' );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}
		$new_status = Automator()->parse->text( $action_data['meta']['WCSTATUSES'], $recipe_id, $user_id, $args );
		if ( empty( $new_status ) ) {
			$error_message                       = __( 'Order status is not valid.', 'uncanny-automator-pro' );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}
		$order = wc_get_order( $order_id );

		if ( ! $order instanceof WC_Order ) {
			$error_message                       = __( 'No order found with the specified order ID.', 'uncanny-automator-pro' );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$order->update_status( $new_status );
		Automator()->complete_action( $user_id, $action_data, $recipe_id );
	}
}
