<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WPF_SUBFORM_PAYPALPAYMENT
 *
 * @package Uncanny_Automator_Pro
 */
class ANON_WPF_SUBFORM_PAYPALPAYMENT {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WPF';

	/**
	 * The trigger code.
	 *
	 * @var string
	 */
	private $trigger_code;

	/**
	 * The trigger meta.
	 *
	 * @var string
	 */
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'ANONWPFSUBFORMPAYMENT';
		$this->trigger_meta = 'WPFFORMS';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/wpforms/' ),
			'is_pro'              => true,
			'type'                => 'anonymous',
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - WPForms */
			'sentence'            => sprintf( esc_attr__( '{{A form:%1$s}} is submitted with PayPal payment', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - WPForms */
			'select_option_name'  => esc_attr__( '{{A form}} is submitted with PayPal payment', 'uncanny-automator-pro' ),
			'action'              => 'wpforms_paypal_standard_process_complete',
			'priority'            => 10,
			'accepted_args'       => 4,
			'validation_function' => array( $this, 'wpform_submit' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * @return array
	 */
	public function load_options() {

		$options = array(
			'options' => array(
				Automator()->helpers->recipe->wpforms->options->list_wp_forms(),
			),
		);

		$options = Automator()->utilities->keep_order_of_options( $options );

		return $options;

	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $fields
	 * @param $form_data
	 * @param $payment_id
	 * @param $data
	 */
	public function wpform_submit( $fields, $form_data, $payment_id, $data ) {

		if ( empty( $data['payment_status'] ) || 'completed' !== strtolower( $data['payment_status'] ) ) {
			return;
		}
		if ( empty( $form_data ) ) {
			return;
		}

		$user_id = get_current_user_id();
		$args    = array(
			'code'    => $this->trigger_code,
			'meta'    => $this->trigger_meta,
			'post_id' => intval( $form_data['id'] ),
			'user_id' => $user_id,
		);

		$args = Automator()->process->user->maybe_add_trigger_entry( $args, false );

		//Adding an action to save form submission in trigger meta
		$recipes = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		do_action( 'automator_save_wp_form', $fields, $form_data, $recipes, $args );

		if ( $args ) {
			foreach ( $args as $result ) {
				if ( true === $result['result'] ) {
					Automator()->process->user->maybe_trigger_complete( $result['args'] );
				}
			}
		}
	}
}
