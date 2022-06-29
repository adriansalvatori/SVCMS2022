<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Clean;
use AutomateWoo\Format;

defined( 'ABSPATH' ) || exit;

/**
 * @class Referral_Codes_List_Table
 */
class Referral_Codes_List_Table extends Admin_List_Table {

	public $name = 'referral-codes';

	public $enable_search = true;


	function __construct() {
		parent::__construct(
			[
				'singular' => __( 'Referral code', 'automatewoo-referrals' ),
				'plural'   => __( 'Referral codes', 'automatewoo-referrals' ),
				'ajax'     => false
			]
		);
		$this->search_button_text = __( 'Search referral codes', 'automatewoo-referrals' );
	}


	function filters() {
		$this->output_advocate_filter();
	}


	function get_columns() {
		$view = $this->get_current_view( 'active' );

		$columns = [
			'cb' => '<input type="checkbox" />',
			'referral_code' => __( 'Referral code', 'automatewoo-referrals' ),
			'advocate' => __( 'Advocate', 'automatewoo-referrals' )
		];


		if ( AW_Referrals()->options()->is_advocate_key_expiry_enabled() ) {
			$columns['expiry'] = $view === 'expired' ? __( 'Expired', 'automatewoo-referrals' ) : __( 'Expires', 'automatewoo-referrals' );
		}

		return $columns;
	}


	/**
	 * @param Advocate_Key $key
	 * @return string
	 */
	function column_cb( $key ) {
		return '<input type="checkbox" name="advocate_key_ids[]" value="' . $key->get_id() . '" />';
	}


	/**
	 * @param Advocate_Key $key
	 * @return string
	 */
	function column_referral_code( $key ) {
		if ( AW_Referrals()->options()->type === 'coupon' ) {
			return Coupons::get_prefix() . strtoupper( $key->get_key() );
		} elseif ( ( AW_Referrals()->options()->type === 'link' ) ) {
			return strtoupper( $key->get_key() );
		}
	}


	/**
	 * @param Advocate_Key $key
	 * @return string
	 */
	function column_advocate( $key ) {
		$advocate = $key->get_advocate();

		if (  $advocate ) {
			return '<a href="'. get_edit_user_link( $advocate->get_id() ) .'">' . esc_html( AW_Referrals()->admin->get_formatted_customer_name( $advocate->get_user() ) ) . '</a>';
		} else {
			return '-';
		}

	}


	/**
	 * @param Advocate_Key $key
	 * @return string
	 */
	function column_expiry( $key ) {
		$date = $key->get_date_expires();

		if ( $date ) {
			return Format::datetime( $date );
		}

		return $this->format_blank();
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
		$view = $this->get_current_view( 'active' );

		$query = new Advocate_Key_Query();
		$query->set_calc_found_rows( true );
		$query->set_limit( $per_page );
		$query->set_offset( $per_page * ( $current_page - 1 ) );
		$query->set_ordering( 'created', 'DESC' );

		if ( AW_Referrals()->options()->is_advocate_key_expiry_enabled() ) {
			switch ( $view ) {
				case 'active':
					$query->where_not_expired();
					break;
				case 'expired':
					$query->where_expired();
					break;
			}
		}

		if ( ! empty( $_GET['_advocate_user'] ) ) {
			$query->where( 'advocate_id', absint( $_GET['_advocate_user'] ) );
		}

		if ( ! empty( $_GET['s'] ) ) {
			$key = strtolower( Clean::string( $_GET['s'] ) );
			// add OR query, search with and without prefix
			$query->where[] = [
				[
					'column' => 'advocate_key',
					'value' => "%$key%",
					'compare' => 'LIKE'
				],
				[
					'column' => 'advocate_key',
					'value' => "%" . aw_str_replace_first_match( $key, strtolower( Coupons::get_prefix() ) ) . "%",
					'compare' => 'LIKE'
				]
			];
		}

		$this->items     = $query->get_results();
		$this->max_items = $query->found_rows;
	}

	/**
	 * @return array
	 */
	function get_bulk_actions() {
		$actions = [
			'bulk_delete' => __( 'Delete', 'automatewoo-referrals' )
		];

		return $actions;
	}

	/**
	 * Get views for this table.
	 *
	 * @return array
	 */
	protected function get_views() {
		if ( ! AW_Referrals()->options()->is_advocate_key_expiry_enabled() ) {
			return [];
		}

		$views = [];

		$active_query = new Advocate_Key_Query();
		$active_query->where_not_expired();

		$expired_query = new Advocate_Key_Query();
		$expired_query->where_expired();

		$views['active']  = $this->generate_view_link_html( 'active', __( 'Active', 'automatewoo-referrals' ), $active_query->get_count(), true );
		$views['expired'] = $this->generate_view_link_html( 'expired', __( 'Expired', 'automatewoo-referrals' ), $expired_query->get_count() );

		return $views;
	}


}
