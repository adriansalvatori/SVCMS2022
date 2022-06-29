<?php

namespace Uncanny_Automator_Pro;

/**
 * Class RESTRICT_CONTENT_MEMBERSHIPEXPIRE_A
 * @package Uncanny_Automator_Pro
 */
class RESTRICT_CONTENT_MEMBERSHIPEXPIRE_A {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'RC';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'RCMEMBERSHIPEXPIRE-A';
		$this->action_meta = 'WPCWUNRLCOURSE';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$option_control = $uncanny_automator->helpers->recipe->restrict_content->options->get_membership_levels( null,
			$this->action_meta,
			[ 'any' => true ]
		);
		if ( ! empty( $option_control['options'] ) ) {
			foreach ( $option_control['options'] as $key => $option ) {
				if ( $key == '-1' ) {
					$option_control['options'][ $key ] = __( 'All memberships', 'uncanny-automator-pro' );
				}
			}
		}
		$action = array(
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/restrict-content/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - RESTRICT CONTENT  */
			'sentence'           => sprintf( __( 'Remove the user from {{a membership level:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - RESTRICT CONTENT  */
			'select_option_name' => __( 'Remove the user from {{a membership level}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'rcp_remove_membership' ),
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
	public function rcp_remove_membership( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$plan = $action_data['meta'][ $this->action_meta ];
		if ( '-1' == $plan ) {
			try {
				$customer = rcp_get_customer_by_user_id( $user_id );
				if ( $customer->get_id() ) {
					rcp_disable_customer_memberships( $customer->get_id() );
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
		} else {
			$customer = rcp_get_customer_by_user_id( $user_id );
			if ( $customer->get_id() ) {
				$args = [
					'customer_id' => absint( $customer->get_id() ),
					'number'      => 1,
					'orderby'     => 'id',
					'order'       => 'ASC',
					'object_id'   => $plan,
				];

				$user_memberships = rcp_get_memberships( $args );
				if ( ! empty( $user_memberships ) ) {
					$user_memberships[0]->disable();
				}
			}
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
		}

		return;
	}
}
