<?php

namespace Uncanny_Automator_Pro;

use FluentCrm\App\Models\Lists;
use FluentCrm\App\Models\Subscriber;

/**
 * Class FCRM_REMOVE_USER_LIST
 * @package Uncanny_Automator
 */
class FCRM_REMOVE_USER_LIST {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'FCRM';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'FCMRREMOVELISTUSER';
		$this->action_meta = 'FCRMLIST';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/fluentcrm/' ),
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			'is_pro'             => true,
			/* translators: Action - FluentCRM */
			'sentence'           => sprintf( esc_attr_x( 'Remove the user from {{lists:%1$s}}', 'FluentCRM', 'uncanny-automator' ), $this->action_meta ),
			/* translators: Action - FluentCRM */
			'select_option_name' => esc_attr_x( 'Remove the user from {{lists}}', 'FluentCRM', 'uncanny-automator' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'remove_user_from_list' ),
			'options'            => [
				$uncanny_automator->helpers->recipe->fluent_crm->options->fluent_crm_lists( null, $this->action_meta, [ 'supports_multiple_values' => true ] )
			],
		);

		$uncanny_automator->register->action( $action );
	}


	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function remove_user_from_list( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$lists_to_remove = array_map( 'intval', json_decode( $action_data['meta'][ $this->action_meta ] ) );
		$user_info       = get_userdata( $user_id );

		if ( $user_info ) {
			$subscriber = Subscriber::where( 'email', $user_info->user_email )->first();

			if ( $subscriber ) {

				$existingLists        = $subscriber->lists;
				$existing_lists       = [];
				$existing_list_titles = [];
				foreach ( $existingLists as $list ) {
					if ( in_array( $list->id, $lists_to_remove ) ) {
						$existing_lists[]                  = (int) $list->id;
						$existing_list_titles[ $list->id ] = $list->title;
					}
				}

				$subscriber->detachLists( $lists_to_remove );

				if ( ! array_diff( $lists_to_remove, $existing_lists ) ) {
					// User has all lists that need to be removed
					$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );

					return;
				}

				// No tags to remove
				$action_data['do-nothing']           = true;
				$action_data['complete_with_errors'] = true;

				$lists_not_removed      = [];
				$lists_to_remove_data   = Lists::whereIn( 'id', $lists_to_remove )->get();
				$lists_to_remove_titles = [];

				if ( ! empty( $lists_to_remove_data ) ) {
					foreach ( $lists_to_remove_data as $list ) {
						$lists_to_remove_titles[ $list->id ] = esc_html( $list->title );
					}
				} else {
					$message = sprintf(
					/* translators: 1. List of lists the user is in. */
						_x( 'None of the lists exist', 'FluentCRM', 'uncanny-automator' ),
						implode(
						/* translators: Character to separate items */
							__( ',', 'uncanny-automator' ) . ' ',
							$lists_to_remove
						)
					);
					$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $message );
				}

				foreach ( $lists_to_remove as $list_to_remove ) {
					if ( ! isset( $existing_list_titles[ $list_to_remove ] ) ) {
						$lists_not_removed[] = $lists_to_remove_titles[ $list_to_remove ];
					}
				}

				$message = sprintf(
				/* translators: 1. List of lists the user is in. */
					_x( 'User did not have list(s): %1$s', 'FluentCRM', 'uncanny-automator' ),
					implode(
					/* translators: Character to separate items */
						__( ', ', 'uncanny-automator' ),
						$lists_not_removed
					)
				);

				$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $message );

				return;
			} else {
				// User is not a contact
				$action_data['do-nothing']           = true;
				$action_data['complete_with_errors'] = true;

				$message = sprintf(
				/* translators: 1. The user email */
					_x( 'User is not a contact: %1$s', 'FluentCRM', 'uncanny-automator' ),
					$user_info->user_email
				);

				$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $message );

				return;
			}
		} else {
			// User does not exist
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;

			$message = sprintf(
			/* translators: 1. The user id */
				_x( 'User does not exist: %1$s', 'FluentCRM', 'uncanny-automator' ),
				$user_id
			);

			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $message );

			return;
		}
	}
}
