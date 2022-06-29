<?php

namespace Objectiv\Plugins\Checkout\Action;

/**
 * @link checkoutwc.com
 * @since 5.4.0
 * @package Objectiv\Plugins\Checkout\Action
 * @author Clifton Griffin <clif@objectiv.co>
 */
class ValidateEmailDomainAction extends CFWAction {
	public function __construct() {
		parent::__construct( 'cfw_validate_email_domain' );
	}

	public function action() {
		if ( empty( $_POST['email'] ) ) {
			$this->out(
				array(
					'message' => 'Invalid email validation request. Must include email.',
				),
				418 // I'm a teapot
			);
		}

		$email_address = sanitize_email( $_POST['email'] );
		$email_domain  = substr( $email_address, strpos( $email_address, '@' ) + 1 );

		// If you don't append dot to the domain, every domain will validate because
		// it will fetch your local MX handler
		$valid = apply_filters( 'cfw_email_domain_valid', checkdnsrr( $email_domain . '.', 'MX' ), $email_domain, $email_address );

		$this->out(
			array(
				// translators: %s is the postcode field label
				'message' => $valid ? '' : __( 'Email address contains invalid domain name.', 'checkout-wc' ),
			),
			$valid ? 200 : 400
		);
	}
}
