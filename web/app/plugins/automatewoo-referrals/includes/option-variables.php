<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo;

/**
 * @class Option_Variables
 * @since 1.3.6
 */
class Option_Variables {

	/** @var Advocate */
	static $advocate;


	static function process( $string, $advocate ) {
		self::$advocate = $advocate;
		$replacer       = new AutomateWoo\Replace_Helper( $string, [ 'AutomateWoo\Referrals\Option_Variables', 'callback' ], 'variables' );
		return $replacer->process();
	}


	/**
	 * @param $variable
	 * @return string
	 */
	static function callback( $variable ) {

		if ( ! self::$advocate ) {
			return '';
		}

		$variable = trim( $variable );
		$return   = false;

		switch ( $variable ) {
			case 'coupon_code':
				$return = self::$advocate->get_shareable_coupon();
				break;

			case 'advocate.first_name':
				$return = self::$advocate->get_first_name();
				break;

			case 'advocate.full_name':
				$return = self::$advocate->get_name();
				break;

			case 'share_url':
				$return = self::$advocate->get_shareable_link();
				break;
		}

		return apply_filters( 'automatewoo/referrals/option_variable_value', $return, $variable );

	}

}
