<?php

namespace WCBEditor\Includes\Coupons;

use WCBEditor\Includes\Abstracts\Bulky_Abstract;
use WCBEditor\Includes\Helper;

defined( 'ABSPATH' ) || exit;

class Coupons extends Bulky_Abstract {
	protected static $instance = null;

	public function __construct() {
		$this->type             = 'coupons';
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

		$columns = [
			'id' => [ 'type' => 'number', 'width' => 70, 'title' => 'ID', 'readOnly' => true ],

			'code' => [
				'type'  => 'text',
				'width' => 130,
				'title' => esc_html__( 'Coupon code', 'bulky-woocommerce-bulk-edit-products' ),
			],

			'post_date' => [
				'type'    => 'calendar',
				'width'   => 120,
				'title'   => esc_html__( 'Created date', 'bulky-woocommerce-bulk-edit-products' ),
				'options' => [ 'format' => 'YYYY-MM-DD HH24:MI', 'time' => 1 ]
			],

			'status' => [
				'type'   => 'dropdown',
				'width'  => 100,
				'title'  => esc_html__( 'Status', 'bulky-woocommerce-bulk-edit-products' ),
				'source' => $this->parse_to_dropdown_source( Helper::post_statuses() ),
			],

			'excerpt' => [
				'type'     => 'text',
				'width'    => 130,
				'title'    => esc_html__( 'Description', 'bulky-woocommerce-bulk-edit-products' ),
				'wordWrap' => true,
			],

			'discount_type' => [
				'type'   => 'dropdown',
				'width'  => 130,
				'title'  => esc_html__( 'Discount type', 'bulky-woocommerce-bulk-edit-products' ),
				'source' => $this->parse_to_dropdown_source( wc_get_coupon_types() )
			],

			'amount' => [
				'type'  => 'number',
				'width' => 130,
				'title' => esc_html__( 'Amount', 'bulky-woocommerce-bulk-edit-products' ),
			],

			'allow_free_shipping' => [
				'type'  => 'checkbox',
				'width' => 70,
				'title' => esc_html__( 'Allow free shipping', 'bulky-woocommerce-bulk-edit-products' ),
			],

			'expiry_date' => [
				'type'    => 'calendar',
				'width'   => 90,
				'title'   => esc_html__( 'Expiry date', 'bulky-woocommerce-bulk-edit-products' ),
				'options' => [ 'format' => 'YYYY-MM-DD' ]
			],

			'minimum_amount' => [
				'type'  => 'text',
				'width' => 100,
				'title' => esc_html__( 'Minimum spend', 'bulky-woocommerce-bulk-edit-products' ),
			],

			'maximum_amount' => [
				'type'  => 'text',
				'width' => 100,
				'title' => esc_html__( 'Maximum spend', 'bulky-woocommerce-bulk-edit-products' ),
			],

			'individual_use' => [
				'type'  => 'checkbox',
				'width' => 70,
				'title' => esc_html__( 'Individual use only', 'bulky-woocommerce-bulk-edit-products' ),
			],

			'exclude_sale_items' => [
				'type'  => 'checkbox',
				'width' => 70,
				'title' => esc_html__( 'Exclude sale items', 'bulky-woocommerce-bulk-edit-products' ),
			],

			'product_ids' => [
				'type'   => 'custom',
				'width'  => 130,
				'title'  => esc_html__( 'Products', 'bulky-woocommerce-bulk-edit-products' ),
				'editor' => 'link_products'
			],

			'exclude_product_ids' => [
				'type'   => 'custom',
				'width'  => 130,
				'title'  => esc_html__( 'Exclude products', 'bulky-woocommerce-bulk-edit-products' ),
				'editor' => 'link_products'
			],

			'product_categories' => [
				'type'       => 'dropdown',
				'width'      => 130,
				'title'      => esc_html__( 'Product categories', 'bulky-woocommerce-bulk-edit-products' ),
				'source'     => Helper::get_categories( true ),
				'multiple'   => true,
				'allowEmpty' => true,
			],

			'exclude_product_categories' => [
				'type'       => 'dropdown',
				'width'      => 130,
				'title'      => esc_html__( 'Exclude categories', 'bulky-woocommerce-bulk-edit-products' ),
				'source'     => Helper::get_categories( true ),
				'multiple'   => true,
				'allowEmpty' => true,
			],

			'customer_email' => [
				'type'  => 'text',
				'width' => 200,
				'title' => esc_html__( 'Allowed emails', 'bulky-woocommerce-bulk-edit-products' ),
			],

			'usage_limit' => [
				'type'  => 'number',
				'width' => 80,
				'title' => esc_html__( 'Usage limit per coupon', 'bulky-woocommerce-bulk-edit-products' ),
			],

			'limit_usage_to_x_items' => [
				'type'  => 'number',
				'width' => 80,
				'title' => esc_html__( 'Limit usage to X items', 'bulky-woocommerce-bulk-edit-products' ),
			],

			'usage_limit_per_user' => [
				'type'  => 'number',
				'width' => 80,
				'title' => esc_html__( 'Usage limit per user', 'bulky-woocommerce-bulk-edit-products' ),
			],
		];

		$meta_fields = get_option( 'vi_wbe_coupon_meta_fields' );

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

	public function load_coupons() {
		$handle_coupon = Handle_Coupon::instance();
		$filter        = Filters::instance();
		$settings      = $this->get_settings();
		$page          = ! empty( $_POST['page'] ) ? sanitize_text_field( $_POST['page'] ) : 1;

		$args = [
			'posts_per_page' => $settings['posts_per_page'],
			'post_type'      => 'shop_coupon',
			'paged'          => $page,
			'paginate'       => true,
			'order'          => $settings['order'],
			'orderby'        => $settings['order_by'],
		];

		$args = $filter->set_args( $args );

		$result = new \WP_Query( $args );
//error_log(print_r($result,true));
		$count         = $result->post_count;
		$max_num_pages = $result->max_num_pages;
		$posts         = $result->posts;

		$coupons_data = $pids = [];

		if ( $posts ) {
			foreach ( $posts as $post ) {
				$pid            = $post->ID;
				$pids[]         = $pid;
				$coupon         = new \WC_Coupon( $pid );
				$coupons_data[] = $handle_coupon->get_coupon_data_for_edit( $coupon, $post );
			}
		}

		$respone_data = [
			'products'      => $coupons_data,
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
		return [ 'id', 'code' ];
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

	public function save_coupons() {
		$coupons     = isset( $_POST['products'] ) ? json_decode( stripslashes( $_POST['products'] ), true ) : '';
		$trash_ids   = ! empty( $_POST['trash'] ) ? wc_clean( $_POST['trash'] ) : '';
		$untrash_ids = ! empty( $_POST['untrash'] ) ? wc_clean( $_POST['untrash'] ) : '';
		$response    = [];

		if ( $untrash_ids ) {
			array_map( 'wp_untrash_post', $untrash_ids );
		}

		if ( $trash_ids ) {
			array_map( 'wp_trash_post', $trash_ids );
		}

		$fields        = $this->filter_fields();
		$handle_coupon = Handle_Coupon::instance();

		if ( ! empty( $coupons ) && is_array( $coupons ) ) {
			foreach ( $coupons as $coupon_data ) {
				if ( empty( $coupon_data[0] ) ) {
					continue;
				}
				$pid = $coupon_data[0] ?? '';

				$coupon = new \WC_Coupon( $pid );

				if ( ! is_object( $coupon ) ) {
					continue;
				}

				foreach ( $coupon_data as $key => $value ) {
					$type = $fields[ $key ] ?? '';

					if ( ! $type || $key === 0 ) {
						continue;
					}

					if ( $type === 'status' ) {
						wp_update_post( [ 'ID' => $pid, 'post_status' => $value ] );
						continue;
					}

					$handle_coupon->parse_coupon_data_to_save( $coupon, $type, $value );
				}

				$coupon->save();
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

		?>

        <div class="two fields">
			<?php
			$this->filter_input_element( [
				'type'  => 'date',
				'id'    => 'post_date_from',
				'label' => esc_html__( 'Coupon date created from', 'bulky-woocommerce-bulk-edit-products' )
			] );
			$this->filter_input_element( [
				'type'  => 'date',
				'id'    => 'post_date_to',
				'label' => esc_html__( 'Coupon date created to', 'bulky-woocommerce-bulk-edit-products' )
			] );
			?>
        </div>

		<?php $this->filter_input_element( [
			'type'    => 'select',
			'id'      => 'has_expire_date',
			'options' => [
				''    => esc_html__( 'Expire date', 'bulky-woocommerce-bulk-edit-products' ),
				'yes' => esc_html__( 'Has expire date', 'bulky-woocommerce-bulk-edit-products' ),
				'no'  => esc_html__( 'Empty', 'bulky-woocommerce-bulk-edit-products' ),
			]
		] ); ?>

        <div class="two fields vi-wbe-expire-date-group">
			<?php
			$this->filter_input_element( [
				'type'  => 'date',
				'id'    => 'expiry_date_from',
				'label' => esc_html__( 'Coupon expiry date from', 'bulky-woocommerce-bulk-edit-products' )
			] );
			$this->filter_input_element( [
				'type'  => 'date',
				'id'    => 'expiry_date_to',
				'label' => esc_html__( 'Coupon expiry date to', 'bulky-woocommerce-bulk-edit-products' )
			] );
			?>
        </div>

        <div class="two fields">
			<?php
			$this->filter_input_element( [
				'type'    => 'select',
				'id'      => 'post_status',
				'options' => array_merge( [ '' => esc_html__( 'Coupon status', 'bulky-woocommerce-bulk-edit-products' ) ], Helper::post_statuses() )
			] );

			$this->filter_input_element( [
				'type'    => 'select',
				'id'      => 'discount_type',
				'options' => array_merge( [ '' => esc_html__( 'Discount type', 'bulky-woocommerce-bulk-edit-products' ) ], wc_get_coupon_types() )
			] );

			?>
        </div>

        <div class="two fields">
			<?php
			$this->filter_input_element( [
				'type'  => 'number',
				'id'    => 'amount_from',
				'label' => esc_html__( 'Coupon amount from', 'bulky-woocommerce-bulk-edit-products' )
			] );
			$this->filter_input_element( [
				'type'  => 'number',
				'id'    => 'amount_to',
				'label' => esc_html__( 'Coupon amount to', 'bulky-woocommerce-bulk-edit-products' )
			] );
			?>
        </div>

		<?php
		$this->filter_input_element( [
			'type'    => 'select',
			'id'      => 'allow_free_shipping',
			'options' => [
				''    => esc_html__( 'Allow free shipping', 'bulky-woocommerce-bulk-edit-products' ),
				'yes' => esc_html__( 'Yes', 'bulky-woocommerce-bulk-edit-products' ),
				'no'  => esc_html__( 'No', 'bulky-woocommerce-bulk-edit-products' ),
			]
		] );
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
			'label' => esc_html__( 'Coupons per page', 'bulky-woocommerce-bulk-edit-products' )
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

	public function get_column_titles() {
		$columns = wp_list_pluck( $this->define_columns(), 'title' );
		unset( $columns['id'] );
		unset( $columns['code'] );

		return $columns;
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
		Coupon_History::instance()->get_history_page();
	}
}
