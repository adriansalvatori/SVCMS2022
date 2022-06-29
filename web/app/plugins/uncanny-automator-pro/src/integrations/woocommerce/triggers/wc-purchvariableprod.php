<?php

namespace Uncanny_Automator_Pro;

use WC_Order_Item_Product;

/**
 * Class WC_PURCHVARIABLEPRROD
 * @package Uncanny_Automator_Pro
 */
class WC_PURCHVARIABLEPROD {
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
		$this->trigger_code = 'WCPURCHVARIPROD';
		$this->trigger_meta = 'WOOVARIPRODUCT';
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
			/* translators: Logged-in trigger - WooCommerce */
			'sentence'            => sprintf(
				__( 'A user purchases {{A variable product:%1$s}} with {{a variation:%2$s}} selected', 'uncanny-automator-pro' ),
				'WOOVARIABLEPRODUCTS' . ':' . $this->trigger_meta,
				$this->trigger_meta
			),
			/* translators: Logged-in trigger - WooCommerce */
			'select_option_name'  => __( 'A user purchases {{a variable product}} with {{a variation}} selected', 'uncanny-automator-pro' ),
			'action'              => array(
				'woocommerce_order_status_completed',
				'woocommerce_thankyou',
				'woocommerce_payment_complete',
			),
			'priority'            => 9,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'wc_payment_completed' ),
			'options_callback'    => array( $this, 'load_options' ),
		);
		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @return array
	 */
	public function load_options() {
		$options_array = array(
			'options_group' => array(
				$this->trigger_meta => array(
					Automator()->helpers->recipe->woocommerce->options->pro->all_wc_variable_products(
						__( 'Product', 'uncanny-automator-pro' ),
						'WOOVARIABLEPRODUCTS',
						array(
							'token'        => false,
							'is_ajax'      => true,
							'target_field' => $this->trigger_meta,
							'endpoint'     => 'select_variations_from_WOOSELECTVARIATION',
						)
					),
					Automator()->helpers->recipe->field->select_field(
						$this->trigger_meta,
						__( 'Variation', 'uncanny-automator-pro' )
					),
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
		global $uncanny_automator;

		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		if ( 'completed' !== $order->get_status() ) {
			return;
		}

		$user_id            = $order->get_customer_id();
		$recipes            = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_product   = $uncanny_automator->get->meta_from_recipes( $recipes, 'WOOVARIABLEPRODUCTS' );
		$required_variation = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );

		$matched_recipe_ids = array();

		//Add where option is set to Any product
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];//return early for all products
				if ( - 1 === intval( $required_product[ $recipe_id ][ $trigger_id ] ) ) {
					$matched_recipe_ids[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					);

					break;
				}
			}
		}
		$items              = $order->get_items();
		$product_variations = array();
		$product_ids        = array();
		/** @var WC_Order_Item_Product $item */
		foreach ( $items as $item ) {
			$product_ids[]        = $item->get_product_id();
			$product_variations[] = $item->get_variation_id();
		}

		//Add where Product ID is set for trigger
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];//return early for all products
				if ( in_array( $required_product[ $recipe_id ][ $trigger_id ], $product_ids, false ) && in_array( $required_variation[ $recipe_id ][ $trigger_id ], $product_variations, false ) ) {
					$matched_recipe_ids[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					);
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
							$uncanny_automator->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}

		return;
	}
}
