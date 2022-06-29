<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WC_VARIATION_SUBSCRIPTIONEXPIRES
 *
 * @package Uncanny_Automator_Pro
 */
class WC_VARIATION_SUBSCRIPTIONEXPIRES {

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
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
			$this->trigger_code = 'WCVARIATIONSUBSCRIPTIONEXPIRED';
			$this->trigger_meta = 'WOOSUBSCRIPTIONS';
			if ( Automator()->helpers->recipe->is_edit_page() ) {
				add_action( 'wp_loaded', array( $this, 'define_trigger' ) );
			} else {
				$this->define_trigger();
			}
		}
	}

	/**
	 *
	 */
	public function define_trigger() {

		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/woocommerce/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - WooCommerce */
			'sentence'            => sprintf( esc_attr__( "A user's subscription to {{a specific:%1\$s}} variation of {{a variable subscription:%2\$s}} expires", 'uncanny-automator-pro' ), 'WOOVARIPRODUCT', $this->trigger_meta . ':WOOVARIPRODUCT' ),
			/* translators: Logged-in trigger - WooCommerce */
			'select_option_name'  => esc_attr__( "A user's subscription to {{a specific}} variation of {{a variable subscription}} expires", 'uncanny-automator-pro' ),
			'action'              => 'woocommerce_subscription_status_expired',
			'priority'            => 30,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'order_expired' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * @return array
	 */
	public function load_options() {
		$options_array = array(
			'options_group' => array(
				'WOOVARIPRODUCT' => array(
					Automator()->helpers->recipe->woocommerce->options->pro->all_wc_variation_subscriptions(
						esc_attr__( 'Product', 'uncanny-automator-pro' ),
						$this->trigger_meta,
						array(
							'token'        => false,
							'is_ajax'      => true,
							'target_field' => 'WOOVARIPRODUCT',
							'endpoint'     => 'select_variations_from_WOOSELECTVARIATION_with_any_option',
						)
					),
					Automator()->helpers->recipe->field->select_field_ajax( 'WOOVARIPRODUCT', esc_attr__( 'Variation', 'uncanny-automator-po' ) ),
				),
			),
		);

		return Automator()->utilities->keep_order_of_options( $options_array );
	}

	/**
	 * Wrapper for wcs_maybe_make_user_inactive() that accepts a subscription instead of a user ID.
	 * Handy for hooks that pass a subscription object.
	 *
	 * @param \WC_Subscription|\WC_Order
	 *
	 * @since 2.2.9
	 */
	public function order_expired( $subscription ) {
		if ( ! $subscription instanceof \WC_Subscription ) {
			return;
		}
		$user_id                    = $subscription->get_user_id();
		$recipes                    = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_product           = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$required_product_variation = Automator()->get->meta_from_recipes( $recipes, 'WOOVARIPRODUCT' );
		$items                      = $subscription->get_items();
		if ( empty( $recipes ) || empty( $required_product ) || empty( $required_product_variation ) || empty( $items ) ) {
			return;
		}

		$matched_recipe_ids = array();
		$product_ids        = array();
		$product_parent_ids = array();

		foreach ( $items as $item ) {
			/** @var \WC_Product $product */
			$product = $item->get_product();
			if ( class_exists( '\WC_Subscriptions_Product' ) && \WC_Subscriptions_Product::is_subscription( $product ) ) {
				if ( $product->is_type( array( 'subscription_variation', 'variable-subscription' ) ) ) {
					$product_ids[]        = (int) $product->get_id();
					$product_parent_ids[] = (int) $product->get_parent_id();
				}
			}
		}

		if ( empty( $product_parent_ids ) ) {
			return;
		}

		//Add where Product ID is set for trigger
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if (
					(
						intval( '-1' ) === intval( $required_product[ $recipe_id ][ $trigger_id ] ) ||
						in_array( $required_product[ $recipe_id ][ $trigger_id ], $product_parent_ids, true )
					)
					&&
					(
						intval( '-1' ) === intval( $required_product_variation[ $recipe_id ][ $trigger_id ] ) ||
						in_array( $required_product_variation[ $recipe_id ][ $trigger_id ], $product_ids, true )
					)
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
			$args      = Automator()->maybe_add_trigger_entry( $pass_args, false );
			if ( empty( $args ) ) {
				continue;
			}
			foreach ( $args as $result ) {
				if ( true !== $result['result'] ) {
					continue;
				}
				$trigger_meta = array(
					'user_id'        => $user_id,
					'trigger_id'     => $result['args']['trigger_id'],
					'trigger_log_id' => $result['args']['get_trigger_id'],
					'run_number'     => $result['args']['run_number'],
				);

				$trigger_meta['meta_key']   = 'subscription_id';
				$trigger_meta['meta_value'] = $subscription->get_id();
				Automator()->insert_trigger_meta( $trigger_meta );

				$trigger_meta['meta_key']   = 'order_id';
				$trigger_meta['meta_value'] = $subscription->get_last_order();
				Automator()->insert_trigger_meta( $trigger_meta );

				Automator()->maybe_trigger_complete( $result['args'] );
			}
		}
	}
}
