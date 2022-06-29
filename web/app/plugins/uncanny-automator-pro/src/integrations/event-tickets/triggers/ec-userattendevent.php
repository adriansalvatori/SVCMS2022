<?php

namespace Uncanny_Automator_Pro;

/**
 * Class EC_USERATTENDEVENT
 *
 * @package Uncanny_Automator_Pro
 */
class EC_USERATTENDEVENT {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'EC';

	private $trigger_code;
	private $trigger_meta;

	/**
	 *  Set Triggers constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'USERATTENDEVENT';
		$this->trigger_meta = 'ATTENDEVENT';
		$this->define_trigger();
	}

	/**
	 *  Define trigger settings
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = [
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/the-events-calendar/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - The Events Calendar */
			'sentence'            => sprintf( __( 'A user attends {{an event:%1$s}}', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - The Events Calendar */
			'select_option_name'  => __( 'A user attends {{an event}}', 'uncanny-automator-pro' ),
			'action'              => [
				'event_tickets_checkin',
				'eddtickets_checkin',
				'rsvp_checkin',
				'wootickets_checkin',
			],
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => [ $this, 'event_checkins' ],
			'options'             => [
				$uncanny_automator->helpers->recipe->event_tickets->options->all_ec_events( __( 'Event', 'uncanny-automator' ), $this->trigger_meta ),
			],
		];

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * Event checkins callback function.
	 *
	 * @param string $attendee_id Attendee id.
	 * @param object $qr QR code data.
	 */
	public function event_checkins( $attendee_id, $qr ) {
		global $uncanny_automator;
		if ( ! $attendee_id ) {
			return;
		}
		$attendee_details = tribe_tickets_get_attendees( $attendee_id, 'rsvp_order' );

		if ( empty( $attendee_details ) ) {
			return;
		}

		foreach ( $attendee_details as $detail ) {
			$user_id  = $detail['user_id'];
			$event_id = $detail['event_id'];
			$args     = [
				'code'         => $this->trigger_code,
				'meta'         => $this->trigger_meta,
				'post_id'      => intval( $event_id ),
				'user_id'      => $user_id,
				'is_signed_in' => true,
			];

			$uncanny_automator->maybe_add_trigger_entry( $args );
		}
	}
}
