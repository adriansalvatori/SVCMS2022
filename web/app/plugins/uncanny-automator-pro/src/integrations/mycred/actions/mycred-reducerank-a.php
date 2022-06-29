<?php

namespace Uncanny_Automator_Pro;

/**
 * Class MYCRED_REDUCERANK_A
 * @package Uncanny_Automator_Pro
 */
class MYCRED_REDUCERANK_A {

	/**
	 * integration code
	 * @var string
	 */
	public static $integration = 'MYCRED';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'MYCREDREDUCERANK';
		$this->action_meta = 'REDUCERANK';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = [
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/mycred/' ),
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			'is_pro'             => true,
			/* translators: Action - myCred */
			'sentence'           => sprintf( __( "Reduce the user's rank for  {{a specific type of:%1\$s}} points", 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - myCred */
			'select_option_name' => __( "Reduce the user's rank for  {{a specific type of}} points", 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'mycred_reduce_rank' ],
			'options'            => [
				$uncanny_automator->helpers->recipe->mycred->options->pro->list_mycred_points_types_for_ranks(
					__( 'Points type', 'uncanny-automator-pro' ),
					$this->action_meta,
					[
						'token'        => false,
						'is_ajax'      => false,
						'target_field' => $this->action_meta
					]
				)
			],
		];

		$uncanny_automator->register->action( $action );
	}

	/**
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 * @param $args
	 */
	public function mycred_reduce_rank( $user_id, $action_data, $recipe_id, $args ) {
		global $uncanny_automator;

		$points_type = $action_data['meta'][ $this->action_meta ];

		$user_current_rank = mycred_get_users_rank( absint( $user_id ), $points_type );
		$all_ranks         = mycred_get_ranks( 'publish', '-1', 'ASC', $points_type );
		$balance           = mycred_get_users_balance( absint( $user_id ), $points_type );

		if ( isset( $all_ranks ) && ! empty( $all_ranks ) ) {
			foreach ( $all_ranks as $k => $rank ) {
				if ( $user_current_rank->post_id == $rank->post_id && $k === 0 ) {
					$error_msg                 = __( 'User is already at lowest rank.', 'uncanny-automator-pro' );
					$action_data['do-nothing'] = true;
					$action_data['completed']  = true;
					$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_msg );

					return;
				} elseif ( $user_current_rank->post_id == $rank->post_id ) {
					if ( $balance >= $all_ranks[ $k - 1 ]->minimum && $balance <= $all_ranks[ $k - 1 ]->maximum ) {
						mycred_save_users_rank( $user_id, $all_ranks[ $k - 1 ]->post_id, $points_type );
					} else {
						$points = ( $balance >= $all_ranks[ $k - 1 ]->minimum ) ? ( $balance - $all_ranks[ $k - 1 ]->minimum ) : $all_ranks[ $k - 1 ]->minimum - $balance;
						mycred_subtract( $rank->point_type->plural, absint( $user_id ), $points, 'Points reduced to assign rank by uncanny automator action', '', '', $points_type );
						mycred_save_users_rank( $user_id, $all_ranks[ $k - 1 ]->post_id, $points_type );
					}
				}
			}
		}
		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
