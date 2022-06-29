<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Format;

defined( 'ABSPATH' ) || exit;

/**
 * @class Invites_List_Table
 * @since 2.3
 */
class Invites_List_Table extends Admin_List_Table {

	public $name = 'invites';


	function __construct() {
		parent::__construct(
			[
				'singular' => __( 'Referral Invite', 'automatewoo-referrals' ),
				'plural'   => __( 'Referral Invites', 'automatewoo-referrals' ),
				'ajax'     => false
			]
		);
	}


	function filters() {
        $this->output_advocate_filter();
	}


	/**
	 * @param Invite $invite
	 * @return string
	 */
	function column_cb( $invite ) {
		return '<input type="checkbox" name="referral_invite_ids[]" value="' . absint( $invite->get_id() ) . '" />';
	}


	/**
	 * @param Invite $invite
	 * @return string
	 */
	function column_email( $invite ) {
		return Format::email( $invite->get_email() );
	}


	/**
	 * @param Invite $invite
	 * @return string
	 */
	function column_advocate( $invite ) {
		return $this->format_user( get_userdata( $invite->get_advocate_id() ) );
	}


	/**
	 * @param Invite $invite
	 * @return string
	 */
	function column_date( $invite ) {
		return $this->format_date( $invite->get_date() );
	}



	/**
	 * get_columns function.
	 */
	function get_columns() {
		$columns = [
			'cb' => '<input type="checkbox" />',
			'email'  => __( 'Email', 'automatewoo-referrals' ),
			'advocate'  => __( 'Advocate', 'automatewoo-referrals' ),
			'date'  => __( 'Date', 'automatewoo-referrals' )
		];

		return $columns;
	}


	function prepare_items() {

		$this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];
		$current_page          = absint( $this->get_pagenum() );
		$per_page              = apply_filters( 'automatewoo_report_items_per_page', 20 );

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
	 * @param $current_page
	 * @param $per_page
	 */
	function get_items( $current_page, $per_page ) {

		$query = new Invite_Query();
		$query->set_calc_found_rows( true );
		$query->set_limit( $per_page );
		$query->set_offset( ($current_page - 1 ) * $per_page );
		$query->set_ordering( 'date', 'DESC' );

		if ( ! empty( $_GET[ '_advocate_user' ] ) ) {
			$query->where( 'advocate_id', absint( $_GET['_advocate_user'] ) );
		}

		$this->items     = $query->get_results();
		$this->max_items = $query->found_rows;

	}


	/**
	 * Retrieve the bulk actions
	 */
	function get_bulk_actions() {
		$actions = [
			'bulk_delete' => __( 'Delete', 'automatewoo-referrals' ),
		];

		return $actions;
	}

}
