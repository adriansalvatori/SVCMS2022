<?php

namespace Uncanny_Automator_Pro;

/**
 * Class ELEM_SUBMITFIELD
 * @package Uncanny_Automator_Pro
 */
class ELEM_SUBMITFIELD {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'ELEM';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'ELEMSUBMITFIELD';
		$this->trigger_meta = 'ELEMFORM';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name(),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code, 'integration/elementor/' ),
			'is_pro'              => true,
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'sentence'            => sprintf(
				__( 'A user submits {{a form:%1$s}} with {{a specific value:%2$s}} in {{a specific field:%3$s}}', 'uncanny-automator-pro' ),
				$this->trigger_meta,
				'SUBVALUE' . ':' . $this->trigger_meta,
				$this->trigger_code . ':' . $this->trigger_meta
			),
			'select_option_name'  => __( 'A user submits {{a form}} with {{a specific value}} in {{a specific field}}', 'uncanny-automator-pro' ),
			'action'              => 'elementor_pro/forms/new_record',
			'priority'            => 10,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'elem_submit_form' ),
			'options_callback'    => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->trigger( $trigger );
	}

	/**
	 * @return array
	 */
	public function load_options() {
		$options = array(
			'options_group' => array(
				$this->trigger_meta => array(
					Automator()->helpers->recipe->elementor->options->all_elementor_forms(
						null,
						$this->trigger_meta,
						array(
							'token'        => false,
							'is_ajax'      => true,
							'target_field' => $this->trigger_code,
							'endpoint'     => 'select_form_fields_ELEMFORMS',
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
	 * Validation function when the trigger action is hit
	 *
	 * @param $entry_id
	 * @param $form_id
	 */
	public function elem_submit_form( $record, $object ) {

		global $uncanny_automator;
		$recipes = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );

		if ( empty( $record ) ) {
			return;
		}

		$form_id = $record->get_form_settings( 'id' );
		if ( empty( $form_id ) ) {
			return;
		}

		$conditions = $uncanny_automator->helpers->recipe->elementor->pro->match_condition( $record, $form_id, $recipes, $this->trigger_meta, $this->trigger_code, 'SUBVALUE' );

		if ( ! $conditions ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( ! empty( $conditions ) ) {
			foreach ( $conditions['recipe_ids'] as $trigger_id => $recipe_id ) {
				if ( ! $uncanny_automator->is_recipe_completed( $recipe_id, $user_id ) ) {
					$args = array(
						'code'             => $this->trigger_code,
						'meta'             => $this->trigger_meta,
						'recipe_to_match'  => $recipe_id,
						'trigger_to_match' => $trigger_id,
						'ignore_post_id'   => true,
						'user_id'          => $user_id,
					);

					$result = $uncanny_automator->maybe_add_trigger_entry( $args, false );
					do_action( 'automator_save_elementor_form_entry', $record, $recipes, $result );
					if ( $result ) {
						foreach ( $result as $r ) {
							if ( true === $r['result'] ) {
								$uncanny_automator->maybe_trigger_complete( $r['args'] );
							}
						}
					}
				}
			}
		}
	}
}
