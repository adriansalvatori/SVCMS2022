<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals\Admin\Controllers;

use AutomateWoo\Clean;
use AutomateWoo\Exception;
use AutomateWoo\Referrals\Advocate_Factory;
use AutomateWoo\Referrals\List_Table_Advocates;

defined( 'ABSPATH' ) || exit;

/**
 * @class Advocates
 */
class Advocates extends Base {

	function handle() {
		$action = $this->get_current_action();

		// Verify the nonce for all actions except the default action.
		if ( $this->default_route !== $action ) {
			$this->verify_nonce_action();
		}

		switch ( $action ) {
			case 'block':
				$this->block_advocate();
				break;

			case 'unblock':
				$this->unblock_advocate();
				break;

			case 'bulk_unblock':
			case 'bulk_block':
				$this->bulk_helper( str_replace( 'bulk_', '', $action ) );
				break;
		}

		$this->output_view_list();
	}


	private function output_view_list() {

		require_once AW_Referrals()->admin_path() . '/list-tables/abstract.php';
		require_once AW_Referrals()->admin_path() . '/list-tables/advocates.php';

		$table = new List_Table_Advocates();
		$table->prepare_items();
		$table->nonce_action = $this->get_nonce_action();

		$sidebar_content = '<p>' . __( 'Advocates are users that are promoting your site through referrals. Current credit is the amount of earned credit the advocate has yet to spend and the total credit is the total amount of credit earned. These figures include approved referrals only.', 'automatewoo-referrals' ) . '</p>';

		$this->output_view(
			'page-table-with-sidebar',
			[
				'table'           => $table,
				'sidebar_content' => $sidebar_content,
			]
		);
	}

	/**
	 * Helper function for bulk blocking and unblocking.
	 *
	 * @param string $method The name of the method to call on the Advocate object.
	 */
	private function bulk_helper( $method ) {
		try {
			$ids = $this->get_clean_ids( 'advocate_ids' );
			foreach ( $ids as $id ) {
				$advocate = Advocate_Factory::get( $id );
				if ( false === $advocate ) {
					continue;
				}

				$advocate->{$method}();
			}
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}

		$this->add_message( __( 'Bulk edit completed.', 'automatewoo-referrals' ) );
	}


	private function block_advocate() {
		$advocate = Advocate_Factory::get( Clean::id( aw_request( 'advocate_id' ) ) );
		if ( $advocate ) {
			$advocate->block();
		}

		$this->add_message( __( 'Advocate blocked.', 'automatewoo-referrals' ) );
	}


	private function unblock_advocate() {
		$advocate = Advocate_Factory::get( Clean::id( aw_request( 'advocate_id' ) ) );
		if ( $advocate ) {
			$advocate->unblock();
		}

		$this->add_message( __( 'Advocate unblocked.', 'automatewoo-referrals' ) );
	}
}

return new Advocates();
