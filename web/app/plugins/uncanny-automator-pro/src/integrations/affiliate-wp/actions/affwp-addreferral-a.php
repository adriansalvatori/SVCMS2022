<?php

namespace Uncanny_Automator_Pro;

/**
 * Class AFFWP_ADDREFERRAL_A
 * @package Uncanny_Automator_Pro
 */
class AFFWP_ADDREFERRAL_A {

	/**
	 * integration code
	 * @var string
	 */

	public static $integration = 'AFFWP';

	/**
	 * @var string
	 */
	private $action_code;
	/**
	 * @var
	 */
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'ADDAREFERRAL';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = [
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/affiliatewp/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Actions - Affiliate WP */
			'sentence'           => sprintf( __( 'Create a {{referral:%1$s}} for the user', 'uncanny-automator-pro' ),
				$this->action_code ),
			/* translators: Actions - Affiliate WP*/
			'select_option_name' => __( 'Create a referral for the user', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'add_referral' ],
			'options'            => [],
			'options_group'      => [
				$this->action_code => [
					$uncanny_automator->helpers->recipe->field->text_field( 'REFERRALAMOUNT',
						__( 'Amount', 'uncanny-automator-pro' ), true, 'text', '', true ),
					$uncanny_automator->helpers->recipe->field->select_field( 'REFERRALTYPE',
						__( 'Referral Type', 'uncanny-automator-pro' ), [
							'sale'   => _x( 'Sale', 'AffiliateWP', 'uncanny-automator-pro' ),
							'opt-in' => _x( 'Opt-In', 'AffiliateWP', 'uncanny-automator-pro' ),
							'lead'   => _x( 'Lead', 'AffiliateWP', 'uncanny-automator-pro' ),
						], 'sale', false ),
					$uncanny_automator->helpers->recipe->field->text_field( 'REFERRALDESCRIPTION',
						__( 'Description', 'uncanny-automator-pro' ), true, 'text', '', true ),
					$uncanny_automator->helpers->recipe->field->text_field( 'REFERRALREFERENCE',
						__( 'Reference', 'uncanny-automator-pro' ), true, 'text', '', false ),
					$uncanny_automator->helpers->recipe->field->text_field( 'REFERRALCONTEXT',
						__( 'Context', 'uncanny-automator-pro' ), true, 'text', '', false ),
					$uncanny_automator->helpers->recipe->field->select_field( 'REFERRALSTATUS',
						__( 'Status', 'uncanny-automator-pro' ), [
							'unpaid'   => _x( 'Unpaid', 'AffiliateWP', 'uncanny-automator-pro' ),
							'paid'     => _x( 'Paid', 'AffiliateWP', 'uncanny-automator-pro' ),
							'rejected' => _x( 'Rejected', 'AffiliateWP', 'uncanny-automator-pro' ),
							'pending'  => _x( 'Pending', 'AffiliateWP', 'uncanny-automator-pro' )
						], 'unpaid', false ),
					$uncanny_automator->helpers->recipe->field->text_field( 'REFERRALCUSTOM',
						__( 'Custom', 'uncanny-automator-pro' ), true, 'text', '', false,
						esc_html__( 'This action will only run if the user is already an affiliate. The referral date will be set to the date the action is run.', 'uncanny-automator-pro' ) ),
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
	public function add_referral( $user_id, $action_data, $recipe_id, $args ) {
		global $uncanny_automator;

		$affiliate_id = affwp_get_affiliate_id( $user_id );

		if ( $affiliate_id ) {
			$referral['amount']       = $uncanny_automator->parse->text( $action_data['meta']['REFERRALAMOUNT'], $recipe_id, $user_id, $args );
			$referral['custom']       = $uncanny_automator->parse->text( $action_data['meta']['REFERRALCUSTOM'], $recipe_id, $user_id, $args );
			$referral['status']       = $action_data['meta']['REFERRALSTATUS'];
			$referral['context']      = $uncanny_automator->parse->text( $action_data['meta']['REFERRALCONTEXT'], $recipe_id, $user_id, $args );
			$referral['reference']    = $uncanny_automator->parse->text( $action_data['meta']['REFERRALREFERENCE'], $recipe_id, $user_id, $args );
			$referral['description']  = $uncanny_automator->parse->text( $action_data['meta']['REFERRALDESCRIPTION'], $recipe_id, $user_id, $args );
			$referral['type']         = $action_data['meta']['REFERRALTYPE'];
			$referral['affiliate_id'] = $affiliate_id;
			$referral['user_id']      = $user_id;
			$user                     = get_user_by( 'id', $user_id );
			$referral['user_name']    = $user->user_login;

			if ( affwp_add_referral( $referral ) ) {
				$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );

				return;
			} else {
				$recipe_log_id                       = $action_data['recipe_log_id'];
				$args['do-nothing']                  = true;
				$action_data['do-nothing']           = true;
				$action_data['complete_with_errors'] = true;
				$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id,
					__( 'We are unable to add referral.', 'uncanny-automator-pro' ), $recipe_log_id, $args );

				return;
			}

		} else {
			$recipe_log_id                       = $action_data['recipe_log_id'];
			$args['do-nothing']                  = true;
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id,
				__( 'The user is not an affiliate.', 'uncanny-automator-pro' ), $recipe_log_id, $args );

			return;
		}

	}

}