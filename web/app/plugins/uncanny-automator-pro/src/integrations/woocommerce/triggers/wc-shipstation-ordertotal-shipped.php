<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WC_SHIPSTATION_ORDERTOTAL_SHIPPED
 *
 * @package Uncanny_Automator_Pro
 */
class WC_SHIPSTATION_ORDERTOTAL_SHIPPED {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WC';

	/**
	 * @var string
	 */
	private $trigger_code;
	/**
	 * @var string
	 */
	private $trigger_meta;
	/**
	 * @var string
	 */
	private $trigger_condition;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		if ( function_exists( 'woocommerce_shipstation_init' ) ) {
			$this->trigger_code = 'WCSHIPSTATIONORDERTOTALSHIPPED';
			$this->trigger_meta = 'WOORDERTOTAL';

			$this->define_trigger();
		}
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;
		// Get the currency symbol
		$currency_symbol = get_woocommerce_currency_symbol();
		$currency_symbol = empty( $currency_symbol ) ? '$' : $currency_symbol;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/woocommerce/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'trigger_meta'        => $this->trigger_code,
			/* translators: Logged-in trigger - WooCommerce */
			'sentence'            => sprintf( esc_attr__( 'An order with a total {{greater than, less than or equal to:%1$s}} %2$s{{a specific amount:%3$s}} is shipped', 'uncanny-automator-pro' ), 'NUMBERCOND', $currency_symbol, $this->trigger_meta ),
			/* translators: Logged-in trigger - WooCommerce */
			'select_option_name'  => sprintf( esc_attr__( 'An order with a total {{greater than, less than or equal to}} {{a specific amount}} is shipped', 'uncanny-automator-pro' ), $currency_symbol ),
			'action'              => 'woocommerce_shipstation_shipnotify',
			'priority'            => 99,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'shipping_completed' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @return array
	 */
	public function load_options() {
		$options_array = array(
			'options' => array(
				Automator()->helpers->recipe->field->float_field( $this->trigger_meta, esc_attr__( 'Order price', 'uncanny-automator' ) ),
				Automator()->helpers->recipe->less_or_greater_than(),
			),
		);

		return Automator()->utilities->keep_order_of_options( $options_array );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $order
	 * @param $argu
	 */

	public function shipping_completed( $order, $argu ) {
		global $uncanny_automator;

		if ( ! $order ) {
			return;
		}

		$user_id = $order->get_user_id();
		if ( 0 === $user_id ) {
			// Its a logged in recipe and
			// user ID is 0. Skip process
			return;
		}
		$matched_recipe_ids = array();

		// Get real order ID from order object.
		$order_id = version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->id : $order->get_id();

		$order_total         = $order->get_total();
		$recipes             = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_totals     = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$required_conditions = $uncanny_automator->get->meta_from_recipes( $recipes, 'NUMBERCOND' );

		//Add where option is set to Any product
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( $uncanny_automator->utilities->match_condition_vs_number( $required_conditions[ $recipe_id ][ $trigger_id ], $required_totals[ $recipe_id ][ $trigger_id ], $order_total ) ) {
					$matched_recipe_ids[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					);
				}
			}
		}

		if ( empty( $matched_recipe_ids ) ) {
			return;
		}

		foreach ( $matched_recipe_ids as $matched_recipe_id ) {

			$pass_args = array(
				'code'             => $this->trigger_code,
				'meta'             => $this->trigger_meta,
				'user_id'          => $user_id,
				'post_id'          => $order_id,
				'ignore_post_id'   => true,
				'is_signed_in'     => true,
				'recipe_to_match'  => $matched_recipe_id['recipe_id'],
				'trigger_to_match' => $matched_recipe_id['trigger_id'],
			);

			$args = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );

			if ( $args ) {
				foreach ( $args as $result ) {
					if ( true === $result['result'] ) {
						// Add token for options
						$uncanny_automator->insert_trigger_meta(
							array(
								'user_id'        => $user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'meta_key'       => 'WOOORDER_TRACKING_NUMBER',
								'meta_value'     => $argu['tracking_number'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							)
						);
						// Add token for options
						$uncanny_automator->insert_trigger_meta(
							array(
								'user_id'        => $user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'meta_key'       => 'WOOORDER_CARRIER',
								'meta_value'     => $argu['carrier'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							)
						);
						// Add token for options
						$uncanny_automator->insert_trigger_meta(
							array(
								'user_id'        => $user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'meta_key'       => 'WOOORDER_SHIP_DATE',
								'meta_value'     => $argu['ship_date'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							)
						);
						// Add token for options
						$uncanny_automator->insert_trigger_meta(
							array(
								'user_id'        => $user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'meta_key'       => 'order_id',
								'meta_value'     => $order_id,
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							)
						);

						$uncanny_automator->maybe_trigger_complete( $result['args'] );
					}
				}
			}
		}

		return;
	}
}
