<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Database_Table;

defined( 'ABSPATH' ) || exit;

/**
 * @class Database_Table_Referrals
 */
class Database_Table_Referrals extends Database_Table {


	function __construct() {
		global $wpdb;

		$this->name        = $wpdb->prefix . 'automatewoo_referrals';
		$this->primary_key = 'id';
	}


	/**
	 * @return array
	 */
	function get_columns() {
		return [
			'id' => '%d',
			'advocate_id' => '%d',
			'order_id' => '%d',
			'user_id' => '%d',
			'date' => '%s',
			'offer_type' => '%s',
			'offer_amount' => '%s',
			'reward_type' => '%s',
			'reward_amount' => '%s',
			'reward_amount_remaining' => '%s',
			'status' => '%s'
		];
	}


	/**
	 * Creates the database table
	 */
	function get_install_query() {
		return "CREATE TABLE {$this->name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			advocate_id bigint(20) NOT NULL default 0,
			order_id bigint(20) NOT NULL default 0,
			user_id bigint(20) NOT NULL default 0,
			date datetime NULL,
			offer_type varchar(100) NOT NULL default '',
			offer_amount varchar(100) NOT NULL default '',
			reward_type varchar(100) NOT NULL default '',
			reward_amount varchar(100) NOT NULL default '',
			reward_amount_remaining varchar(100) NOT NULL default '',
			status varchar(100) NOT NULL default '',
			PRIMARY KEY  (id),
			KEY advocate_id (advocate_id),
			KEY order_id (order_id),
			KEY user_id (user_id),
			KEY status (status),
			KEY advocate_id_status_reward (advocate_id, status, reward_amount_remaining)
			) {$this->get_collate()};";
	}

}

return new Database_Table_Referrals();
