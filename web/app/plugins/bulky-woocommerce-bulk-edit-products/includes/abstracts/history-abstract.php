<?php

namespace WCBEditor\Includes\Abstracts;

use WCBEditor\Includes\Products\Handle_Product;
use WCBEditor\Includes\Products\Products;

defined( 'ABSPATH' ) || exit;

class History_Abstract {

	protected static $instance = null;
	protected $wpdb;
	protected $table;
	protected $limit = 5;
	protected $type;

	public function __construct() {
		global $wpdb;
		$this->wpdb  = $wpdb;
		$this->table = $wpdb->prefix . 'vi_wbe_history';

		$this->update_database();
	}

	public static function instance() {
		return self::$instance == null ? self::$instance = new self : self::$instance;
	}

	public function create_database_table() {
		$collate = $this->wpdb->has_cap( 'collation' ) ? $this->wpdb->get_charset_collate() : '';
		$query   = "CREATE TABLE IF NOT EXISTS {$this->table} 
					(
					`id` int(11) NOT NULL AUTO_INCREMENT, 
					`date` int(16) NOT NULL, 
					`user_id` int(11) NOT NULL,
					`history` longtext, 
					`type` varchar(20), 
					PRIMARY KEY (`id`)
					) 
					{$collate}";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $query );
	}

	public function get_remove_history_time() {
		return false;
	}

	public function remove_revision() {
		$remove_time = $this->get_remove_history_time();

		if ( ! $remove_time ) {
			return;
		}

		$time  = current_time( 'U' ) - floatval( $remove_time ) * DAY_IN_SECONDS;
		$query = "delete from {$this->table} where date < %d and type='%s'";
		$this->wpdb->query( $this->wpdb->prepare( $query, $time, $this->type ) );
	}

	public function set( $data ) {
		if ( empty( $data ) ) {
			return;
		}
		$user_id = get_current_user_id();
		$date    = current_time( 'U' );
		$query   = "insert into {$this->table} (user_id, date, history, type) values (%d,%d,%s,%s) ";
		$this->wpdb->query( $this->wpdb->prepare( $query, $user_id, $date, maybe_serialize( $data ), $this->type ) );
	}

	public function get() {
		$query  = "select id,date,user_id from {$this->table} order by id desc limit {$this->limit}";
		$result = $this->wpdb->get_results( $query, ARRAY_A );

		return $result;
	}

	public function count_history_pages() {
		$query  = "select count(id) from {$this->table} where type='{$this->type}'";
		$result = $this->wpdb->get_var( $query );
		$result = ceil( $result / $this->limit );

		return $result;
	}

	public function get_history_by_id( $id ) {
		$query           = "select history,date from {$this->table} where id=%d";
		$result          = $this->wpdb->get_row( $this->wpdb->prepare( $query, $id ) );
		$result->history = maybe_unserialize( $result->history );

		return $result;
	}

	public function get_history_page( $page = 1 ) {
		$offset    = ( $page - 1 ) * $this->limit;
		$query     = "select id,date,user_id from {$this->table} where type='{$this->type}' order by id desc limit {$offset}, {$this->limit}";
		$histories = $this->wpdb->get_results( $query, ARRAY_A );

		if ( ! empty( $histories ) ) {
			foreach ( $histories as $history ) {
				$user = get_user_by( 'ID', $history['user_id'] );
				if ( ! is_object( $user ) ) {
					continue;
				}
				printf( '<tr>
								    <td>%s</td>
								    <td>%s</td>
								    <td class="">
								        <div class="vi-wbe-action-col">
								            <button type="button" class="vi-ui button basic mini vi-wbe-view-history-point" data-id="%s">
								                <i class="icon eye"> </i>
								            </button>
								            <button type="button" class="vi-ui button basic mini vi-wbe-recover" data-id="%s">
								                <i class="icon undo"> </i>
								            </button>
								        </div>
								    </td>
								</tr>',
					esc_html( date_i18n( wc_date_format() . ' ' . wc_time_format(), $history['date'] ) ),
					esc_html( $user->__get( 'display_name' ) ), esc_attr( $history['id'] ), esc_attr( $history['id'] ) );
			}
		}
	}

	public function update_database() {
		if ( ! get_option( 'vi_wbe_update_db_structure_1' ) ) {
			global $wpdb;
			$dbname = DB_NAME;
			$col    = 'type';
			$format = 'varchar(20)';
			$after  = 'history';

			$sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA ='{$dbname}' AND TABLE_NAME = '{$this->table}' AND COLUMN_NAME = '{$col}'";

			$check_exist = $wpdb->query( $sql );

			if ( ! $check_exist ) {
				$sql_add_col = " ALTER TABLE {$this->table} ADD {$col} {$format}  AFTER {$after}";
				$result      = $wpdb->query( $sql_add_col );
				if ( $result ) {
					update_option( 'vi_wbe_update_db_structure_1', true );
				}
			} else {
				update_option( 'vi_wbe_update_db_structure_1', true );

			}
		}

	}
}
