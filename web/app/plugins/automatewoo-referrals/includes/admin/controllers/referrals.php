<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals\Admin\Controllers;

use AutomateWoo\Exception;
use AutomateWoo\Fields;
use AutomateWoo\Referrals\Referral_Manager;
use AutomateWoo\Referrals\Referrals_List_Table;
use AutomateWoo\Referrals\Referral_Factory;

defined( 'ABSPATH' ) || exit;

/**
 * @class Referrals
 */
class Referrals extends Base {

	function handle() {

		$action = $this->get_current_action();

		switch ( $action ) {

			case 'view':
				$this->output_view_single();
				break;

			case 'save':
				$this->action_save();
				$this->output_view_single();
				break;

			case 'delete':
				$this->action_delete();
				$this->output_view_list();
				break;

			case 'reject':
				$this->action_reject();
				$this->output_view_list();
				break;

			case 'approve':
				$this->action_approve();
				$this->output_view_list();
				break;

			case 'bulk_approved':
			case 'bulk_rejected':
			case 'bulk_pending':
			case 'bulk_potential-fraud':
			case 'bulk_delete':
				$this->action_bulk_edit( str_replace( 'bulk_', '', $action ) );
				$this->output_view_list();
				break;

			default:
				$this->output_view_list();
				break;
		}
	}


	private function output_view_list() {

		require_once AW_Referrals()->admin_path() . '/list-tables/abstract.php';
		require_once AW_Referrals()->admin_path() . '/list-tables/referrals.php';

		$table                 = new Referrals_List_Table();
		$table->controller     = $this;
		$table->nonce_action   = $this->get_nonce_action();
		$table->sections       = $this->get_list_sections();
		$table->section_totals = $this->get_section_totals();

		$this->output_view(
			'page-list-referrals',
			[
				'table' => $table
			]
		);
	}


	/**
	 *
	 */
	private function output_view_single() {

		$referral        = $this->get_referral();
		$field_name_base = 'referral_data';

		$status_field = new Fields\Select( false );
		$status_field
			->set_name_base( $field_name_base )
			->set_name( 'status' )
			->set_title( __( 'Status', 'automatewoo-referrals' ) )
			->set_options( AW_Referrals()->get_referral_statuses() )
			->set_description( __( 'The referral status controls whether the advocate can use any reward credit. Store credit can only be used when the referral is approved.', 'automatewoo-referrals' ) )
			->set_required();

		$reward_amount_field = ( new Fields\Price() )
			->set_name_base( $field_name_base )
			->set_name( 'reward_amount' )
			->set_title( __( 'Amount', 'automatewoo-referrals' ) );

		$reward_amount_remaining_field = ( new Fields\Price() )
			->set_name_base( $field_name_base )
			->set_name( 'reward_amount_remaining' )
			->set_title( __( 'Amount', 'automatewoo-referrals' ) );


		$this->output_view(
			'page-view-referral',
			[
				'referral'                      => $referral,
				'status_field'                  => $status_field,
				'reward_amount_field'           => $reward_amount_field,
				'reward_amount_remaining_field' => $reward_amount_remaining_field
			]
		);
	}


	private function action_delete() {

		$this->verify_nonce_action();

		$referral = $this->get_referral();

		if ( ! $referral ) {
			$this->referral_missing_error();
			return;
		}

		$referral->delete();

		$this->add_message( __( 'Referral successfully deleted.', 'automatewoo-referrals' ) );
	}


	private function action_save() {

		$this->verify_nonce_action();

		$referral = $this->get_referral();

		if ( ! $referral ) {
			$this->referral_missing_error();
			return;
		}

		if ( ! isset( $_POST[ 'referral_data' ] ) ) {
			return;
		}

		$data = $_POST[ 'referral_data' ];

		if ( isset( $data['status'] ) ) {
			$referral->update_status( $data['status'] );
		}

		if ( isset( $data[ 'reward_amount' ] ) ) {
			$referral->set_reward_amount( $data[ 'reward_amount' ] );
		}

		if ( isset( $data[ 'reward_amount_remaining' ] ) ) {
			$referral->set_reward_amount_remaining( $data[ 'reward_amount_remaining' ] );
		}

		$referral->save();

		$this->add_message( __( 'Referral successfully updated.', 'automatewoo-referrals' ) );
	}


	private function action_approve() {

		$this->verify_nonce_action();

		$referral = $this->get_referral();

		if ( ! $referral ) {
			$this->referral_missing_error();
			return;
		}

		if ( $referral->has_status( 'approved' ) )
			return;

		$referral->update_status( 'approved' );

		$this->add_message( __( 'Referral marked as approved.', 'automatewoo-referrals' ) );
	}


	private function action_reject() {

		$this->verify_nonce_action();

		$referral = $this->get_referral();

		if ( ! $referral ) {
			$this->referral_missing_error();
			return;
		}

		if ( $referral->has_status( 'rejected' ) )
			return;

		$referral->update_status( 'rejected' );

		$this->add_message( __( 'Referral marked as rejected.', 'automatewoo-referrals' ) );
	}


	/**
	 * @param $action
	 */
	private function action_bulk_edit( $action ) {

		$this->verify_nonce_action();

		try {
			$ids = $this->get_clean_ids( 'referral_ids' );
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}

		foreach ( $ids as $id ) {

			$referral = Referral_Factory::get( $id );

			if ( ! $referral )
				continue;

			switch ( $action ) {
				case 'approved':
				case 'rejected':
				case 'pending':
				case 'potential-fraud':
					$referral->update_status( $action );
					break;

				case 'delete':
					$referral->delete();
					break;
			}
		}

		$this->add_message( __( 'Bulk edit completed.', 'automatewoo-referrals' ) );
	}


	/**
	 * @return false|\AutomateWoo\Referrals\Referral
	 */
	private function get_referral() {

		$referral_id = absint( aw_request( 'referral_id' ) );

		if ( ! $referral_id )
			return false;

		return Referral_Factory::get( $referral_id );
	}


	private function referral_missing_error() {
		$this->add_error( __( 'Referral could not be found.', 'automatewoo-referrals' ) );
	}


	/**
	 * @param $route
	 * @param \AutomateWoo\Referrals\Referral|bool $referral
	 * @return string
	 */
	function get_route_url( $route = false, $referral = false ) {

		$base_url = admin_url( 'admin.php?page=automatewoo-referrals' );

		if ( ! $route ) {
			return $base_url;
		}

		$args = [
			'action' => sanitize_title( $route ),
			'referral_id' => $referral ? $referral->get_id() : false
		];

		switch ( $args['action'] ) {
			case 'view':
				return add_query_arg( $args, $base_url );
				break;

			case 'delete':
			case 'reject':
			case 'approve':
			case 'save':
				return wp_nonce_url( add_query_arg( $args, $base_url ), $this->get_nonce_action() );
				break;
		}

		return '';
	}


	/**
	 * @return array
	 */
	function get_list_sections() {
		return [
			'' => __( 'All', 'automatewoo-referrals' ),
			'approved' => __( 'Approved', 'automatewoo-referrals' ),
			'rejected' => __( 'Rejected', 'automatewoo-referrals' ),
			'pending' => __( 'Pending', 'automatewoo-referrals' ),
			'potential-fraud' => __( 'Potential Fraud', 'automatewoo-referrals' )
		];
	}


	/**
	 * @return array
	 */
	function get_section_totals() {

		$counts = [];

		foreach ( $this->get_list_sections() as $section_id => $section ) {
			$counts[ $section_id ] = Referral_Manager::get_referrals_count( $section_id );
		}

		return $counts;
	}

}

return new Referrals();
