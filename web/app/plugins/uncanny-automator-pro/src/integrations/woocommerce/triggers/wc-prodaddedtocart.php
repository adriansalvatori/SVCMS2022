<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WC_PRODADDEDTOCART
 *
 * @package Uncanny_Automator_Pro
 */
class WC_PRODADDEDTOCART {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WC';

	/**
	 * Trigger code.
	 *
	 * @var string
	 */
	private $trigger_code;

	/**
	 * Trigger meta.
	 *
	 * @var string
	 */
	private $trigger_meta;

	/**
	 * SetAutomatorTriggers constructor
	 */
	public function __construct() {

		// Overwrite the relevant tokens.
		add_filter( 'uap_option_all_wc_products', array( $this, 'set_relevant_tokens' ), 10 );

		$this->trigger_code = 'WCPRODUCTTOCART';

		$this->trigger_meta = 'ADDEDTOCART';

		$this->define_trigger();

	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Anonymous trigger - WooCommerce */
			'sentence'            => sprintf( __( 'A user adds {{a product:%1$s}} to their cart', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Anonymous trigger - WooCommerce */
			'select_option_name'  => __( 'A user adds {{a product}} to their cart', 'uncanny-automator-pro' ),
			'action'              => array( 'woocommerce_add_to_cart' ),
			'priority'            => 999,
			'accepted_args'       => 6,
			'validation_function' => array( $this, 'product_added_to_cart' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	public function set_relevant_tokens( $options ) {

		if ( 'ADDEDTOCART' === $options['option_code'] ) {

			$options['relevant_tokens'] = array(
				$this->trigger_meta                => esc_attr__( 'Product title', 'uncanny-automator' ),
				$this->trigger_meta . '_ID'        => esc_attr__( 'Product ID', 'uncanny-automator' ),
				$this->trigger_meta . '_URL'       => esc_attr__( 'Product URL', 'uncanny-automator' ),
				$this->trigger_meta . '_THUMB_ID'  => esc_attr__( 'Product featured image ID', 'uncanny-automator' ),
				$this->trigger_meta . '_THUMB_URL' => esc_attr__( 'Product featured image URL', 'uncanny-automator' ),
			);

		}

		return $options;

	}

	/**
	 * Load options.
	 *
	 * @return array
	 */
	public function load_options() {

		$options = Automator()->helpers->recipe->woocommerce->options->all_wc_products( __( 'Product', 'uncanny-automator' ), $this->trigger_meta );

		$options['options'] = array( '-1' => __( 'Any product', 'uncanny-automator-pro' ) ) + $options['options'];
		$options_array      = array( 'options' => array( $options ) );

		return Automator()->utilities->keep_order_of_options( $options_array );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $cart_item_key
	 * @param $product_id
	 * @param $quantity
	 * @param $variation_id
	 * @param $variation
	 * @param $cart_item_data
	 */
	public function product_added_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {

		$product = wc_get_product( $product_id );

		global $uncanny_automator;
		$user_id            = get_current_user_id();
		$recipes            = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_product   = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = array();

		// Bailout if there is no user id since this trigger is a user type.
		if ( empty( absint( $user_id ) ) ) {
			return;
		}

		//Add where option is set to Any product / specific product
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( intval( '-1' ) === intval( $required_product[ $recipe_id ][ $trigger_id ] ) ||
					absint( $required_product[ $recipe_id ][ $trigger_id ] ) === absint( $product_id ) ) {
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

		//	If recipe matches
		foreach ( $matched_recipe_ids as $matched_recipe_id ) {
			$pass_args = array(
				'code'             => $this->trigger_code,
				'meta'             => $this->trigger_meta,
				'user_id'          => $user_id,
				'recipe_to_match'  => $matched_recipe_id['recipe_id'],
				'trigger_to_match' => $matched_recipe_id['trigger_id'],
				'post_id'          => $product_id,
			);

			$args = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );

			if ( $args ) {
				foreach ( $args as $result ) {
					if ( true === $result['result'] ) {
						$trigger_meta = array(
							'user_id'        => $user_id,
							'trigger_id'     => $result['args']['trigger_id'],
							'trigger_log_id' => $result['args']['get_trigger_id'],
							'run_number'     => $result['args']['run_number'],
						);

						$trigger_meta['meta_key']   = 'PRODUCT_PRICE';
						$trigger_meta['meta_value'] = $product->get_price();
						$uncanny_automator->insert_trigger_meta( $trigger_meta );

						// Add the product cart qty.
						$quantities = WC()->cart->get_cart_item_quantities();

						if ( ! empty( $quantities[ $product_id ] ) ) {
							$quantity = $quantities[ $product_id ];
						}

						$trigger_meta['meta_key']   = 'PRODUCT_QUANTITY';
						$trigger_meta['meta_value'] = $quantity;
						$uncanny_automator->insert_trigger_meta( $trigger_meta );

						$variations = '';
						if ( is_array( $variation ) && ! empty( $variation ) ) {
							foreach ( $variation as $k => $vari ) {
								$variations .= str_replace( 'attribute_', '', $k ) . ' : ' . $vari . '<br/>';
							}
						}

						$trigger_meta['meta_key']   = 'PRODUCT_VARIATION';
						$trigger_meta['meta_value'] = $variations;
						$uncanny_automator->insert_trigger_meta( $trigger_meta );

						$uncanny_automator->maybe_trigger_complete( $result['args'] );
					}
				}
			}
		}

	}

}
