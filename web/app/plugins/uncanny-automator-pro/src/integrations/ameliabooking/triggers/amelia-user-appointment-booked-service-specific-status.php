<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Recipe;

/**
 * Class AMELIA_USER_APPOINTMENT_BOOKED_SERVICE_SPECIFIC_STATUS
 *
 * @package Uncanny_Automator
 */
class AMELIA_USER_APPOINTMENT_BOOKED_SERVICE_SPECIFIC_STATUS {

	use Recipe\Triggers;

	/**
	 * Trigger code.
	 *
	 * @var string
	 */
	const TRIGGER_CODE = 'AMELIA_USER_APPOINTMENT_BOOKED_SERVICE_SPECIFIC_STATUS';

	/**
	 * Trigger meta.
	 *
	 * @var string
	 */
	const TRIGGER_META = 'AMELIA_USER_APPOINTMENT_BOOKED_SERVICE_SPECIFIC_STATUS_META';

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {

		// Only enable in Amelia Pro.
		if ( ! defined( 'AMELIA_LITE_VERSION' ) ) {

			$this->setup_trigger();

		}

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
		$this->set_is_pro( true );
		$this->set_is_login_required( true );

		// The action hook to attach this trigger into.
		$this->add_action( array( 'AmeliaBookingStatusUpdated', 'AmeliaBookingCanceled' ) );

		// The number of arguments that the action hook accepts.
		$this->set_action_args_count( 3 );

		$this->set_sentence(
			sprintf(
				/* Translators: Trigger sentence */
				esc_html__( "A user's booking of an appointment for {{a service:%1\$s}} has been changed to {{a specific status:%2\$s}}", 'uncanny-automator' ),
				$this->get_trigger_meta(),
				$this->get_trigger_meta() . '_STATUS'
			)
		);

		$this->set_readable_sentence(
			/* Translators: Trigger sentence */
			esc_html__( "A user's booking of an appointment for {{a service}} has been changed to {{a specific status}}", 'uncanny-automator' )
		);

		// Set the options field group.
		$this->set_options_group( $this->get_trigger_option_fields() );

		$this->set_options(
			array(
				array(
					'input_type'  => 'select',
					'option_code' => $this->get_trigger_meta() . '_STATUS',
					'required'    => true,
					'label'       => esc_html__( 'Status', 'uncanny-automator' ),
					'options'     => array(
						'-1'       => esc_html__( 'Any status', 'uncanny-automator' ),
						'approved' => esc_html__( 'Approved', 'uncanny-automator' ),
						'pending'  => esc_html__( 'Pending', 'uncanny-automator' ),
						'rejected' => esc_html__( 'Rejected', 'uncanny-automator' ),
					),
				),
			)
		);

		// Register the trigger.
		$this->register_trigger();

	}

	/**
	 * The trigger options fields.
	 *
	 * @return array The field options.
	 */
	public function get_trigger_option_fields() {

		$existing_fields = Automator()->helpers->recipe->ameliabooking->options->get_option_fields(
			$this->get_trigger_code(),
			$this->get_trigger_meta()
		);

		return $existing_fields;

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
	 * Validate if trigger matches the condition.
	 *
	 * @param $args
	 *
	 * @return array
	 */
	protected function validate_conditions( $args ) {

		$service_id = isset( $args[0]['serviceId'] ) ? $args[0]['serviceId'] : null;

		$status = isset( $args[0]['status'] ) ? $args[0]['status'] : null;

		$matched_recipe_ids = array();

		if ( empty( $service_id ) || empty( $status ) ) {

			return $matched_recipe_ids;

		}

		$recipes = $this->trigger_recipes();

		if ( empty( $recipes ) ) {

			return $matched_recipe_ids;

		}

		$required_service = Automator()->get->meta_from_recipes( $recipes, $this->get_trigger_meta() );

		$required_status = Automator()->get->meta_from_recipes( $recipes, $this->get_trigger_meta() . '_STATUS' );

		if ( empty( $required_service ) || empty( $required_status ) ) {

			return $matched_recipe_ids;

		}

		foreach ( $recipes as $recipe_id => $recipe ) {

			foreach ( $recipe['triggers'] as $trigger ) {

				$trigger_id = absint( $trigger['ID'] );

				if ( ! isset( $required_service[ $recipe_id ] ) && ! isset( $required_status[ $recipe_id ] ) ) {
					continue;
				}

				if ( ! isset( $required_status[ $recipe_id ][ $trigger_id ] ) && ! isset( $required_status[ $recipe_id ][ $trigger_id ] ) ) {
					continue;
				}

				$is_any_service = intval( '-1' ) === intval( $required_service[ $recipe_id ][ $trigger_id ] );

				$is_any_status = intval( '-1' ) === intval( $required_status[ $recipe_id ][ $trigger_id ] );

				if (
					( $is_any_service || absint( $service_id ) === absint( $required_service[ $recipe_id ][ $trigger_id ] ) ) &&
					( $is_any_status || $status === $required_status[ $recipe_id ][ $trigger_id ] )
				) {
					$matched_recipe_ids[ $recipe_id ] = $trigger_id;
				}
			}
		}

		return $matched_recipe_ids;

	}

}
