<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BO_REVOKEACHIEVEMENT_A
 *
 * @package Uncanny_Automator_Pro
 */
class BO_REVOKEACHIEVEMENT_A {

	/**
	 * integration code
	 *
	 * @var string
	 */
	public static $integration = 'BO';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'REVOKEACHIEVEMENT';
		$this->action_meta = 'BOACHIEVEMENT';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {
		$action = array(
			'author'             => Automator()->get_author_name(),
			'support_link'       => Automator()->get_author_support_link(),
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			'is_pro'             => true,
			/* translators: Actions - BadgeOS */
			'sentence'           => sprintf( esc_attr__( 'Revoke {{an achievement:%1$s}} from the user', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Actions - BadgeOS */
			'select_option_name' => esc_attr__( 'Revoke {{an achievement}} from the user', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'revoke_bo_achievement' ),
			'options_group'      => array(
				$this->action_meta => array(
					Automator()->helpers->recipe->badgeos->options->list_bo_award_types(
						esc_attr__( 'Achievement type', 'uncanny-automator-pro' ),
						'BOAWARDTYPES',
						array(
							'token'        => false,
							'is_ajax'      => true,
							'target_field' => $this->action_meta,
							'endpoint'     => 'select_achievements_from_types_REVOKEACHIEVEMENT',
							'include_all'  => true,
						)
					),
					Automator()->helpers->recipe->field->select_field( $this->action_meta, esc_attr__( 'Revoke', 'uncanny-automator-pro' ) ),
				),
			),
		);

		Automator()->register->action( $action );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function revoke_bo_achievement( $user_id, $action_data, $recipe_id ) {
		$achievement_id = $action_data['meta'][ $this->action_meta ];
		if ( empty( $achievement_id ) ) {
			$error_message                       = esc_html__( "The user didn't have the specified achievement.", 'uncanny-automator-pro' );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		if ( $achievement_id === '-1' ) {
			$args = array( 'user_id' => absint( $user_id ) );
		} else {
			$args = array( 'user_id' => absint( $user_id ), 'achievement_id' => absint( $achievement_id ) );
		}
		
		$earned_achievements = badgeos_get_user_achievements( $args );

		if ( ! empty( $earned_achievements ) ) {
			global $wpdb;
			foreach ( $earned_achievements as $key => $earned_achievement ) {
				if ( $earned_achievement->ID === $achievement_id || $achievement_id === '-1' ) {
					$table_name = $wpdb->prefix . 'badgeos_achievements';
					if ( $wpdb->get_var( "show tables like '$table_name'" ) === $table_name ) {
						$wpdb->delete( $table_name, array(
							'user_id'    => $user_id,
							'ID'         => $earned_achievement->ID,
							'sub_nom_id' => $earned_achievement->sub_nom_id,
							'entry_id'   => $earned_achievement->entry_id
						) );
						Automator()->complete_action( $user_id, $action_data, $recipe_id );

						return;
					}
				}
			}
		}
	}

}
