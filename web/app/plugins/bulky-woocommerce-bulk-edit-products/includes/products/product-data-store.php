<?php

namespace WCBEditor\Includes\Products;

defined( 'ABSPATH' ) || exit;

class Product_Data_Store extends \WC_Product_Data_Store_CPT {
	protected static $instance = null;

	public function __construct() {
	}

	public static function instance() {
		return self::$instance == null ? self::$instance = new self : self::$instance;
	}

	public function get_internal_meta_keys() {
		$this->internal_meta_keys[] = '_button_text';
		$this->internal_meta_keys[] = '_children';
		$this->internal_meta_keys[] = '_product_url';

		return $this->internal_meta_keys;
	}
}