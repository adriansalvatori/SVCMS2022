<?php

namespace Uncanny_Automator_Pro;

/**
 * Class FUSION_SETUSERTAG
 *
 * @package Uncanny_Automator_Pro
 */
class WF_REMOVEUSERTAG {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WF';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'REMOVEUSERTAG';
		$this->action_meta = 'SETUSERVAL';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/wp-fusion/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - WP Fusion */
			'sentence'           => sprintf( __( 'Remove {{a tag:%1$s}} from the user', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - WP Fusion */
			'select_option_name' => __( 'Remove {{a tag}} from the user', 'uncanny-automator-pro' ),
			'priority'           => 11,
			'accepted_args'      => 3,
			'execution_function' => array( $this, 'remove_user_tag' ),
			'options_callback'   => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->action( $action );
	}

	public function load_options() {

		$options = array(
			'options' => array(
				$this->fusion_tags(),
			),
		);

		$options = Automator()->utilities->keep_order_of_options( $options );

		return $options;

	}

	/**
	 * Fusion tags.
	 *
	 * @param string $label
	 * @param string $option_code
	 *
	 * @return mixed
	 */
	public function fusion_tags( $label = '' ) {

		if ( empty( $label ) ) {
			$label = __( 'Tag', 'uncanny-automator' );
		}

		$tags   = wp_fusion()->settings->get( 'available_tags' );
		$option = array(
			'option_code' => $this->action_meta,
			'label'       => $label,
			'input_type'  => 'select',
			'required'    => true,
			'options'     => $tags,
		);

		return apply_filters( 'uap_option_wp_user_roles', $option );
	}

	/**
	 * Validation function when the action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function remove_user_tag( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		if ( ! empty( $user_id ) ) {
			// is the use in DB?
			$contact_id = wp_fusion()->user->get_contact_id( $user_id, true );

			// if not lets add then
			if ( false === $contact_id ) {

				wp_fusion()->user->user_register( $user_id );
			}
			// get tag yo set
			$tag = sanitize_text_field( $action_data['meta'][ $this->action_meta ] );

			// us get_tag_id to id the real ID or return the tag so that this works with all CMS
			$tag = wp_fusion()->user->get_tag_id( $tag );

			$current_tags = wp_fusion()->user->get_tags( $user_id );

			// check we don't have the tag
			if ( in_array( $tag, $current_tags, true ) ) {
				// add tag
				wp_fusion()->user->remove_tags( array( $tag ), $user_id );
			}
		} else {
			$error_msg = $uncanny_automator->error_message->get( 'not-logged-in' );
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_msg );

			return;
		}

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
