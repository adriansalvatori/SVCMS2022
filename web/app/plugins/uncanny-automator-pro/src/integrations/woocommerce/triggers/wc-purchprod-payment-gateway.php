<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WC_PURCHPROD_PAYMENT_GATEWAY
 * @package Uncanny_Automator_Pro
 */
class WC_PURCHPROD_PAYMENT_GATEWAY {

	/**
	 * Integration code
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
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code      = 'WCPURCHPRODUCTVIAGATEWAY';
		$this->trigger_meta      = 'WOOPAYMENTGATEWAY';
		$this->trigger_condition = 'TRIGGERCOND';

		if ( Automator()->helpers->recipe->is_edit_page() ) {
			add_action( 'wp_loaded', array( $this, 'plugins_loaded' ), 9999 );
		} else {
			$this->define_trigger();
		}
	}

	/**
	 *
	 */
	public function plugins_loaded() {
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/woocommerce/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - WooCommerce */
			'sentence'            => sprintf( __( 'A user {{completes, pays for, lands on a thank you page for:%1$s}} an order paid for with {{a specific method:%2$s}}', 'uncanny-automator-pro' ), $this->trigger_condition, $this->trigger_meta ),
			/* translators: Logged-in trigger - WooCommerce */
			'select_option_name'  => __( 'A user {{completes, pays for, lands on a thank you page for}} an order paid for with {{a specific method}}', 'uncanny-automator-pro' ),
			'action'              => array(
				'woocommerce_order_status_completed',
				'woocommerce_thankyou',
				'woocommerce_payment_complete',
			),
			'priority'            => 999,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'wc_payment_gateway' ),
			'options_callback'    => array( $this, 'load_options' ),

		);
		Automator()->register->trigger( $trigger );
	}

	/**
	 * @return array
	 */
	public function load_options() {
		$trigger_condition = Automator()->helpers->recipe->woocommerce->pro->get_woocommerce_trigger_conditions( $this->trigger_condition );
		$options_array     = array(
			'options' => array(
				$trigger_condition,
				Automator()->helpers->recipe->woocommerce->options->pro->all_wc_payment_gateways(
					__( 'Payment method', 'uncanny-automator-pro' ),
					$this->trigger_meta
				),
			),
		);

		return Automator()->utilities->keep_order_of_options( $options_array );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $order_id
	 */
	public function wc_payment_gateway( $order_id ) {

		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order instanceof \WC_Order ) {
			return;
		}

		$user_id                 = $order->get_customer_id();
		$recipes                 = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_payment_method = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$required_condition      = Automator()->get->meta_from_recipes( $recipes, $this->trigger_condition );
		$matched_recipe_ids      = array();
		$trigger_cond_ids        = array();

		if ( empty( $recipes ) ) {
			return;
		}

		if ( empty( $required_payment_method ) ) {
			return;
		}

		if ( empty( $required_condition ) ) {
			return;
		}

		//Add where Product ID is set for trigger
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];//return early for all products
				if ( (string) current_action() === (string) $required_condition[ $recipe_id ][ $trigger_id ] ) {
					$trigger_cond_ids[] = absint( $recipe_id );
				}
			}
		}

		if ( empty( $trigger_cond_ids ) ) {
			return;
		}

		if ( 'woocommerce_order_status_completed' === (string) current_action() ) {
			if ( 'completed' !== $order->get_status() ) {
				return;
			}
		}

		$payment_method = $order->get_payment_method();

		if ( empty( $payment_method ) ) {
			return;
		}

		//Add where Product ID is set for trigger
		foreach ( $recipes as $recipe_id => $recipe ) {
			$recipe_id = absint( $recipe_id );
			if ( ! in_array( $recipe_id, $trigger_cond_ids, true ) ) {
				continue;
			}
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = absint( $trigger['ID'] );

				if ( ! isset( $required_payment_method[ $recipe_id ] ) ) {
					continue;
				}
				if ( ! isset( $required_payment_method[ $recipe_id ][ $trigger_id ] ) ) {
					continue;
				}
				if (
					intval( '-1' ) === intval( $required_payment_method[ $recipe_id ][ $trigger_id ] ) ||
					(string) $payment_method === (string) $required_payment_method[ $recipe_id ][ $trigger_id ]
				) {
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
				'recipe_to_match'  => $matched_recipe_id['recipe_id'],
				'trigger_to_match' => $matched_recipe_id['trigger_id'],
				'ignore_post_id'   => true,
				'is_signed_in'     => true,
			);

			$args = Automator()->maybe_add_trigger_entry( $pass_args, false );

			//Adding an action to save order id in trigger meta
			do_action( 'uap_wc_trigger_save_meta', $order_id, $matched_recipe_id['recipe_id'], $args, 'order' );

			if ( $args ) {
				foreach ( $args as $result ) {
					if ( true === $result['result'] ) {
						if ( true !== $result['result'] ) {
							continue;
						}
						$trigger_meta = array(
							'user_id'        => $user_id,
							'trigger_id'     => $result['args']['trigger_id'],
							'trigger_log_id' => $result['args']['get_trigger_id'],
							'run_number'     => $result['args']['run_number'],
						);

						$trigger_meta['meta_key']   = 'order_id';
						$trigger_meta['meta_value'] = $order_id;
						Automator()->insert_trigger_meta( $trigger_meta );

						Automator()->maybe_trigger_complete( $result['args'] );
					}
				}
			}
		}
	}
}
