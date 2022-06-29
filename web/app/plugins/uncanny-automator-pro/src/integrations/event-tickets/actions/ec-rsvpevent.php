<?php

namespace Uncanny_Automator_Pro;

use Tribe__Tickets__RSVP;
use Tribe__Tickets__Tickets_Handler;

/**
 * Class EC_RSVPEVENT
 * @package Uncanny_Automator_Pro
 */
class EC_RSVPEVENT {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'EC';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->action_code = 'ECRSVPEVENT';
		$this->action_meta = 'ECEVENTS';
		$this->define_action();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/the-events-calendar/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - The Events Calendar */
			'sentence'           => sprintf( __( 'RSVP on behalf of the user for {{an event:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - The Events Calendar */
			'select_option_name' => __( 'RSVP on behalf of the user for {{an event}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'rsvp_event' ),
			'options'            => [
				$uncanny_automator->helpers->recipe->event_tickets->options->all_ec_rsvp_events(),
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
	public function rsvp_event( $user_id, $action_data, $recipe_id, $args ) {
		global $uncanny_automator;
		$event_id       = $action_data['meta'][ $this->action_meta ];
		$event_post     = get_post( $event_id );
		$ticket_handler = new Tribe__Tickets__Tickets_Handler();
		$rsvp_tickets   = $ticket_handler->get_event_rsvp_tickets( $event_post );

		if ( empty( $rsvp_tickets ) ) {
			return;
		}
		$product_id = 0;
		foreach ( $rsvp_tickets as $rsvp_ticket ) {
			if ( $rsvp_ticket->capacity < 0 ) {
				$product_id = $rsvp_ticket->ID;
			} elseif ( $rsvp_ticket->capacity > 0 && $rsvp_ticket->capacity > $rsvp_ticket->qty_sold ) {
				$product_id = $rsvp_ticket->ID;
			}
			if ( $product_id > 0 ) {
				break;
			}
		}

		$user             = get_userdata( $user_id );
		$attendee_details = [
			'full_name'    => $user->display_name,
			'email'        => $user->user_email,
			'order_status' => 'yes',
			'optout'       => false,
			'order_id'     => '-1',
		];

		$order = new Tribe__Tickets__RSVP();
		$order->generate_tickets_for( $product_id, 1, $attendee_details );
		$order->send_tickets_email( $attendee_details['order_id'], $event_id );

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
