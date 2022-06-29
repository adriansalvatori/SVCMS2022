<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

class ResursBank {
	public function init() {
		add_action( 'plugins_loaded', array( $this, 'maybe_add_filter' ) );
	}

	public function maybe_add_filter() {
		if ( ! defined( 'RB_WOO_VERSION' ) ) {
			return;
		}

		add_filter( 'cfw_payment_method_li_class', array( $this, 'put_payment_method_class_at_end' ) );
	}

	/**
	 * The ResursBank plugin assumes that the payment method class is at the end of the classname. This function ensures that that is the case.
	 *
	 * @param string $class_string
	 * @return void
	 */
	public function put_payment_method_class_at_end( string $class_string ) {
		if ( preg_match( '/payment_method_[^\s]+$/', $class_string ) ) {
			return $class_string;
		}

		$classes              = explode( ' ', $class_string );
		$ordered_classes      = array();
		$payment_method_class = '';

		foreach ( $classes as $class ) {
			if ( ! preg_match( '/(payment_method_[^\s]+)/', $class ) ) {
				$ordered_classes[] = $class;
				continue;
			}

			$payment_method_class = $class;
		}

		array_push( $ordered_classes, $payment_method_class );

		return join( ' ', array_filter( $ordered_classes ) );
	}
}
