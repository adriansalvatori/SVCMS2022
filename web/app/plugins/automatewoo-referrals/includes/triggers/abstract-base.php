<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;
use AutomateWoo\Customer_Factory;
use AutomateWoo\Data_Layer;

defined( 'ABSPATH' ) || exit;

/**
 * @class Trigger_Abstract
 */
abstract class Trigger_Abstract extends AutomateWoo\Trigger {


	function init() {
		$this->group = __( 'Refer A Friend', 'automatewoo-referrals' );

		// if using signups referrals we don't have any order data
		if ( AW_Referrals()->options()->get_reward_event() === 'signup' ) {
			unset( $this->supplied_data_items[ 'order' ] );
		}
	}


	/**
	 * @since 2.2.0
	 *
	 * @param Referral $referral
	 * @param string   $primary_customer friend|customer
	 *
	 * @return Data_Layer
	 */
	function get_referral_data_layer( $referral, $primary_customer = 'friend' ) {
		$user_id = $primary_customer === 'friend' ? $referral->get_user_id() : $referral->get_advocate_id();
		$data    = [
			'referral' => $referral,
			'advocate' => $referral->get_advocate(),
			'customer' => Customer_Factory::get_by_user_id( $user_id ),
		];

		if ( AW_Referrals()->options()->get_reward_event() === 'purchase' ) {
			$data['order'] = $referral->get_order();
		}

		$data_layer = new Data_Layer( $data );

		// if the advocate is the customer prevent order data from overriding the customer data
		if ( $primary_customer === 'advocate' ) {
			$data_layer->order_belongs_to_customer = false;
		}

		return $data_layer;
	}


	/**
	 * @since 2.2.0
	 *
	 * @return AutomateWoo\Fields\Select
	 */
	function get_primary_customer_field() {
		$field = new AutomateWoo\Fields\Select( false );
		$field->set_name( 'primary_customer' );
		$field->set_title( __( 'Customer', 'automatewoo-referrals' ) );
		$field->set_description( __( "Since a referral has two customers, this field sets which customer's data is used in the workflow. The friend i.e. the referred customer or the advocate i.e. the customer who invited the friend.", 'automatewoo-referrals' ) );
		$field->set_default( 'friend' );
		$field->set_options(
			[
				'friend'   => __( 'Friend (Invited customer)', 'automatewoo-referrals' ),
				'advocate' => __( 'Advocate (Original customer)', 'automatewoo-referrals' ),
			]
		);
		$field->set_required();
		return $field;
	}


	/**
	 * @return AutomateWoo\Fields\Number
	 */
	function get_advocate_limit_field() {
		$field = ( new AutomateWoo\Fields\Number() )
			->set_name( 'limit_per_advocate' )
			->set_title( __( 'Limit per advocate', 'automatewoo-referrals' ) )
			->set_description( __( 'Limit how many times this workflow will ever run for each advocate.', 'automatewoo-referrals' ) )
			->set_placeholder( __( 'Leave blank for no limit', 'automatewoo-referrals' ) );

		return $field;
	}


	/**
	 * @param int $workflow_id
	 * @param int $advocate_id
	 *
	 * @return int
	 */
	function get_times_run_for_advocate( $workflow_id, $advocate_id ) {
		$query = ( new AutomateWoo\Log_Query() )
			->where( 'workflow_id', $workflow_id )
			->where( 'advocate_id', $advocate_id );

		return $query->get_count();
	}


	/**
	 * @param int $limit
	 * @param int $workflow_id
	 * @param int $advocate_id
	 *
	 * @return bool
	 */
	protected function validate_limit_per_advocate( $limit, $workflow_id, $advocate_id ) {
		if ( ! $limit  ) {
			return true;
		}

		if ( $limit <= $this->get_times_run_for_advocate( $workflow_id, $advocate_id ) ) {
			return false;
		}

		return true;
	}

}
