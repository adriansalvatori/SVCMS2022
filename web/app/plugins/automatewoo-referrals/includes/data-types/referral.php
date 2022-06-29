<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\DataTypes\AbstractDataType;

defined( 'ABSPATH' ) || exit;

/**
 * @class Data_Type_Referral
 */
class Data_Type_Referral extends AbstractDataType {

	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		return is_a( $item, 'AutomateWoo\Referrals\Referral' );
	}


	/**
	 * @param Referral $item
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
		return Referral_Factory::get( $compressed_item );
	}

}

return new Data_Type_Referral();
