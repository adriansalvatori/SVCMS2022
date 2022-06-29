<?php

namespace Uncanny_Automator_Pro;

/**
 * Class EM_TOKENS
 *
 * @package Uncanny_Automator_Pro
 */
class EM_TOKENS {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'EVENTSMANAGER';

	/**
	 * EM_TOKENS constructor.
	 */
	public function __construct() {
		add_filter( 'automator_maybe_parse_token', array( $this, 'parse_em_trigger_tokens' ), 20, 6 );
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 * @param $replace_args
	 *
	 * @return float|int|mixed|string|null
	 */
	public function parse_em_trigger_tokens( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		$tokens = array(
			'SELECTEDEVENT',
			'SELECTEDEVENT_ID',
			'SELECTEDEVENT_URL',
			'SELECTEDEVENT_STARTDATE',
			'SELECTEDEVENT_ENDDATE',
			'SELECTEDEVENT_LOCATION',
			'SELECTEDEVENT_AVAILABLESPACES',
			'SELECTEDEVENT_CONFIRMEDSPACES',
			'SELECTEDEVENT_PENDINGSPACES',
			'EMUNREGISTER',
			'EMUNREGISTER_ID',
			'EMUNREGISTER_URL',
			'EMUNREGISTER_STARTDATE',
			'EMUNREGISTER_ENDDATE',
			'EMUNREGISTER_LOCATION',
			'EMUNREGISTER_AVAILABLESPACES',
			'EMUNREGISTER_CONFIRMEDSPACES',
			'EMUNREGISTER_PENDINGSPACES',
			'EMEVENTS',
		);

		if ( $pieces && isset( $pieces[2] ) ) {
			$meta_field = $pieces[2];
			if ( ! empty( $meta_field ) && in_array( $meta_field, $tokens ) ) {
				if ( $trigger_data ) {
					global $wpdb;
					foreach ( $trigger_data as $trigger ) {
						$trigger_id = $trigger['ID'];
						$meta_key   = $trigger_id . ':' . $trigger['meta']['code'] . ':' . $meta_field;
						$meta_value = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}uap_trigger_log_meta WHERE meta_key = %s AND automator_trigger_id = %d ORDER BY ID DESC LIMIT 0,1", $meta_key, $trigger_id ) );
						if ( ! empty( $meta_value ) && ! is_numeric( $meta_value ) ) {
							$value = maybe_unserialize( $meta_value );
						} else {
							$value = $meta_value;
						}
					}
				}
			}
		}

		return $value;
	}

	/**
	 * @param $trigger_meta
	 * @param $args
	 * @param \EM_Booking $em_booking_obj
	 *
	 * @return void
	 */
	public static function em_save_tokens( $trigger_meta, $args, \EM_Booking $em_booking_obj ) {
		$em_event_obj = $em_booking_obj->get_event();
		$person       = $em_booking_obj->person->data;
		$user_id      = $em_booking_obj->person_id;

		$location_name      = '-';
		$location_address   = '-';
		$location_town      = '-';
		$location_state     = '-';
		$location_postcode  = '-';
		$location_region    = '-';
		$location_country   = '-';
		$location_url       = '-';
		$location_link_text = '-';

		$location_obj = $em_event_obj->get_location();

		if ( $em_event_obj instanceof \EM_Event ) {
			if ( 0 !== $em_event_obj->location_id && $location_obj instanceof \EM_Location && 'url' !== $em_event_obj->event_location_type ) {
				$location_name     = $location_obj->location_name;
				$location_address  = $location_obj->location_address;
				$location_town     = $location_obj->location_town;
				$location_state    = $location_obj->location_state;
				$location_postcode = $location_obj->location_postcode;
				$location_region   = $location_obj->location_region;
				$location_country  = $location_obj->location_country;
			}
			if ( 'url' === $em_event_obj->event_location_type ) {
				/** @var \EM_Event_Locations\Event_Location $event_location_obj */
				$event_location_obj = $em_event_obj->event_location;
				$data               = $event_location_obj->data;
				$location_url       = ! empty( $data['url'] ) ? $data['url'] : '-';
				$location_link_text = ! empty( $data['text'] ) ? $data['text'] : '-';
			}
		}

		$trigger_meta_args = array(
			'user_id'        => $user_id,
			'trigger_id'     => $args['trigger_id'],
			'trigger_log_id' => $args['get_trigger_id'],
			'run_number'     => $args['run_number'],
		);

		$trigger_meta_args['meta_key']   = $trigger_meta . '_ATTENDEE_NAME';
		$trigger_meta_args['meta_value'] = maybe_serialize( $person->display_name );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_ATTENDEE_EMAIL';
		$trigger_meta_args['meta_value'] = maybe_serialize( $person->user_email );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_ATTENDEE_PHONE';
		$trigger_meta_args['meta_value'] = maybe_serialize( $person->phone );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta;
		$trigger_meta_args['meta_value'] = maybe_serialize( $em_booking_obj->event->event_name );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_ID';
		$trigger_meta_args['meta_value'] = maybe_serialize( $em_booking_obj->event->event_id );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_URL';
		$trigger_meta_args['meta_value'] = maybe_serialize( get_permalink( $em_booking_obj->event->post_id ) );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_THUMB_URL';
		$trigger_meta_args['meta_value'] = ( empty( get_the_post_thumbnail_url( $em_booking_obj->event->post_id, 'full' ) ) ) ? '-' : maybe_serialize( get_the_post_thumbnail_url( $em_booking_obj->event->post_id, 'full' ) );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_THUMB_ID';
		$trigger_meta_args['meta_value'] = ( empty( get_post_thumbnail_id( $em_booking_obj->event->post_id ) ) ) ? '-' : maybe_serialize( get_post_thumbnail_id( $em_booking_obj->event->post_id ) );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_BOOKED_SPACES';
		$trigger_meta_args['meta_value'] = maybe_serialize( $em_booking_obj->get_spaces() );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_PRICE_PAID';
		$trigger_meta_args['meta_value'] = maybe_serialize( $em_booking_obj->get_price() );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_COMMENT';
		$trigger_meta_args['meta_value'] = maybe_serialize( $em_booking_obj->booking_comment );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_START_DATE';
		$trigger_meta_args['meta_value'] = maybe_serialize( $em_event_obj->event_start_date );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_END_DATE';
		$trigger_meta_args['meta_value'] = maybe_serialize( $em_event_obj->event_end_date );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_LOCATION_NAME';
		$trigger_meta_args['meta_value'] = maybe_serialize( $location_name );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_LOCATION_ADDRESS';
		$trigger_meta_args['meta_value'] = maybe_serialize( $location_address );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_LOCATION_TOWN';
		$trigger_meta_args['meta_value'] = maybe_serialize( $location_town );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_LOCATION_STATE';
		$trigger_meta_args['meta_value'] = maybe_serialize( $location_state );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_LOCATION_POSTCODE';
		$trigger_meta_args['meta_value'] = maybe_serialize( $location_postcode );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_LOCATION_REGION';
		$trigger_meta_args['meta_value'] = maybe_serialize( $location_region );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_LOCATION_COUNTRY';
		$trigger_meta_args['meta_value'] = maybe_serialize( $location_country );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_CONFIRMED_SPACES';
		$trigger_meta_args['meta_value'] = ( $em_event_obj->get_bookings()->get_booked_spaces() > 0 ) ? $em_event_obj->get_bookings()->get_booked_spaces() : 0;
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_PENDING_SPACES';
		$trigger_meta_args['meta_value'] = ( $em_event_obj->get_bookings()->get_pending_spaces() > 0 ) ? $em_event_obj->get_bookings()->get_pending_spaces() : 0;
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_AVAILABLE_SPACES';
		$trigger_meta_args['meta_value'] = ( $em_event_obj->get_bookings()->get_available_spaces() > 0 ) ? $em_event_obj->get_bookings()->get_available_spaces() : 0;
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_TOTAL_SPACES';
		$trigger_meta_args['meta_value'] = maybe_serialize( $em_event_obj->get_bookings()->get_spaces() );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_MAX_SPACES';
		$trigger_meta_args['meta_value'] = maybe_serialize( $em_event_obj->event_rsvp_spaces );
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_LOCATION_URL';
		$trigger_meta_args['meta_value'] = $location_url;
		Automator()->insert_trigger_meta( $trigger_meta_args );

		$trigger_meta_args['meta_key']   = $trigger_meta . '_LOCATION_LINK_TITLE';
		$trigger_meta_args['meta_value'] = $location_link_text;
		Automator()->insert_trigger_meta( $trigger_meta_args );
	}
}
