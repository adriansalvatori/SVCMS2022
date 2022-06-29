<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Divi_Helpers;

/**
 * Divi submit form specific field trigger
 */
class DIVI_SUBMITFORMFIELD {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'DIVI';

	/**
	 * Trigger Code
	 *
	 * @var string
	 */
	private $trigger_code;
	/**
	 * Trigger Meta
	 *
	 * @var string
	 */
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'DIVISUBMITFORMFIELD';
		$this->trigger_meta = 'DIVIFORM';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {
		global $uncanny_automator;
		$trigger = array(
			'author'              => Automator()->get_author_name(),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/divi/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'sentence'            => sprintf(
			/* translators: Logged-in trigger - Divi */
				esc_attr__( 'A user submits {{a form:%1$s}} with {{a value:%2$s}} in {{a field:%3$s}}', 'uncanny-automator-pro' ),
				$this->trigger_meta,
				'SUBVALUE:' . $this->trigger_meta,
				$this->trigger_code . ':' . $this->trigger_meta
			),
			/* translators: Logged-in trigger - Divi */
			'select_option_name'  => esc_attr__( 'A user submits {{a form}} with {{a value}} in {{a field}}', 'uncanny-automator-pro' ),
			'action'              => 'et_pb_contact_form_submit',
			'priority'            => 100,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'divi_form_submitted' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * @return array
	 */
	public function load_options() {
		$options = array(
			'options_group' => array(
				$this->trigger_meta => array(
					Automator()->helpers->recipe->divi->options->all_divi_forms(
						null,
						$this->trigger_meta,
						array(
							'token'        => false,
							'is_ajax'      => true,
							'target_field' => $this->trigger_code,
							'endpoint'     => 'select_form_fields_DIVIFORMS',
						)
					),
					Automator()->helpers->recipe->field->select_field( $this->trigger_code, __( 'Field', 'uncanny-automator-pro' ) ),
					Automator()->helpers->recipe->field->text_field( 'SUBVALUE', __( 'Value', 'uncanny-automator-pro' ) ),
				),
			),
		);

		return Automator()->utilities->keep_order_of_options( $options );
	}

	/**
	 * Form submission handler
	 *
	 * @param $fields_values
	 * @param $et_contact_error
	 * @param $contact_form_info
	 */
	public function divi_form_submitted( $fields_values, $et_contact_error, $contact_form_info ) {

		if ( ! is_user_logged_in() ) {
			return;
		}
		if ( true === $et_contact_error ) {
			return;
		}
		// If the form doesn't have the contact_form_unique_id, return
		if ( ! isset( $contact_form_info['contact_form_unique_id'] ) ) {
			return;
		}
		$unique_id  = $contact_form_info['contact_form_unique_id'];
		$post_id    = $contact_form_info['post_id'];
		$form_id    = "$post_id-$unique_id";
		$user_id    = wp_get_current_user()->ID;
		$recipes    = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$conditions = Divi_Pro_Helpers::match_pro_condition( $fields_values, $form_id, $recipes, $this->trigger_meta, $this->trigger_code, 'SUBVALUE' );

		if ( ! $conditions ) {
			return;
		}

		if ( empty( $conditions ) ) {
			return;
		}

		foreach ( $conditions['recipe_ids'] as $recipe_id ) {
			$args = array(
				'code'            => $this->trigger_code,
				'meta'            => $this->trigger_meta,
				'recipe_to_match' => $recipe_id,
				'ignore_post_id'  => true,
				'user_id'         => $user_id,
			);

			$args = Automator()->process->user->maybe_add_trigger_entry( $args, false );
			if ( empty( $args ) ) {
				continue;
			}
			foreach ( $args as $result ) {
				if ( false === $result['result'] ) {
					continue;
				}
				Divi_Helpers::save_tokens( $result, $fields_values, $form_id, $this->trigger_meta, $user_id );

				Automator()->process->user->maybe_trigger_complete( $result['args'] );
			}
		}
	}
}
