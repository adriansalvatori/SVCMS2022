<?php

namespace Uncanny_Automator_Pro;

use WC_Order_Item_Product;

/**
 * Class ANON_WC_PURCHASEPROD
 * @package Uncanny_Automator_Pro
 */
class ANON_WC_PURCHPROD {

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
		$this->trigger_code      = 'ANONWCPURCHASEPROD';
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
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Anonymous trigger - WooCommerce */
			'sentence'            => sprintf( __( 'A guest {{completes, pays for, lands on a thank you page for:%1$s}} an order with {{a product:%2$s}}', 'uncanny-automator-pro' ), $this->trigger_condition, $this->trigger_meta ),
			/* translators: Anonymous trigger - WooCommerce */
			'select_option_name'  => __( 'A guest {{completes, pays for, lands on a thank you page for}} an order with {{a product}}', 'uncanny-automator-pro' ),
			'action'              => array(
				'woocommerce_order_status_completed',
				'woocommerce_thankyou',
				'woocommerce_payment_complete',
			),
			'priority'            => 999,
			'accepted_args'       => 1,
			'type'                => 'anonymous',
			'validation_function' => array( $this, 'payment_completed' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @return array
	 */
	public function load_options() {
		$options = Automator()->helpers->recipe->woocommerce->options->all_wc_products( __( 'Product', 'uncanny-automator' ) );

		$options['options'] = array( '-1' => __( 'Any product', 'uncanny-automator' ) ) + $options['options'];

		$trigger_condition = Automator()->helpers->recipe->woocommerce->pro->get_woocommerce_trigger_conditions( $this->trigger_condition );
		$options_array     = array(
			'options' => array(
				$trigger_condition,
				$options,
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

		$user_id            = $order->get_customer_id();
		$recipes            = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_condition = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_condition );
		$required_product   = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$trigger_cond_ids   = array();
		$matched_recipe_ids = array();
		$product_ids        = array();

		if ( empty( $required_condition ) ) {
			return;
		}

		if ( empty( $required_product ) ) {
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

		$items = $order->get_items();
		/** @var WC_Order_Item_Product $item */
		foreach ( $items as $item ) {
			$product_ids[] = $item->get_product_id();
		}

		//Add where option is set to Any product
		foreach ( $recipes as $recipe_id => $recipe ) {
			if ( ! in_array( (int) $recipe_id, array_map( 'absint', $trigger_cond_ids ), true ) ) {
				continue;
			}
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];//return early for all products
				if ( (int) '-1' === (int) $required_product[ $recipe_id ][ $trigger_id ] || in_array( (int) $required_product[ $recipe_id ][ $trigger_id ], array_map( 'absint', $product_ids ), true ) ) {
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
			);

			$args = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );
			//Adding an action to save order id in trigger meta
			do_action( 'uap_wc_trigger_save_meta', $order_id, $matched_recipe_id['recipe_id'], $args, 'product' );

			if ( $args ) {
				foreach ( $args as $result ) {
					if ( true === $result['result'] ) {
						$uncanny_automator->maybe_trigger_complete( $result['args'] );
					}
				}
			}
		}
	}
}
