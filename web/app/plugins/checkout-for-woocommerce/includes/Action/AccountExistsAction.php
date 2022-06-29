<?php

namespace Objectiv\Plugins\Checkout\Action;

/**
 * Class AccountExistsAction
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout\Action
 * @author Brandon Tassone <brandontassone@gmail.com>
 */
class AccountExistsAction extends CFWAction {

	/**
	 * AccountExistsAction constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct( 'account_exists' );
	}

	/**
	 * Checks whether the account exists on the website or not
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function action() {
		$email = $_POST['email'] ?? '';

		$this->out(
			array(
				/**
				 * Filters whether or not an email address has an account
				 *
				 * @since 1.0.0
				 *
				 * @param bool $exists Whether an email exists or not
				 * @param string $email The email address we are checking
				 */
				'account_exists' => (bool) apply_filters( 'cfw_email_exists', email_exists( $email ), $email ),
			)
		);
	}
}
