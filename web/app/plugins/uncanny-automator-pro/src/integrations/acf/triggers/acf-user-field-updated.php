<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Recipe;

/**
 * Class ACF_USER_FIELD_UPDATED
 *
 * @package Uncanny_Automator
 */
class ACF_USER_FIELD_UPDATED {

	use Recipe\Triggers;

	/**
	 * Trigger code.
	 *
	 * @var string
	 */
	const TRIGGER_CODE = 'ACF_USER_FIELD_UPDATED';

	/**
	 * Trigger meta.
	 *
	 * @var string
	 */
	const TRIGGER_META = 'ACF_USER_FIELD_UPDATED_META';

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

		$this->set_integration( 'ACF' );

		$this->set_trigger_code( self::TRIGGER_CODE );

		$this->set_trigger_meta( self::TRIGGER_META );

		$this->set_is_pro( true );

		$this->set_is_login_required( true );

		$this->set_sentence(
			sprintf(
				/* Translators: Trigger sentence */
				esc_html__( "A user's {{field:%1\$s}} is updated", 'uncanny-automator' ),
				$this->get_trigger_meta()
			)
		);

		/* Translators: Trigger sentence */
		$this->set_readable_sentence( esc_html__( "A user's {{field}} is updated", 'uncanny-automator' ) ); // Non-active state sentence to show

		// Which do_action() fires this trigger.
		$this->add_action( array( 'updated_user_meta', 'added_user_meta' ) );

		// The number of arguments the action hook is accepting.
		$this->set_action_args_count( 4 );

		$this->set_options_callback( array( $this, 'load_options' ) );

		$this->register_trigger();

	}

	public function load_options() {

		$options = array(
			'options' => array(
				array(
					'input_type'      => 'select',
					'required'        => true,
					'option_code'     => $this->get_trigger_meta(),
					'options'         => Automator()->helpers->recipe->acf->options->get_user_fields(),
					'label'           => esc_html__( 'ACF field', 'uncanny-automator' ),
					'relevant_tokens' => array(),
				),
			),
		);

		$options = Automator()->utilities->keep_order_of_options( $options );

		return $options;

	}

	public function validate_trigger( ...$args ) {

		$args = array_shift( $args );

		if ( ! isset( $args[2] ) ) {
			return false;
		}

		// The $args[2] is the meta key.
		return in_array( $args[2], array_keys( Automator()->helpers->recipe->acf->options->get_user_fields() ), true );

	}

	public function prepare_to_run( $data ) {

		// Set the user to complete with the one we are editing instead of current login user.
		$this->set_user_id( absint( $data[1] ) );

		$this->set_conditional_trigger( true );

	}

	protected function trigger_conditions( $args ) {

		// Match 'Any fields' condition.
		$this->do_find_any( true );

		// Match specific condition.
		$this->do_find_this( $this->get_trigger_meta() );

		// Where.
		$this->do_find_in( $args[2] );

	}

}
