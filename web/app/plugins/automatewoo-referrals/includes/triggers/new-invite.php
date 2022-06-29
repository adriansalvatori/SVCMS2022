<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Customer_Factory;

defined( 'ABSPATH' ) || exit;

/**
 * @class Trigger_New_Invite
 */
class Trigger_New_Invite extends Trigger_Abstract {


	function init() {
		$this->supplied_data_items = [ 'advocate' ];

		if ( ! AW_Referrals()->options()->anonymize_invited_emails ) {
			$this->supplied_data_items[] = 'customer';
		}

		$this->title = __( 'New Invite Email Sent', 'automatewoo-referrals' );
		parent::init();

		if ( AW_Referrals()->options()->anonymize_invited_emails ) {
			$this->description = __( "Customer data is not available with this trigger due to your privacy settings.", 'automatewoo-referrals' );
		}
	}


	function register_hooks() {
		add_action( 'automatewoo/referrals/invite/sent', [ $this, 'invite_sent' ] );
	}


	/**
	 * @param Invite $invite
	 */
	function invite_sent( $invite ) {
		$data = [
			'advocate' => Advocate_Factory::get( $invite->get_advocate_id() )
		];

		if ( ! AW_Referrals()->options()->anonymize_invited_emails ) {
			$data[ 'customer' ] = Customer_Factory::get_by_email( $invite->get_email() );
		}

		$this->maybe_run( $data );
	}

}
