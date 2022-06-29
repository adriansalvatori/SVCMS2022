<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

namespace Uncanny_Automator_Pro;

/**
 * Class MEC_USER_BOOKING_CANCELLED
 *
 * @package Uncanny_Automator_Pro
 */
class MEC_USER_BOOKING_PENDING {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'MEC';

	/**
	 * @var string
	 */
	private $trigger_code;

	/**
	 * @var string
	 */
	private $trigger_meta;

	/**
	 * The tokens that will be used.
	 */
	private $token = 'MECTOKENS_';

	/**
	 * Our class constructor.
	 *
	 * @return void.
	 */
	public function __construct() {

		$this->trigger_code = 'MECUSERBOOKPENDING';
		$this->trigger_meta = 'MECUSERPENDINGBOOK';

		$this->define_trigger();

	}

	/**
	 * Define and register the trigger by pushing it into the Automator object.
	 *
	 * @return void.
	 */
	public function define_trigger() {

		$helper = Automator()->helpers->recipe->modern_events_calendar->options;

		$events_options = $helper->get_events_select_field(
			array(
				'option_code'     => $this->trigger_code,
				'fill_values_in'  => '',
				'is_ajax'         => false,
				'description'     => esc_html__( 'Select from the list of available events.', 'uncanny-automator-pro' ),
				'relevant_tokens' => array(
					$this->token . 'EVENT_TITLE'     => esc_html__( 'Event title', 'uncanny-automator-pro' ),
					$this->token . 'EVENT_DATE'      => esc_html__( 'Event date', 'uncanny-automator-pro' ),
					$this->token . 'EVENT_TIME'      => esc_html__( 'Event time', 'uncanny-automator-pro' ),
					$this->token . 'EVENT_LOCATION'  => esc_html__( 'Event location', 'uncanny-automator-pro' ),
					$this->token . 'EVENT_ORGANIZER' => esc_html__( 'Event organizer', 'uncanny-automator-pro' ),
					$this->token . 'EVENT_COST'      => esc_html__( 'Event cost', 'uncanny-automator-pro' ),
					$this->token . 'EVENT_THUMB_ID'      => esc_html__( 'Event featured image ID', 'uncanny-automator' ),
					$this->token . 'EVENT_THUMB_URL'      => esc_html__( 'Event featured image URL', 'uncanny-automator' ),
				),
			)
		);

		$events_options['options'] = array( '-1' => __( 'Any event', 'uncanny-automator-pro' ) ) + $events_options['options'];

		$trigger = array(
			'author'              => Automator()->get_author_name(),
			'support_link'        => Automator()->get_author_support_link(),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'is_pro'              => true,
			'sentence'            => sprintf(
				/* translators: The Event or `Any Event` */
				esc_attr__( "A user's booking of {{an event:%1\$s}} is pending", 'uncanny-automator-pro' ),
				$this->trigger_code
			),
			'select_option_name'  => esc_attr__( "A user's booking of {{an event}} is pending", 'uncanny-automator-pro' ),
			'action'              => 'mec_booking_pended',
			'priority'            => 99,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'mec_user_booking_pended' ),
			'options'             => array(
				$events_options,
			),
		);

		Automator()->register->trigger( $trigger );

	}

	/**
	 * Callback function to register trigger parameters.
	 *
	 * @param $booking_id The accepted post id of `mec_user_booking_cancelled` action.
	 *
	 * @return void.
	 */
	public function mec_user_booking_pended( $booking_id ) {

		$matched_recipe_ids = array();

		$recipes = Automator()->get->recipes_from_trigger_code( $this->trigger_code );

		$event = Automator()->get->meta_from_recipes( $recipes, $this->trigger_code );

		$event_id = absint( get_post_meta( $booking_id, 'mec_event_id', true ) );

		$attendees = get_post_meta( $booking_id, 'mec_attendees', true );

		foreach ( $recipes as $recipe_id => $recipe ) {

			foreach ( $recipe['triggers'] as $trigger ) {

				$trigger_id = $trigger['ID'];

				// Check to see if trigger matches `Any` trigger or a specific Event.
				if (
					intval( '-1' ) === intval( $event[ $recipe_id ][ $trigger_id ] )
					|| intval( $event_id ) === intval( $event[ $recipe_id ][ $trigger_id ] )
				) {

					$matched_recipe_ids[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					);

				}
			}
		}

		// Run each trigger for each registered users.
		foreach ( $attendees as $key => $attendee ) {

			$user = get_user_by( 'email', $attendee['email'] );

			if ( ! empty( $user ) && ! empty( $user->ID ) ) {

				if ( ! empty( $matched_recipe_ids ) ) {

					foreach ( $matched_recipe_ids as $matched_recipe_id ) {

						$args = array(
							'code'             => $this->trigger_code,
							'meta'             => $this->trigger_meta,
							'user_id'          => $user->ID,
							'recipe_to_match'  => $matched_recipe_id['recipe_id'],
							'trigger_to_match' => $matched_recipe_id['trigger_id'],
							'ignore_post_id'   => true,
						);

						$args = Automator()->maybe_add_trigger_entry( $args, false );

						// Save trigger meta
						if ( $args ) {

							foreach ( $args as $result ) {

								if ( true === $result['result'] && $result['args']['trigger_id'] && $result['args']['get_trigger_id'] ) {

									$run_number = Automator()->get->trigger_run_number( $result['args']['trigger_id'], $result['args']['get_trigger_id'], $user->ID );

									$event_book_id_action_meta = array(
										'user_id'        => $user->ID,
										'trigger_id'     => $result['args']['trigger_id'],
										'run_number'     => $run_number, //get run number
										'trigger_log_id' => $result['args']['get_trigger_id'],
										'meta_key'       => $this->trigger_meta,
										'meta_value'     => sprintf( 'EVENT_BOOKING_%d_PENDING', $booking_id ),
									);

									Automator()->insert_trigger_meta( $event_book_id_action_meta );

									// Save the Event Id as trigger meta.
									$event_id_action_meta = array(
										'user_id'        => $user->ID,
										'trigger_id'     => $result['args']['trigger_id'],
										'run_number'     => $run_number, //get run number
										'trigger_log_id' => $result['args']['get_trigger_id'],
										'meta_key'       => 'MEC_EVENT_ID',
										'meta_value'     => sprintf( '%d', $event_id ),
									);

									Automator()->insert_trigger_meta( $event_id_action_meta );

									Automator()->maybe_trigger_complete( $result['args'] );

								}
							}
						}
					}
				}
			}
		}

	}
}
