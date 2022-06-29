<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

namespace Uncanny_Automator_Pro;

/**
 * Class MEC_BOOK_USER
 *
 * @package Uncanny_Automator_Pro
 */
class MEC_BOOK_USER {

	/**
	 * The integration code.
	 *
	 * @var string
	 */
	public static $integration = 'MEC';

	/**
	 * The Payment Gateway to be used in Tickets. We will use pay locally.
	 *
	 * @var string
	 */
	const GATEWAY = 'MEC_gateway_pay_locally';

	/**
	 * The action code that we will use.
	 *
	 * @var string
	 */
	private $action_code;

	/**
	 * The action meta that we will use.
	 *
	 * @var string
	 */
	private $action_meta;

	/**
	 * Set Triggers constructor.
	 *
	 * @return void.
	 */
	public function __construct() {

		$this->action_code = 'MEC_BOOK_USER';

		$this->action_meta = 'MEC_SELECTED_EVENT_ID';

		$this->define_action();

	}

	/**
	 * Define and register the action by pushing it into the Automator object.
	 *
	 * @return void
	 */
	public function define_action() {

		global $uncanny_automator;

		$events = Automator()->helpers->recipe->modern_events_calendar->options;

		$args = array(
			'endpoint'     => 'ua_mec_select_events',
			'target_field' => 'MEC_SELECTED_TICKET_ID',
		);

		$action = array(
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link(),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: The event. */
			'sentence'           => sprintf( esc_attr__( 'Register the user for {{an event:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			'select_option_name' => esc_attr__( 'Register the user for {{an event}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'book_user_to_event' ),
			'options_group'      => array(

				$this->action_meta => array(

					$events->get_events_select_field(),
					$events->get_tickets_select_field(),

				),
			),
		);

		$uncanny_automator->register->action( $action );

	}

	/**
	 * Book the current logged-in user to the selected event.
	 *
	 * @param string $user_id
	 * @param array $action_data
	 * @param string $recipe_id
	 *
	 * @return void
	 */
	public function book_user_to_event( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		// Check hard dependency for \MEC_gateway_pay_locally class if it exists.
		if ( ! class_exists( '\MEC_gateway_pay_locally' ) ) {
			return;
		}

		// Check hard dependency for \MEC_feature_books class if it exists.
		if ( ! class_exists( '\MEC_feature_books' ) ) {
			return;
		}

		$gateway  = new \MEC_gateway_pay_locally();
		$mec_book = new \MEC_feature_books();

		$event_id = absint( $action_data['meta']['MEC_SELECTED_EVENT_ID'] );

		$selected_ticket_id = absint( $action_data['meta']['MEC_SELECTED_TICKET_ID'] );

		$user = get_user_by( 'ID', $user_id );

		$book = $mec_book->getBook();

		$attendee = array(
			'email' => $user->user_email,
			'name'  => $user->display_name,
			'reg'   => array(),
		);

		// Generate new user id from gateway registration.
		$user_id = $gateway->register_user( $attendee );

		// The date.
		$event_date           = null;
		$event_date_from_meta = get_post_meta( $event_id, 'mec_date', true );

		// OCC Timestamp.
		if ( isset( $event_date_from_meta['start'] ) && isset( $event_date_from_meta['end'] ) ) {
			$event_date = $book->timestamp( $event_date_from_meta['start'], $event_date_from_meta['end'] );
		} else {
			// log error here.
			$error_message = 'Event Start Date and End Date is missing. Please check if the select Event has a corresponding dates.';
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );
		}

		// The attendees count. We will set it to `1` since there can only be 1 logged-in user at a time.
		$attendees_count = 1;

		// The ticket ID.
		$tickets = array();

		// This will hold the comma separated value later on for the ticket IDs.
		$ticket_ids = '';

		for ( $i = 1; $i <= $attendees_count; $i++ ) {
			$tickets[] = array_merge(
				$attendee,
				array(
					'id'         => $selected_ticket_id, // MEC_SELECTED_TICKET_ID.
					'count'      => 1,
					'variations' => array(),
					'reg'        => ( isset( $attendee['reg'] ) ? $attendee['reg'] : array() ),
				)
			);

			$ticket_ids .= $selected_ticket_id . ',';
		}

		$raw_tickets   = array( $selected_ticket_id => $attendees_count );
		$event_tickets = get_post_meta( $event_id, 'mec_tickets', true );

		// Calculate price of bookings
		$price_details = $book->get_price_details( $raw_tickets, $event_id, $event_tickets, array() );

		// Configure the transaction.
		$transaction = array(
			'tickets'       => $tickets,
			'date'          => $event_date,
			'event_id'      => $event_id,
			'price_details' => $price_details,
			'total'         => $price_details['total'],
			'discount'      => 0,
			'price'         => $price_details['total'],
			'coupon'        => null,
			'fields'        => array(),
		);

		// Save The Transaction
		$transaction_id = $book->temporary( $transaction );

		// Create new booking (CPT).
		$book_args = array(
			'post_author' => $user_id,
			'post_type'   => 'mec-books',
			'post_title'  => sprintf( '%s - %s', $user->display_name, $user->user_email ),
		);

		$booking_id = $book->add( $book_args, $transaction_id, ',' . trim( $ticket_ids, ', ' ) . ',' );

		// Update the `mec_attendees`.
		update_post_meta( $booking_id, 'mec_attendees', $tickets );
		update_post_meta( $booking_id, 'mec_reg', ( isset( $attendee['reg'] ) ? $attendee['reg'] : array() ) );
		update_post_meta( $booking_id, 'mec_gateway', self::GATEWAY );
		update_post_meta( $booking_id, 'mec_gateway_label', $gateway->title() );

		// For Booking Badge
		update_post_meta( $booking_id, 'mec_book_date_submit', gmdate( 'YmdHis', time() ) );

		// Execute pending action.
		do_action( 'mec_booking_pended', $booking_id );

		// Send notification if its a new booking.
		try {

			if ( $this->is_new_booking( $booking_id ) ) {

				do_action( 'mec_booking_completed', $booking_id );

			}

		} catch ( \Exception $e ) {

			automator_log( $e->getMessage(), 'MEC Error', true, 'mec' );

		}

		// Done.
		$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id );

	}

	/**
	 * Check if booking is new or not.
	 *
	 * @return Boolean True if booking already exists. Otherwise, false.
	 */
	public function is_new_booking( $booking_id = 0 ) {

		// Log error if booking is empty.
		if ( empty( $booking_id ) ) {

			throw new \Exception( 'Booking ID is empty.' );

		}

		// Return true since the MEC action `Register the user for {event}` always registers a new booking.
		return true;

	}

}
