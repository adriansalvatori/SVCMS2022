<?php

namespace Uncanny_Automator_Pro;

/**
 *
 */
class WC_ORDERPRODUCTQTY {

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
	 * @var string
	 */
	private $trigger_product;

	/**
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		$this->trigger_code      = 'WCORDERQTYCONDITION';
		$this->trigger_meta      = 'WOOORDERQTYTOTAL';
		$this->trigger_condition = 'TRIGGERCOND';
		$this->trigger_product   = 'WOOPRODUCT';
		$this->define_trigger();
	}

	/**
	 *
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/woocommerce/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - WooCommerce. 2. Currency symbol */
			'sentence'            => sprintf( __( 'A user {{completes, pays for, lands on a thank you page for:%4$s}} an order with a quantity {{greater than, less than or equal to:%1$s}} {{a quantity:%3$s}} of {{a product:%2$s}}', 'uncanny-automator-pro' ), 'NUMBERCOND', $this->trigger_product, $this->trigger_meta, $this->trigger_condition ),
			/* translators: Logged-in trigger - WooCommerce */
			'select_option_name'  => __( 'A user {{completes, pays for, lands on a thank you page for}} an order with a quantity {{greater than, less than or equal to}} {{a quantity}} of {{a product}}', 'uncanny-automator-pro' ),
			'action'              => array(
				'woocommerce_order_status_completed',
				'woocommerce_thankyou',
				'woocommerce_payment_complete',
			),
			'priority'            => 99,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'order_completed' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @return array
	 */
	public function load_options() {
		global $uncanny_automator;
		$trigger_condition = $uncanny_automator->helpers->recipe->woocommerce->pro->get_woocommerce_trigger_conditions( $this->trigger_condition );
		$options           = $uncanny_automator->helpers->recipe->woocommerce->options->all_wc_products( __( 'Product', 'uncanny-automator' ) );

		$options['options'] = array( '-1' => __( 'Any product', 'uncanny-automator' ) ) + $options['options'];
		$options_array      = array(
			'options' => array(
				$uncanny_automator->helpers->recipe->field->int(
					array(
						'option_code' => $this->trigger_meta,
						'label'       => __( 'Quantity', 'uncanny-automator' ),
						'placeholder' => 1,
						'default'     => 1,
					)
				),
				$uncanny_automator->helpers->recipe->less_or_greater_than(),
				$options,
				$trigger_condition,
			),
		);

		return Automator()->utilities->keep_order_of_options( $options_array );
	}

	/**
	 * @param $order_id
	 */
	public function order_completed( $order_id ) {

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

		global $uncanny_automator;

		$order               = wc_get_order( $order_id );
		$user_id             = $order->get_user_id();
		$recipes             = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_qty        = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$required_conditions = $uncanny_automator->get->meta_from_recipes( $recipes, 'NUMBERCOND' );
		$required_product    = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_product );
		$trigger_condition   = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_condition );
		$quantities          = $this->products_vs_qty( $order );
		$matched_recipe_ids  = array();
		$trigger_cond_ids    = array();
		$product_ids         = array();
		$matched_product_id  = array();

		if ( empty( $recipes ) ) {
			return;
		}

		if ( empty( $required_qty ) ) {
			return;
		}

		if ( empty( $required_conditions ) ) {
			return;
		}

		if ( empty( $trigger_condition ) ) {
			return;
		}

		if ( empty( $required_product ) ) {
			return;
		}

		$items = $order->get_items();
		/** @var \WC_Order_Item_Product $item */
		foreach ( $items as $item ) {
			$product_ids[] = $item->get_product_id();
		}

		//Add where Product ID is set for trigger
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];//return early for all products
				if ( (string) current_action() === (string) $trigger_condition[ $recipe_id ][ $trigger_id ] ) {
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

		/**
		 * Match Product IDs first!
		 */
		foreach ( $recipes as $recipe_id => $recipe ) {
			if ( ! in_array( (int) $recipe_id, array_map( 'absint', $trigger_cond_ids ), true ) ) {
				continue;
			}
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				// Check if the product matches the order
				if ( intval( '-1' ) === intval( $required_product[ $recipe_id ][ $trigger_id ] ) || in_array( (int) $required_product[ $recipe_id ][ $trigger_id ], array_map( 'absint', $product_ids ), true ) ) {
					$product_id = $required_product[ $recipe_id ][ $trigger_id ];
					// product matched + match qty for product. Logic for "Any" product
					if ( intval( '-1' ) === intval( $product_id ) ) {
						foreach ( $product_ids as $p_id ) {
							$condition_matched = $uncanny_automator->utilities->match_condition_vs_number( $required_conditions[ $recipe_id ][ $trigger_id ], $required_qty[ $recipe_id ][ $trigger_id ], $quantities[ $p_id ] );
							if ( ! $condition_matched ) {
								continue;
							}
							$matched_recipe_ids[ $recipe_id ] = array(
								'recipe_id'  => $recipe_id,
								'trigger_id' => $trigger_id,
							);
						}
					} else {
						// Logic for a specific product
						$condition_matched = $uncanny_automator->utilities->match_condition_vs_number( $required_conditions[ $recipe_id ][ $trigger_id ], $required_qty[ $recipe_id ][ $trigger_id ], $quantities[ $product_id ] );
						if ( $condition_matched ) {
							$matched_recipe_ids[ $recipe_id ] = array(
								'recipe_id'  => $recipe_id,
								'trigger_id' => $trigger_id,
							);
						}
					}
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
			);

			$args = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );

			//Adding an action to save order id in trigger meta
			do_action( 'uap_wc_trigger_save_meta', $order_id, $matched_recipe_id['recipe_id'], $args, 'order' );

			if ( empty( $args ) ) {
				return;
			}
			foreach ( $args as $result ) {
				if ( true === $result['result'] ) {
					$uncanny_automator->maybe_trigger_complete( $result['args'] );
				}
			}
		}
	}

	/**
	 * @param $order
	 *
	 * @return array
	 */
	public function products_vs_qty( $order ) {
		$quantities = array();
		/** @var \WC_Order_Item_Product $item */
		$items = $order->get_items();
		foreach ( $items as $item ) {
			$quantities[ $item->get_product_id() ] = $item->get_quantity();
		}

		return $quantities;
	}

}
