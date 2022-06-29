<?php

namespace Uncanny_Automator_Pro;

/**
 * Class LF_REMOVEMEMBERSHIP_A
 *
 * @package Uncanny_Automator_Pro
 */
class LF_REMOVEMEMBERSHIP_A {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'LF';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'LFREMOVEMEMBERSHIP-A';
		$this->action_meta = 'LFMEMBERSHIP';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {
		$action = array(
			'author'             => Automator()->get_author_name( $this->action_code ),
			'support_link'       => Automator()->get_author_support_link( $this->action_code, 'integration/lifterlms/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - LifterLMS */
			'sentence'           => sprintf( esc_attr__( 'Remove the user from {{a membership:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - LifterLMS */
			'select_option_name' => esc_attr__( 'Remove the user from {{a membership}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'lf_remove_user_membership' ),
			'options'            => array(
				Automator()->helpers->recipe->lifterlms->options->all_lf_memberships( esc_attr__( 'Membership', 'uncanny-automator' ), $this->action_meta, false, true ),
			),
		);

		Automator()->register->action( $action );
	}


	/**
	 * Validation function when the action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function lf_remove_user_membership( $user_id, $action_data, $recipe_id, $args ) {
		if ( ! function_exists( 'llms_unenroll_student' ) ) {
			$error_message = 'The function llms_unenroll_student does not exist';
			Automator()->complete->action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}
		if ( empty( $user_id ) ) {
			return false;
		}
		$membership_id = $action_data['meta'][ $this->action_meta ];
		if ( empty( $membership_id ) ) {
			return false;
		}

		if ( intval( '-1' ) === intval( $membership_id ) ) {
			$student     = llms_get_student( $user_id );
			$memberships = $student->get_memberships( array( 'limit' => 999 ) );
			if ( isset( $memberships['results'] ) && ! empty( $memberships['results'] ) ) {
				foreach ( $memberships['results'] as $membership ) {
					llms_unenroll_student( $user_id, $membership, 'expired' );
				}
				Automator()->complete->action( $user_id, $action_data, $recipe_id );
			}
		} else {
			llms_unenroll_student( $user_id, $membership_id, 'expired' );
			Automator()->complete->action( $user_id, $action_data, $recipe_id );
		}

	}
}
