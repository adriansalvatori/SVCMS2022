<?php
// phpcs:ignoreFile

/**
 * @version     1.1.4
 * @package     AutomateWoo Referrals/Updates
 */

defined( 'ABSPATH' ) || exit;


$query = new WP_User_Query(
	[
		'number'     => - 1,
		'fields'     => 'ID',
		'meta_query' => array(
			array(
				'key'     => '_aw_referrals_advocate_key',
				'compare' => 'EXISTS',
			)
		)
	]
);

$users = $query->get_results();

foreach ( $users as $user_id ) {

	$advocate_key = get_user_meta( $user_id, '_aw_referrals_advocate_key', true );

	$object               = new AutomateWoo\Referrals\Advocate_Key();
	$object->advocate_id  = $user_id;
	$object->created      = current_time( 'mysql', true );
	$object->advocate_key = $advocate_key;
	$object->save();

	if ( $object->exists ) {
		delete_user_meta( $user_id, '_aw_referrals_advocate_key' );
	}
}