<?php
/**
 * Liker
 * Liker helps you rate and like articles on a website and keep track of results.
 * Exclusively on https://1.envato.market/liker
 *
 * @encoding        UTF-8
 * @version         2.2.3
 * @copyright       (C) 2018 - 2022 Merkulove ( https://merkulov.design/ ). All rights reserved.
 * @license         Envato License https://1.envato.market/KYbje
 * @contributors    Nemirovskiy Vitaliy (nemirovskiyvitaliy@gmail.com), Dmitry Merkulov (dmitry@merkulov.design)
 * @support         help@merkulov.design
 **/

namespace Merkulove\Liker;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

use Merkulove\Liker\Unity\Settings;

/**
 * SINGLETON: Class adds admin styles.
 * @since 1.0.0
 **/
final class Request {

	/**
	 * The one true Request.
	 *
	 * @var Request
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * @var array
	 */
	public static $empty_like = [
		'positive' => 0,
		'neutral' => 0,
		'negative' => 0,
		'amount' => 0
	];

	/**
	 * Sets up a new Request instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

	}

	/**
	 * Insert new like value.
	 *
	 * @param $liker_id
	 * @param $val_1
	 * @param $val_2
	 * @param $val_3
	 * @param $user_ip
	 * @param $guid
	 * @param $created
	 * @param $modified
	 * @param bool $limit
	 *
	 * @return array
	 */
	public function insert_like( $liker_id, $val_1, $val_2, $val_3, $user_ip, $guid, $created, $modified, $limit = true )
	{

		// Dont add vote if limit by ip is reached
		if ( ! $limit ) {

			$options = Settings::get_instance()->options;
			return [ 'status' => false ,'message' => $options[ 'limit_msg' ], 'wpdb' => 0 ];

		}

		global $wpdb;

		// Store vote in database
		$wpdb_callback = $wpdb->insert(
			$wpdb->liker,
			[
				'liker_id' => $liker_id,
				'val_1' => $val_1,
				'val_2' => $val_2,
				'val_3' => $val_3,
				'ip' => $user_ip,
				'guid' => $guid,
				'created' => $created,
				'modified' => $modified
			],
			[
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s'
			]
		);

		// Store vote in post meta
		PostMeta::get_instance()->update_liker_meta( $liker_id );

		return [ 'status' => true, 'message' => 'Insert', 'wpdb' => $wpdb_callback ];

	}

	/**
	 * Update existing like value.
	 *
	 * @param $liker_id
	 * @param $val_1
	 * @param $val_2
	 * @param $val_3
	 * @param $user_ip
	 * @param $guid
	 * @param $created
	 * @param $modified
	 * @param bool $revoting
	 * @param bool $limit
	 *
	 * @return array
	 */
	public function update_like( $liker_id, $val_1, $val_2, $val_3, $user_ip, $guid, $created, $modified, $revoting = false, $limit = true )
	{

		if ( $val_1 + $val_2 + $val_3 > 0 ) {

			$request_callback = $this->change_like( $liker_id, $val_1, $val_2, $val_3, $user_ip, $guid, $created, $modified, $revoting, $limit );

		} else {

			$request_callback = $this->delete_like( $liker_id, $val_1, $val_2, $val_3, $user_ip, $guid, $created, $modified, $revoting, $limit );

		}

		// Store vote in post meta
		PostMeta::get_instance()->update_liker_meta( $liker_id );

		return $request_callback;

	}

	/**
	 * Change existing like value or insert new
	 *
	 * @param $liker_id
	 * @param $val_1
	 * @param $val_2
	 * @param $val_3
	 * @param $user_ip
	 * @param $guid
	 * @param $created
	 * @param $modified
	 * @param bool $revoting
	 * @param bool $limit
	 *
	 * @return array
	 */
	public function change_like( $liker_id, $val_1, $val_2, $val_3, $user_ip, $guid, $created, $modified, $revoting = false, $limit = true )
	{

		global $wpdb;

		// Store vote in database
		$wpdb_callback = $wpdb->update(

			$wpdb->liker,
			[
				'val_1' => $val_1,
				'val_2' => $val_2,
				'val_3' => $val_3,
				'modified' => $modified
			],
			[
				'liker_id' => $liker_id,
				'ip' => $user_ip,
				'guid' => $guid
			],
			[
				'%d',
				'%d',
				'%d',
				'%s',
				'%s'
			],
			[
				'%d',
				'%s',
				'%s'
			]

		);

		if ( $wpdb_callback === 0 ) {

			if ( $limit ) {

				$callback = $this->insert_like( $liker_id, $val_1, $val_2, $val_3, $user_ip, $guid, $created, $modified, $limit );

			} else {

				$options = Settings::get_instance()->options;
				$callback = [ 'status' => false, 'message' => $options[ 'limit_msg' ], 'wpdb' => $wpdb_callback ];

			}

		} else {

			$callback = [ 'status' => true, 'message' => 'Change', 'wpdb' => $wpdb_callback ];

		}

		// Store vote in post meta
		PostMeta::get_instance()->update_liker_meta( $liker_id );

		return $callback;

	}

	/**
	 * Delete existing like value or insert new
	 *
	 * @param $liker_id
	 * @param $val_1
	 * @param $val_2
	 * @param $val_3
	 * @param $user_ip
	 * @param $guid
	 * @param $created
	 * @param $modified
	 * @param false $revoting
	 * @param bool $limit
	 *
	 * @return array
	 */
	public function delete_like( $liker_id, $val_1, $val_2, $val_3, $user_ip, $guid, $created, $modified, $revoting = false, $limit = true )
	{

		global $wpdb;

		// Remove vote
		$wpdb_callback = $wpdb->delete(
			$wpdb->liker,
			[
				'liker_id' => $liker_id,
				'ip' => $user_ip,
				'guid' => $guid
			],
			[
				'%d',
				'%s',
				'%s'
			]
		);

		// Insert re-voted vote
		if ( $wpdb_callback === 0 && $revoting ) {

			$callback = $this->insert_like( $liker_id, $val_1, $val_2, $val_3, $user_ip, $guid, $created, $modified, $limit );

		} else {

			$callback = [ 'status' => true, 'message' => 'Delete', 'wpdb' => $wpdb_callback ];

		}

		// Store vote in post meta
		PostMeta::get_instance()->update_liker_meta( $liker_id );

		return $callback;

	}

	/**
	 * Get liker information from database by liker_id
	 *
	 * @param $liker_id
	 *
	 * @return array|mixed
	 */
	public function get_likes( $liker_id )
	{

		global $wpdb;

		/** @noinspection SqlDialectInspection */
		/** @noinspection SqlNoDataSourceInspection */
		$result = $wpdb->get_results(
			$wpdb->prepare("
                SELECT $wpdb->liker.liker_id,
                   SUM( $wpdb->liker.val_1 ) as positive, 
                   SUM( $wpdb->liker.val_2 ) as neutral, 
                   SUM( $wpdb->liker.val_3 ) as negative, 
                   ( SUM( $wpdb->liker.val_1 ) - SUM( $wpdb->liker.val_3 ) ) AS amount 
                FROM $wpdb->liker
                WHERE $wpdb->liker.liker_id = %d
                GROUP BY $wpdb->liker.liker_id", [ $liker_id ] )
		);

		return is_array( $result ) &&  isset( $result[ 0 ] ) ? $result[ 0 ] : [];

	}

	/**
	 * Get likes data.
	 *
	 * @param $liker_id
	 *
	 * @return array
	 **/
	public function get_likes_data( $liker_id ) {

		global $wpdb;

		/** @noinspection SqlDialectInspection */
		/** @noinspection SqlNoDataSourceInspection */
		$result = $wpdb->get_results(
			$wpdb->prepare("
                SELECT $wpdb->liker.liker_id, SUM( $wpdb->liker.val_1 ) AS cnt_val_1, SUM( $wpdb->liker.val_2 ) AS cnt_val_2, SUM( $wpdb->liker.val_3 ) AS cnt_val_3
                FROM $wpdb->liker
                WHERE $wpdb->liker.liker_id = %d
                GROUP BY $wpdb->liker.liker_id", [$liker_id] )
		);

		// Return values if data exist
		if ( count( $result ) > 0 ) {

			$res[1] = $result[0]->cnt_val_1;
			$res[2] = $result[0]->cnt_val_2;
			$res[3] = $result[0]->cnt_val_3;

			// Returns empty array if no data in DB
		} else {

			$res = array();

		}

		return $res;

	}

	/**
	 * Get all likes in object
	 * @return array|object|null
	 */
	public function get_all_likes()
	{

		// Get rating for each of post that has a rating record in the data base
		global $wpdb;

		/** @noinspection SqlDialectInspection */
		/** @noinspection SqlNoDataSourceInspection */
		return $wpdb->get_results(
			$wpdb->prepare("
            SELECT $wpdb->liker.liker_id, 
                   SUM( $wpdb->liker.val_1 ) as positive, 
                   SUM( $wpdb->liker.val_2 ) as neutral, 
                   SUM( $wpdb->liker.val_3 ) as negative, 
                   ( SUM( $wpdb->liker.val_1 ) - SUM( $wpdb->liker.val_3 ) ) AS amount, 
                   COUNT( $wpdb->liker.val_1 ) as total, $wpdb->posts.post_type
            FROM $wpdb->liker
            INNER JOIN $wpdb->posts ON $wpdb->liker.liker_id=$wpdb->posts.ID
            GROUP BY $wpdb->liker.liker_id
            ORDER BY %s DESC", ['amount'] )
		);

	}

	/**
	 * Check IP limit
	 *
	 * @param $liker_id
	 * @param string $ip
	 * @return bool
	 **/
	public function check_likes_limits( $liker_id, $ip )
	{

		global $wpdb;

		/** @noinspection SqlDialectInspection */
		/** @noinspection SqlNoDataSourceInspection */
		$result = $wpdb->get_results(
			$wpdb->prepare("
                SELECT * FROM $wpdb->liker
                WHERE liker_id = %d AND ip = %s",
				[ $liker_id, $ip ]
			)
		);

		$options = Settings::get_instance()->options;

		if ( $options['limit_by_ip'] === 'on' ) {

			$voting_limit = $options['voting_limit'];
			return count( $result ) < $voting_limit;

		} else {

			return true;

		}

	}

	/**
	 * Main Request Instance.
	 *
	 * Insures that only one instance of Request exists in memory at any one time.
	 *
	 * @static
	 * @return Request
	 * @since 1.0.0
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

} // End Class Request.
