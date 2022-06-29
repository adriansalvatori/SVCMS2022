<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WC_SUBSCRIPTION_RENEWED
 * @package Uncanny_Automator_Pro
 */
class WC_SUBSCRIPTIONRENEWED {

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
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if ( is_plugin_active( 'woocommerce-subscriptions' . DIRECTORY_SEPARATOR . 'woocommerce-subscriptions.php' ) ) {
			$this->trigger_code = 'WCSUBSCRIPTIONRENEWED';
			$this->trigger_meta = 'WOOSUBSCRIPTIONS';
			$this->define_trigger();
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
			'sentence'            => sprintf( __( 'A user renews a subscription to {{a product:%1$s}} {{a number of:%2$s}} times', 'uncanny-automator-pro' ), $this->trigger_meta, 'NUMTIMES' ),
			/* translators: Logged-in trigger - WooCommerce */
			'select_option_name'  => __( 'A user renews a subscription to {{a product}}', 'uncanny-automator-pro' ),
			'action'              => 'woocommerce_subscription_renewal_payment_complete',
			'priority'            => 30,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'order_renewed' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * @return array
	 */
	public function load_options() {
		$options       = Automator()->helpers->recipe->woocommerce->options->pro->all_wc_subscriptions();
		$options_array = array(
			'options' => array(
				Automator()->helpers->recipe->options->number_of_times(),
				$options,
			),
		);

		return Automator()->utilities->keep_order_of_options( $options_array );
	}

	/**
	 * Do action after a WooCommerce subscription renewal payment has been completed
	 *
	 * @param $subscription
	 * @param $last_order
	 *
	 * @return void
	 */
	public function order_renewed( $subscription, $last_order ) {

		if ( ! $subscription instanceof \WC_Subscription || ! $last_order instanceof \WC_Order ) {
			return;
		}

		$user_id            = $subscription->get_user_id();
		$recipes            = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_product   = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = array();
		$items              = $subscription->get_items();
		$product_ids        = array();
		foreach ( $items as $item ) {
			$product_ids[] = (int) $item->get_product_id();
		}

		//Add where option is set to Any product
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( ! isset( $required_product[ $recipe_id ] ) ) {
					continue;
				}
				if ( ! isset( $required_product[ $recipe_id ][ $trigger_id ] ) ) {
					continue;
				}
				if (
					intval( '-1' ) === intval( $required_product[ $recipe_id ][ $trigger_id ] ) ||
					in_array( absint( $required_product[ $recipe_id ][ $trigger_id ] ), $product_ids, true )
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
				$trigger_meta['meta_value'] = $last_order->get_id();
				Automator()->insert_trigger_meta( $trigger_meta );

				Automator()->maybe_trigger_complete( $result['args'] );
			}
		}
	}
}
