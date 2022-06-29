<?php

namespace Uncanny_Automator_Pro;

use WC_Order_Item_Product;

/**
 * Class WC_QUANTITY_PURCHPROD
 * @package Uncanny_Automator
 */
class WC_QUANTITY_PURCHPROD {

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
	 * @var string
	 */
	private $trigger_condition;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code      = 'WCQNTYPURCHPROD';
		$this->trigger_meta      = 'WOOPRODUCT';
		$this->trigger_condition = 'TRIGGERCOND';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/woocommerce/' ),
			'integration'         => self::$integration,
			'is_pro'              => true,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - WooCommerce */
			'sentence'            => sprintf( esc_attr__( 'A user {{completes, pays for, lands on a thank you page for:%1$s}} an order with {{a specific quantity:%2$s}} of {{a product:%3$s}}', 'uncanny-automator-pro' ), $this->trigger_condition, 'WOOQNTY', $this->trigger_meta ),
			/* translators: Logged-in trigger - WooCommerce */
			'select_option_name'  => esc_attr__( 'A user {{completes, pays for, lands on a thank you page for}} an order with {{a specific quantity}} of {{a product}}', 'uncanny-automator-pro' ),
			'action'              => array(
				'woocommerce_order_status_completed',
				'woocommerce_thankyou',
				'woocommerce_payment_complete',
			),
			'priority'            => 99,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'payment_completed' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @return array
	 */
	public function load_options() {
		$options = Automator()->helpers->recipe->woocommerce->options->all_wc_products( esc_attr__( 'Product', 'uncanny-automator' ) );

		$options['options'] = array( '-1' => esc_attr__( 'Any product', 'uncanny-automator' ) ) + $options['options'];

		$trigger_condition = Automator()->helpers->recipe->woocommerce->pro->get_woocommerce_trigger_conditions( $this->trigger_condition );
		$options_array     = array(
			'options' => array(
				$trigger_condition,
				$options,
				array(
					'option_code' => 'WOOQNTY',
					'label'       => __( 'Quantity', 'uncanny-automator' ),
					'input_type'  => 'int',
					'required'    => true,
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
	public function payment_completed( $order_id ) {
		global $uncanny_automator;

		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$user_id = $order->get_user_id();
		if ( 0 === $user_id ) {
			// Its a logged in recipe and
			// user ID is 0. Skip process
			return;
		}

		$recipes              = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_product     = $uncanny_automator->get->meta_from_recipes( $recipes, 'WOOPRODUCT' );
		$required_product_qty = $uncanny_automator->get->meta_from_recipes( $recipes, 'WOOQNTY' );
		$required_condition   = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_condition );
		$matched_recipe_ids   = array();

		if ( ! $required_product_qty ) {
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
					$trigger_cond_ids[] = $recipe_id;
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

		$items          = $order->get_items();
		$order_products = array();

		/** @var WC_Order_Item_Product $item */
		foreach ( $items as $item ) {
			$order_products[ absint( $item->get_product_id() ) ] = absint( $item->get_quantity() );
		}

		//Add where option is set to Any product
		foreach ( $recipes as $recipe_id => $recipe ) {
			if ( ! in_array( $recipe_id, $trigger_cond_ids, false ) ) {
				continue;
			}
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if (
					isset( $required_product[ $recipe_id ] )
					&& isset( $required_product[ $recipe_id ][ $trigger_id ] )
					&& isset( $required_product_qty[ $recipe_id ] )
					&& isset( $required_product_qty[ $recipe_id ][ $trigger_id ] )
				) {

					$product_id  = $required_product[ $recipe_id ][ $trigger_id ];
					$product_qty = absint( $required_product_qty[ $recipe_id ][ $trigger_id ] );

					if ( absint( '-1' ) === absint( $product_id ) ) {
						if ( in_array( $product_qty, $order_products, false ) ) {
							$matched_recipe_ids[] = array(
								'recipe_id'    => $recipe_id,
								'trigger_id'   => $trigger_id,
								'product_qnty' => $product_qty,
							);
						}
					} elseif ( isset( $order_products[ absint( $product_id ) ] ) && $product_qty === $order_products[ absint( $product_id ) ] ) {
						$matched_recipe_ids[] = array(
							'recipe_id'    => $recipe_id,
							'trigger_id'   => $trigger_id,
							'product_qnty' => $product_qty,
						);
					}
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$pass_args = array(
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $user_id,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
				);

				$args = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );

				//Adding an action to save order id in trigger meta
				do_action( 'uap_wc_trigger_save_meta', $order_id, $matched_recipe_id['recipe_id'], $args, 'product' );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {

							$user_id        = (int) $result['args']['user_id'];
							$trigger_log_id = (int) $result['args']['get_trigger_id'];
							$run_number     = (int) $result['args']['run_number'];

							$args = array(
								'user_id'        => $user_id,
								'trigger_id'     => $trigger_id,
								'meta_key'       => 'WOOQNTY',
								'meta_value'     => $matched_recipe_id['product_qnty'],
								'run_number'     => $run_number, //get run number
								'trigger_log_id' => $trigger_log_id,
							);

							$uncanny_automator->insert_trigger_meta( $args );

							$uncanny_automator->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}
	}
}
