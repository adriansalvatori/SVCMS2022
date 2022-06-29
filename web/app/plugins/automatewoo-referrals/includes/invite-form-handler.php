<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Events;
use AutomateWoo\Clean;
use AutomateWoo\Language;

/**
 * @class Invite_Form_Handler
 * @since 1.2.14
 */
class Invite_Form_Handler {

	/** @var array */
	public $emails = [];

	/** @var array */
	public $errors = [];

	/** @var int */
	public $valid_emails_count = 0;

	/** @var Advocate  */
	public $advocate;


	/**
	 * Handle the email share
	 */
	function handle() {

		$this->emails   = $this->get_emails();
		$this->advocate = $this->get_advocate();

		if ( ! $this->advocate ) {
			return;
		}

		if ( empty( $this->emails ) ) {
			$this->errors[] = __( 'Please enter some email addresses.', 'automatewoo-referrals' );
			return;
		}

		$this->advocate->store_ip(); // update advocate's IP each time they share

		$valid_emails                 = [];
		$ineligible_email_count       = 0;
		$ineligible_email_error_codes = [ 'existing_customer', 'already_referred' ];

		foreach ( $this->emails as $email ) {

			$sharable = $this->is_email_sharable( $email );

			if ( is_wp_error( $sharable ) ) {
				if ( in_array( $sharable->get_error_code(), $ineligible_email_error_codes, true ) ) {
					$ineligible_email_count++;
				} else {
					$this->errors[] = $sharable->get_error_message();
				}
			} else {
				$valid_emails[] = $email;
			}
		}

		// Give a deliberately vague notice for failures due to the email belonging to an existing customer (GDPR).
		if ( $ineligible_email_count ) {
			$this->errors[] = sprintf(
				_n(
					'Unable to send %d invite. The email address may be unsubscribed or not eligible to be referred.',
					'Unable to send %d invites. The email addresses may be unsubscribed or not eligible to be referred.',
					$ineligible_email_count,
					'automatewoo-referrals'
				),
				$ineligible_email_count
			);
		}

		$this->valid_emails_count = count( $valid_emails );
		$this->dispatch( $valid_emails );
	}


	/**
	 * Dispatch share emails
	 * @param $emails
	 */
	function dispatch( $emails ) {
		// If more than 3 emails need to be sent, send them async
		if ( $this->valid_emails_count > 3 ) {
			foreach ( $emails as $email ) {
				\AW()->action_scheduler()->enqueue_async_action(
					'automatewoo/referrals/send_invite_email',
					[
						'email'    => $email,
						'advocate' => $this->advocate->get_id(),
						'language' => Language::get_current(),
					]
				);
			}
		} else {
			foreach ( $emails as $email ) {
				$mailer = new Invite_Email( $email, $this->advocate );
				$result = $mailer->send();

				if ( is_wp_error( $result ) ) {
					$this->errors[] = $result->get_error_message();
				}
			}
		}
	}

	/**
	 * Add errors/success notices
	 */
	function set_response_notices() {
		// if no errors and no emails sent
		if ( empty( $this->errors ) && $this->valid_emails_count === 0 ) {
			$this->errors[] = __( 'Sorry, your emails failed to send.', 'automatewoo-referrals' );
		}

		foreach ( $this->errors as $error ) {
			wc_add_notice( $error, 'error' );
		}

		if ( $this->valid_emails_count > 0 ) {
			wc_add_notice(
				sprintf(
					'<strong>' . _n( 'Success! %d referral email sent.', 'Success! %d referral emails sent.', $this->valid_emails_count, 'automatewoo-referrals' ) . '</strong>',
					$this->valid_emails_count
				)
			);
		}
	}


	/**
	 * @param $email
	 * @return true|\WP_Error
	 */
	function is_email_sharable( $email ) {

		if ( ! is_email( $email ) ) {
			return new \WP_Error( 'invalid_email', sprintf( __( '%s is not a valid email address.', 'automatewoo-referrals' ), "<strong>$email</strong>" ) );
		}

		if ( $email == Clean::email( $this->advocate->get_email() ) ) {
			return new \WP_Error( 'own_email', sprintf( __( 'Referring your own email (%s) is not allowed.', 'automatewoo-referrals' ), $email ) );
		}

		if ( $this->is_existing_customer( $email ) ) {

			if ( apply_filters( 'automatewoo/referrals/block_existing_customer_share', ! AW_Referrals()->options()->allow_existing_customer_referrals, $email ) ) {
				return new \WP_Error( 'existing_customer', sprintf( __( '<strong>%s</strong> is already a customer.', 'automatewoo-referrals' ), $email ) );
			} else {
				// ensure the email has not already been successfully referred
				if ( count( Referral_Manager::get_referrals_by_customer( $email ) ) !== 0 ) {
					return new \WP_Error( 'already_referred', sprintf( __( '<strong>%s</strong> has already been referred.', 'automatewoo-referrals' ), $email ) );
				}
			}
		}

		return true;
	}


	/**
	 * Check if the email belongs to an existing user or a guest who has placed an order
	 * @param $email
	 * @return bool
	 */
	function is_existing_customer( $email ) {

		$user = get_user_by( 'email', $email );

		if ( $user )
			return true;

		$orders = wc_get_orders(
			[
				'type'     => 'shop_order',
				'customer' => $email,
				'limit'    => 1,
				'return'   => 'ids'
			]
		);

		return ! empty( $orders );
	}


	/**
	 * @return array
	 */
	function get_emails() {

		$emails = Clean::recursive( aw_request( 'emails' ) );

		if ( empty( $emails ) )
			return [];

		// handle comma separated textarea
		if ( ! is_array( $emails ) ) {
			$emails = explode( ',', $emails );
		}

		$emails = array_map( [ 'AutomateWoo\Clean', 'email' ], $emails );
		$emails = array_unique( $emails );

		return array_filter( $emails );
	}


	/**
	 * advocate is the current user
	 * @return Advocate|false
	 */
	function get_advocate() {
		return Advocate_Factory::get( get_current_user_id() );
	}

}
