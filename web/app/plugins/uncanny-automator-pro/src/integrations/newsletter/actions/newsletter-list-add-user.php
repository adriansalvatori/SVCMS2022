<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

namespace Uncanny_Automator_Pro;

/**
 * Class NEWSLETTER_LIST_ADD_USER
 * @package Uncanny_Automator_Pro
 */
class NEWSLETTER_LIST_ADD_USER {

	// Use Uncanny_Automator core Recipe\Actions Trait.
	use \Uncanny_Automator\Recipe\Actions;

	/**
	 * Class constructor. Setups the action.
	 *
	 * @return void.
	 */
	public function __construct() {
		// Setup our action.
		$this->setup_action();
	}

	/**
	 * Setups our new action.
	 *
	 * @return void.
	 */
	protected function setup_action() {

		$this->set_integration( 'NEWSLETTER' );
		$this->set_action_code( 'NEWSLETTER_CODE' );
		$this->set_action_meta( 'NEWSLETTER_META' );
		$this->set_is_pro( true );

		/* translators: Action - WordPress */
		$this->set_sentence( sprintf( esc_attr__( 'Add the user to {{a list:%1$s}}', 'uncanny-automator-pro' ), $this->get_action_meta() ) );

		/* translators: Action - WordPress */
		$this->set_readable_sentence( esc_attr__( 'Add the user to {{a list}}', 'uncanny-automator-pro' ) );

		// Set the options.
		$options = array(
			array(
				'option_code'              => $this->get_action_meta(),
				/* translators: Email field */
				'label'                    => esc_attr__( 'List(s)', 'uncanny-automator-pro' ),
				'input_type'               => 'select',
				'required'                 => true,
				'supports_multiple_values' => true,
				'options'                  => $this->get_newsletter_list(),
			),
		);

		$this->set_options( $options );

		// Register the action.
		$this->register_action();

	}

	/**
	 * Implement the process_action method.
	 *
	 * @return void.
	 */
	public function process_action( $user_id, $action_data, $recipe_id, $args, $parsed ) {

		// Just bail out if no user id.
		if ( empty( $user_id ) ) {
			return;
		}
		// We convert literal array string to actual array.
		// Example: ["list_1, "list_2"] will be converted to array().
		$recipe_selected_list = trim(
			str_replace(
				array(
					'"',
					"'",
					'[',
					']',
				),
				'',
				sanitize_text_field( $parsed['NEWSLETTER_META'] )
			)
		);

		// Actual coversion after trimming and removal of invalid characters.
		$recipe_selected_list_array = explode( ',', $recipe_selected_list );

		// Trim whatever spaces left to string.
		array_walk(
			$recipe_selected_list_array,
			function ( &$value ) {
				$value = trim( $value );
			}
		);
		$user       = get_user_by( 'ID', $user_id );
		$newsletter = \Newsletter::instance();
		//Check if the user is a "Subscriber" of the Newsletter plugin
		$subscriber_user = $newsletter->get_user( $user->user_email );
		if ( empty( $subscriber_user ) ) {
			// It's not, pass email and name
			$subscriber_user = (object) array(
				'wp_user_id' => $user_id,
				'email'      => $user->user_email,
				'name'       => sprintf( '%s %s', $user->first_name, $user->last_name ),
			);
		}

		// Set value of list to 1 to add the user. 0 to remove.
		// Can also use true of false.
		foreach ( $recipe_selected_list_array as $list ) {
			$subscriber_user->$list = 1;
		}

		// Actually save the record.
		$subscriber = $newsletter->save_user( $subscriber_user );

		// Useful error logging when for some reason the newsletter instance has failed to save the user.
		if ( ! $subscriber ) {
			$action_data['complete_with_errors'] = true;
			$this->set_error_message( esc_html__( 'Failed to save the user to list.', 'uncanny-automator-pro' ) );
		}

		if ( isset( $action_data['complete_with_errors'] ) && true === $action_data['complete_with_errors'] ) {
			// Complete the action with error message if there are errors.
			Automator()->complete->action( $user_id, $action_data, $recipe_id, $this->get_error_message() );

			return;
		}
		// Otherwise, complete the action successfully.
		Automator()->complete->action( $user_id, $action_data, $recipe_id );
	}

	/**
	 * Get the newsletter lists.
	 *
	 * @return $lists array The collection of list.
	 */
	private function get_newsletter_list() {

		$lists = array();

		if ( class_exists( '\Newsletter' ) ) {
			$newsletter_lists = \Newsletter::instance()->get_lists();
			if ( ! empty( $newsletter_lists ) ) {
				foreach ( $newsletter_lists as $list ) {
					$list_id           = sprintf( 'list_%d', $list->id );
					$lists[ $list_id ] = $list->name;
				}
			}
		}

		return $lists;
	}
}
