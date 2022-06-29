<?php

namespace Uncanny_Automator_Pro;

/**
 * Class PMP_ADDUSERTOMEMBERSHIP
 * @package Uncanny_Automator_Pro
 */
class PMP_ADDUSERTOMEMBERSHIP {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'PMP';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'PMPADDMEMBERSHIPLEVEL';
		$this->action_meta = 'ADDUSERTOMEMBERSHIPLEVEL';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$options = $uncanny_automator->helpers->recipe->paid_memberships_pro->options->all_memberships( esc_attr__( 'Membership level', 'uncanny-automator-pro' ), $this->action_meta );
		unset( $options['options']['-1'] );

		$action = array(
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/paid-memberships-pro/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - Paid Memberships Pro */
			'sentence'           => sprintf( esc_attr__( 'Add the user to {{a membership level:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - Paid Memberships Pro */
			'select_option_name' => esc_attr__( 'Add the user to {{a membership level}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'add_user_to_membership_level' ),
			'options'            => array(
				$options,
			),
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
	public function add_user_to_membership_level( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator, $wpdb;

		$membership_level = $action_data['meta'][ $this->action_meta ];
		$current_level    = pmpro_getMembershipLevelForUser( $user_id );

		if ( ! empty( $current_level ) && absint( $current_level->ID ) == absint( $membership_level ) ) {
			$error_msg                           = sprintf( __( 'User is already a member of the specified level.', 'uncanny-automator-pro' ) );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_msg );

			return;
		}

		$pmpro_membership_level = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->pmpro_membership_levels WHERE id = %d", $membership_level ) );

		if ( null === $pmpro_membership_level ) {
			$error_msg                           = sprintf( __( 'Invalid level.', 'uncanny-automator-pro' ) );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_msg );

			return;
		}

		$new_level = null;
		if ( ! empty( $pmpro_membership_level->expiration_number ) ) {

			$start_date = apply_filters( 'uap_pmpro_membership_level_start_date', "'" . current_time( 'mysql' ) . "'", $user_id, $pmpro_membership_level );
			$end_date   = "'" . date_i18n( 'Y-m-d', strtotime( '+ ' . $pmpro_membership_level->expiration_number . ' ' . $pmpro_membership_level->expiration_period, current_time( 'timestamp' ) ) ) . "'";
			$end_date   = apply_filters( 'uap_pmpro_membership_level_end_date', $end_date, $user_id, $pmpro_membership_level, $start_date );

			$level = array(
				'user_id'         => $user_id,
				'membership_id'   => $pmpro_membership_level->id,
				'code_id'         => 0,
				'initial_payment' => 0,
				'billing_amount'  => 0,
				'cycle_number'    => 0,
				'cycle_period'    => 0,
				'billing_limit'   => 0,
				'trial_amount'    => 0,
				'trial_limit'     => 0,
				'startdate'       => $start_date,
				'enddate'         => $end_date,
			);

			$new_level = pmpro_changeMembershipLevel( $level, absint( $user_id ) );
		} else {
			$new_level = pmpro_changeMembershipLevel( absint( $membership_level ), absint( $user_id ) );
		}

		if ( $new_level === true ) {
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );

			return;
		} else {
			$error_msg                           = sprintf( __( "We're unable to assign the specified level to the user.", 'uncanny-automator-pro' ) );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_msg );

			return;
		}
	}

}
