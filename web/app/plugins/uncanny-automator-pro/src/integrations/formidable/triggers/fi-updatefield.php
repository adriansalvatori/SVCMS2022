<?php

namespace Uncanny_Automator_Pro;

use FrmEntryMeta;

/**
 * Class FI_UPDATEFIELD
 * @package Uncanny_Automator_Pro
 */
class FI_UPDATEFIELD {

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
		$this->trigger_code = 'FIUPDATEFIELD';
		$this->trigger_meta = 'FIFORM';
		$this->define_trigger();
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
			'sentence'            => sprintf(
			/* translators: Logged-in trigger - Formidable */
				esc_attr__( 'A user updates an entry in {{a form:%1$s}}', 'uncanny-automator-pro' ),
				$this->trigger_meta
			),
			/* translators: Logged-in trigger - Formidable */
			'select_option_name'  => esc_attr__( 'A user updates an entry in {{a form}}', 'uncanny-automator-pro' ),
			'action'              => 'frm_after_update_entry',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'fi_update_form' ),
			'options'             => array(),
			'options_group'       => array(
				$this->trigger_meta => array(
					$uncanny_automator->helpers->recipe->formidable->options->all_formidable_forms( null, $this->trigger_meta, array(
						'token' => false
					) )
				),
			),
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $id
	 * @param $values
	 */
	public function fi_update_form( $entry_id, $form_id ) {

		global $uncanny_automator;

		$user_id = get_current_user_id();
		if ( empty( $user_id ) ) {
			return;
		}

		$args = array(
			'code'    => $this->trigger_code,
			'meta'    => $this->trigger_meta,
			'post_id' => intval( $form_id ),
			'user_id' => intval( $user_id ),
		);

		$result = $uncanny_automator->maybe_add_trigger_entry( $args, false );

		if ( $result ) {
			foreach ( $result as $r ) {
				if ( true === $r['result'] ) {
					if ( isset( $r['args'] ) && isset( $r['args']['get_trigger_id'] ) ) {
						//Saving form values in trigger log meta for token parsing!
						$fi_args = array(
							'trigger_id'     => (int) $r['args']['trigger_id'],
							'meta_key'       => $this->trigger_meta,
							'user_id'        => $user_id,
							'trigger_log_id' => $r['args']['get_trigger_id'],
							'run_number'     => $r['args']['run_number'],
						);

						$uncanny_automator->helpers->recipe->formidable->extract_save_fi_fields( $entry_id, $form_id, $fi_args );
					}
					$uncanny_automator->maybe_trigger_complete( $r['args'] );
				}
			}
		}
	}
}
