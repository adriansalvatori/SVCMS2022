<?php

namespace Uncanny_Automator_Pro;

/**
 * Class EDD_PRO_TOKENS
 *
 * @package Uncanny_Automator_Pro
 */
class EDD_PRO_TOKENS {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'EDD';

	/**
	 * EDD_PRO_TOKENS constructor.
	 */
	public function __construct() {
		add_filter( 'automator_maybe_parse_token', array( $this, 'parse_edd_pro_trigger_tokens' ), 20, 6 );
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 * @param $replace_args
	 *
	 * @return mixed
	 */
	public function parse_edd_pro_trigger_tokens( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {

		if ( $pieces ) {

			// Parse new tokens.
			if ( isset( $pieces[2] ) && in_array( $pieces[2], $this->get_edd_order_tokens(), true ) ) {

				$order_info = json_decode( Automator()->db->token->get( 'EDD_DOWNLOAD_ORDER_PAYMENT_INFO', $replace_args ) );

				return $this->meta_to_value( $order_info, $pieces[2] );
			}

			// Old tokens.
			if ( in_array( 'EDDDISCOUNTCODE', $pieces, true ) || in_array( 'EDDPRODPURCHDISCOUNT', $pieces, true ) ) {

				global $wpdb;

				$trigger_id     = $pieces[0];
				$trigger_meta   = $pieces[2];
				$trigger_log_id = isset( $replace_args['trigger_log_id'] ) ? absint( $replace_args['trigger_log_id'] ) : 0;

				$entry = $wpdb->get_var(
					"SELECT meta_value
					FROM {$wpdb->prefix}uap_trigger_log_meta
					WHERE meta_key = '{$trigger_meta}'
					AND automator_trigger_log_id = {$trigger_log_id}
					AND automator_trigger_id = {$trigger_id}
					LIMIT 0,1"
				);

				$value = maybe_unserialize( $entry );
			}
		}

		return $value;

	}

	/**
	 * Get the order tokens.
	 *
	 * @return array The order tokens.
	 */
	public function get_edd_order_tokens() {

		return array(
			'EDDPRODUCTS_DISCOUNT_CODES',
			'EDDPRODUCTS_ORDER_DISCOUNTS',
			'EDDPRODUCTS_ORDER_SUBTOTAL',
			'EDDPRODUCTS_ORDER_TAX',
			'EDDPRODUCTS_ORDER_TOTAL',
			'EDDPRODUCTS_PAYMENT_METHOD',
			'EDDPRODUCTS_LICENSE_KEY',
		);
	}

	/**
	 * Map the provided object with key.
	 *
	 * @return mixed The value.
	 */
	public function meta_to_value( $object, $key = '' ) {

		if ( empty( $key ) ) {
			return '';
		}

		$meta = array(
			'EDDPRODUCTS_DISCOUNT_CODES'  => $object->discount_codes,
			'EDDPRODUCTS_ORDER_DISCOUNTS' => number_format( $object->order_discounts, 2 ),
			'EDDPRODUCTS_ORDER_SUBTOTAL'  => number_format( $object->order_subtotal, 2 ),
			'EDDPRODUCTS_ORDER_TAX'       => number_format( $object->order_tax, 2 ),
			'EDDPRODUCTS_ORDER_TOTAL'     => number_format( $object->order_total, 2 ),
			'EDDPRODUCTS_PAYMENT_METHOD'  => $object->payment_method,
			'EDDPRODUCTS_LICENSE_KEY'     => $object->license_key,
		);

		if ( ! array_key_exists( $key, $meta ) ) {
			return '';
		}

		return $meta[ $key ];

	}

}
