<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals\Admin\Controllers;

use AutomateWoo\Exception;
use AutomateWoo\Referrals\Advocate_Key_Manager;
use AutomateWoo\Referrals\Referral_Codes_List_Table;

defined( 'ABSPATH' ) || exit;

/**
 * @class Referral_Codes
 */
class Referral_Codes extends Base {


	function handle() {

		$action = $this->get_current_action();

		switch ( $action ) {
			case 'bulk_delete':
				$this->action_bulk_delete();
				$this->output_view_list();
				break;

			default:
				$this->output_view_list();
				break;
		}
	}


	private function output_view_list() {

		require_once AW_Referrals()->admin_path() . '/list-tables/abstract.php';
		require_once AW_Referrals()->admin_path() . '/list-tables/referral-codes.php';

		$table = new Referral_Codes_List_Table();
		$table->prepare_items();
		$table->nonce_action = $this->get_nonce_action();

		$sidebar_content = '<p>' . __( 'Referral codes are unique keys that are used to identify advocates in the referral process. Depending on whether you are using coupon or link based tracking the referral code will be used as a part of the shared URL or coupon.', 'automatewoo-referrals' ) . '</p>';

		if ( AW_Referrals()->options()->is_advocate_key_expiry_enabled() ) {
			$sidebar_content .= '<p>' . sprintf( __( 'These codes are automatically deleted %d days after they expire.', 'automatewoo-referrals' ), Advocate_Key_Manager::get_days_to_keep_expired_keys_for() ) . '</p>';
		}

		$this->output_view(
			'page-table-with-sidebar',
			[
				'table'           => $table,
				'sidebar_content' => $sidebar_content,
			]
		);
	}


	/**
	 * Bulk delete keys
	 */
	private function action_bulk_delete() {
		$this->verify_nonce_action();

		try {
			$ids = $this->get_clean_ids( 'advocate_key_ids' );
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}

		foreach ( $ids as $id ) {
			$key = AW_Referrals()->get_advocate_key( $id );

			if ( $key ) {
				$key->delete();
			}
		}

		$this->add_message( __( 'Bulk edit completed.', 'automatewoo-referrals' ) );
	}

}

return new Referral_Codes();
