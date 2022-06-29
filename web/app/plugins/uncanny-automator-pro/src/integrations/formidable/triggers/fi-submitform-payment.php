<?php

namespace Uncanny_Automator_Pro;

/**
 * Class FI_SUBMITFORM_PAYMENT
 * @package Uncanny_Automator_Pro
 */
class FI_SUBMITFORM_PAYMENT {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'FI';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		if ( class_exists( 'FrmPaymentsController' ) ) {
			$this->trigger_code = 'FISUBMITFORMPAYMENT';
			$this->trigger_meta = 'FIFORM';
			$this->define_trigger();
		}
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name(),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/formidable-forms/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* translators: Logged-in trigger - Formidable */
			'sentence'            => sprintf( __( 'A user submits {{a form:%1$s}} with payment', 'uncanny-automator-pro' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - Formidable */
			'select_option_name'  => __( 'A user submits {{a form}} with payment', 'uncanny-automator-pro' ),
			'action'              => 'frm_payment_paypal_ipn',
			'priority'            => 10,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'fi_submit_payment_form' ),
			'options'             => [
				$uncanny_automator->helpers->recipe->formidable->options->all_formidable_forms( null, $this->trigger_meta ),
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param object $params params array.
	 */
	public function fi_submit_payment_form( $params ) {

		if ( isset( $params['pay_vars'] ) && isset( $params['pay_vars']['completed'] ) && 1 == $params['pay_vars']['completed'] ) {
			global $uncanny_automator;
			$entry   = $params['entry'];
			$user_id = $entry->user_id;
			if ( empty( $user_id ) ) {
				return;
			}

			$args = [
				'code'         => $this->trigger_code,
				'meta'         => $this->trigger_meta,
				'post_id'      => intval( $entry->form_id ),
				'user_id'      => intval( $user_id ),
				'is_signed_in' => true,
			];

			$result = $uncanny_automator->maybe_add_trigger_entry( $args, false );

			if ( $result ) {
				foreach ( $result as $r ) {
					if ( true === $r['result'] ) {
						if ( isset( $r['args'] ) && isset( $r['args']['get_trigger_id'] ) ) {
							//Saving form values in trigger log meta for token parsing!
							$fi_args = [
								'trigger_id'     => (int) $r['args']['trigger_id'],
								'meta_key'       => $this->trigger_meta,
								'user_id'        => $user_id,
								'trigger_log_id' => $r['args']['get_trigger_id'],
								'run_number'     => $r['args']['run_number'],
							];

							$uncanny_automator->helpers->recipe->formidable->pro->extract_save_fi_fields( $entry->entry_id, $entry->form_id, $fi_args );
						}
						$uncanny_automator->maybe_trigger_complete( $r['args'] );
					}
				}
			}
		}
	}
}