<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WC_VARIATIONSUBCANCEL
 *
 * @package Uncanny_Automator_Pro
 */
class WC_VARIATIONSUBCANCEL {
	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WC';

	/**
	 * @var string
	 */
	private $action_code;
	/**
	 * @var string
	 */
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if ( is_plugin_active( 'woocommerce-subscriptions' . '/' . 'woocommerce-subscriptions.php' ) ) {
			$this->action_code = 'WCVARIATIONSUBCANCELLED';
			$this->action_meta = 'WOOVARIATIONSUBS';
			$this->define_action();
		}
	}

	/**
	 * Define and register the action by pushing it into the Automator object Cancel the user's subscription to {a product}
	 */
	public function define_action() {

		$action = array(
			'author'             => Automator()->get_author_name(),
			'support_link'       => Automator()->get_author_support_link( $this->action_code, 'integration/wishlist-member/' ),
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			'is_pro'             => true,
			/* translators: Action - WooCommerce */
			'sentence'           => sprintf( esc_attr__( "Cancel the user's subscription to {{a specific variation:%1\$s}} of {{a variable subscription variation:%2\$s}}", 'uncanny-automator-pro' ), 'WOOVARIPRODUCT:' . $this->action_meta, $this->action_meta ),
			/* translators: Action - WooCommerce */
			'select_option_name' => esc_attr__( "Cancel the user's subscription to {{a specific variation}} of {{a variable subscription variation}}", 'uncanny-automator-pro' ),
			'priority'           => 99,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'wcs_cancel_user_variation_subscription' ),
			'options_callback'   => array( $this, 'load_options' ),
		);

		Automator()->register->action( $action );
	}

	/**
	 * @return array
	 */
	public function load_options() {
		$options_array = array(
			'options_group' => array(
				$this->action_meta => array(
					Automator()->helpers->recipe->woocommerce->options->pro->all_wc_variation_subscriptions(
						esc_attr__( 'Variable subscription product', 'uncanny-automator-pro' ),
						$this->action_meta,
						array(
							'token'        => false,
							'is_ajax'      => true,
							'target_field' => 'WOOVARIPRODUCT',
							'endpoint'     => 'select_variations_from_WOOSELECTVARIATION',
						)
					),
					Automator()->helpers->recipe->field->select_field_ajax( 'WOOVARIPRODUCT', esc_attr__( 'Variation', 'uncanny-automator-pro' ) ),
				),
			),
		);

		return Automator()->utilities->keep_order_of_options( $options_array );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function wcs_cancel_user_variation_subscription( $user_id, $action_data, $recipe_id, $args ) {

		$subscriptions = wcs_get_users_subscriptions( $user_id );
		$variation_id  = $action_data['meta'][ $this->action_meta ];

		if ( empty( $subscriptions ) ) {
			$recipe_log_id                       = $action_data['recipe_log_id'];
			$args['do-nothing']                  = true;
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$error_message                       = __( 'No subscription is associated with the user', 'uncanny-automator-pro' );
			Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message, $recipe_log_id, $args );

			return;
		}

		$subscription_cancelled = false;
		$error_message          = 'The user was not a subscriber of the specified product.';

		foreach ( $subscriptions as $subscription ) {
			$items = $subscription->get_items();
			foreach ( $items as $index => $item ) {
				if ( ( intval( '-1' ) ) === intval( $variation_id ) || ( absint( $item->get_product_id() ) === absint( $variation_id ) ) ) {
					if ( $subscription->has_status( array( 'active' ) ) && $subscription->can_be_updated_to( 'cancelled' ) ) {
						$subscription->update_status( 'cancelled' );
						$subscription_cancelled = true;
					} else {
						$error_message = 'We are not able to change subscription status.';
					}
				}
			}
		}

		if ( $subscription_cancelled === false ) {
			$recipe_log_id                       = $action_data['recipe_log_id'];
			$args['do-nothing']                  = true;
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$message                             = __( $error_message, 'uncanny-automator-pro' );
			Automator()->complete_action( $user_id, $action_data, $recipe_id, $message, $recipe_log_id, $args );

			return;
		} else {
			Automator()->complete_action( $user_id, $action_data, $recipe_id );

			return;
		}
	}
}
