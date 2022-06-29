<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Database_Table;

defined( 'ABSPATH' ) || exit;

/**
 * @class Database_Table_Referral_Advocate_Keys
 */
class Database_Table_Referral_Advocate_Keys extends Database_Table {


	function __construct() {
		global $wpdb;

		$this->name        = $wpdb->prefix . 'automatewoo_referral_advocate_keys';
		$this->primary_key = 'id';
	}


	/**
	 * @return array
	 */
	function get_columns() {
		return [
			'id' => '%d',
			'advocate_id' => '%d',
			'advocate_key' => '%s',
			'created' => '%s',
		];
	}


	/**
	 * @return string
	 */
	function get_install_query() {
		return "CREATE TABLE {$this->name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			advocate_id bigint(20) NOT NULL default 0,
			advocate_key varchar(100) NOT NULL default '',
			created datetime NULL,
			PRIMARY KEY  (id),
			KEY advocate_id (advocate_id)
			) {$this->get_collate()};";
	}

}

return new Database_Table_Referral_Advocate_Keys();
