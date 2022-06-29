<?php
namespace Uncanny_Automator_Pro;

/**
 * Amelia Pro tokens.
 */
class AMELIABOOKING_PRO_TOKENS {


	const EMPLOYEE_TOKENS_FROM_APPOINTMENT_TRIGGERS = array(
		'AMELIA_APPOINTMENT_BOOKED',
		'AMELIA_APPOINTMENT_BOOKED_SERVICE',
		'AMELIA_USER_APPOINTMENT_BOOKED',
		'AMELIA_USER_APPOINTMENT_BOOKED_SERVICE',
	);

	public function __construct() {

		// Bailout if user is running amelia lite.
		if ( defined( 'AMELIA_LITE_VERSION' ) ) {
			if ( true === AMELIA_LITE_VERSION ) {
				return;
			}
		}

		add_action( 'automator_before_trigger_completed', array( $this, 'save_token_data' ), 20, 2 );

		foreach ( self::EMPLOYEE_TOKENS_FROM_APPOINTMENT_TRIGGERS as $trigger ) {

			add_filter( 'automator_maybe_trigger_ameliabooking_' . strtolower( $trigger ) . '_tokens', array( $this, 'register_tokens' ), 20, 2 );

		}

		add_filter( 'automator_maybe_parse_token', array( $this, 'parse_pro_tokens' ), 20, 6 );

	}

	/**
	 * Register the tokens.
	 *
	 * @param  mixed $tokens
	 * @param  mixed $args
	 * @return void
	 */
	public function register_tokens( $tokens = array(), $args = array() ) {

		if ( ! automator_pro_do_identify_tokens() ) {
			return $tokens;
		}

		$trigger_integration = $args['integration'];

		$trigger_meta = $args['meta'];

		$tokens_collection = $this->get_employee_tokens();

		$arr_column_tokens_collection = array_column( $tokens_collection, 'name' );

		array_multisort( $arr_column_tokens_collection, SORT_ASC, $tokens_collection );

		foreach ( $tokens_collection as $token ) {
			$tokens[] = array(
				'tokenId'         => str_replace( ' ', '_', $token['id'] ),
				'tokenName'       => $token['name'],
				'tokenType'       => 'text',
				'tokenIdentifier' => strtoupper( 'AMELIA_' . $token['id'] ),
			);
		}

		return $tokens;

	}

	/**
	 * Save the token data.
	 *
	 * @param  mixed $args
	 * @param  mixed $trigger
	 * @return void
	 */
	public function save_token_data( $args, $trigger ) {

		if ( ! isset( $args['trigger_args'] ) || ! isset( $args['entry_args']['code'] ) ) {
			return;
		}

		// Check if trigger code is for Amelia.
		if ( in_array( $args['entry_args']['code'], self::EMPLOYEE_TOKENS_FROM_APPOINTMENT_TRIGGERS, true ) ) {

			$booking_data_arr = array_shift( $args['trigger_args'] );

			// Provider id.
			$provider = $this->get_provider( absint( $booking_data_arr['appointment']['providerId'] ) );

			$provider_id = 0;

			// Get the provider id. Do not depend on externalId - its returning blank sometimes.
			if ( isset( $provider->email ) && ! empty( $provider->email ) ) {
				$wp_user = get_user_by( 'email', $provider->email );
				if ( false !== $wp_user ) {
					$provider_id = $wp_user->ID;
				}
			}

			// Add the employee information as part of the data.
			$booking_data_arr['employee']['id']        = $provider_id; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$booking_data_arr['employee']['email']     = $provider->email;
			$booking_data_arr['employee']['firstname'] = $provider->firstName; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$booking_data_arr['employee']['lastname']  = $provider->lastName; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$booking_data_arr['employee']['phone']     = $provider->phone;

			$booking_data = wp_json_encode( $booking_data_arr );

			Automator()->db->token->save( 'AMELIA_BOOKING_PRO_DATA', $booking_data, $args['trigger_entry'] );

		}

	}

	/**
	 * Get the employee tokens.
	 *
	 * @return array[]
	 */
	public function get_employee_tokens() {
		return array(
			array(
				'name' => esc_html__( 'Employee ID', 'uncanny-automator' ),
				'id'   => 'employee_id',
			),
			array(
				'name' => esc_html__( 'Employee email', 'uncanny-automator' ),
				'id'   => 'employee_email',
			),
			array(
				'name' => esc_html__( 'Employee first name', 'uncanny-automator' ),
				'id'   => 'employee_firstname',
			),
			array(
				'name' => esc_html__( 'Employee last name', 'uncanny-automator' ),
				'id'   => 'employee_lastname',
			),
			array(
				'name' => esc_html__( 'Employee phone', 'uncanny-automator' ),
				'id'   => 'employee_phone',
			),
		);
	}

	/**
	 * Parse tokens.
	 */
	public function parse_pro_tokens( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {

		$trigger_code = '';

		if ( isset( $trigger_data[0]['meta']['code'] ) ) {
			$trigger_code = $trigger_data[0]['meta']['code'];
		}

		if ( empty( $trigger_code ) || ! in_array( $trigger_code, self::EMPLOYEE_TOKENS_FROM_APPOINTMENT_TRIGGERS, true ) ) {
			return $value;
		}

		if ( ! is_array( $pieces ) || ! isset( $pieces[1] ) || ! isset( $pieces[2] ) ) {
			return $value;
		}

		// The $pieces[2] is the token id.
		$token_id_parts = explode( '_', $pieces[2] );

		// Get the meta from database record.
		$booking_data = json_decode( Automator()->db->token->get( 'AMELIA_BOOKING_PRO_DATA', $replace_args ), true );

		// Add a check to prevent notice.
		if ( isset( $token_id_parts[0] ) && isset( $token_id_parts[1] ) ) {
			// Example: $booking_data['appointment']['id].
			if ( isset( $booking_data[ $token_id_parts[0] ][ $token_id_parts[1] ] ) ) {
				$value = $booking_data[ $token_id_parts[0] ][ $token_id_parts[1] ];
			}
		}

		return $value;

	}

	/**
	 * Get the provider information.
	 */
	public function get_provider( $provider_id ) {

		global $wpdb;

		$results = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}amelia_users WHERE id = %d",
				$provider_id
			)
		);

		return $results;
	}

}
