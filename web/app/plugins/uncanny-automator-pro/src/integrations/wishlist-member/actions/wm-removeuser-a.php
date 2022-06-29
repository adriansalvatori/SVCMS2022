<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WM_REMOVEUSER_A
 * @package Uncanny_Automator_Pro
 */
class WM_REMOVEUSER_A {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WISHLISTMEMBER';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'WMREMOVEUSER';
		$this->action_meta = 'WMMEMBERSHIPLEVELS';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object Add the user to {a membership level}
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = [
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/wishlist-member/' ),
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			'is_pro'             => true,
			/* translators: Action - Wishlist Member */
			'sentence'           => sprintf( esc_attr__( 'Remove the user from {{a membership level:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - Wishlist Member */
			'select_option_name' => esc_attr__( 'Remove the user from {{a membership level}}', 'uncanny-automator-pro' ),
			'priority'           => 99,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'remove_user_to_membership_levels' ],
			'options'            => [
				$uncanny_automator->helpers->recipe->wishlist_member->options->wm_get_all_membership_levels( null,
					$this->action_meta,
					[
						'include_all' => true
					]
				),
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
	public function remove_user_to_membership_levels( $user_id, $action_data, $recipe_id, $args ) {
		global $WishListMemberInstance;
		global $uncanny_automator;

		$level_ids = $WishListMemberInstance->GetMembershipLevels( $user_id );

		if ( is_array( $level_ids ) && count( $level_ids ) == 1 ) {
			$recipe_log_id                       = $action_data['recipe_log_id'];
			$args['do-nothing']                  = true;
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$error_message                       = __( 'The user is not in any membership level.', 'uncanny-automator-pro' );
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message, $recipe_log_id, $args );

			return;
		}

		$wm_level = $action_data['meta'][ $this->action_meta ];

		if ( $wm_level == '-1' ) {
			$all_levels = $WishListMemberInstance->GetOption( 'wpm_levels' );
			if ( is_array( $all_levels ) ) {
				foreach ( $all_levels as $index => $levels ) {
					if ( is_numeric( $levels ) ) {
						unset( $all_levels[ $index ] );
					}
				}

				$level_ids = $all_levels;
			}
		} else {
			if ( ! in_array( $wm_level, $level_ids ) ) {
				$recipe_log_id                       = $action_data['recipe_log_id'];
				$args['do-nothing']                  = true;
				$action_data['do-nothing']           = true;
				$action_data['complete_with_errors'] = true;
				$error_message                       = __( 'The user was not a member of the specified level.', 'uncanny-automator-pro' );
				$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message, $recipe_log_id, $args );

				return;
			} else {
				foreach ( $level_ids as $index => $level ) {
					if ( is_numeric( $level ) && $level == $wm_level ) {
						unset( $level_ids[ $index ] );
					}
				}
			}
		}

		$WishListMemberInstance->SetMembershipLevels( $user_id, $level_ids );
		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );

		return;
	}

}