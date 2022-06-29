<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals\Admin\Controllers;

use AutomateWoo\Exception;
use AutomateWoo\Referrals\Invites_List_Table;

defined( 'ABSPATH' ) || exit;

/**
 * @class Invites
 */
class Invites extends Base {


	function handle() {

		$action = $this->get_current_action();

		switch ( $action ) {

			case 'bulk_delete':
				$this->action_bulk_edit( str_replace( 'bulk_', '', $action ) );
				$this->output_view_list();
				break;

			default:

				$this->output_view_list();
				break;
		}
	}


	private function output_view_list() {

		require_once AW_Referrals()->admin_path() . '/list-tables/abstract.php';
		require_once AW_Referrals()->admin_path() . '/list-tables/invites.php';

		$table = new Invites_List_Table();
		$table->prepare_items();
		$table->nonce_action = $this->get_nonce_action();

		$sidebar_content = __( 'A record is made for each referral invite that is sent. Social shares are not tracked.', 'automatewoo-referrals' );

		if ( AW_Referrals()->options()->anonymize_invited_emails ) {
			$sidebar_content .= ' '  . __( 'These emails have been anonymized due to your privacy settings.', 'automatewoo-referrals' );
		}

		$this->output_view(
			'page-table-with-sidebar',
			[
				'table'           => $table,
				'sidebar_content' => '<p>' . $sidebar_content . '</p>',
			]
		);
	}


	/**
	 * @param $action
	 */
	private function action_bulk_edit( $action ) {

		$this->verify_nonce_action();

		try {
			$ids = $this->get_clean_ids( 'referral_invite_ids' );
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}

		foreach ( $ids as $id ) {

			if ( ! $invite = AW_Referrals()->get_invite( $id ) )
				continue;

			switch ( $action ) {
				case 'delete':
					$invite->delete();
					break;
			}
		}

		$this->add_message( __( 'Bulk edit completed.', 'automatewoo-referrals' ) );
	}

}

return new Invites();
