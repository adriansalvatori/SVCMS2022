<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Factory;
use AutomateWoo\Cache;

defined( 'ABSPATH' ) || exit;

/**
 * @class Advocate_Key_Factory
 * @since 1.5
 */
class Advocate_Key_Factory extends Factory {

	static $model = 'AutomateWoo\Referrals\Advocate_Key';


	/**
	 * @param $key
	 * @return Advocate_Key|bool
	 */
	static function get_by_key( $key ) {

		if ( ! $key ) return false;

		if ( Cache::exists( $key, 'advocate_key_key' ) ) {
			return static::get( Cache::get( $key, 'advocate_key_key' ) );
		}

		$key_object = new Advocate_Key();
		$key_object->get_by( 'advocate_key', $key );

		if ( ! $key_object->exists ) {
			Cache::set( $key, 0, 'advocate_key_key' );
			return false;
		}

		return $key_object;
	}


	/**
	 * @param Advocate_Key $key_object
	 */
	static function update_cache( $key_object ) {
		parent::update_cache( $key_object );

		Cache::set( $key_object->get_key(), $key_object->get_id(), 'advocate_key_key' );
	}


	/**
	 * @param Advocate_Key $key_object
	 */
	static function clean_cache( $key_object ) {
		parent::clean_cache( $key_object );

		self::clear_cached_prop( $key_object, 'advocate_key', 'advocate_key_key' );
	}

}