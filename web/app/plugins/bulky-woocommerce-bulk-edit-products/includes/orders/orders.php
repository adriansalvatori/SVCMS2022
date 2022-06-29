<?php

namespace WCBEditor\Includes\Orders;

use WCBEditor\Includes\Abstracts\Bulky_Abstract;

defined( 'ABSPATH' ) || exit;

class Orders extends Bulky_Abstract {
	protected static $instance = null;

	public function __construct() {
		$this->type             = 'orders';
		$this->default_settings = [
			'edit_fields'          => [],
			'posts_per_page'       => 20,
			'order_by'             => 'ID',
			'order'                => 'DESC',
			'auto_remove_revision' => 30,
		];

		add_filter( 'bulky_filter_behaviors_list', [ $this, 'config_behavior' ] );
		add_filter( 'bulky_filter_operators_list', [ $this, 'config_operators' ] );


		parent::__construct();
	}

	public static function instance() {
		return self::$instance == null ? self::$instance = new self : self::$instance;
	}

	public function define_columns() {

		$post_status = $this->parse_to_dropdown_source( wc_get_order_statuses() );
		$countries   = $this->parse_to_dropdown_source( WC()->countries->get_countries() );

		if ( WC()->payment_gateways() ) {
			$payment_gateways = WC()->payment_gateways->payment_gateways();
		} else {
			$payment_gateways = array();
		}

		$pm_gateways[] = [ 'id' => 'n/a', 'name' => 'N/A' ];
		if ( ! empty( $payment_gateways ) ) {
			foreach ( $payment_gateways as $gateway ) {
				if ( 'yes' === $gateway->enabled ) {
					$pm_gateways[] = [ 'id' => $gateway->id, 'name' => $gateway->title ];
				}
			}
		}
		$pm_gateways[] = [ 'id' => 'other', 'name' => esc_html__( 'Other', 'bulky-woocommerce-bulk-edit-products' ) ];

		$columns = [
			'id' => [ 'type' => 'number', 'width' => 70, 'title' => 'ID', 'readOnly' => true ],

			'post_date' => [
				'type'    => 'calendar',
				'width'   => 120,
				'title'   => esc_html__( 'Created date', 'bulky-woocommerce-bulk-edit-products' ),
				'options' => [ 'format' => 'YYYY-MM-DD HH24:MI', 'time' => 1 ]
			],

			'status' => [
				'type'   => 'dropdown',
				'width'  => 130,
				'title'  => esc_html__( 'Status', 'bulky-woocommerce-bulk-edit-products' ),
				'source' => $post_status,
			],

			'items' => [ 'type' => 'text', 'width' => 200, 'title' => esc_html__( 'Items', 'bulky-woocommerce-bulk-edit-products' ), 'readOnly' => true, 'wordWrap' => true, 'align' => 'left' ],
			'total' => [ 'type' => 'text', 'width' => 70, 'title' => esc_html__( 'Total', 'bulky-woocommerce-bulk-edit-products' ), 'readOnly' => true ],

			'order_key' => [ 'type' => 'text', 'width' => 180, 'title' => esc_html__( 'Order key', 'bulky-woocommerce-bulk-edit-products' ) ],

			'billing_fisrt_name' => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Billing fisrt name', 'bulky-woocommerce-bulk-edit-products' ) ],
			'billing_last_name'  => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Billing last name', 'bulky-woocommerce-bulk-edit-products' ) ],
			'billing_company'    => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Billing company', 'bulky-woocommerce-bulk-edit-products' ) ],
			'billing_address_1'  => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Billing address 1', 'bulky-woocommerce-bulk-edit-products' ) ],
			'billing_address_2'  => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Billing address 2', 'bulky-woocommerce-bulk-edit-products' ) ],
			'billing_city'       => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Billing city', 'bulky-woocommerce-bulk-edit-products' ) ],
			'billing_postcode'   => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Billing postcode', 'bulky-woocommerce-bulk-edit-products' ) ],
			'billing_country'    => [ 'type' => 'dropdown', 'width' => 100, 'title' => esc_html__( 'Billing country', 'bulky-woocommerce-bulk-edit-products' ), 'source' => $countries ],
			'billing_state'      => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Billing state', 'bulky-woocommerce-bulk-edit-products' ) ],
			'billing_email'      => [ 'type' => 'text', 'width' => 180, 'title' => esc_html__( 'Email address', 'bulky-woocommerce-bulk-edit-products' ) ],
			'billing_phone'      => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Billing phone', 'bulky-woocommerce-bulk-edit-products' ) ],
			'payment_method'     => [ 'type' => 'dropdown', 'width' => 100, 'title' => esc_html__( 'Payment method', 'bulky-woocommerce-bulk-edit-products' ), 'source' => $pm_gateways ],
			'transaction_id'     => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Transaction ID', 'bulky-woocommerce-bulk-edit-products' ) ],

			'shipping_fisrt_name' => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Shipping fisrt name', 'bulky-woocommerce-bulk-edit-products' ) ],
			'shipping_last_name'  => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Shipping last name', 'bulky-woocommerce-bulk-edit-products' ) ],
			'shipping_company'    => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Shipping company', 'bulky-woocommerce-bulk-edit-products' ) ],
			'shipping_address_1'  => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Shipping address 1', 'bulky-woocommerce-bulk-edit-products' ) ],
			'shipping_address_2'  => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Shipping address 2', 'bulky-woocommerce-bulk-edit-products' ) ],
			'shipping_city'       => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Shipping city', 'bulky-woocommerce-bulk-edit-products' ) ],
			'shipping_postcode'   => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Shipping postcode', 'bulky-woocommerce-bulk-edit-products' ) ],
			'shipping_country'    => [ 'type' => 'dropdown', 'width' => 100, 'title' => esc_html__( 'Shipping country', 'bulky-woocommerce-bulk-edit-products' ), 'source' => $countries ],
			'shipping_state'      => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Shipping state', 'bulky-woocommerce-bulk-edit-products' ) ],
			'shipping_phone'      => [ 'type' => 'text', 'width' => 100, 'title' => esc_html__( 'Shipping phone', 'bulky-woocommerce-bulk-edit-products' ) ],
			'post_excerpt'        => [ 'type' => 'text', 'width' => 200, 'title' => esc_html__( 'Customer provided note', 'bulky-woocommerce-bulk-edit-products' ), 'wordWrap' => true ],
			'order_notes'         => [ 'type' => 'custom', 'width' => 70, 'title' => esc_html__( 'Order notes', 'bulky-woocommerce-bulk-edit-products' ), 'editor' => 'order_notes' ],
		];


		$meta_fields = get_option( 'vi_wbe_order_meta_fields' );

		$meta_field_columns = [];
		if ( ! empty( $meta_fields ) && is_array( $meta_fields ) ) {
			foreach ( $meta_fields as $meta_key => $meta_field ) {
				if ( empty( $meta_field['active'] ) ) {
					continue;
				}

				$type   = 'text';
				$editor = '';

				switch ( $meta_field['input_type'] ) {
					case 'textinput':
						$type = 'text';
						break;
					case 'numberinput':
						$type = 'number';
						break;
					case 'checkbox':
						$type = 'checkbox';
						break;
					case 'array':
					case 'json':
						$type   = 'custom';
						$editor = 'array';
						break;
					case 'calendar':
						$type = 'calendar';
						break;
					case 'texteditor':
						$type   = 'custom';
						$editor = 'textEditor';
						break;
					case 'image':
						$type   = 'custom';
						$editor = 'image';
						break;
				}

				$meta_field_columns[ $meta_key ] = [
					'title'  => ! empty( $meta_field['column_name'] ) ? $meta_field['column_name'] : $meta_key,
					'width'  => 100,
					'type'   => $type,
					'editor' => $editor,
				];

			}
		}

		$columns = array_merge( $columns, $meta_field_columns );

		return $columns;
	}

	public function load_orders() {
		$handle_order = Handle_Order::instance();
		$filter       = Filters::instance();
		$settings     = $this->get_settings();
		$page         = ! empty( $_POST['page'] ) ? sanitize_text_field( $_POST['page'] ) : 1;

		$args = [
			'posts_per_page' => $settings['posts_per_page'],
			'post_type'      => 'shop_order',
			'paged'          => $page,
			'paginate'       => true,
			'order'          => $settings['order'],
			'orderby'        => $settings['order_by'],
		];

		$args = $filter->set_args( $args );

		$result        = wc_get_orders( $args );
		$count         = $result->total;
		$max_num_pages = $result->max_num_pages;
		$orders        = $result->orders;

		$orders_data = $pids = [];

		if ( $orders ) {
			foreach ( $orders as $order ) {
				$pid           = $order->get_id();
				$pids[]        = $pid;
				$orders_data[] = $handle_order->get_order_data_for_edit( $order );
			}
		}


		$respone_data = [
			'products'      => $orders_data,
			'count'         => $count,
			'max_num_pages' => $max_num_pages,
		];

		if ( isset( $_POST['re_create'] ) && $_POST['re_create'] === 'true' ) {
			$columns                       = $this->get_columns();
			$id_mapping                    = array_keys( $columns );
			$respone_data['idMapping']     = $id_mapping;
			$respone_data['idMappingFlip'] = array_flip( $id_mapping );
			$respone_data['columns']       = json_encode( array_values( $columns ) );
		}

		wp_send_json_success( $respone_data );
	}

	public function fixed_columns() {
		return [ 'id', 'post_title' ];
	}

	public function filter_fields() {
		$defined_columns     = array_keys( $this->define_columns() );
		$edit_fields         = $this->get_setting( 'edit_fields' );
		$exclude_edit_fields = $this->get_setting( 'exclude_edit_fields' );

		$r = $defined_columns;

		if ( ! empty( $edit_fields ) && is_array( $edit_fields ) ) {
			$edit_fields = array_merge( $this->fixed_columns(), $edit_fields );

			foreach ( $r as $i => $key ) { //Keep piority
				if ( $key !== false && ! in_array( $key, $edit_fields ) ) {
					unset( $r[ $i ] );
				}
			}
		}

		if ( ! empty( $exclude_edit_fields ) && is_array( $exclude_edit_fields ) ) {
			foreach ( $exclude_edit_fields as $field ) {
				$key = array_search( $field, $r );

				if ( $key !== false && isset( $r[ $key ] ) ) {
					unset( $r[ $key ] );
				}
			}
		}

		return array_values( $r );
	}

	public function save_orders() {
		$orders      = isset( $_POST['products'] ) ? json_decode( stripslashes( $_POST['products'] ), true ) : '';
		$trash_ids   = ! empty( $_POST['trash'] ) ? wc_clean( $_POST['trash'] ) : '';
		$untrash_ids = ! empty( $_POST['untrash'] ) ? wc_clean( $_POST['untrash'] ) : '';

		$response = [];

		if ( $untrash_ids ) {
			array_map( 'wp_untrash_post', $untrash_ids );
		}

		if ( $trash_ids ) {
			array_map( 'wp_trash_post', $trash_ids );
		}

		$fields       = $this->filter_fields();
		$handle_order = Handle_Order::instance();

		if ( ! empty( $orders ) && is_array( $orders ) ) {

			foreach ( $orders as $order_data ) {
				if ( empty( $order_data[0] ) ) {
					continue;
				}
				$pid = $order_data[0] ?? '';

				$order = wc_get_order( $pid );

				if ( ! is_object( $order ) ) {
					continue;
				}

				foreach ( $order_data as $key => $value ) {
					$type = $fields[ $key ] ?? '';

					if ( ! $type || $key === 0 ) {
						continue;
					}

					$handle_order->parse_order_data_to_save( $order, $type, $value );
				}
				$order->save();
			}
		}

		wp_send_json_success( $response );
	}

	public function filter_tab() {
		$this->filter_input_element( [
			'type'  => 'text',
			'id'    => 'id',
			'label' => esc_html__( 'ID (Use comma or minus for range)', 'bulky-woocommerce-bulk-edit-products' ),
		] );

		$this->filter_input_element( [
			'type'     => 'text',
			'id'       => '_billing_email',
			'label'    => esc_html__( "Customer's email", 'bulky-woocommerce-bulk-edit-products' ),
			'behavior' => true
		] );

		$this->filter_input_element( [
			'type'     => 'text',
			'id'       => 'post_excerpt',
			'label'    => esc_html__( "Customer provided note", 'bulky-woocommerce-bulk-edit-products' ),
			'behavior' => true
		] );

		?>

        <div class="two fields">
			<?php
			$this->filter_input_element( [
				'type'  => 'date',
				'id'    => 'post_date_from',
				'label' => esc_html__( 'Order date from', 'bulky-woocommerce-bulk-edit-products' )
			] );
			$this->filter_input_element( [
				'type'  => 'date',
				'id'    => 'post_date_to',
				'label' => esc_html__( 'Order date to', 'bulky-woocommerce-bulk-edit-products' )
			] );
			?>
        </div>

        <div class="two fields">
			<?php
			$this->filter_input_element( [
				'type'    => 'select',
				'id'      => 'status',
				'options' => array_merge( [ '' => esc_html__( 'Order status', 'bulky-woocommerce-bulk-edit-products' ) ], wc_get_order_statuses() )
			] );

			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_transaction_id',
				'label'    => esc_html__( "Transaction ID", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );
			?>
        </div>

        <div class="two fields">
			<?php
			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_billing_first_name',
				'label'    => esc_html__( "Billing first name", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );

			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_billing_last_name',
				'label'    => esc_html__( "Billing last name", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );
			?>
        </div>

        <div class="two fields">
			<?php
			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_billing_address_1',
				'label'    => esc_html__( "Billing address 1", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );

			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_billing_address_2',
				'label'    => esc_html__( "Billing address 2", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );
			?>
        </div>

        <div class="two fields">
			<?php
			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_billing_city',
				'label'    => esc_html__( "Billing city", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );

			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_billing_postcode',
				'label'    => esc_html__( "Billing postcode", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );
			?>
        </div>

        <div class="two fields">
			<?php
			$this->filter_input_element( [
				'type'         => 'multi-select',
				'id'           => '_billing_country',
				'select_class' => 'vi-wbe-select2 search',
				'options'      => array_merge( [ '' => esc_html__( "Billing country", 'bulky-woocommerce-bulk-edit-products' ) ], wc()->countries->get_countries() ),
				'operator'     => true,
			] );

			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_billing_state',
				'label'    => esc_html__( "Billing state", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );
			?>
        </div>

        <div class="two fields">
			<?php

			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_billing_company',
				'label'    => esc_html__( "Billing company", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );

			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_billing_phone',
				'label'    => esc_html__( "Billing phone", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );
			?>
        </div>

        <div class="two fields">
			<?php
			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_shipping_first_name',
				'label'    => esc_html__( "Shipping first name", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );

			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_shipping_last_name',
				'label'    => esc_html__( "Shipping last name", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );
			?>
        </div>

        <div class="two fields">
			<?php
			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_shipping_address_1',
				'label'    => esc_html__( "Shipping address 1", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );

			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_shipping_address_2',
				'label'    => esc_html__( "Shipping address 2", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );
			?>
        </div>

        <div class="two fields">
			<?php
			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_shipping_city',
				'label'    => esc_html__( "Shipping city", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );

			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_shipping_postcode',
				'label'    => esc_html__( "Shipping postcode", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );
			?>
        </div>

        <div class="two fields">
			<?php
			$this->filter_input_element( [
				'type'         => 'multi-select',
				'id'           => '_shipping_country',
				'select_class' => 'search',
				'options'      => array_merge( [ '' => esc_html__( "Shipping country", 'bulky-woocommerce-bulk-edit-products' ) ], wc()->countries->get_countries() ),
				'operator'     => true,
			] );

			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_shipping_state',
				'label'    => esc_html__( "Shipping state", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );
			?>
        </div>

        <div class="two fields">
			<?php

			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_shipping_company',
				'label'    => esc_html__( "Shipping company", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );

			$this->filter_input_element( [
				'type'     => 'text',
				'id'       => '_shipping_phone',
				'label'    => esc_html__( "Shipping phone", 'bulky-woocommerce-bulk-edit-products' ),
				'behavior' => true
			] );
			?>
        </div>

		<?php
	}

	public function settings_tab() {
		$columns = $this->get_column_titles();

		$this->setting_input_element( [
			'type'         => 'multi-select',
			'id'           => 'edit_fields',
			'select_class' => 'vi-wbe-select-columns-to-edit vi-wbe-select2 search',
			'label'        => esc_html__( 'Fields to edit', 'bulky-woocommerce-bulk-edit-products' ),
			'options'      => [ '' => esc_html__( 'All fields', 'bulky-woocommerce-bulk-edit-products' ) ] + $columns,
			'clear_button' => true
		] );

		$this->setting_input_element( [
			'type'         => 'multi-select',
			'id'           => 'exclude_edit_fields',
			'select_class' => 'vi-wbe-exclude-fields-to-edit vi-wbe-select2 search',
			'label'        => esc_html__( 'Exclude fields to edit', 'bulky-woocommerce-bulk-edit-products' ),
			'options'      => [ '' => esc_html__( 'No field', 'bulky-woocommerce-bulk-edit-products' ) ] + $columns,
			'clear_button' => true
		] );

		$this->setting_input_element( [
			'type'  => 'number',
			'id'    => 'posts_per_page',
			'min'   => 1,
			'max'   => 50,
			'label' => esc_html__( 'Orders per page', 'bulky-woocommerce-bulk-edit-products' )
		] );

		$this->setting_input_element( [
			'type'    => 'select',
			'id'      => 'order_by',
			'label'   => esc_html__( 'Order by', 'bulky-woocommerce-bulk-edit-products' ),
			'options' => [
				'ID' => 'ID',
			]
		] );

		$this->setting_input_element( [
			'type'    => 'select',
			'id'      => 'order',
			'label'   => esc_html__( 'Order', 'bulky-woocommerce-bulk-edit-products' ),
			'options' => [ 'DESC' => 'DESC', 'ASC' => 'ASC', ]
		] );

		$this->setting_input_element( [
			'type'  => 'number',
			'id'    => 'auto_remove_revision',
			'min'   => 0,
			'max'   => 1000,
			'label' => esc_html__( 'Time to delete revision', 'bulky-woocommerce-bulk-edit-products' ),
			'unit'  => esc_html__( 'day(s)', 'bulky-woocommerce-bulk-edit-products' ),
		] );

		$this->setting_input_element( [
			'type'  => 'checkbox',
			'id'    => 'save_filter',
			'label' => esc_html__( 'Save filter when reload page', 'bulky-woocommerce-bulk-edit-products' ),
		] );
	}

	public function config_behavior( $behavior ) {
		unset( $behavior['empty'] );
		unset( $behavior['begin'] );
		unset( $behavior['end'] );

		return $behavior;
	}

	public function config_operators( $operators ) {
		unset( $operators['and'] );

		return $operators;
	}

	public function get_history_page() {
		Order_History::instance()->get_history_page();
	}
}
