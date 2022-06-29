<?php

namespace Uncanny_Automator_Pro;

/**
 * Class EDD_PRODPURCHWITHDISCOUNTCODE
 *
 * @package Uncanny_Automator_Pro
 */
class EDD_PRODPURCHWITHDISCOUNTCODE {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'EDD';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'EDDPRODPURCHDISCOUNT';
		$this->trigger_meta = 'EDDDISCOUNTCODE';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/easy-digital-downloads/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'is_pro'              => true,
			/* translators: Logged-in trigger - Easy Digital Downloads */
			'sentence'            => sprintf( esc_attr__( '{{A product:%1$s}} is purchased with {{a discount code:%2$s}}', 'uncanny-automator' ), 'EDDPRODUCTS', $this->trigger_meta ),
			/* translators: Logged-in trigger - Easy Digital Downloads */
			'select_option_name'  => esc_attr__( '{{A product}} is purchased with {{a discount code}}', 'uncanny-automator' ),
			'action'              => 'edd_complete_purchase',
			'priority'            => 10,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'edd_product_purchase' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		Automator()->register->trigger( $trigger );

	}

	public function load_options() {

		$options = array(
			'options' => array(
				Automator()->helpers->recipe->edd->options->all_edd_downloads( esc_attr__( 'Product', 'uncanny-automator' ), 'EDDPRODUCTS' ),
				Automator()->helpers->recipe->edd->options->pro->all_edd_discount_codes( esc_attr__( 'Discount code', 'uncanny-automator' ), $this->trigger_meta ),
			),
		);

		$options = Automator()->utilities->keep_order_of_options( $options );

		return $options;

	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $payment_id
	 * @param $payment
	 * @param $customer
	 */
	public function edd_product_purchase( $payment_id, $payment, $customer ) {

		$cart_items = edd_get_payment_meta_cart_details( $payment_id );

		if ( empty( $cart_items ) ) {
			return;
		}

		if ( 'none' === $payment->user_info['discount'] ) {
			return;
		}

		$recipes                = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_discount_code = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$required_product       = Automator()->get->meta_from_recipes( $recipes, 'EDDPRODUCTS' );
		$code_id                = edd_get_discount_id_by_code( $payment->user_info['discount'] );
		$user_id                = $customer->user_id;
		$matched_recipe_ids     = array();

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( ( in_array( absint( $required_product[ $recipe_id ][ $trigger_id ] ), array_column( $cart_items, 'id' ), false )
					   || intval( '-1' ) === intval( $required_product[ $recipe_id ][ $trigger_id ] ) )
					 && ( absint( $required_discount_code[ $recipe_id ][ $trigger_id ] ) === absint( $code_id )
						  || intval( '-1' ) === intval( $required_discount_code[ $recipe_id ][ $trigger_id ] ) ) ) {
					$matched_recipe_ids[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					);
					break;
				}
			}
		}

		// Get the sum of all the discount per item.
		$total_discount = 0;
		foreach ( $cart_items as $item ) {
			$total_discount += $item['discount'];
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

					$args = Automator()->maybe_add_trigger_entry( $pass_args, false );

					if ( $args ) {

						foreach ( $args as $result ) {

							if ( true === $result['result'] ) {

								$trigger_meta = array(
									'user_id'        => $user_id,
									'trigger_id'     => $result['args']['trigger_id'],
									'trigger_log_id' => $result['args']['get_trigger_id'],
									'run_number'     => $result['args']['run_number'],
								);

								$product_index = 0;

								if ( $required_product[ $result['args']['recipe_id'] ][ $result['args']['trigger_id'] ] !== intval( '-1' ) ) {
									$product_index = array_search( absint( $required_product[ $result['args']['recipe_id'] ][ $result['args']['trigger_id'] ] ), array_column( $cart_items, 'id' ), false );
								}

								// Old tokens.
								$trigger_meta['meta_key']   = $this->trigger_meta;
								$trigger_meta['meta_value'] = maybe_serialize( $payment->user_info['discount'] );
								Automator()->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = 'EDDPRODUCTS';
								$trigger_meta['meta_value'] = maybe_serialize( $cart_items[ $product_index ]['name'] );
								Automator()->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = 'EDDPRODUCTS_ID';
								$trigger_meta['meta_value'] = maybe_serialize( $cart_items[ $product_index ]['id'] );
								Automator()->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = 'EDDPRODUCTS_URL';
								$trigger_meta['meta_value'] = maybe_serialize( get_permalink( $cart_items[ $product_index ]['id'] ) );
								Automator()->insert_trigger_meta( $trigger_meta );

								$license_key = '';

								// Check if get_licenses already exists in Automator free.
								if ( method_exists( Automator()->helpers->recipe->edd->options, 'get_licenses' ) ) {
									$license_key = Automator()->helpers->recipe->edd->options->get_licenses( $payment_id );
								}

								$payment_info = array(
									'discount_codes'  => $payment->discounts,
									'order_discounts' => number_format( (float) $total_discount, 2, '.', '' ),
									'order_subtotal'  => $payment->subtotal,
									'order_total'     => $payment->total,
									'order_tax'       => $payment->tax,
									'payment_method'  => $payment->gateway,
									'license_key'     => $license_key,
								);

								Automator()->db->token->save( 'EDD_DOWNLOAD_ORDER_PAYMENT_INFO', wp_json_encode( $payment_info ), $result['args'] );

								Automator()->maybe_trigger_complete( $result['args'] );
							}
						}
					}
			}
		}

	}
}
