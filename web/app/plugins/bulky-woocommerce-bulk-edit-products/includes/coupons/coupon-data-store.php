<?php

namespace WCBEditor\Includes\Coupons;

defined( 'ABSPATH' ) || exit;

class Coupon_Data_Store extends \WC_Coupon_Data_Store_CPT {
	protected static $instance = null;

	public function __construct() {
	}

	public static function instance() {
		return self::$instance == null ? self::$instance = new self : self::$instance;
	}

	public function get_internal_meta_keys() {
		return $this->internal_meta_keys;
	}
}