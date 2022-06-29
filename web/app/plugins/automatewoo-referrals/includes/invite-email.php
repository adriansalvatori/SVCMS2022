<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;

/**
 * @class Invite_Email
 */
class Invite_Email {

	/** @var string */
	public $email;

	/** @var Advocate */
	public $advocate;


	/**
	 * Constructor
	 * @param $email
	 * @param Advocate $advocate
	 */
	function __construct( $email, $advocate ) {
		WC()->mailer(); // wc mailer must be loaded
		$this->email    = $email;
		$this->advocate = $advocate;
	}


	/**
	 * @return string
	 */
	function get_subject() {
		return $this->replace_variables( AW_Referrals()->options()->share_email_subject );
	}


	/**
	 * @return string
	 */
	function get_heading() {
		return $this->replace_variables( AW_Referrals()->options()->share_email_heading );
	}


	/**
	 * @return string
	 */
	function get_content() {
		$content = $this->replace_variables( AW_Referrals()->options()->share_email_body );

		if ( AW_Referrals()->options()->type === 'link' ) {
			$content = $this->make_trackable_urls( $content );
		}

		return $content;
	}


	/**
	 *
	 */
	function get_template() {
		return AW_Referrals()->options()->share_email_template;
	}


	/**
	 *
	 */
	function get_html() {
		$mailer = $this->get_mailer();
		return $mailer->get_email_body();
	}


	/**
	 * @return AutomateWoo\Mailer
	 */
	function get_mailer() {

		$mailer = new AutomateWoo\Mailer();
		$mailer->set_subject( $this->get_subject() );
		$mailer->set_email( $this->email );
		$mailer->set_content( $this->get_content() );
		$mailer->set_template( $this->get_template() );
		$mailer->set_heading( $this->get_heading() );

		return apply_filters( 'automatewoo/referrals/invite_email/mailer', $mailer, $this );
	}


	/**
	 * @param $content string
	 * @return string
	 */
	function replace_variables( $content ) {
		return Option_Variables::process( $content, $this->advocate );
	}


	/**
	 * @param $content string
	 * @return string
	 */
	function make_trackable_urls( $content ) {
		$replacer = new AutomateWoo\Replace_Helper( $content, [ $this, '_callback_trackable_urls' ], 'href_urls' );
		return $replacer->process();
	}


	/**
	 * @param $url
	 *
	 * @return string
	 */
	function _callback_trackable_urls( $url ) {

		if ( ! $url )
			return '';

		$url = add_query_arg(
			[
				AW_Referrals()->options()->share_link_parameter => $this->advocate->get_advocate_key()
			],
			$url
		);

		return 'href="' . $url . '"';
	}


	/**
	 * @param bool $is_resend
	 * @return \WP_Error|true
	 */
	function send( $is_resend = false ) {

		$mailer = $this->get_mailer();
		$sent   = $mailer->send();

		if ( ! is_wp_error( $sent ) && ! $is_resend ) {
			$invite = $this->create_record();
			do_action( 'automatewoo/referrals/invite/sent', $invite );
		}

		return $sent;
	}


	/**
	 * Record each email shared
	 */
	function create_record() {
		$invite = new Invite();
		$invite->set_email( $this->email );
		$invite->set_advocate_id( $this->advocate->get_id() );
		$invite->set_date( new \DateTime() );
		$invite->save();
		return $invite;
	}

}
