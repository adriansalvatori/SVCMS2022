<?php

namespace Uncanny_Automator_Pro;

/**
 * Class EM_UNREGISTER
 * @package Uncanny_Automator_Pro
 */
class EM_UNREGISTER {
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
		$this->trigger_code = 'UNREGISTERS';
		$this->trigger_meta = 'EMUNREGISTER';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$all_events = $uncanny_automator->helpers->recipe->events_manager->options->all_em_events( __( 'Event', 'uncanny-automator-pro' ), $this->trigger_meta, [ 'any_option' => true ] );

		$all_events['relevant_tokens']['EMUNREGISTER_STARTDATE']       = __( 'Event start date', 'uncanny-automator-pro' );
		$all_events['relevant_tokens']['EMUNREGISTER_ENDDATE']         = __( 'Event end date', 'uncanny-automator-pro' );
		$all_events['relevant_tokens']['EMUNREGISTER_LOCATION']        = __( 'Event location', 'uncanny-automator-pro' );
		$all_events['relevant_tokens']['EMUNREGISTER_AVAILABLESPACES'] = __( 'Available spaces', 'uncanny-automator-pro' );
		$all_events['relevant_tokens']['EMUNREGISTER_CONFIRMEDSPACES'] = __( 'Confirmed spaces', 'uncanny-automator-pro' );
		$all_events['relevant_tokens']['EMUNREGISTER_PENDINGSPACES']   = __( 'Pending spaces', 'uncanny-automator-pro' );

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/events-manager/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'is_pro'              => true,
			/* translators: Logged-in trigger - The Events Manager */
			'sentence'            => sprintf( __( 'A user unregisters from {{an event:%1$s}}', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - The Events Manager */
			'select_option_name'  => __( 'A user unregisters from {{an event}}', 'uncanny-automator-pro' ),
			'action'              => 'em_booking_set_status',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'user_unregisters_from_event' ),
			'options'             => [ $all_events ],
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
	public function user_unregisters_from_event( $em_status, $em_booking_obj ) {
		global $uncanny_automator, $EM_Event;

		if ( 0 === (int) get_option( 'dbem_bookings_user_cancellation', 0 ) || $em_booking_obj->get_status() != 'Cancelled' ) {
			return $em_status;
		}

		if ( $em_booking_obj->person->ID == get_current_user_id() ) {

			$user_id        = $em_booking_obj->person_id;
			$em_event_id    = $em_booking_obj->event_id;
			$recipes        = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
			$required_event = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );

			$matched_recipe_ids = [];

			$location = $EM_Event->get_location()->location_address . ', ' . $EM_Event->get_location()->location_town . ', '
			            . $EM_Event->get_location()->location_state . ', ' . $EM_Event->get_location()->location_region;


			foreach ( $recipes as $recipe_id => $recipe ) {
				foreach ( $recipe['triggers'] as $trigger ) {
					$trigger_id = $trigger['ID'];//return early for all products
					if ( $required_event[ $recipe_id ][ $trigger_id ] == $em_event_id || $required_event[ $recipe_id ][ $trigger_id ] == '-1' ) {
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

								$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta;
								$trigger_meta['meta_value'] = maybe_serialize( $em_booking_obj->event->event_name );
								$uncanny_automator->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta . '_ID';
								$trigger_meta['meta_value'] = maybe_serialize( $em_booking_obj->event->event_id );
								$uncanny_automator->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta . '_URL';
								$trigger_meta['meta_value'] = maybe_serialize( get_permalink( $em_booking_obj->event->post_id ) );
								$uncanny_automator->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta . '_STARTDATE';
								$trigger_meta['meta_value'] = maybe_serialize( $EM_Event->event_start_date );
								$uncanny_automator->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta . '_ENDDATE';
								$trigger_meta['meta_value'] = maybe_serialize( $EM_Event->event_end_date );
								$uncanny_automator->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta . '_LOCATION';
								$trigger_meta['meta_value'] = maybe_serialize( $location );
								$uncanny_automator->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta . '_CONFIRMEDSPACES';
								$trigger_meta['meta_value'] = maybe_serialize( $EM_Event->get_bookings()->get_booked_spaces() );
								$uncanny_automator->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta . '_PENDINGSPACES';
								$trigger_meta['meta_value'] = maybe_serialize( $EM_Event->get_bookings()->get_pending_spaces() );
								$uncanny_automator->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = $result['args']['trigger_id'] . ':' . $this->trigger_code . ':' . $this->trigger_meta . '_AVAILABLESPACES';
								$trigger_meta['meta_value'] = maybe_serialize( $EM_Event->get_bookings()->get_available_spaces() );
								$uncanny_automator->insert_trigger_meta( $trigger_meta );

								$uncanny_automator->maybe_trigger_complete( $result['args'] );
							}
						}
					}
				}
			}

			return $em_status;
		}
	}
}