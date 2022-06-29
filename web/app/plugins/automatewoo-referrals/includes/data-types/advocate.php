<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\DataTypes\AbstractDataType;

defined( 'ABSPATH' ) || exit;

/**
 * @class Data_Type_Advocate
 */
class Data_Type_Advocate extends AbstractDataType {

	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		return is_a( $item, 'AutomateWoo\Referrals\Advocate' );
	}


	/**
	 * @param Advocate $item
	 * @return mixed
	 */
	function compress( $item ) {
		return $item->get_id();
	}


	/**
	 * @param $compressed_item
	 * @param $compressed_data_layer
	 * @return mixed
	 */
	function decompress( $compressed_item, $compressed_data_layer ) {
		if ( ! $compressed_item ) {
			return false;
		}

		return Advocate_Factory::get( $compressed_item );
	}

}

return new Data_Type_Advocate();
