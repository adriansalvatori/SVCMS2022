<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Recipe;

/**
 * Class GK_ENTRY_DISAPPROVED
 *
 * @package Uncanny_Automator_Pro
 */
class GK_ENTRY_DISAPPROVED {
	use Recipe\Triggers;

	/**
	 * Trigger code.
	 *
	 * @var string
	 */
	const TRIGGER_CODE = 'GK_ENTRY_DISAPPROVED';

	/**
	 * Trigger meta.
	 *
	 * @var string
	 */
	const TRIGGER_META = 'GK_ENTRY_METADATA';

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

		$this->set_integration( 'GK' );
		$this->set_trigger_code( self::TRIGGER_CODE );
		$this->set_trigger_meta( self::TRIGGER_META );
		$this->set_trigger_type( 'anonymous' );
		$this->set_support_link( Automator()->get_author_support_link( $this->trigger_code, 'integration/gravity-forms/' ) );
		$this->set_sentence(
			sprintf(
			/* Translators: Trigger sentence */
				esc_attr__( 'An entry for {{a specific form:%1$s}} is rejected', 'uncanny-automator-pro' ),
				$this->get_trigger_meta()
			)
		);

		// Non-active state sentence to show
		$this->set_readable_sentence( esc_attr__( 'An entry for {{a specific form}} is rejected', 'uncanny-automator-pro' ) );

		// Which do_action() fires this trigger.
		$this->add_action( 'gravityview/approve_entries/disapproved' );
		$this->set_action_args_count( 1 );
		$this->set_options(
			array(
				Automator()->helpers->recipe->gravity_forms->options->list_gravity_forms( esc_attr__( 'Form', 'uncanny-automator' ), $this->get_trigger_meta() ),
			)
		);
		$this->register_trigger();

	}

	/**
	 * Validate the trigger.
	 *
	 * @param $args
	 *
	 * @return bool
	 */
	protected function validate_trigger( ...$args ) {
		$args = array_shift( $args );

		if ( empty( $args[0] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Prepare to run the trigger.
	 *
	 * @param $data
	 *
	 * @return void
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
		$matched_recipe_ids = array();
		list ( $entry_id )  = $args;
		if ( empty( $entry_id ) ) {
			return $matched_recipe_ids;
		}

		global $wpdb;
		$form_id = $wpdb->get_var( $wpdb->prepare( "SELECT form_id from {$wpdb->prefix}gf_entry WHERE id=%d", $entry_id ) );

		$recipes = $this->trigger_recipes();
		if ( empty( $recipes ) ) {
			return $matched_recipe_ids;
		}

		$required_form = Automator()->get->meta_from_recipes( $recipes, $this->get_trigger_meta() );
		if ( empty( $required_form ) ) {
			return $matched_recipe_ids;
		}

		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = absint( $trigger['ID'] );
				if ( ! isset( $required_form[ $recipe_id ] ) ) {
					continue;
				}
				if ( ! isset( $required_form[ $recipe_id ][ $trigger_id ] ) ) {
					continue;
				}
				if ( absint( $form_id ) === absint( $required_form[ $recipe_id ][ $trigger_id ] ) ) {
					$matched_recipe_ids[ $recipe_id ] = $trigger_id;
				}
			}
		}

		return $matched_recipe_ids;

	}

	public function do_continue_anon_trigger( ...$args ) {

		return true;

	}
}
