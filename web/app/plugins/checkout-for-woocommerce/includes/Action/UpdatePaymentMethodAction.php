<?php

namespace Objectiv\Plugins\Checkout\Action;

/**
 * Class UpdatePaymentMethodAction
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout\Action
 * @author Brandon Tassone <brandontassone@gmail.com>
 */
class UpdatePaymentMethodAction extends CFWAction {

	/**
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct( 'update_payment_method' );
	}

	/**
	 * Logs in the user based on the information passed. If information is incorrect it returns an error message
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function action() {
		WC()->session->set( 'chosen_payment_method', empty( $_POST['paymentMethod'] ) ? '' : $_POST['paymentMethod'] );

		$this->out(
			array(
				'payment_method' => WC()->session->get( 'chosen_payment_method' ),
			)
		);
	}
}
