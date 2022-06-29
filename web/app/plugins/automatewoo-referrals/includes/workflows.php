<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;

/**
 * Integrate referrals with AutomateWoo workflows
 *
 * @class Workflows
 */
class Workflows {


	/**
	 * Load referral triggers
	 * @param array $triggers
	 * @return array
	 */
	static function triggers( $triggers ) {

		include_once AW_Referrals()->path( '/includes/triggers/abstract-base.php' );
		include_once AW_Referrals()->path( '/includes/triggers/referral-status-changed.php' );
		include_once AW_Referrals()->path( '/includes/triggers/new-referral.php' );
		include_once AW_Referrals()->path( '/includes/triggers/new-invite.php' );

		$triggers['new_referral']            = 'AutomateWoo\Referrals\Trigger_Referral_Status_Changed'; // WRONG TRIGGER NAME
		$triggers['referral_status_changed'] = 'AutomateWoo\Referrals\Trigger_New_Referral'; // WRONG TRIGGER NAME
		$triggers['new_invite']              = 'AutomateWoo\Referrals\Trigger_New_Invite';

		return $triggers;
	}


	/**
	 * @param array $actions
	 * @return array
	 */
	static function actions( $actions ) {
		if ( ! AW_Referrals()->options()->anonymize_invited_emails ) {
			include_once AW_Referrals()->path( '/includes/actions/resend-invite-email.php' );
			$actions['resend_invite_email'] = 'AutomateWoo\Referrals\Action_Resend_Invite_Email';
		}

		return $actions;
	}


	/**
	 * @param array $keys
	 * @return array
	 */
	static function log_data_layer_storage_keys( $keys ) {
		$keys['referral'] = 'referral_id';
		$keys['advocate'] = 'advocate_id';
		return $keys;
	}


	/**
	 * @param $formatted_data array
	 * @param $data_layer AutomateWoo\Data_Layer
	 * @return array
	 */
	static function filter_formatted_data_layer( $formatted_data, $data_layer ) {

		foreach ( $data_layer->get_raw_data() as $data_type => $data_item ) {

			if ( ! $data_item ) {
				continue;
			}

			switch ( $data_type ) {

				case 'referral':
					/** @var Referral $data_item */
					$link = AW_Referrals()->admin->page_url( 'view-referral', $data_item->get_id() );

					$formatted_data[] = [
						'title' => __( 'Referral', 'automatewoo-referrals' ),
						'value' => "<a href='$link'>#{$data_item->get_id()}</a>"
					];

					break;

				case 'advocate':
					/** @var $data_item Advocate */
					$link  = get_edit_user_link( $data_item->get_id() );
					$value = $data_item->get_name();

					if ( ! $value ) {
						$value = $data_item->get_email();
					}

					$formatted_data[] = [
						'title' => __( 'Advocate', 'automatewoo-referrals' ),
						'value' => "<a href='$link'>$value</a>"
					];

					break;
			}
		}

		return $formatted_data;
	}


	/**
	 * @param $vars
	 * @return array
	 */
	static function inject_variables( $vars ) {

		$vars['customer']['referral_widget'] = AW_Referrals()->path( '/includes/variables/customer-referral-widget.php' );

		if ( AW_Referrals()->options()->type === 'coupon' ) {
			$vars['customer']['referral_coupon'] = AW_Referrals()->path( '/includes/variables/customer-referral-coupon.php' );
		} elseif ( AW_Referrals()->options()->type === 'link' ) {
			$vars['customer']['referral_link'] = AW_Referrals()->path( '/includes/variables/customer-referral-link.php' );
		}

		$vars['referral'] = [
			'id' => AW_Referrals()->path( '/includes/variables/referral-id.php' ),
			'status' => AW_Referrals()->path( '/includes/variables/referral-status.php' )
		];

		$vars['advocate'] = [
			'id' => AW_Referrals()->path( '/includes/variables/advocate-id.php' ),
			'email' => AW_Referrals()->path( '/includes/variables/advocate-email.php' ),
			'firstname' => AW_Referrals()->path( '/includes/variables/advocate-firstname.php' ),
			'lastname' => AW_Referrals()->path( '/includes/variables/advocate-lastname.php' ),
			'generate_coupon' => AW_Referrals()->path( '/includes/variables/advocate-generate_coupon.php' )
		];

		if ( AW_Referrals()->options()->type === 'coupon' ) {
			$vars['advocate']['referral_coupon'] = AW_Referrals()->path( '/includes/variables/advocate-referral-coupon.php' );
		} elseif ( AW_Referrals()->options()->type === 'link' ) {
			$vars['advocate']['referral_link'] = AW_Referrals()->path( '/includes/variables/advocate-referral-link.php' );
		}

		return $vars;
	}


	/**
	 * @param $data_types array
	 * @return array
	 */
	static function inject_data_types( $data_types ) {
		$data_types['advocate'] = AW_Referrals()->path( '/includes/data-types/advocate.php' );
		$data_types['referral'] = AW_Referrals()->path( '/includes/data-types/referral.php' );
		return $data_types;
	}

	/**
	 * Inject preview data for referral and advocate data types.
	 *
	 * @param array $data_layer
	 * @param array $required_data_items
	 *
	 * @return array
	 */
	static function inject_preview_data( $data_layer, $required_data_items ) {
		if ( in_array( 'referral', $required_data_items, true ) || in_array( 'advocate', $required_data_items, true ) ) {
			$referral = new Referral();
			$referral->set_id( 1 );
			$referral->set_status( 'approved' );

			$advocate = Advocate_Factory::get( get_current_user_id() );

			$data_layer['referral'] = $referral;
			$data_layer['advocate'] = $advocate;
		}

		return $data_layer;
	}


	/**
	 * @param $rules
	 * @return array
	 */
	static function include_rules( $rules ) {
		$path = AW_Referrals()->path( '/includes/rules/' );

		$rules[ 'advocate_total_referral_count' ]           = $path . 'advocate-total-referral-count.php';
		$rules[ 'advocate_pending_referral_count' ]         = $path . 'advocate-pending-referral-count.php';
		$rules[ 'advocate_approved_referral_count' ]        = $path . 'advocate-approved-referral-count.php';
		$rules[ 'advocate_rejected_referral_count' ]        = $path . 'advocate-rejected-referral-count.php';
		$rules[ 'advocate_potential_fraud_referral_count' ] = $path . 'advocate-potential-fraud-referral-count.php';

		$rules[ 'customer_total_referral_count' ]           = $path . 'customer-total-referral-count.php';
		$rules[ 'customer_pending_referral_count' ]         = $path . 'customer-pending-referral-count.php';
		$rules[ 'customer_approved_referral_count' ]        = $path . 'customer-approved-referral-count.php';
		$rules[ 'customer_rejected_referral_count' ]        = $path . 'customer-rejected-referral-count.php';
		$rules[ 'customer_potential_fraud_referral_count' ] = $path . 'customer-potential-fraud-referral-count.php';

		return $rules;
	}

}
