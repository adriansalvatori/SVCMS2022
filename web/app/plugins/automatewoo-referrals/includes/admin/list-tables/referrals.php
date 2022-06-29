<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Clean;
use AutomateWoo\Format;

defined( 'ABSPATH' ) || exit;

/**
 * @class Referrals_List_Table
 */
class Referrals_List_Table extends Admin_List_Table {

	public $name = 'referrals';

	/** @var int */
	public $total_items;

	/** @var array */
	public $sections = [];

	/** @var array */
	public $section_totals = [];

	/** @var int */
	public $_items_per_page = 20;

	/** @var Admin\Controllers\Referrals */
	public $controller;


	function __construct() {
		parent::__construct(
			[
				'singular' => 'referral',
				'plural'   => 'referrals',
				'ajax'     => false
			]
		);
	}


	function prepare_items() {

		$this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];

		$query = ( new Referral_Query() )
			->set_calc_found_rows( true )
			->set_limit( $this->_items_per_page )
			->set_offset( $this->_items_per_page * ( $this->get_pagenum() - 1 ) )
			->set_ordering( 'date', 'DESC' );

		if ( ! empty( $_GET['_advocate_user'] ) ) {
			$query->where( 'advocate_id', absint( $_GET['_advocate_user'] ) );
		}

		if ( $this->get_current_section() ) {
			switch ( $this->get_current_section() ) {
				case 'rejected':
				case 'approved':
				case 'pending':
				case 'potential-fraud':
					$query->where( 'status', $this->get_current_section() );
					break;
			}
		}

		$this->items       = $query->get_results();
		$this->total_items = $query->found_rows;

		$this->set_pagination_args(
			[
				'total_items' => $this->total_items,
				'per_page'    => $this->_items_per_page,
				'total_pages' => ceil( $this->total_items / $this->_items_per_page )
			]
		);

	}



	function get_columns() {
		$columns = [
			'cb' => '<input type="checkbox" />',
			'status' => __( 'Status', 'automatewoo-referrals' ),
			'advocate' => __( 'Advocate', 'automatewoo-referrals' ),
			'customer' => __( 'Customer', 'automatewoo-referrals' ),
			'order_id' => __( 'Order', 'automatewoo-referrals' ),
			'created' => __( 'Created', 'automatewoo-referrals' ),
			'offer' => __( 'Customer Discount', 'automatewoo-referrals' ),
			'reward' => __( 'Advocate Reward', 'automatewoo-referrals' ),
			'actions' => __( 'Actions', 'automatewoo-referrals' ),
		];

		if ( AW_Referrals()->options()->get_reward_event() === 'signup' ) {
			unset( $columns['order_id'] );
		}

		if ( AW_Referrals()->options()->type === 'link' ) {
			unset( $columns['offer'] );
		}

		return $columns;
	}


	/**
	 * @param $referral Referral
	 * @return string
	 */
	function column_cb( $referral ) {
		return '<input type="checkbox" name="referral_ids[]" value="' . absint( $referral->get_id() ) . '" />';
	}


	/**
	 * @param $referral Referral
	 * @return string
	 */
	function column_referral_id( $referral ) {
		return '#' . $referral->get_id();
	}


	/**
	 * @param $referral Referral
	 * @return string
	 */
	function column_advocate( $referral ) {
		$advocate = $referral->get_advocate();

		if ( $advocate ) {
			return '<a href="'. get_edit_user_link( $advocate->get_user_id() ) .'">' . esc_html( AW_Referrals()->admin->get_formatted_customer_name( $advocate->get_user() ) ) . '</a>';
		} else {
			return '-';
		}

	}


	/**
	 * @param $referral Referral
	 * @return string
	 */
	function column_customer( $referral ) {
		if ( $user = $referral->get_customer()  ) {
			return '<a href="'. get_edit_user_link( $user->ID ) .'">' . esc_html( AW_Referrals()->admin->get_formatted_customer_name( $user ) ) . '</a>';
		} else {
			return esc_html( AW_Referrals()->admin->get_formatted_customer_name_from_order( $referral->get_order() ) );
		}
	}


	/**
	 * @param $referral Referral
	 * @return string
	 */
	function column_created( $referral ) {
		return esc_html( Format::date( $referral->get_date() ) );
	}


	/**
	 * @param $referral Referral
	 * @return string
	 */
	function column_order_id( $referral ) {
		$order = wc_get_order( $referral->get_order_id() );

		if ( ! $order ) {
			return sprintf( __( '#%s - Deleted', 'automatewoo-referrals' ), $referral->get_order_id() );
		}

		return '<a href="'. get_edit_post_link( $order->get_id() ) .'">#' . $order->get_order_number(). '</a>'
			. ' - ' . wc_get_order_status_name( $order->get_status() );
	}


	/**
	 * @param $referral Referral
	 * @return string
	 */
	function column_reward( $referral ) {

		if ( $referral->has_status( 'rejected' ) ) {
			return '-';
		}

		if ( $referral->is_reward_store_credit() ) {
			return wc_price( $referral->get_reward_amount() ) . ' ' . sprintf( __( '(%s remaining)', 'automatewoo-referrals' ), wc_price( $referral->get_reward_amount_remaining() ) );
		} else {
			return __( 'None', 'automatewoo-referrals' );
		}
	}


	/**
	 * @param $referral Referral
	 * @return string
	 */
	function column_offer( $referral ) {

		switch ( $referral->get_offer_type() ) {
			case 'coupon_discount':
				return wc_price( $referral->get_offer_amount() );
				break;

			case 'coupon_percentage_discount':
				return $referral->get_offer_amount() . '% ';
				break;
		}

		return '-';
	}


	/**
	 * @param $referral Referral
	 * @return string
	 */
	function column_status( $referral ) {
		return '<div class="automatewoo-referral-status status-' . esc_attr( $referral->get_status() ) . ' automatewoo-tiptip" data-tip="'.$referral->get_status_name().'"></div>';
	}


	/**
	 * @param $referral Referral
	 * @return string
	 */
	function column_actions( $referral ) {

		$html = '';

		$actions = [];

		$actions['view'] = __( 'View', 'automatewoo-referrals' );

		if ( $referral->get_status() !== 'approved' ) {
			$actions['approve'] = __( 'Approve', 'automatewoo-referrals' );
		}

		if ( $referral->get_status() !== 'rejected' ) {
			$actions['reject'] = __( 'Reject', 'automatewoo-referrals' );
		}

		foreach ( $actions as $action => $action_name ) {
			$html .= '<a class="button '.$action.' aw-button-icon automatewoo-tiptip" data-tip="'.$action_name.'" href="' . $this->controller->get_route_url( $action, $referral ) .'"></a> ';
		}

		return $html;
	}


	function filters() {
	    $this->output_advocate_filter();
    }


	/**
	 * @return array
	 */
	function get_bulk_actions() {

		$actions = [
			'bulk_approved' => __( 'Mark as Approved', 'automatewoo-referrals' ),
			'bulk_rejected' => __( 'Mark as Rejected', 'automatewoo-referrals' ),
			'bulk_pending' => __( 'Mark as Pending', 'automatewoo-referrals' ),
			'bulk_potential-fraud' => __( 'Mark as Potential Fraud', 'automatewoo-referrals' ),
			'bulk_delete' => __( 'Delete', 'automatewoo-referrals' )
		];

		return $actions;
	}



	/**
	 * @return string
	 */
	function get_current_section() {
		return Clean::string( aw_request( 'section' ) );
	}


	/**
	 *
	 */
	function display_section_nav() {

		if ( empty( $this->sections ) )
			return;

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $this->sections );

		foreach ( $this->sections as $id => $label ) {
			$url = add_query_arg( 'section', $id, $this->controller->get_route_url() );
			echo '<li><a href="' . esc_url( $url ) . '" class="' . ( $this->get_current_section() == $id ? 'current' : '' ) . '">' . esc_html( $label );
			echo isset( $this->section_totals[$id] ) ? ' <span class="count">(' . esc_html( $this->section_totals[$id] ) . ')</span>' : '';
			echo '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul>';
	}

}
