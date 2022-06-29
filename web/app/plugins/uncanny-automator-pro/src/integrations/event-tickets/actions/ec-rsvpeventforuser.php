<?php

namespace Uncanny_Automator_Pro;

class EC_RSVPEVENTFORUSER {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'EC';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->action_code = 'ECRSVPEVENTFORUSER';
		$this->action_meta = 'ECEVENTS';
		$this->define_action();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_action() {

		$action = array(
			'author'             => Automator()->get_author_name(),
			'support_link'       => Automator()->get_author_support_link( $this->action_code, 'integration/the-events-calendar/' ),
			'is_pro'             => true,
			'requires_user'      => false,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - The Events Calendar */
			'sentence'           => sprintf( __( 'RSVP for {{an event:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - The Events Calendar */
			'select_option_name' => __( 'RSVP for {{an event}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'rsvp_for_user' ),
			'options'            => array(),
			'options_group'      => array(
				$this->action_meta => array(
					Automator()->helpers->recipe->event_tickets->options->all_ec_rsvp_events( null, $this->action_meta ),
					Automator()->helpers->recipe->field->text(
						array(
							'option_code' => 'USER_EMAIL',
							'label'       => esc_attr__( 'Email', 'uncanny-automator-po' ),
							'input_type'  => 'text',
							'default'     => '',
							'required'    => true,
						)
					),
					Automator()->helpers->recipe->field->text(
						array(
							'option_code' => 'USER_NAME',
							'label'       => esc_attr__( 'Full name', 'uncanny-automator-pro' ),
							'input_type'  => 'text',
							'default'     => '',
							'required'    => true,
						)
					),
					Automator()->helpers->recipe->field->int(
						array(
							'option_code' => 'NUM_GUESTS',
							'label'       => esc_attr__( 'Number of guests', 'uncanny-automator' ),
							'placeholder' => esc_attr__( 'Example: 1', 'uncanny-automator' ),
							'default'     => '1',
						)
					),
				),
			),
		);

		Automator()->register->action( $action );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function rsvp_for_user( $user_id, $action_data, $recipe_id, $args ) {

		$event_id         = $action_data['meta'][ $this->action_meta ];
		$user_name        = Automator()->parse->text( $action_data['meta']['USER_NAME'], $recipe_id, $user_id, $args );
		$user_email       = Automator()->parse->text( $action_data['meta']['USER_EMAIL'], $recipe_id, $user_id, $args );
		$number_of_guests = absint( Automator()->parse->text( $action_data['meta']['NUM_GUESTS'], $recipe_id, $user_id, $args ) );
		$event_post       = get_post( $event_id );
		$ticket_handler   = new \Tribe__Tickets__Tickets_Handler();
		$rsvp_tickets     = $ticket_handler->get_event_rsvp_tickets( $event_post );

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

		$attendee_details = [
			'full_name'    => $user_name,
			'email'        => $user_email,
			'order_status' => 'yes',
			'optout'       => false,
			'order_id'     => '-1',
		];

		$order = new \Tribe__Tickets__RSVP();
		$order->generate_tickets_for( $product_id, $number_of_guests, $attendee_details );
		$order->send_tickets_email( $attendee_details['order_id'], $event_id );

		Automator()->complete_action( $user_id, $action_data, $recipe_id );
	}

}
