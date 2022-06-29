<?php

namespace Uncanny_Automator_Pro;

use MeprHooks;
use MeprSubscription;
use MeprTransaction;

/**
 * Class MP_REMOVEUSERMEMBERSHIP
 * @package Uncanny_Automator_Pro
 */
class MP_REMOVEUSERMEMBERSHIP {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'MP';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'MPREMOVEUSERMEMBERSHIP';
		$this->action_meta = 'MPUSERMEMBERSHIP';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/memberpress/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - MemberPress */
			'sentence'           => sprintf( __( 'Remove the user from {{a membership:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - MemberPress */
			'select_option_name' => __( 'Remove the user from {{a membership}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'remove_memberships' ),
			'options'            => [
				$uncanny_automator->helpers->recipe->memberpress->pro->all_memberpress_products( __( 'Membership', 'uncanny-automator' ), $this->action_meta, [
					'uo_include_any' => true,
					'uo_any_label'   => __( 'All memberships', 'uncanny-automator' ),
				] ),
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
	public function remove_memberships( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;
		$membership = $uncanny_automator->parse->text( $action_data['meta'][ $this->action_meta ] );
		$user_obj   = get_user_by( 'id', $user_id );
		$table      = MeprSubscription::account_subscr_table(
			'created_at', 'DESC',
			'', '', 'any', '', false,
			array(
				'member'   => $user_obj->user_login,
				'statuses' => array(
					MeprSubscription::$active_str,
					MeprSubscription::$suspended_str,
					MeprSubscription::$cancelled_str,
				),
			),
			MeprHooks::apply_filters( 'mepr_user_subscriptions_query_cols', array( 'id', 'product_id', 'created_at' ) )
		);

		if ( $table['count'] > 0 ) {
			foreach ( $table['results'] as $row ) {
				if ( $row->product_id == $membership || $membership == - 1 ) {
					if ( $row->sub_type == 'subscription' ) {
						$sub = new MeprSubscription( $row->id );
					} else if ( $row->sub_type == 'transaction' ) {
						$sub = new MeprTransaction( $row->id );
					}
					$sub->destroy();
					$member = $sub->user();
					$member->update_member_data();
				}
			}
		}

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
