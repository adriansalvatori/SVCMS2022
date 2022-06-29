<?php

namespace Uncanny_Automator_Pro;

/**
 * Class EM_REGISTERFOREVENT
 * @package Uncanny_Automator_Pro
 */
class EM_REGISTERFOREVENT {
	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'EVENTSMANAGER';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'SPECIFICTICKET';
		$this->trigger_meta = 'EMEVENTS';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$all_events = $uncanny_automator->helpers->recipe->events_manager->options->all_em_events( __( 'Event', 'uncanny-automator-pro' ), 'SELECTEDEVENT',
			[
				'token'        => true,
				'is_ajax'      => true,
				'target_field' => $this->trigger_meta,
				'endpoint'     => 'select_all_tickets_from_SELECTEDEVENT',
			] );

		$all_events['relevant_tokens']['SELECTEDEVENT_STARTDATE']       = __( 'Event start date', 'uncanny-automator-pro' );
		$all_events['relevant_tokens']['SELECTEDEVENT_ENDDATE']         = __( 'Event end date', 'uncanny-automator-pro' );
		$all_events['relevant_tokens']['SELECTEDEVENT_LOCATION']        = __( 'Event location', 'uncanny-automator-pro' );
		$all_events['relevant_tokens']['SELECTEDEVENT_AVAILABLESPACES'] = __( 'Available spaces', 'uncanny-automator-pro' );
		$all_events['relevant_tokens']['SELECTEDEVENT_CONFIRMEDSPACES'] = __( 'Confirmed spaces', 'uncanny-automator-pro' );
		$all_events['relevant_tokens']['SELECTEDEVENT_PENDINGSPACES']   = __( 'Pending spaces', 'uncanny-automator-pro' );

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/events-manager/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'is_pro'              => true,
			/* translators: Logged-in trigger - The Events Manager */
			'sentence'            => sprintf( __( 'A user registers for {{an event:%1$s}} with a {{specific:%2$s}} ticket', 'uncanny-automator-pro' ), 'SELECTEDEVENT' . ':' . $this->trigger_meta, $this->trigger_meta ),
			/* translators: Logged-in trigger - The Events Manager */
			'select_option_name'  => __( 'A user registers for {{an event}} with a {{specific}} ticket', 'uncanny-automator-pro' ),
			'action'              => 'em_booking_save',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'user_purchased_specific_ticket' ),
			'options'             => [],
			'options_group'       => [
				$this->trigger_meta => [
					$all_events,
					$uncanny_automator->helpers->recipe->field->select_field(
						$this->trigger_meta,
						__( 'Ticket', 'uncanny-automator-pro' )
					),
				],
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * @param $em_event_id
	 * @param $em_booking_obj
	 *
	 * @return mixed
	 */
	public function user_purchased_specific_ticket( $em_event_id, $em_booking_obj ) {
		// In case of filter with an error
		if ( ! $em_event_id ) {
			return $em_event_id;
		}
		global $uncanny_automator, $EM_Event;

		$location              = '';
		$user_id               = $em_booking_obj->person_id;
		$recipes               = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		$required_event_ticket = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$required_event        = $uncanny_automator->get->meta_from_recipes( $recipes, 'SELECTEDEVENT' );
		$booked_tickets        = $em_booking_obj->tickets_bookings->tickets_bookings;

		$matched_recipe_ids = [];

		if ( $EM_Event->get_location()->location_id != 0 ) {
			$location = $EM_Event->get_location()->location_address . ', ' . $EM_Event->get_location()->location_town . ', '
			            . $EM_Event->get_location()->location_state . ', ' . $EM_Event->get_location()->location_region;
		}

		//		Get ids of all purchased tickets
		foreach ( $booked_tickets as $tickets ) {
			$ticket_ids[]                        = $tickets->ticket_id;
			$ticket_names[ $tickets->ticket_id ] = $tickets->ticket->ticket_name;
		}

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];//return early for all products
				if ( in_array( $required_event_ticket[ $recipe_id ][ $trigger_id ], $ticket_ids ) && $required_event == $em_event_id ) {
					$event_ticket         = $ticket_names[ $required_event_ticket[ $recipe_id ][ $trigger_id ] ];
					$matched_recipe_ids[] = [
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					];
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$pass_args = [
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $user_id,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
				];

				$args = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {

							$trigger_meta = [
								'user_id'        => $user_id,
								'trigger_id'     => $result['args']['trigger_id'],
								'trigger_log_id' => $result['args']['get_trigger_id'],
								'run_number'     => $result['args']['run_number'],
							];

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':SELECTEDEVENT';
							$trigger_meta['meta_value'] = maybe_serialize( $em_booking_obj->event->event_name );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':SELECTEDEVENT_ID';
							$trigger_meta['meta_value'] = maybe_serialize( $em_booking_obj->event->event_id );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':SELECTEDEVENT_URL';
							$trigger_meta['meta_value'] = maybe_serialize( get_permalink( $em_booking_obj->event->post_id ) );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':SELECTEDEVENT_STARTDATE';
							$trigger_meta['meta_value'] = maybe_serialize( $EM_Event->event_start_date );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':SELECTEDEVENT_ENDDATE';
							$trigger_meta['meta_value'] = maybe_serialize( $EM_Event->event_end_date );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':SELECTEDEVENT_LOCATION';
							$trigger_meta['meta_value'] = maybe_serialize( $location );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':SELECTEDEVENT_CONFIRMEDSPACES';
							$trigger_meta['meta_value'] = $EM_Event->get_bookings()->get_booked_spaces();
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':SELECTEDEVENT_PENDINGSPACES';
							$trigger_meta['meta_value'] = $EM_Event->get_bookings()->get_pending_spaces();
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':SELECTEDEVENT_AVAILABLESPACES';
							$trigger_meta['meta_value'] = $EM_Event->get_bookings()->get_available_spaces();
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':EMEVENTS';
							$trigger_meta['meta_value'] = maybe_serialize( $event_ticket );
							$uncanny_automator->insert_trigger_meta( $trigger_meta );

							$uncanny_automator->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}

		return $em_event_id;
	}
}