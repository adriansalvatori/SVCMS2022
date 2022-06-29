<?php

namespace Uncanny_Automator_Pro;

use WC_Order_Item_Product;

/**
 * Class WC_PURCHPROD_CATEGORY
 * @package Uncanny_Automator_Pro
 */
class WC_PURCHPROD_CATEGORY {

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
		$this->trigger_code      = 'WCPURCHPRODUCTINCAT';
		$this->trigger_meta      = 'WOOPRODCAT';
		$this->trigger_condition = 'TRIGGERCOND';
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
			'sentence'            => sprintf( __( 'A user {{completes, pays for, lands on a thank you page for:%1$s}} an order with a product in {{a category:%2$s}}', 'uncanny-automator-pro' ), $this->trigger_condition, $this->trigger_meta ),
			/* translators: Logged-in trigger - WooCommerce */
			'select_option_name'  => __( 'A user {{completes, pays for, lands on a thank you page for}} an order with a product in {{a category}}', 'uncanny-automator-pro' ),
			'action'              => array(
				'woocommerce_order_status_completed',
				'woocommerce_thankyou',
				'woocommerce_payment_complete',
			),
			'priority'            => 999,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'wc_payment_completed' ),
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
				Automator()->helpers->recipe->woocommerce->options->pro->all_wc_product_categories(
					__( 'Product category', 'uncanny-automator-pro' ),
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
	public function wc_payment_completed( $order_id ) {

		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$user_id                   = $order->get_customer_id();
		$recipes                   = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_product_category = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$required_condition        = Automator()->get->meta_from_recipes( $recipes, $this->trigger_condition );
		$matched_recipe_ids        = array();
		$trigger_cond_ids          = array();
		$product_ids               = array();

		if ( empty( $recipes ) ) {
			return;
		}

		if ( empty( $required_product_category ) ) {
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

		$items = $order->get_items();
		/** @var WC_Order_Item_Product $item */
		foreach ( $items as $item ) {
			$product_ids[] = $item->get_product_id();
		}

		if ( empty( $product_ids ) ) {
			return;
		}

		// Get Product Categories
		$category_ids = array();
		foreach ( $product_ids as $product_id ) {
			$categories = get_the_terms( $product_id, 'product_cat' );
			if ( ! $categories ) {
				continue;
			}
			foreach ( $categories as $category ) {
				$category_ids[] = absint( $category->term_id );
			}
		}

		if ( empty( $category_ids ) ) {
			return;
		}

		//Add where option is set to Any product
		foreach ( $recipes as $recipe_id => $recipe ) {
			$recipe_id = absint( $recipe_id );
			if ( ! in_array( $recipe_id, $trigger_cond_ids, true ) ) {
				continue;
			}
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = absint( $trigger['ID'] );

				if ( ! isset( $required_product_category[ $recipe_id ] ) ) {
					continue;
				}
				if ( ! isset( $required_product_category[ $recipe_id ][ $trigger_id ] ) ) {
					continue;
				}

				if (
					intval( '-1' ) === intval( $required_product_category[ $recipe_id ][ $trigger_id ] ) ||
					in_array( absint( $required_product_category[ $recipe_id ][ $trigger_id ] ), $category_ids, true )
				) {
					$matched_recipe_ids[ $recipe_id ] = array(
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
			do_action( 'uap_wc_trigger_save_meta', $order_id, $matched_recipe_id['recipe_id'], $args, 'product' );

			if ( $args ) {
				foreach ( $args as $result ) {
					if ( true === $result['result'] ) {
						Automator()->maybe_trigger_complete( $result['args'] );
					}
				}
			}
		}
	}
}
