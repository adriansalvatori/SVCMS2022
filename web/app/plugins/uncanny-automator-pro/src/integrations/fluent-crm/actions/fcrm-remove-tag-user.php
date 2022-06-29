<?php

namespace Uncanny_Automator_Pro;

use FluentCrm\App\Models\Subscriber;
use FluentCrm\App\Models\Tag;

/**
 * Class FCRM_REMOVE_TAG_USER
 * @package Uncanny_Automator
 */
class FCRM_REMOVE_TAG_USER {

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
		$this->action_code = 'FCMRREMOVETAGUSER';
		$this->action_meta = 'FCRMTAG';
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
			'sentence'           => sprintf( esc_attr_x( 'Remove {{tags:%1$s}} from the user', 'FluentCRM', 'uncanny-automator' ), $this->action_meta ),
			/* translators: Action - FluentCRM */
			'select_option_name' => esc_attr_x( 'Remove {{tags}} from the user', 'FluentCRM', 'uncanny-automator' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'remove_tag_user' ),
			'options'            => [
				$uncanny_automator->helpers->recipe->fluent_crm->options->fluent_crm_tags( null, $this->action_meta, [ 'supports_multiple_values' => true ] )
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
	public function remove_tag_user( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$tags_to_remove = array_map( 'intval', json_decode( $action_data['meta'][ $this->action_meta ] ) );
		$user_info      = get_userdata( $user_id );

		if ( $user_info ) {
			$subscriber = Subscriber::where( 'email', $user_info->user_email )->first();

			if ( $subscriber ) {

				$existingTags        = $subscriber->tags;
				$existing_tags       = [];
				$existing_tag_titles = [];
				foreach ( $existingTags as $tag ) {
					if ( in_array( $tag->id, $tags_to_remove ) ) {
						$existing_tags[]                 = (int) $tag->id;
						$existing_tag_titles[ $tag->id ] = $tag->title;
					}
				}

				$subscriber->detachTags( $tags_to_remove );

				if ( ! array_diff( $tags_to_remove, $existing_tags ) ) {
					// User has all tags that need to be removed
					$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );

					return;
				}

				// No tags to remove
				$action_data['do-nothing']           = true;
				$action_data['complete_with_errors'] = true;

				$tags_not_removed      = [];
				$tags_to_remove_data   = Tag::whereIn( 'id', $tags_to_remove )->get();
				$tags_to_remove_titles = [];

				if ( ! empty( $tags_to_remove_data ) ) {
					foreach ( $tags_to_remove_data as $tag ) {
						$tags_to_remove_titles[ $tag->id ] = esc_html( $tag->title );
					}
				} else {
					$message = sprintf(
					/* translators: 1. List of lists the user is in. */
						_x( 'None of the tags exist', 'FluentCRM', 'uncanny-automator' ),
						implode(
						/* translators: Character to separate items */
							__( ',', 'uncanny-automator' ) . ' ',
							$tags_to_remove
						)
					);
					$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $message );
				}

				foreach ( $tags_to_remove as $tag_to_remove ) {
					if ( ! isset( $existing_tag_titles[ $tag_to_remove ] ) ) {
						$tags_not_removed[] = $tags_to_remove_titles[ $tag_to_remove ];
					}
				}

				$message = sprintf(
				/* translators: 1. List of lists the user is in. */
					_x( 'User did not have tag(s): %1$s', 'FluentCRM', 'uncanny-automator' ),
					implode(
					/* translators: Character to separate items */
						__( ', ', 'uncanny-automator' ),
						$tags_not_removed
					)
				);

				$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $message );

				return;
			} else {
				// User is not a contact
				$action_data['do-nothing']           = true;
				$action_data['complete_with_errors'] = true;
				$message                             = sprintf(
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
			$message                             = sprintf(
			/* translators: 1. The user id */
				_x( 'User does not exist: %1$s', 'FluentCRM', 'uncanny-automator' ),
				$user_id
			);

			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $message );

			return;
		}
	}
}
