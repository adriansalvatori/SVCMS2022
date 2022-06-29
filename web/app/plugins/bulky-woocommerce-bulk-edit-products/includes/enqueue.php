<?php

namespace WCBEditor\Includes;

use WCBEditor\Includes\Coupons\Coupon_History;
use WCBEditor\Includes\Coupons\Coupons;
use WCBEditor\Includes\Orders\Order_History;
use WCBEditor\Includes\Orders\Orders;
use WCBEditor\Includes\Products\Product_History;
use WCBEditor\Includes\Products\Products;

defined( 'ABSPATH' ) || exit;

class Enqueue {

	protected static $instance = null;

	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'remove_other_plugin_scripts' ], PHP_INT_MAX );
	}

	public static function instance() {
		return self::$instance == null ? self::$instance = new self : self::$instance;
	}

	public function register_styles( $styles, $libs = false ) {
		$src    = $libs ? WCBE_CONST['libs_url'] : WCBE_CONST['dist_url'];
		$suffix = $libs || ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '.min.css' : '.css';

		$styles = explode( ',', str_replace( ' ', '', $styles ) );
		foreach ( $styles as $style ) {
			wp_register_style( WCBE_CONST['assets_slug'] . $style, $src . $style . $suffix, '', WCBE_CONST['version'] );
		}
	}

	public function register_scripts( $scripts, $libs = false ) {
		$suffix = SCRIPT_DEBUG ? '' : '.min';
		$suffix = $libs ? '.min' : $suffix;

		$src = $libs ? WCBE_CONST['libs_url'] : WCBE_CONST['dist_url'];

		foreach ( $scripts as $script => $depend ) {
			wp_register_script( WCBE_CONST['assets_slug'] . $script, $src . $script . $suffix . '.js', (array) $depend, WCBE_CONST['version'] );
		}
	}

	public function enqueue_styles( $styles ) {
		$styles = explode( ',', str_replace( ' ', '', $styles ) );
		foreach ( $styles as $style ) {
			wp_enqueue_style( WCBE_CONST['assets_slug'] . $style );
		}
	}

	public function enqueue_scripts( $scripts ) {
		$scripts = explode( ',', str_replace( ' ', '', $scripts ) );
		foreach ( $scripts as $script ) {
			wp_enqueue_script( WCBE_CONST['assets_slug'] . $script );
		}
	}

	public function edit_page_enqueue() {
		$this->enqueue_styles( 'message,checkbox,accordion,jsoneditor,popup,tab,table,dimmer,modal,label,input,form,select2,transition,dropdown,icon,segment,menu,button,jsuite,jexcel,editor' );
		$this->enqueue_scripts( 'accordion,jsoneditor,tab,dimmer,modal,select2,transition,dropdown,jsuite,jexcel,editor' );
	}

	public function admin_enqueue_scripts() {
		if ( isset( $_GET['page'] ) && $_GET['page'] ) {
			$page = sanitize_text_field( $_GET['page'] );

			$this->register_styles( 'message,tab,menu,segment,form,table,checkbox,dropdown,transition,popup,accordion,select2,button,input,label,list,dimmer,modal,icon,jsoneditor,jsuite,jexcel', true );

			$this->register_styles( 'settings, editor' );

			$lib_scripts = [
				'transition' => [ 'jquery' ],
				'dropdown'   => [ 'jquery' ],
				'modal'      => [ 'jquery' ],
				'dimmer'     => [ 'jquery' ],
				'select2'    => [ 'jquery' ],
				'accordion'  => [ 'jquery' ],
				'tab'        => [ 'jquery' ],
//				'jsuite'     => [],
				'jsoneditor' => [],
			];

			$scripts = [
				'jsuite'   => [],
				'jexcel'   => [],
				'editor'   => [ 'jquery' ],
				'settings' => [ 'jquery' ]
			];

			$this->register_scripts( $lib_scripts, true );
			$this->register_scripts( $scripts );

			$localize_params = [
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'nonce'         => wp_create_nonce( 'vi_wbe_nonce' ),
				'adminUrl'      => admin_url(),
				'frontendUrl'   => site_url(),
				'columns'       => '',
				'idMapping'     => '',
				'idMappingFlip' => '',
				'attributes'    => '',
				'metaFields'    => '',
				'settings'      => '',
				'historyPages'  => '',
				'editType'      => '',
			];

			switch ( $page ) {
				case 'vi_wbe_edit_products':
					wp_enqueue_media();
					wp_enqueue_editor();
					wp_enqueue_script( 'jquery-ui-sortable' );
					$this->edit_page_enqueue();

					$products_class = Products::instance();
					$columns        = $products_class->get_columns();
					$id_mapping     = array_keys( $columns );

					$attribute_taxonomies = wc_get_attribute_taxonomies();

					$attr_data = [];
					foreach ( $attribute_taxonomies as $tax ) {
						$taxonomy = wc_attribute_taxonomy_name( $tax->attribute_name );

						$attr_data[ $taxonomy ]['data'] = (array) $tax;

						if ( taxonomy_exists( $taxonomy ) ) {
							$terms = get_terms( $taxonomy, 'hide_empty=0' );
							foreach ( $terms as $term ) {
								$attr_data[ $taxonomy ]['terms'][ $term->term_id ] = [ 'slug' => $term->slug, 'text' => $term->name ];
							}
						}
					}

					$localize_params['editType']       = 'products';
					$localize_params['columns']        = wp_json_encode( array_values( $columns ) );
					$localize_params['idMapping']      = $id_mapping;
					$localize_params['idMappingFlip']  = array_flip( $id_mapping );
					$localize_params['attributes']     = $attr_data;
					$localize_params['metaFields']     = get_option( 'vi_wbe_product_meta_fields' );
					$localize_params['settings']       = $products_class->get_settings();
					$localize_params['historyPages']   = Product_History::instance()->count_history_pages();
					$localize_params['cellDependType'] = $this->set_cell_depend();

					break;

				case 'vi_wbe_edit_orders':
					wp_enqueue_media();
					wp_enqueue_editor();
					$this->edit_page_enqueue();

					$orders_class = Orders::instance();
					$columns      = $orders_class->get_columns();
					$id_mapping   = array_keys( $columns );

					$localize_params['editType']      = 'orders';
					$localize_params['columns']       = json_encode( array_values( $columns ) );
					$localize_params['idMapping']     = $id_mapping;
					$localize_params['idMappingFlip'] = array_flip( $id_mapping );
					$localize_params['metaFields']    = get_option( 'vi_wbe_order_meta_fields' );
					$localize_params['settings']      = $orders_class->get_settings();
					$localize_params['historyPages']  = Order_History::instance()->count_history_pages();
					$localize_params['orderActions']  = Helper::order_actions();

					break;

				case 'vi_wbe_edit_coupons':
					wp_enqueue_media();
					wp_enqueue_editor();
					$this->edit_page_enqueue();

					$coupons_class = Coupons::instance();
					$columns       = $coupons_class->get_columns();
					$id_mapping    = array_keys( $columns );

					$localize_params['editType']       = 'coupons';
					$localize_params['columns']        = json_encode( array_values( $columns ) );
					$localize_params['idMapping']      = $id_mapping;
					$localize_params['idMappingFlip']  = array_flip( $id_mapping );
					$localize_params['metaFields']     = get_option( 'vi_wbe_coupon_meta_fields' );
					$localize_params['settings']       = $coupons_class->get_settings();
					$localize_params['historyPages']   = Coupon_History::instance()->count_history_pages();
					$localize_params['couponGenerate'] = [
						'characters'  => apply_filters( 'woocommerce_coupon_code_generator_characters', 'ABCDEFGHJKMNPQRSTUVWXYZ23456789' ),
						'char_length' => apply_filters( 'woocommerce_coupon_code_generator_character_length', 8 ),
						'prefix'      => apply_filters( 'woocommerce_coupon_code_generator_prefix', '' ),
						'suffix'      => apply_filters( 'woocommerce_coupon_code_generator_suffix', '' ),
					];

					break;

				case 'vi_wbe_settings':
					$this->enqueue_styles( 'form,icon,input,menu,tab,checkbox,button,segment,settings' );
					$this->enqueue_scripts( 'tab,settings' );

					return;
			}

			wp_localize_script( WCBE_CONST['assets_slug'] . 'editor', 'wbeParams', $localize_params );
			wp_localize_script( WCBE_CONST['assets_slug'] . 'editor', 'wbeI18n', [ 'i18n' => I18n::i18n() ] );
		}

		$screen = get_current_screen()->id;
		if ( $screen == 'edit-product' ) {
			$this->register_scripts( [ 'admin' => [ 'jquery' ] ] );
			$this->enqueue_scripts( 'admin' );
			$params = [
				'url'  => admin_url( 'admin.php?page=vi_wbe_edit_products' ),
				'text' => esc_html__( 'Go to Bulk Editor page', 'bulky-woocommerce-bulk-edit-products' ),
			];

			wp_localize_script( WCBE_CONST['assets_slug'] . 'admin', 'viWbeParams', $params );
		}

	}

	public function remove_other_plugin_scripts() {
		if ( isset( $_GET['page'] ) && $_GET['page'] ) {
			if ( in_array( $_GET['page'], [ 'vi_wbe_edit_products', 'vi_wbe_settings' ] ) ) {
				global $wp_scripts;
				$excludes = [ 'uip-toolbar-app', 'uip-app', 'uip-vue', 'query-monitor' ];
				$scripts  = $wp_scripts->registered;
				foreach ( $scripts as $k => $script ) {
					if ( strpos( $script->src, WP_CONTENT_URL ) !== false && strpos( $script->handle, WCBE_CONST['assets_slug'] ) === false ) {
						if ( in_array( $script->handle, $excludes ) ) {
							continue;
						}
//						unset( $wp_scripts->registered[ $k ] );
//						wp_dequeue_script( $script->handle );
					}
				}

			}
		}
	}

	public function set_cell_depend() {

		$depend = [
			'simple'    => [
				'default_attributes',
				'grouped_products',
				'product_url',
				'button_text',
			],
			'variable'  => [
				'regular_price',
				'sale_price',
				'download_file',
				'download_limit',
				'download_expiry',
				'stock_status',
				'downloadable',
				'sale_date_from',
				'sale_date_to',
				'virtual',
				'grouped_products',
				'product_url',
				'button_text',
			],
			'grouped'   => [
				'regular_price',
				'sale_price',
				'product_url',
				'button_text',
				'default_attributes',
				'cross_sells',
			],
			'external'  => [
				'grouped_products',
				'default_attributes',
				'cross_sells',
				'allow_backorder',
				'sold_individually',
				'virtual',
				'download_file',
				'download_limit',
				'download_expiry',
				'stock_status',
				'downloadable',
			],
			'variation' => [
				'post_excerpt',
				'post_title',
				'post_date',
				'slug',
				'featured',
				'product_cat',
				'product_type',
				'catalog_visibility',
				'allow_reviews',
				'sold_individually',
				'author',
				'tax_status',
				'tags',
				'upsells',
				'cross_sells',
				'post_name',
				'purchase_note',
				'gallery',
				'password',
				'default_attributes',
				'grouped_products',
				'product_url',
				'button_text',
			],
		];

		$taxes = Helper::get_extra_product_taxonomies();

		if ( ! empty( $taxes ) ) {
			foreach ( $taxes as $tax => $name ) {
				$taxonomy    = get_taxonomy( $tax );
				$object_type = $taxonomy->object_type;
				if ( ! in_array( 'product', $object_type ) ) {
					$depend['simple'][]   = $tax;
					$depend['variable'][] = $tax;
					$depend['grouped'][]  = $tax;
					$depend['external'][] = $tax;
				}
				if ( ! in_array( 'product_variation', $object_type ) ) {
					$depend['variation'][] = $tax;
				}
			}
		}

		return apply_filters( 'vi_wbe_set_cell_depend', $depend );
	}
}