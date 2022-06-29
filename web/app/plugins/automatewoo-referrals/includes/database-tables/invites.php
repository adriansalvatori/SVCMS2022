<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Database_Table;

defined( 'ABSPATH' ) || exit;

/**
 * @class Database_Table_Referral_Invites
 */
class Database_Table_Referral_Invites extends Database_Table {


	function __construct() {
		global $wpdb;

		$this->name        = $wpdb->prefix . 'automatewoo_referral_invites';
		$this->primary_key = 'id';
	}


	/**
	 * @return array
	 */
	function get_columns() {
		return [
			'id' => '%d',
			'advocate_id' => '%d',
			'email' => '%s',
			'date' => '%s'
		];
	}


	/**
	 * @return string
	 */
	function get_install_query() {
		return "CREATE TABLE {$this->name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			advocate_id bigint(20) NOT NULL default 0,
			email varchar(255) NOT NULL default '',
			date datetime NULL,
			PRIMARY KEY  (id),
			KEY advocate_id (advocate_id),
			KEY email (email({$this->max_index_length})),
			KEY date (date)
			) {$this->get_collate()};";
	}

}

return new Database_Table_Referral_Invites();
