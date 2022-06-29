<?php

namespace Uncanny_Automator_Pro;

/**
 * Class GIVE_ADDNOTES_A
 * @package Uncanny_Automator_Pro
 */
class GIVE_ADDNOTES_A {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'GIVEWP';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'ADDDONORNOTES';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = [
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link($this->action_code,'integration/givewp/'),
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			'is_pro'             => true,
			'requires_user'      => false,
			/* translators: Actions - Give WP */
			'sentence'           => sprintf( __( 'Add a note to {{a donor:%1$s}}', 'uncanny-automator-pro' ), $this->action_code ),
			/* translators: Actions - Give WP */
			'select_option_name' => __( 'Add a note to {{a donor}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'give_add_notes' ],
			'options'            => [],
			'options_group'      => [
				$this->action_code => [
					$uncanny_automator->helpers->recipe->field->text_field( 'EMAIL', __( 'Email', 'uncanny-automator-pro' ), true, 'text', '', true, '' ),
					$uncanny_automator->helpers->recipe->field->text_field( 'NOTES', __( 'Notes', 'uncanny-automator-pro' ), true, 'textarea', '', false, '' ),
				],
			],
		];

		$uncanny_automator->register->action( $action );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function give_add_notes( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		// Email is mandatory. Return error its not valid.
		if ( isset( $action_data['meta']['EMAIL'] ) ) {
			$email = $uncanny_automator->parse->text( $action_data['meta']['EMAIL'], $recipe_id, $user_id, $args );
			if ( ! is_email( $email ) ) {
				$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id, sprintf(
				/* translators: Create a {{donor}} - Error while creating a new donor */
					__( 'Invalid email: %1$s', 'uncanny-automator-pro' )
					, $email ) );
			}
		} else {
			$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id, __( 'Email was not set', 'uncanny-automator-pro' ) );

			return;
		}

		if ( isset( $action_data['meta']['NOTES'] ) && ! empty( $action_data['meta']['NOTES'] ) ) {
			$donor_notes = $uncanny_automator->parse->text( $action_data['meta']['NOTES'], $recipe_id, $user_id, $args );
		}

		$donor = new \Give_Donor( $email, false );
		if ( $donor->id != 0 ) {
			$donor->add_note( $donor_notes );
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );

			return;
		} else {
			$recipe_log_id                       = $action_data['recipe_log_id'];
			$args['do-nothing']                  = true;
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id, __( 'This donor does not exist.', 'uncanny-automator-pro' ), $recipe_log_id, $args );

			return;
		}

	}

}
