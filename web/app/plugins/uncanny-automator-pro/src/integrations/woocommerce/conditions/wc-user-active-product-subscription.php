<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WC_USER_ACTIVE_PRODUCT_SUBSCRIPTION
 *
 * @package Uncanny_Automator_Pro
 */
class WC_USER_ACTIVE_PRODUCT_SUBSCRIPTION extends Action_Condition {

	/**
	 * Define_condition
	 *
	 * @return void
	 */
	public function define_condition() {
		$this->integration = 'WC';
		/*translators: Token */
		$this->name = __( 'The user has an active subscription to {{a specific product}}', 'uncanny-automator-pro' );
		$this->code = 'ACTIVE_PRODUCT_SUBSCRIPTION';
		/*translators: A token matches a value */
		$this->dynamic_name  = sprintf( esc_html__( 'The user has an active subscription to {{a specific product:%1$s}}', 'uncanny-automator-pro' ), 'PRODUCT' );
		$this->is_pro        = true;
		$this->requires_user = true;

		$this->active = $this->woo_subscriptions_active();
	}

	/**
	 * @param $class
	 *
	 * @return bool
	 */
	public function woo_subscriptions_active() {

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( ! is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * fields
	 *
	 * @return array
	 */
	public function fields() {

		$products_field_args = array(
			'option_code'           => 'PRODUCT',
			'label'                 => esc_html__( 'Select a product', 'uncanny-automator-pro' ),
			'required'              => true,
			'options'               => $this->wc_products_options(),
			'supports_custom_value' => false,
		);

		return array(
			// Course field
			$this->field->select_field_args( $products_field_args ),
		);
	}

	/**
	 * @return array[]
	 */
	public function wc_products_options() {

		$return                        = array();
		$all_woo_subscription_products = Automator()->helpers->recipe->woocommerce->pro->all_wc_subscriptions();
		$options                       = isset( $all_woo_subscription_products['options'] ) ? $all_woo_subscription_products['options'] : array();
		if ( empty( $options ) ) {
			return $return;
		}
		foreach ( $options as $id => $text ) {
			if ( intval( '-1' ) === $id ) {
				continue;
			}
			$return[] = array(
				'value' => $id,
				'text'  => $text,
			);
		}

		return $return;
	}

	/**
	 * Evaluate_condition
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function evaluate_condition() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if ( ! is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
			$message = __( 'WooCommerce Subscription plugin is not active.', 'uncanny-automator-pro' );
			$this->condition_failed( $message );

			return;
		}

		$product_id = $this->get_parsed_option( 'PRODUCT' );

		$validate = wcs_user_has_subscription( $this->user_id, $product_id, 'active' );

		// Check if the user is enrolled in the course here
		if ( false === $validate ) {

			$message = __( 'User does not have an active subscription to ', 'uncanny-automator-pro' ) . $this->get_option( 'PRODUCT_readable' );
			$this->condition_failed( $message );
		}
	}
}
