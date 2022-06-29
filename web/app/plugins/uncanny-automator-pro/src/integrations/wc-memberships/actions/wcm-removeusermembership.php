<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WCM_REMOVEUSERMEMBERSHIP
 * @package Uncanny_Automator_Pro
 */
class WCM_REMOVEUSERMEMBERSHIP {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WCMEMBERSHIPS';

	private $action_code;
	private $action_meta;

	/**
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		$this->action_code = 'WCMREMOVEUSERMEMBERSHIP_A';
		$this->action_meta = 'WCMMEMBERSHIPPLAN';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$option_control = $uncanny_automator->helpers->recipe->wc_memberships->options->wcm_get_all_membership_plans( null, $this->action_meta, [ 'is_any' => true ] );
		if ( ! empty( $option_control['options'] ) ) {
			foreach ( $option_control['options'] as $key => $option ) {
				if ( $key == '-1' ) {
					$option_control['options'][ $key ] = __( 'All membership plans', 'uncanny-automator-pro' );
				}
			}
		}
		$action = array(
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/woocommerce-memberships/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - WooCommerce Memberships */
			'sentence'           => sprintf( __( 'Remove the user from {{a membership plan:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - WooCommerce Memberships */
			'select_option_name' => __( 'Remove the user from {{a membership plan}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'remove_user_membership' ),
			'options'            => [
				$option_control,
			],
		);

		$uncanny_automator->register->action( $action );
	}

	/**
	 * Validation function when the action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function remove_user_membership( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$plan = $action_data['meta'][ $this->action_meta ];
		if ( '-1' == $plan ) {
			$user_all_memberships = wc_memberships_get_user_memberships( $user_id );
			if ( empty( $user_all_memberships ) ) {
				$recipe_log_id             = $action_data['recipe_log_id'];
				$args['do-nothing']        = true;
				$action_data['do-nothing'] = true;
				$action_data['completed']  = true;
				$error_message             = esc_attr__( 'The user was not a member of any membership plans.', 'uncanny-automator-pro' );
				$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message, $recipe_log_id, $args );
			} else {
				try {
					foreach ( $user_all_memberships as $membership ) {
						wp_delete_post( $membership->post->ID );
					}
					$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
				} catch ( \Exception $e ) {
					$error_message                       = $e->getMessage();
					$recipe_log_id                       = $action_data['recipe_log_id'];
					$args['do-nothing']                  = true;
					$action_data['do-nothing']           = true;
					$action_data['complete_with_errors'] = true;
					$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message, $recipe_log_id, $args );
				}
			}
		} else {
			$check_membership_plan = wc_memberships_is_user_member( $user_id, $plan );
			if ( true !== $check_membership_plan ) {
				$recipe_log_id             = $action_data['recipe_log_id'];
				$args['do-nothing']        = true;
				$action_data['do-nothing'] = true;
				$action_data['completed']  = true;
				$error_message             = esc_attr__( 'The user was not a member of the specified membership plan.', 'uncanny-automator-pro' );
				$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message, $recipe_log_id, $args );
			} else {
				try {
					$user_membership = wc_memberships_get_user_membership( $user_id, $plan );
					wp_delete_post( $user_membership->post->ID );
					$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
				} catch ( \Exception $e ) {
					$error_message                       = $e->getMessage();
					$recipe_log_id                       = $action_data['recipe_log_id'];
					$args['do-nothing']                  = true;
					$action_data['do-nothing']           = true;
					$action_data['complete_with_errors'] = true;
					$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message, $recipe_log_id, $args );
				}
			}
		}

		return;
	}
}
