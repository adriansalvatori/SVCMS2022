<?php

namespace Uncanny_Automator_Pro;

/**
 * Class EC_ANON_REGISTERATTANDEE
 * @package Uncanny_Automator_Pro
 */
class EC_ANON_REGISTERATTANDEE {
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
		$this->trigger_code = 'ATTENDEEREGISTERED';
		$this->trigger_meta = 'ANONATTENDEE';
		$this->define_trigger();
	}

	/**
	 *  Define trigger settings
	 */
	public function define_trigger() {

		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/the-events-calendar/' ),
			'is_pro'              => true,
			'type'                => 'anonymous',
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - The Events Calendar */
			'sentence'            => sprintf( __( 'An attendee is registered for {{an event:%1$s}}', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - The Events Calendar */
			'select_option_name'  => __( 'An attendee is registered for {{an event}}', 'uncanny-automator-pro' ),
			'action'              => array(
				'event_tickets_rsvp_attendee_created',
				'event_ticket_woo_attendee_created',
				'event_ticket_edd_attendee_created',
				'event_tickets_tpp_attendee_created',
				'event_tickets_tpp_attendee_updated',
				'tec_tickets_commerce_attendee_after_create',
			),
			'priority'            => 10,
			'accepted_args'       => 5,
			'validation_function' => array( $this, 'attendee_registered' ),
			'options'             => array(
				Automator()->helpers->recipe->event_tickets->options->all_ec_events( __( 'Event', 'uncanny-automator-pro' ), $this->trigger_meta ),
			),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * Attendee registered callback function.
	 *
	 * @param $attendee_id
	 * @param $post_id
	 * @param $order
	 * @param $attendee_product_id
	 * @param $attendee_order_status
	 */
	public function attendee_registered( $attendee_id, $post_id, $order, $attendee_product_id, $attendee_order_status = null ) {

		if ( 'tec_tickets_commerce_attendee_after_create' === (string) current_action() ) {
			$attendee_id = $attendee_id->ID;
			$post_id     = $attendee_id->event_id;
		}

		if ( ! $attendee_id ) {
			return;
		}

		$attendee_details = tribe_tickets_get_attendees( $attendee_id );

		if ( empty( $attendee_details ) ) {
			return;
		}

		$recipes            = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_event     = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = array();

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( intval( '-1' ) === intval( $required_event[ $recipe_id ][ $trigger_id ] ) || absint( $post_id ) === absint( $required_event[ $recipe_id ][ $trigger_id ] ) ) {
					$matched_recipe_ids[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					);
				}
			}
		}

		if ( empty( $matched_recipe_ids ) ) {
			return;
		}
		foreach ( $matched_recipe_ids as $matched_recipe_id ) {
			foreach ( $attendee_details as $detail ) {
				$user_id  = $detail['user_id'];
				$event_id = $detail['event_id'];
				$args     = array(
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'post_id'          => intval( $event_id ),
					'user_id'          => $user_id,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
				);

				$result = Automator()->maybe_add_trigger_entry( $args, false );
				if ( $result ) {
					foreach ( $result as $r ) {
						if ( true === $r['result'] ) {
							if ( isset( $r['args'] ) && isset( $r['args']['get_trigger_id'] ) ) {
								$trigger_meta = array(
									'user_id'        => $user_id,
									'trigger_id'     => (int) $r['args']['trigger_id'],
									'trigger_log_id' => $r['args']['trigger_log_id'],
									'run_number'     => $r['args']['run_number'],
								);

								$trigger_meta['meta_key']   = 'holder_name';
								$trigger_meta['meta_value'] = maybe_serialize( $detail['holder_name'] );
								Automator()->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = 'holder_email';
								$trigger_meta['meta_value'] = maybe_serialize( $detail['holder_email'] );
								Automator()->insert_trigger_meta( $trigger_meta );
							}
							Automator()->maybe_trigger_complete( $r['args'] );
						}
					}
				}
			}
		}

	}
}
