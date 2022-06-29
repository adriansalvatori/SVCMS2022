<?php

namespace Uncanny_Automator_Pro;

/**
 * Class EM_UNREGISTER_A
 * @package Uncanny_Automator_Pro
 */
class EM_UNREGISTER_A {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'EVENTSMANAGER';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->action_code = 'UNREGISTERUSER';
		$this->action_meta = 'EMUNREGISTERUSER';
		$this->define_action();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$all_events = $uncanny_automator->helpers->recipe->events_manager->options->all_em_events( __( 'Event', 'uncanny-automator-pro' ), $this->action_meta );

		$all_events['options']['-1'] = __( 'All events', 'uncanny-automator-pro' );

		$action = array(
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/events-manager/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - The Events Manager */
			'sentence'           => sprintf( __( 'Unregister the user from {{an event:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - The Events Manager */
			'select_option_name' => __( 'Unregister the user from {{an event}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'unregister_user_from_event' ),
			'options'            => [
				$all_events,
			],
		);

		$uncanny_automator->register->action( $action );

		return;
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function unregister_user_from_event( $user_id, $action_data, $recipe_id, $args ) {
		global $uncanny_automator;
		$event_id = $action_data['meta'][ $this->action_meta ];
		$event    = $action_data['meta'][ $this->action_meta . '_readable' ];

		global $wpdb;
		$events   = $wpdb->prefix . 'em_events';
		$bookings = $wpdb->prefix . 'em_bookings';
		$query    = "SELECT b.booking_id FROM  $bookings as b INNER JOIN $events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.booking_status NOT IN (2,3) AND b.person_id = $user_id AND b.event_id = $event_id AND e.event_end_date >= CURRENT_DATE";
		if ( $event_id == '-1' || 'All events' == $event ) {
			$query = "SELECT b.booking_id FROM  $bookings as b INNER JOIN $events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.booking_status NOT IN (2,3) AND b.person_id = $user_id AND e.event_end_date >= CURRENT_DATE";
		}


		$all_bookings = $wpdb->get_results( $query );

		if ( empty( $all_bookings ) ) {
			$error_msg                           = sprintf( __( 'The user was not registered for the specified event.', 'uncanny-automator-pro' ) );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_msg );

			return;
		}

		foreach ( $all_bookings as $booking ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$bookings} SET booking_status= 3 WHERE booking_id=%d", $booking->booking_id ) );
		}

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}

}
