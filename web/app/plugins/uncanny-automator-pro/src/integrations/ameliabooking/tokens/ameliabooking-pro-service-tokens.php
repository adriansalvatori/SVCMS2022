<?php
namespace Uncanny_Automator_Pro;

/**
 * AMELIABOOKING_PRO_SERVICE_TOKENS
 *
 * This token class is intended for service tokens
 * where Amelia returns a different kind of object.
 *
 * These triggers are fired from 'Amelia' . ucwords($action) action hook
 * and returns the reservation tokens which can be an appointment, an event, etc.
 *
 * This tokens are for Pro user's only. We can't re-use the same tokens in free because
 * we are using different action hook and have a different object to work with.
 *
 * @see /plugins/ameliabooking/src/Application/Services/WebHook/WebHookApplicationService.php
 */
class AMELIABOOKING_PRO_SERVICE_TOKENS {

	/**
	 * These Triggers registers and parses this token.
	 *
	 * @var string
	 */
	const SERVICE_TOKENS_ENABLED_TRIGGERS = array(

		'AMELIA_USER_APPOINTMENT_BOOKED_SERVICE_RESCHEDULED',
		'AMELIA_USER_APPOINTMENT_BOOKED_SERVICE_CANCELLED',

		'AMELIA_APPOINTMENT_BOOKED_SERVICE_RESCHEDULED',
		'AMELIA_APPOINTMENT_BOOKED_SERVICE_CANCELLED',

		'AMELIA_USER_APPOINTMENT_BOOKED_SERVICE_SPECIFIC_STATUS',
		'AMELIA_APPOINTMENT_BOOKED_SERVICE_SPECIFIC_STATUS',

	);

	const TOKEN_META = 'AMELIABOOKING_PRO_SERVICE_TOKENS';

	public function __construct() {

		if ( defined( 'AMELIA_LITE_VERSION' ) && true === AMELIA_LITE_VERSION ) {

			return;

		}

		foreach ( self::SERVICE_TOKENS_ENABLED_TRIGGERS as $trigger ) {

			add_filter( 'automator_maybe_trigger_ameliabooking_' . strtolower( $trigger ) . '_tokens', array( $this, 'register_tokens' ), 20, 2 );

		}

		add_filter( 'automator_before_trigger_completed', array( $this, 'save_token_data' ), 20, 2 );

		add_filter( 'automator_maybe_parse_token', array( $this, 'parse_pro_tokens' ), 20, 6 );

	}

	/**
	 * Method register_tokens.
	 *
	 * Register the tokens for consumption.
	 *
	 * @return array The list of tokens.
	 */
	public function register_tokens( $tokens = array(), $args = array() ) {

		if ( ! automator_pro_do_identify_tokens() ) {

			return $tokens;

		}

		$trigger_integration = $args['integration'];

		$trigger_meta = $args['meta'];

		$tokens_collection = array_merge(
			$this->get_employee_tokens(),
			$this->get_appointment_tokens(),
			$this->get_customer_tokens(),
			$this->get_booking_tokens()
		);

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
	 * Method save_token_data.
	 *
	 * Save the token data before parsing.
	 */
	public function save_token_data( $args, $trigger ) {

		if ( ! isset( $args['trigger_args'] ) || ! isset( $args['entry_args']['code'] ) ) {
			return;
		}

		// Check if trigger code is for Amelia.
		if ( in_array( $args['entry_args']['code'], self::SERVICE_TOKENS_ENABLED_TRIGGERS, true ) ) {

			$booking = array_shift( $args['trigger_args'] );

			$booking = $this->fill_data( $booking );

			Automator()->db->token->save( self::TOKEN_META, wp_json_encode( $booking ), $args['trigger_entry'] );

		}
	}

	/**
	 * Method parse_pro_tokens.
	 *
	 * @return mixed $value The token value.
	 */
	public function parse_pro_tokens( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {

		$trigger_code = '';

		if ( isset( $trigger_data[0]['meta']['code'] ) ) {

			$trigger_code = $trigger_data[0]['meta']['code'];

		}

		if ( empty( $trigger_code ) || ! in_array( $trigger_code, self::SERVICE_TOKENS_ENABLED_TRIGGERS, true ) ) {

			return $value;

		}

		if ( ! is_array( $pieces ) || ! isset( $pieces[1] ) || ! isset( $pieces[2] ) ) {

			return $value;

		}

		// The $pieces[2] is the token id.
		$token_keys = explode( '_', $pieces[2] );

		// Get the meta from database record.
		$booking_data = json_decode( Automator()->db->token->get( self::TOKEN_META, $replace_args ), true );

		// 1 dimension array.
		if ( 1 === count( $token_keys ) && isset( $token_keys[0] ) ) {

			// Example: $booking_data['id'].
			if ( isset( $booking_data[ $token_keys[0] ] ) ) {

				$value = $booking_data[ $token_keys[0] ];

			}
		}

		// 2 dimentional arrays.
		if ( 2 === count( $token_keys ) && isset( $token_keys[0] ) && isset( $token_keys[1] ) ) {

			// Example: $booking_data['appointment']['id].
			if ( isset( $booking_data[ $token_keys[0] ][ $token_keys[1] ] ) ) {

				$value = $booking_data[ $token_keys[0] ][ $token_keys[1] ];

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

	public function get_provider_user_id( $provider ) {

		$provider_id = 0;

		if ( empty( $provider->email ) ) {

			$wp_user = get_user_by( 'email', $provider->email );

			if ( false !== $wp_user ) {

				$provider_id = $wp_user->ID;

			}
		}

		return $provider_id;

	}

	public function get_customer( $customer_id = 0 ) {

		global $wpdb;

		$results = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}amelia_users WHERE id = %d",
				$customer_id
			)
		);

		return $results;
	}

	public function fill_data( $booking ) {

		$provider = $this->get_provider( $booking['providerId'] );

		$provider_id = $this->get_provider_user_id( $provider );

		// Add the employee information as part of the data.
		$booking['employee']['id']        = $provider_id; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$booking['employee']['email']     = $provider->email;
		$booking['employee']['firstname'] = $provider->firstName; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$booking['employee']['lastname']  = $provider->lastName; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$booking['employee']['phone']     = $provider->phone;

		// Unshift booking.
		$booking['booking'] = $booking['bookings'][0];

		// Add the customer.
		$booking['customer'] = $this->get_customer( $booking['booking']['customerId'] );

		return $booking;

	}

	/**
	 * Get the employee tokens.
	 *
	 * @return array The employee tokens.
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
	 * Get appointment tokens.
	 *
	 * @return array The appointment tokens.
	 */
	public function get_appointment_tokens() {
		return array(
			array(
				'name' => esc_html__( 'Appointment ID', 'uncanny-automator' ),
				'id'   => 'id',
			),
			array(
				'name' => esc_html__( 'Appointment booking start', 'uncanny-automator' ),
				'id'   => 'bookingStart',
			),
			array(
				'name' => esc_html__( 'Appointment booking end', 'uncanny-automator' ),
				'id'   => 'bookingEnd',
			),
			array(
				'name' => esc_html__( 'Appointment provider ID', 'uncanny-automator' ),
				'id'   => 'providerId',
			),
			array(
				'name' => esc_html__( 'Appointment status', 'uncanny-automator' ),
				'id'   => 'status',
			),
		);
	}

	/**
	 * Get customer related tokens.
	 *
	 * @return array The customer tokens.
	 */
	public function get_customer_tokens() {

		// The id is mapped into booking data keys.
		return array(

			array(
				'name' => esc_html__( 'Customer first name', 'uncanny-automator' ),
				'id'   => 'customer_firstName',
			),
			array(
				'name' => esc_html__( 'Customer last name', 'uncanny-automator' ),
				'id'   => 'customer_lastName',
			),
			array(
				'name' => esc_html__( 'Customer ID', 'uncanny-automator' ),
				'id'   => 'customer_id',
			),
			array(
				'name' => esc_html__( 'Customer email', 'uncanny-automator' ),
				'id'   => 'customer_email',
			),
			array(
				'name' => esc_html__( 'Customer phone', 'uncanny-automator' ),
				'id'   => 'customer_phone',
			),
			array(
				'name' => esc_html__( 'Customer locale', 'uncanny-automator' ),
				'id'   => 'customer_translations',
			),
			array(
				'name' => esc_html__( 'Customer timezone', 'uncanny-automator' ),
				'id'   => 'customer_timeZone',
			),
		);
	}

	public function get_booking_tokens() {
		return array(
			array(
				'name' => esc_html__( 'Booking ID', 'uncanny-automator' ),
				'id'   => 'booking_id',
			),
			array(
				'name' => esc_html__( 'Booking status', 'uncanny-automator' ),
				'id'   => 'booking_status',
			),
			array(
				'name' => esc_html__( 'Booking price', 'uncanny-automator' ),
				'id'   => 'booking_price',
			),
			array(
				'name' => esc_html__( 'Booking appointment ID', 'uncanny-automator' ),
				'id'   => 'booking_appointmentId',
			),
			array(
				'name' => esc_html__( 'Booking number of persons', 'uncanny-automator' ),
				'id'   => 'booking_persons',
			),
		);
	}

}
