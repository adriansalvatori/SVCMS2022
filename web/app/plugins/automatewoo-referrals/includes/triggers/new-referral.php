<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;
use AutomateWoo\Clean;
use AutomateWoo\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * @class Trigger_New_Referral
 */
class Trigger_New_Referral extends Trigger_Abstract {

	public $supplied_data_items = [ 'referral', 'advocate', 'customer', 'order' ];


	function init() {
		$this->title = __( 'New Referral Created', 'automatewoo-referrals' );
		parent::init();
	}


	function load_fields() {
		$initial_status = ( new Fields\Select() )
			->set_name( 'status' )
			->set_title( __( 'Initial Status', 'automatewoo-referrals' ) )
			->set_options( AW_Referrals()->get_referral_statuses() )
			->set_description( __( 'The initial status will usually only be either Pending or Potentially Fraudulent.', 'automatewoo-referrals' ) )
			->set_placeholder( __( 'Leave blank for any status', 'automatewoo-referrals' ) )
			->set_multiple();

		$this->add_field( $this->get_primary_customer_field() );
		$this->add_field( $initial_status );
		$this->add_field_recheck_status( 'referral' );
	}


	function register_hooks() {
		add_action( 'automatewoo/referrals/referral_created', [ $this, 'catch_hooks' ], 100, 1 );
	}


	/**
	 * @param Referral $referral
	 */
	function catch_hooks( $referral ) {
		if ( ! $workflows = $this->get_workflows() ) {
			return;
		}

		// since the customer is set at the workflow level, we must loop through the workflow here
		foreach ( $workflows as $workflow ) {
			$primary_customer = Clean::string( $workflow->get_trigger_option( 'primary_customer', 'friend' ) );
			$data             = $this->get_referral_data_layer( $referral, $primary_customer );
			$workflow->maybe_run( $data );
		}
	}


	/**
	 * @param $workflow AutomateWoo\Workflow
	 *
	 * @return bool
	 */
	function validate_workflow( $workflow ) {
		/** @var $referral Referral */
		if ( ! $referral = $workflow->data_layer()->get_item( 'referral' ) ) {
			return false;
		}

		$status = Clean::recursive( $workflow->get_trigger_option( 'status' ) );

		if ( ! $this->validate_status_field( $status, $referral->get_status() ) ) {
			return false;
		}

		return true;
	}


	/**
	 * @param AutomateWoo\Workflow $workflow
	 * @return bool
	 */
	function validate_before_queued_event( $workflow ) {
		/** @var $referral Referral */
		if ( ! $referral = $workflow->data_layer()->get_item( 'referral' ) ) {
			return false;
		}

		$status = Clean::recursive( $workflow->get_trigger_option( 'status' ) );

		// Option to validate order status
		if ( $workflow->get_trigger_option( 'recheck_status_before_queued_run' ) ) {
			if ( ! $this->validate_status_field( $status, $referral->get_status() ) )
				return false;
		}

		return true;
	}

}
