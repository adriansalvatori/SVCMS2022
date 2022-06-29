<?php

namespace Uncanny_Automator_Pro;

/**
 * Class ANON_GF_SUBFORM_PAYMENT
 *
 * @package Uncanny_Automator_Pro
 */
class ANON_GF_SUBFORM_PAYMENT {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'GF';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'ANONGFSUBFORMPAYMENT';
		$this->trigger_meta = 'ANONGFFORMS';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/gravity-forms/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'sentence'            => sprintf( esc_attr__( '{{A form:%1$s}} is submitted with payment', 'uncanny-automator-pro' ), $this->trigger_meta ),
			'select_option_name'  => esc_attr__( '{{A form}} is submitted with payment', 'uncanny-automator-pro' ),
			'action'              => 'gform_post_payment_completed',
			'type'                => 'anonymous',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'gform_submit' ),
			'options'             => array(
				Automator()->helpers->recipe->gravity_forms->options->list_gravity_forms( null, $this->trigger_meta ),
			),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $entry
	 * @param $form
	 */
	public function gform_submit( $entry, $action ) {
		if ( empty( $entry ) ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( ! empty( $action ) && isset( $action['type'] ) && $action['type'] === 'complete_payment' ) {
			$form_id   = isset( $entry->form_id ) ? $entry->form_id : $entry['form_id'];
			$pass_args = array(
				'code'    => $this->trigger_code,
				'meta'    => $this->trigger_meta,
				'post_id' => $form_id,
				'user_id' => $user_id,
			);

			$args = Automator()->maybe_add_trigger_entry( $pass_args, false );

			if ( ! empty( $args ) ) {
				foreach ( $args as $result ) {
					if ( true === $result['result'] ) {
						$trigger_meta = array(
							'user_id'        => $user_id,
							'trigger_id'     => $result['args']['trigger_id'],
							'trigger_log_id' => $result['args']['get_trigger_id'],
							'run_number'     => $result['args']['run_number'],
						);

						$trigger_meta['meta_key']   = 'GFENTRYID';
						$trigger_meta['meta_value'] = $entry['id'];
						Automator()->insert_trigger_meta( $trigger_meta );

						$trigger_meta['meta_key']   = 'GFUSERIP';
						$trigger_meta['meta_value'] = maybe_serialize( $entry['ip'] );
						Automator()->insert_trigger_meta( $trigger_meta );

						$trigger_meta['meta_key']   = 'GFENTRYDATE';
						$trigger_meta['meta_value'] = maybe_serialize( \GFCommon::format_date( $entry['date_created'], false, 'Y/m/d' ) );
						Automator()->insert_trigger_meta( $trigger_meta );

						$trigger_meta['meta_key']   = 'GFENTRYSOURCEURL';
						$trigger_meta['meta_value'] = maybe_serialize( $entry['source_url'] );
						Automator()->insert_trigger_meta( $trigger_meta );

						Automator()->maybe_trigger_complete( $result['args'] );
					}
				}
			}
		}
	}
}
