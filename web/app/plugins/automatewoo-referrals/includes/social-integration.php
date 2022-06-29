<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;
use AutomateWoo\Customer_Factory;

/**
 * Class Social_Integration
 */
abstract class Social_Integration {

	/** @var string */
	protected $id;

	/**
	 * @var string
	 */
	protected $button_class = '';

	/**
	 * The color of the share button.
	 *
	 * @var string
	 */
	protected $button_color = '#43454b';


	/**
	 * @param Advocate $advocate
	 * @return string
	 */
	abstract function get_share_url( $advocate );


	/**
	 * @param string $context, browser|email
	 * @return string
	 */
	abstract function get_button_text( $context = 'browser' );


	/**
	 * @param $id
	 */
	function set_id( $id ) {
		$this->id = $id;
	}


	/**
	 * @return string
	 */
	function get_id() {
		return $this->id;
	}


	/**
	 * @return string
	 */
	function get_button_class() {
		return $this->button_class;
	}


	/**
	 * Return button color CSS value.
	 * @return string
	 */
	function get_button_color() {
		return $this->button_color;
	}


	/**
	 * Returns a URL that redirects to the share URL after generating a code and storing the advocate's IP.
	 *
	 * WARNING: Only set advocate for emails, on website pages use the logged in user so page caching is supported.
	 *
	 * @since 2.1
	 *
	 * @param Advocate|false $advocate
	 * @return string
	 */
	function get_redirect_to_share_url( $advocate = false ) {
		$args = [
			'aw-referrals-action' => 'redirect-to-social-share',
			'social' => $this->get_id()
		];

		if ( $advocate ) {
			if ( $customer = Customer_Factory::get_by_user_id( $advocate->get_user_id() ) ) {
				$args['customer'] = $customer->get_key();
			}
		}

		$url = add_query_arg( $args, AW_Referrals()->get_share_page_url() );

		return apply_filters( 'automatewoo/referrals/social_integration/redirect_to_share_url', $url, $this );
	}


}
