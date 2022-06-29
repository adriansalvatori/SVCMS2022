<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Factory;
use AutomateWoo\Temporary_Data;
use AutomateWoo\Clean;
use AutomateWoo\Cache;

defined( 'ABSPATH' ) || exit;

/**
 * @class Referral_Factory
 */
class Referral_Factory extends Factory {

	static $model = 'AutomateWoo\Referrals\Referral';


	/**
	 * @param int $id
	 * @return Referral|bool
	 */
	static function get( $id ) {
		return parent::get( $id );
	}


	/**
	 * @param int $order_id
	 * @return Referral|bool
	 */
	static function get_by_order_id( $order_id ) {
		$order_id = Clean::id( $order_id );

		if ( ! $order_id ) {
			return false;
		}

		if ( Cache::exists( $order_id, 'referral_order_id' ) ) {
			return static::get( Cache::get( $order_id, 'referral_order_id' ) );
		}

		$referral = new Referral();
		$referral->get_by( 'order_id', $order_id );

		if ( ! $referral->exists ) {
			// order has no referral so set this in cache
			Cache::set( $order_id, 0, 'referral_order_id' );
			return false;
		}

		return $referral;
	}


	/**
	 * @param Referral $referral
	 */
	static function update_cache( $referral ) {
		parent::update_cache( $referral );

		if ( $referral->get_order_id() ) {
			Cache::set( $referral->get_order_id(), $referral->get_id(), 'referral_order_id' );
		}
	}


	/**
	 * @param Referral $referral
	 */
	static function clean_cache( $referral ) {
		parent::clean_cache( $referral );

		if ( isset( $referral->original_data['advocate_id'] ) ) {
			Temporary_Data::delete( 'referrals_available_credit', $referral->original_data['advocate_id'] );
		}

		static::clear_cached_prop( $referral, 'order_id', 'referral_order_id' );
	}

}