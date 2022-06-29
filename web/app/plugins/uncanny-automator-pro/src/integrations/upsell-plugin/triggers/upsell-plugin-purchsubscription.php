<?php

namespace Uncanny_Automator_Pro;

/**
 * Class UPSELL_PLUGIN_PURCHSUBSCRIPTION
 * @package Uncanny_Automator_Pro
 */
class UPSELL_PLUGIN_PURCHSUBSCRIPTION {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'UPSELLPLUGIN';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'USPURCHSUBSCRIPTION';
		$this->trigger_meta = 'USSUBSCRIPTION';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/upsell-plugin/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - Upsell */
			'sentence'            => sprintf( __( 'A user subscribes to {{a product:%1$s}}', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - Upsell */
			'select_option_name'  => __( 'A user subscribes to {{a product}}', 'uncanny-automator-pro' ),
			'action'              => 'upsell_order_status_completed',
			'priority'            => 99,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'upsell_product_subscribed' ),
			'options'             => [
				$uncanny_automator->helpers->recipe->upsell_plugin->pro->all_upsell_subscriptions( __( 'Subscription', 'uncanny-automator-pro' ) ),
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;

	}

	public function upsell_product_subscribed( $order ) {
		global $uncanny_automator;

		if ( ! $order ) {
			return;
		}

		if ( 'completed' !== $order->status() ) {
			return;
		}

		$customer = get_user_by_email( $order->customer_email );
		$user_id  = ( ! empty( $customer ) ) ? $customer->ID : 0;
		if ( 0 === $user_id ) {
			// Its a logged in recipe and
			// user ID is 0. Skip process
			return;
		}

		$recipes            = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_product   = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = [];

		$items       = $order->items();
		$product_ids = array();


		foreach ( $items as $index => $item ) {
			$product_ids[] = $item['id'];
		}

		//Add where option is set to Any product subscription
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];//return early for all products
				if ( isset( $required_product[ $recipe_id ] ) && isset( $required_product[ $recipe_id ][ $trigger_id ] ) ) {
					if ( - 1 === intval( $required_product[ $recipe_id ][ $trigger_id ] ) ) {
						foreach ( $product_ids as $product_id ) {
							$product_type = get_post_meta( $product_id, '_payment', true );
							if ( $product_type == 'subscription' ) {
								$matched_recipe_ids[] = [
									'recipe_id'  => $recipe_id,
									'trigger_id' => $trigger_id,
								];
							}
						}
						break;
					}
				}
			}
		}

		//Add where Product ID is set for trigger
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];//return early for all products
				if ( isset( $required_product[ $recipe_id ] ) && isset( $required_product[ $recipe_id ][ $trigger_id ] ) ) {
					if ( in_array( $required_product[ $recipe_id ][ $trigger_id ], $product_ids ) ) {
						$matched_recipe_ids[] = [
							'recipe_id'  => $recipe_id,
							'trigger_id' => $trigger_id,
						];
					}
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$pass_args = [
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $user_id,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
					'is_signed_in'     => true,
				];

				$args = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );

				//Adding an action to save order id in trigger meta
				do_action( 'uap_wc_trigger_save_meta', $order->id, $matched_recipe_id['recipe_id'], $args, 'subscription' );

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