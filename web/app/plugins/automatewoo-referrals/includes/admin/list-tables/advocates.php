<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Cache;
use AutomateWoo\Database_Tables;

defined( 'ABSPATH' ) || exit;

/**
 * @class List_Table_Advocates
 * @since 2.3
 */
class List_Table_Advocates extends Admin_List_Table {

	public $name = 'advocates';

	public $nonce_action = 'automatewoo-advocates-action';

	function __construct() {
		parent::__construct(
			[
				'singular' => __( 'Advocates', 'automatewoo-referrals' ),
				'plural'   => __( 'Advocates', 'automatewoo-referrals' ),
				'ajax'     => false
			]
		);
	}


	function filters() {
		$this->output_advocate_filter();
	}


	function get_columns() {
		$columns = [
			'cb' => '<input type="checkbox" />',
			'advocate' => __( 'Advocate', 'automatewoo-referrals' ),
			'invites_sent' => __( 'Invites sent', 'automatewoo-referrals' ),
			'referral_count' => __( 'Referral count', 'automatewoo-referrals' ),
			'referral_revenue' => __( 'Referral revenue', 'automatewoo-referrals' ),
			'credit_current' => __( 'Current credit', 'automatewoo-referrals' ),
			'credit_total' => __( 'Total credit', 'automatewoo-referrals' ),
		];

		return $columns;
	}

	/**
	 * Generates and display row actions links for the list table.
	 *
	 * @since 2.3.0
	 *
	 * @param object $item        The item being acted upon.
	 * @param string $column_name Current column name.
	 * @param string $primary     Primary column name.
	 *
	 * @return string The row actions HTML, or an empty string if the current column is the primary column.
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( $column_name !== $primary ) {
			return '';
		}

		if ( ! $item instanceof Advocate ) {
			return '';
		}

		$action  = $item->is_blocked() ? 'unblock' : 'block';
		$message = $item->is_blocked()
			? __( 'Unblock', 'automatewoo-referrals' )
			: __( 'Block', 'automatewoo-referrals' );

		$actions = [
			$action => sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					add_query_arg(
						[
							'advocate_id' => $item->get_id(),
							'action'      => $action,
							'_wpnonce'    => wp_create_nonce( $this->nonce_action ),
						],
						AW_Referrals()->admin->page_url( 'advocates' )
					)
				),
				esc_html( $message )
			),
		];

		return $this->row_actions( $actions, true );
	}

	/**
	 * @param Advocate $advocate
	 *
	 * @return string
	 */
	function column_cb( $advocate ) {
		return sprintf( '<input type="checkbox" name="advocate_ids[]" value="%s" />', esc_attr( $advocate->get_id() ) );
	}

	/**
	 * @param Advocate $advocate
	 * @return string
	 */
	function column_advocate( $advocate ) {
		$formatted = $this->format_user( $advocate->get_user() );
		if ( $advocate->is_blocked() ) {
			$formatted .= ' ' . _x( '(Blocked)', 'indication in list table that advocate is blocked', 'automatewoo-referrals' );
		}

		return $formatted;
	}

	/**
	 * @param Advocate $advocate
	 * @return string
	 */
	function column_invites_sent( $advocate ) {
		$link  = esc_url( add_query_arg( '_advocate_user', $advocate->get_id(), AW_Referrals()->admin->page_url( 'invites' ) ) );
		$count = $advocate->get_invites_count();
		return "<a href='$link'>$count</a>";
	}


	/**
	 * @param Advocate $advocate
	 * @return string
	 */
	function column_referral_count( $advocate ) {
		$link  = esc_url( add_query_arg( '_advocate_user', $advocate->get_id(), AW_Referrals()->admin->page_url( 'referrals' ) ) );
		$count = $advocate->get_referral_count( 'approved' );
		return "<a href='$link'>$count</a>";
	}


	/**
	 * @param Advocate $advocate
	 * @return string
	 */
	function column_referral_revenue( $advocate ) {
		return wc_price( $advocate->get_referral_revenue() );
	}


	/**
	 * @param Advocate $advocate
	 * @return string
	 */
	function column_credit_current( $advocate ) {
		return wc_price( $advocate->get_current_credit() );
	}


	/**
	 * @param Advocate $advocate
	 * @return string
	 */
	function column_credit_total( $advocate ) {
		return wc_price( $advocate->get_total_credit() );
	}



	function prepare_items() {

		$this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];
		$current_page          = absint( $this->get_pagenum() );
		$per_page              = (int) apply_filters( 'automatewoo_report_items_per_page', 20 );

		$this->get_items( $current_page, $per_page );

		$this->set_pagination_args(
			[
				'total_items' => $this->max_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $this->max_items / $per_page )
			]
		);
	}


	/**
	 * @param int $current_page
	 * @param int $per_page
	 */
	function get_items( $current_page, $per_page ) {

		if ( ! $advocate_ids = $this->get_advocates() ) {
			return;
		}

		if ( ! empty( $_GET['_advocate_user'] ) ) {
			$advocate_ids = [ absint( $_GET['_advocate_user'] ) ];
		}

		$this->items     = [];
		$this->max_items = count( $advocate_ids );

		$advocate_ids = array_slice( $advocate_ids, $per_page * ( $current_page - 1 ), $per_page );

		foreach ( $advocate_ids as $advocate_id ) {
			if ( $advocate = Advocate_Factory::get( $advocate_id ) ) {
				$this->items[] = $advocate;
			}
		}

	}


	/**
	 * @return array
	 */
	function get_advocates() {
		global $wpdb;

		if ( $cache = Cache::get_transient( 'current_advocates' ) ) {
			return $cache;
		}

		$referrals_table = Database_Tables::get( 'referrals' );
		$advocates1      = $wpdb->get_results( "SELECT DISTINCT advocate_id FROM {$referrals_table->get_name()}", ARRAY_N );

		$invites_table = Database_Tables::get( 'referral-invites' );
		$advocates2    = $wpdb->get_results( "SELECT DISTINCT advocate_id FROM {$invites_table->get_name()}", ARRAY_N );

		if ( ! is_array( $advocates1 ) || ! is_array( $advocates2 ) ) {
			return [];
		}

		$advocates = array_unique( array_merge( wp_list_pluck( $advocates1, 0 ), wp_list_pluck( $advocates2, 0 ) ) );

		Cache::set_transient( 'current_advocates', $advocates, 0.25 );

		return $advocates;
	}

	/**
	 * Get bulk actions available on this table.
	 *
	 * @return array Associative array (option_name => option_title) of bulk actions available on this table.
	 */
	protected function get_bulk_actions() {
		return [
			'bulk_block'   => __( 'Block', 'automatewoo-referrals' ),
			'bulk_unblock' => __( 'Unblock', 'automatewoo-referrals' ),
		];
	}
}
