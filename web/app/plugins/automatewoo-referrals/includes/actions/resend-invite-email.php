<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Action;
use AutomateWoo\Clean;
use AutomateWoo\Fields;

defined( 'ABSPATH' ) || exit;

class Action_Resend_Invite_Email extends Action {

	public $required_data_items = [ 'advocate' ];

	function init() {
		$this->title       = __( 'Resend Referral Invite Email', 'automatewoo-referrals' );
		$this->description = __( 'Resend a referral invite email from the advocate to a specified email address. For example, this can be used to resend the invite email after a delay.', 'automatewoo-referrals' );
		$this->group       = __( 'Refer A Friend', 'automatewoo-referrals' );
	}


	function load_fields() {

		$email = ( new Fields\Text() )
			->set_name( 'email' )
			->set_title( __( 'Email', 'automatewoo-referrals' ) )
			->set_variable_validation()
			->set_required();

		$this->add_field( $email );
	}


	function run() {

		$advocate = $this->workflow->data_layer()->get_item( 'advocate' );
		$email    = Clean::email( $this->get_option( 'email', true ) );

		if ( ! $advocate || ! $email ) {
			return;
		}

		$mailer = new Invite_Email( $email, $advocate );
		$sent   = $mailer->send( true );

		if ( is_wp_error( $sent ) ) {
			$this->workflow->log_action_email_error( $sent, $this );
		} else {
			$this->workflow->log_action_note( $this, sprintf( __( 'Email successfully sent.', 'automatewoo-referrals' ), $email ) );
		}
	}

}
