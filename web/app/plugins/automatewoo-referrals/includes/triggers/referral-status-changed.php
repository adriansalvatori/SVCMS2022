<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;
use AutomateWoo\Clean;
use AutomateWoo\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * @class Trigger_Referral_Status_Changed
 */
class Trigger_Referral_Status_Changed extends Trigger_Abstract {

	public $supplied_data_items = [ 'referral', 'advocate', 'customer', 'order' ];


	function init() {
		$this->title = __( 'Referral Status Changed', 'automatewoo-referrals' );
		parent::init();
	}


	function load_fields() {
		$from_status = ( new Fields\Select() )
			->set_name( 'from_status' )
			->set_title( __( 'From status', 'automatewoo-referrals' ) )
			->set_options( AW_Referrals()->get_referral_statuses() )
			->set_placeholder( __( 'Leave blank for any status', 'automatewoo-referrals' ) )
			->set_multiple();

		$to_status = clone $from_status;
		$to_status
			->set_name( 'to_status' )
			->set_title( __( 'To status', 'automatewoo-referrals' ) );

		$this->add_field( $this->get_primary_customer_field() );
		$this->add_field( $from_status );
		$this->add_field( $to_status );
		$this->add_field_recheck_status( 'referral' );
		$this->add_field( $this->get_advocate_limit_field() );
	}


	function register_hooks() {
		add_action( 'automatewoo/referrals/referral_status_changed', [ $this, 'catch_hooks' ], 100, 3 );
	}


	/**
	 * @param Referral $referral
	 * @param string   $old_status
	 * @param string   $new_status
	 */
	function catch_hooks( $referral, $old_status, $new_status ) {
		if ( ! $workflows = $this->get_workflows() ) {
			return;
		}

		AutomateWoo\Temporary_Data::set( 'referral_old_status', $referral->get_id(), $old_status );

		// since the customer is set at the workflow level, we must loop through the workflow here
		foreach ( $workflows as $workflow ) {
			$primary_customer = Clean::string( $workflow->get_trigger_option( 'primary_customer', 'friend' ) );
			$data             = $this->get_referral_data_layer( $referral, $primary_customer );
			$workflow->maybe_run( $data );
		}
	}


	/**
	 * @param AutomateWoo\Workflow $workflow
	 *
	 * @return bool
	 */
	function validate_workflow( $workflow ) {
		/** @var $referral Referral */
		$referral = $workflow->get_data_item( 'referral' );
		/** @var $advocate Advocate */
		$advocate = $workflow->get_data_item( 'advocate' );

		if ( ! $referral ) {
			return false;
		}

		// Get options
		$from_status    = Clean::recursive( $workflow->get_trigger_option( 'from_status' ) );
		$to_status      = Clean::recursive( $workflow->get_trigger_option( 'to_status' ) );
		$advocate_limit = absint( $workflow->get_trigger_option( 'limit_per_advocate' ) );
		$old_status     = AutomateWoo\Temporary_Data::get( 'referral_old_status', $referral->get_id() );

		if ( ! $this->validate_status_field( $from_status, $old_status ) )
			return false;

		if ( ! $this->validate_status_field( $to_status, $referral->get_status() ) )
			return false;

		if ( ! $this->validate_limit_per_advocate( $advocate_limit, $workflow->get_id(), $advocate->get_id() ) )
			return false;

		return true;
	}


	/**
	 * @param AutomateWoo\Workflow $workflow
	 *
	 * @return bool
	 */
	function validate_before_queued_event( $workflow ) {

		$advocate_limit = absint( $workflow->get_trigger_option( 'limit_per_advocate' ) );
		$to_status      = Clean::recursive( $workflow->get_trigger_option( 'to_status' ) );

		/** @var $referral Referral */
		$referral = $workflow->get_data_item( 'referral' );
		/** @var $advocate Advocate */
		$advocate = $workflow->get_data_item( 'advocate' );

		if ( ! $referral || ! $advocate ) {
			return false;
		}

		// Option to validate order status
		if ( $workflow->get_trigger_option( 'recheck_status_before_queued_run' ) ) {
			if ( ! $this->validate_status_field( $to_status, $referral->get_status() ) )
				return false;
		}

		if ( ! $this->validate_limit_per_advocate( $advocate_limit, $workflow->get_id(), $advocate->get_user_id() ) )
			return false;

		return true;
	}

}
