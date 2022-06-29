<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Recipe;

/**
 * Class AMELIA_APPOINTMENT_BOOKED_SERVICE
 *
 * @package Uncanny_Automator
 */
class AMELIA_APPOINTMENT_BOOKED_SERVICE {

	use Recipe\Triggers;

	/**
	 * Trigger code.
	 *
	 * @var string
	 */
	const TRIGGER_CODE = 'AMELIA_APPOINTMENT_BOOKED_SERVICE';

	/**
	 * Trigger meta.
	 *
	 * @var string
	 */
	const TRIGGER_META = 'AMELIA_APPOINTMENT_BOOKED_SERVICE_META';

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->setup_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function setup_trigger() {

		// Bailout if helpers from base Automator is not found.
		if ( is_null( Automator()->helpers->recipe->ameliabooking ) ) {
			return;
		}

		$this->set_integration( 'AMELIABOOKING' );
		$this->set_trigger_code( self::TRIGGER_CODE );
		$this->set_trigger_meta( self::TRIGGER_META );
		$this->set_trigger_type( 'anonymous' );
		$this->set_is_login_required( false );
		$this->set_is_pro( true );

		// The action hook to attach this trigger into.
		$this->add_action( 'AmeliaBookingAddedBeforeNotify' );

		// The number of arguments that the action hook accepts.
		$this->set_action_args_count( 2 );

		$this->set_sentence(
			sprintf(
				/* Translators: Trigger sentence */
				esc_html__( 'An appointment is booked for {{a specific service:%1$s}}', 'uncanny-automator' ),
				$this->get_trigger_meta()
			)
		);

		$this->set_readable_sentence(
			/* Translators: Trigger sentence */
			esc_html__( 'An appointment is booked for {{a specific service}}', 'uncanny-automator' )
		);

		// Set the options field group.
		$this->set_options_group( $this->get_trigger_option_fields() );
		// Register the trigger.
		$this->register_trigger();

	}

	/**
	 * Validate the trigger.
	 *
	 * Return false if returned booking data is empty.
	 */
	public function validate_trigger( ...$args ) {

		return Automator()->helpers->recipe->ameliabooking->options->validate_trigger( $args );

	}

	/**
	 * Prepare to run.
	 *
	 * Sets the conditional trigger to true.
	 *
	 * @return void.
	 */
	public function prepare_to_run( $data ) {

		$this->set_conditional_trigger( true );

	}

	/**
	 * Trigger conditions.
	 *
	 * Only run the trigger if service is set to 'Any' or if service id is equals to the one set in the recipe.
	 *
	 * @return void.
	 */
	protected function trigger_conditions( $args ) {

		// Grab the returned booking data.
		$booking_data = $args[0];

		// Match 'Any services' condition.
		$this->do_find_any( true );

		// Match specific condition.
		$this->do_find_this( $this->get_trigger_meta() );
		$this->do_find_in( array( $booking_data['appointment']['serviceId'] ) );

	}

	/**
	 * The trigger options fields.
	 *
	 * @return array The field options.
	 */
	public function get_trigger_option_fields() {

		return Automator()->helpers->recipe->ameliabooking->options->get_option_fields(
			$this->get_trigger_code(),
			$this->get_trigger_meta()
		);

	}

	/**
	 * Continue trigger process even for logged-in user.
	 *
	 * @return boolean True.
	 */
	public function do_continue_anon_trigger( ...$args ) {

		return true;

	}

}
