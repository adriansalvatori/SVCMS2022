<?php

namespace Uncanny_Automator_Pro;

/**
 * Class MYCRED_REVOKEBADGES_A
 * @package Uncanny_Automator_Pro
 */
class MYCRED_REVOKEBADGES_A {

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
		$this->action_code = 'MYCREDREVOKEBADGE';
		$this->action_meta = 'MYCREDBADGE';
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
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - myCred */
			'sentence'           => sprintf( __( 'Revoke {{a badge:%1$s}} from the user', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - myCred */
			'select_option_name' => __( 'Revoke {{a badge}} from the user', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'revoke_mycred_badge' ],
			'options'            => [],
			'options_group'      => [
				$this->action_meta => [
					$uncanny_automator->helpers->recipe->mycred->options->list_mycred_badges(
						__( 'Badge', 'uncanny-automator-pro' ),
						$this->action_meta,
						[
							'token'        => false,
							'is_ajax'      => true,
							'include_all'  => true,
							'target_field' => $this->action_meta
						]
					)
				]
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
	public function revoke_mycred_badge( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$badge_id = $action_data['meta'][ $this->action_meta ];
		$badges   = mycred_get_users_badges( absint( $user_id ) );

		if ( 'ua-all-mycred-badges' === $badge_id ) {
			if ( is_array( $badges ) && ! empty( $badges ) ) {
				foreach ( $badges as $k => $v ) {
					$meta_key = MYCRED_BADGE_KEY . $k;
					mycred_delete_user_meta( absint( $user_id ), $meta_key );
				}
				mycred_delete_user_meta( $user_id, MYCRED_BADGE_KEY . '_ids' );
			}
		} else {
			if ( is_array( $badges ) && ! empty( $badges ) ) {
				foreach ( $badges as $k => $v ) {
					if ( $k === $badge_id ) {
						unset( $badges[ $k ] );
						break;
					}
				}
			}
			$meta_key = MYCRED_BADGE_KEY . $badge_id;
			mycred_update_user_meta( $user_id, MYCRED_BADGE_KEY . '_ids', '', $badges );
			mycred_delete_user_meta( absint( $user_id ), $meta_key );
		}

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}