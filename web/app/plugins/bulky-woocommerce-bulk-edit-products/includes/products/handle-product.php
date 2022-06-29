<?php

namespace WCBEditor\Includes\Products;

use WCBEditor\Includes\Helper;

defined( 'ABSPATH' ) || exit;

class Handle_Product {

	protected static $instance = null;
	protected $fields;
	protected $meta_fields;
	protected $taxonomy_fields;

	public function __construct() {
		$this->fields          = Products::instance()->filter_fields();
		$this->meta_fields     = get_option( 'vi_wbe_product_meta_fields' );
		$this->taxonomy_fields = get_option( 'vi_wbe_product_taxonomy_fields' );
	}

	public static function instance() {
		return self::$instance == null ? self::$instance = new self : self::$instance;
	}

	public function get_product_data( \WC_Product $product, $fields ) {
		$p_data = [];
		foreach ( $fields as $field ) {
			switch ( $field ) {

				case 'id':
					$p_data[] = $product->get_id();
					break;

				case 'parent_id':
					$p_data[] = $product->get_parent_id();
					break;

				case 'image':
					$p_data[] = $product->get_image_id();
					break;

				case 'post_title':
					$formatted_variation_list = wc_get_formatted_variation( $product, true, false, true );
					$formatted_variation_list = $formatted_variation_list ? ' - ' . $formatted_variation_list : $formatted_variation_list;

					$p_data[] = $product->get_name( 'edit' ) . $formatted_variation_list;
					break;

				case 'post_name':
					$p_data[] = urldecode( $product->get_slug( 'edit' ) );
					break;

				case 'post_date':
					$date     = $product->get_date_created( 'edit' );
					$p_data[] = ! $product->is_type( 'variation' ) && $date ? $date->date_i18n( 'Y-m-d H:i' ) : '';
					break;

				case 'product_type':
					$p_data[] = $product->get_type();
					break;

				case 'post_content':
					$p_data[] = ( $product->get_description() );
					break;

				case 'post_excerpt':
					$p_data[] = $product->get_short_description();
					break;

				case 'gallery':
					$p_data[] = $product->get_gallery_image_ids( 'edit' );
					break;

				case 'status':
					$p_data[] = $product->get_status( 'edit' );
					break;

				case 'password':
					$p_data[] = $product->get_post_password();
					break;

				case 'sku':
					$p_data[] = $product->get_sku( 'edit' );
					break;

				case 'featured':
					$p_data[] = $product->get_featured( 'edit' );
					break;

				case 'regular_price':
					$p_data[] = wc_format_localized_price( $product->get_regular_price( 'edit' ) );
					break;

				case 'sale_price':
					$p_data[] = wc_format_localized_price( $product->get_sale_price( 'edit' ) );
					break;

				case 'sale_date_from':
					$date     = $product->get_date_on_sale_from( 'edit' );
					$p_data[] = $date ? $date->date_i18n() : '';
					break;

				case 'sale_date_to':
					$date     = $product->get_date_on_sale_to( 'edit' );
					$p_data[] = $date ? $date->date_i18n() : '';
					break;

				case 'manage_stock':
					$p_data[] = $product->get_manage_stock( 'edit' );
					break;

				case 'stock':
					$p_data[] = $product->get_stock_quantity( 'edit' );
					break;

				case 'stock_status':
					$p_data[] = $product->get_stock_status( 'edit' );
					break;

				case 'allow_backorder':
					$p_data[] = $product->get_backorders();
					break;

				case 'sold_individually':
					$p_data[] = $product->get_sold_individually( 'edit' );
					break;

				case 'virtual':
					$p_data[] = $product->get_virtual( 'edit' );
					break;

				case 'product_cat':
					$p_data[] = $product->get_category_ids( 'edit' );
					break;

				case 'tags':
					$tag_ids = $product->get_tag_ids( 'edit' );
					$tags    = [];
					if ( ! empty( $tag_ids ) ) {
						$terms = get_terms( [
							'taxonomy'   => 'product_tag',
							'include'    => $tag_ids,
							'hide_empty' => false
						] );
						if ( ! empty( $terms ) ) {
							foreach ( $terms as $term ) {
								$tags[] = [ 'id' => $term->term_id, 'text' => $term->name ];
							}
						}
					}

					$p_data[] = $tags;
					break;

				case 'shipping_class':
					$p_data[] = $product->get_shipping_class_id( 'edit' );
					break;

				case 'weight':
					$p_data[] = $product->get_weight( 'edit' );
					break;

				case 'width':
					$p_data[] = $product->get_width( 'edit' );
					break;

				case 'height':
					$p_data[] = $product->get_height( 'edit' );
					break;

				case 'length':
					$p_data[] = $product->get_length( 'edit' );
					break;

				case 'product_url':
					$p_data[] = $product->is_type( 'external' ) ? $product->get_product_url() : '';
					break;

				case 'button_text':
					$p_data[] = $product->is_type( 'external' ) ? $product->get_button_text() : '';
					break;

				case 'grouped_products':
					$grouped_products = [];
					if ( $product->is_type( 'grouped' ) ) {
						$ids = $product->get_children();
						if ( ! empty( $ids ) ) {
							$products = wc_get_products( [ 'limit' => - 1, 'include' => $ids ] );
							if ( ! empty( $products ) ) {
								foreach ( $products as $p ) {
									$grouped_products[] = [ 'id' => $p->get_id(), 'text' => $p->get_name() ];
								}
							}
						}
					}

					$p_data[] = $grouped_products;
					break;

				case 'cross_sells':
					$p_data[] = Helper::get_multiple_products( $product->get_cross_sell_ids( 'edit' ) );
					break;

				case 'upsells':
					$p_data[] = Helper::get_multiple_products( $product->get_upsell_ids( 'edit' ) );
					break;

				case 'downloadable':
					$p_data[] = $product->get_downloadable( 'edit' );
					break;

				case 'download_file':
					$files = $product->get_downloads( 'edit' );
					$_file = [];
					if ( ! empty( $files ) && is_array( $files ) ) {
						foreach ( $files as $file ) {
							$_file[] = $file->get_data();
						}
					}
					$p_data[] = $_file;
					break;

				case 'download_limit':
					$limit = $product->get_download_limit( 'edit' );
					if ( $limit == - 1 ) {
						$limit = '';
					}
					$p_data[] = $limit;
					break;

				case 'download_expiry':
					$expiry = $product->get_download_expiry( 'edit' );
					if ( $expiry == - 1 ) {
						$expiry = '';
					}
					$p_data[] = $expiry;
					break;

				case 'tax_status':
					$tax_status = '';
					if ( ! $product->is_type( 'variation' ) ) {
						$tax_status = $product->get_tax_status( 'edit' );
					}
					$p_data[] = $tax_status;
					break;

				case 'tax_class':
					$p_data[] = $product->get_tax_class( 'edit' );
					break;

				case 'purchase_note':
					$p_data[] = $product->get_purchase_note( 'edit' );
					break;

				case 'menu_order':
					$p_data[] = $product->get_menu_order( 'edit' );
					break;

				case 'allow_reviews':
					$p_data[] = $product->get_reviews_allowed( 'edit' );
					break;

				case 'author':
					$author = '';
					$post   = get_post( $product->get_id() );
					if ( is_object( $post ) ) {
						$author = $post->post_author;
					}
					$p_data[] = $author;
					break;

				case 'catalog_visibility':
					$catalog_visibility = '';
					if ( ! $product->is_type( 'variation' ) ) {
						$catalog_visibility = $product->get_catalog_visibility( 'edit' );
					}
					$p_data[] = $catalog_visibility;
					break;

				case 'action':
					$href     = $product->get_permalink();
					$p_data[] = sprintf( "<span><a href='%s' target='_blank'>View</a></span>", esc_url( $href ) );

					break;

				case 'attributes':
					$attrs = $product->get_attributes();
					if ( $product->is_type( 'variation' ) ) {
						$p_data[] = $attrs;
					} else {
						$attr_data = [];
						foreach ( $attrs as $key => $attr ) {
							$attr_data[] = $attr->get_data();
						}
						$p_data[] = $attr_data;
					}

					break;

				case 'default_attributes':
					$p_data[] = $product->is_type( 'variable' ) ? $product->get_default_attributes() : '';

					break;

				default:
					if ( ! empty( $this->meta_fields[ $field ] ) ) {
						$meta_type = $this->meta_fields[ $field ]['input_type'];
						$data      = get_post_meta( $product->get_id(), $field, true );
						if ( $meta_type == 'json' && ! is_array( $data ) ) {
							$data = json_decode( $data, true );
						}
					}

					if ( ! empty( $this->taxonomy_fields ) && in_array( $field, $this->taxonomy_fields ) ) {
						$terms = get_the_terms( $product->get_id(), $field );
						$data  = wp_list_pluck( $terms, 'term_id' );
					}

					$p_data[] = $data ?? '';
					break;
			}
		}

		return $p_data;
	}

	public function get_product_data_for_edit( $product ) {
		return $this->get_product_data( $product, $this->fields );
	}

	public function parse_product_data_to_save( \WC_Product &$product, $type, $value ) {
		$pid    = $product->get_id();
		$p_type = $product->get_type();

		switch ( $type ) {
			case 'parent_id':
			case 'product_type':
			case 'sku':
				break;

			case 'image':
				$product->set_image_id( $value );
				break;
			case 'post_title':
				$product->set_name( $value );
				break;

			case 'post_name':
				$product->set_slug( $value );
				break;

			case 'post_date':
				$product->set_date_created( $value );
				break;

			case 'post_content':
				$product->set_description( $value );
				break;

			case 'post_excerpt':
				$product->set_short_description( $value );
				break;

			case 'gallery':
				$product->set_gallery_image_ids( $value );
				break;

			case 'status':
				$product->set_status( $value );
				break;

			case 'password':
				$product->set_post_password( $value );
				break;

			case 'featured':
				$product->set_featured( $value );
				break;

			case 'regular_price':
				if ( $p_type !== 'variable' ) {
					$product->set_regular_price( $value );
				}
				break;

			case 'sale_price':
				if ( $p_type !== 'variable' ) {
					$product->set_sale_price( $value );
				}
				break;

			case 'sale_date_from':
				if ( $p_type !== 'variable' ) {
					$product->set_date_on_sale_from( $value );
				}
				break;

			case 'sale_date_to':
				if ( $p_type !== 'variable' ) {
					$product->set_date_on_sale_to( $value );
				}
				break;

			case 'manage_stock':
				$product->set_manage_stock( $value );
				break;

			case 'stock':
				$product->set_stock_quantity( $value );
				break;

			case 'stock_status':
				$product->set_stock_status( $value );
				break;

			case 'allow_backorder':
				if ( ! $product->is_type( 'external' ) ) {
					$product->set_backorders( $value );
				}

				break;

			case 'sold_individually':
				$product->set_sold_individually( $value );
				break;

			case 'virtual':
				$product->set_virtual( $value );
				break;

			case 'product_cat':
				if ( is_string( $value ) ) {
					$value = explode( ';', $value );
				}
				$product->set_category_ids( $value );
				break;

			case 'tags':
				if ( ! is_array( $value ) ) {
					$value = [];
				}
				$tag_ids = wp_list_pluck( $value, 'id' );
				$product->set_tag_ids( $tag_ids );
				break;

			case 'shipping_class':
				$product->set_shipping_class_id( $value );
				break;

			case 'weight':
				$product->set_weight( $value );
				break;

			case 'width':
				$product->set_width( $value );
				break;

			case 'height':
				$product->set_height( $value );
				break;

			case 'length':
				$product->set_length( $value );
				break;

			case 'product_url':
				if ( $product->is_type( 'external' ) ) {
					$product->set_product_url( $value );
				}
				break;

			case 'button_text':
				if ( $product->is_type( 'external' ) ) {
					$product->set_button_text( $value );
				}
				break;

			case 'grouped_products':
				if ( ! is_array( $value ) ) {
					$value = [];
				}
				$children = wp_list_pluck( $value, 'id' );
				update_post_meta( $pid, '_children', $children );
				break;

			case 'upsells':
				if ( ! is_array( $value ) ) {
					$value = [];
				}
				$upsell_ids = wp_list_pluck( $value, 'id' );
				$product->set_upsell_ids( $upsell_ids );
				break;

			case 'cross_sells':
				if ( ! is_array( $value ) ) {
					$value = [];
				}
				$crosssell_ids = wp_list_pluck( $value, 'id' );
				$product->set_cross_sell_ids( $crosssell_ids );
				break;

			case 'downloadable':
				$product->set_downloadable( $value );
				break;

			case 'download_file':
				if ( ! empty( $value ) ) {
					if ( is_array( $value ) ) {
						$product->set_downloads( $value );
					}
				}
				break;

			case 'download_limit':
				if ( $value === '' ) {
					$value = - 1;
				}
				$product->set_download_limit( $value );
				break;

			case 'download_expiry':
				if ( $value === '' ) {
					$value = - 1;
				}
				$product->set_download_expiry( $value );
				break;

			case 'tax_status':
				if ( ! $product->is_type( 'variation' ) ) {
					$product->set_tax_status( $value );
				}
				break;

			case 'tax_class':
				$product->set_tax_class( $value );
				break;

			case 'purchase_note':
				$product->set_purchase_note( $value );
				break;

			case 'menu_order':
				$product->set_menu_order( $value );
				break;

			case 'allow_reviews':
				$product->set_reviews_allowed( $value );
				break;

			case 'author':
//				$post              = get_post( $product->get_id() );
//				$post->post_author = $value;

				break;

			case 'catalog_visibility':
				if ( ! $product->is_type( 'variation' ) ) {
					$value = $value ? $value : 'visible';
					$product->set_catalog_visibility( $value );
				}
				break;

			case 'attributes':
				if ( $product->get_type() !== 'variation' ) {
					$attributes = $this->prepare_attributes( $value );
					$product->set_attributes( $attributes );
				} else {
					$product->set_attributes( $value );
				}
				break;

			case 'default_attributes':
				if ( $product->get_type() == 'variable' ) {
					$product->set_default_attributes( $value );
				}

				break;

			default:
				$pid = $product->get_id();

				$meta_fields = get_option( 'vi_wbe_product_meta_fields' );

				if ( ! empty( $meta_fields ) && is_array( $meta_fields ) && in_array( $type, array_keys( $meta_fields ) ) ) {
					$data_type = $meta_fields[ $type ]['input_type'] ?? '';

					if ( $data_type ) {
						switch ( $data_type ) {
							case 'textinput':
							case 'numberinput':
							case 'checkbox':
							case 'select':
								$value = sanitize_text_field( $value );
								break;

							case 'texteditor':
								$value = wp_kses_post( $value );
								break;

							case 'multiselect':
								$value = wc_clean( $value );
								break;
							case 'array':
								$value = array_map( 'wp_kses_post', $value );
								break;

							case 'json':
								$value = array_map( 'wp_kses_post', $value );
								$value = json_encode( $value );
								break;

							case 'image':
								$value = esc_url_raw( $value );
								break;
						}

						$_POST[ $type ] = $value;

						update_post_meta( $pid, $type, $value );
					}
				}

				$taxonomy_fields = get_option( 'vi_wbe_product_taxonomy_fields' );

				if ( ! empty( $taxonomy_fields ) && is_array( $taxonomy_fields ) && in_array( $type, $taxonomy_fields ) ) {
					wp_set_object_terms( $pid, $value, $type );
				}

				break;
		}

	}

	public function prepare_attributes( $attributes ) {
		if ( empty( $attributes ) || ! is_array( $attributes ) ) {
			return [];
		}

		$result = [];
		foreach ( $attributes as $attr ) {
			$attribute_id   = 0;
			$attribute_name = esc_html( $attr['name'] );

			if ( 'pa_' === substr( $attribute_name, 0, 3 ) ) {
				$attribute_id = wc_attribute_taxonomy_id_by_name( $attribute_name );
			}

			if ( isset( $attr['is_taxonomy'] ) && $attr['is_taxonomy'] ) {
				$options = $attr['options'] ?? '';
			} else {
				$options = $attr['value'] ?? '';
			}

			if ( is_array( $options ) ) {
				// Term ids sent as array.
				$options = wp_parse_id_list( $options );
			} else {
				// Terms or text sent in textarea.
				$options = 0 < $attribute_id ? wc_sanitize_textarea( esc_html( wc_sanitize_term_text_based( $options ) ) ) : wc_sanitize_textarea( esc_html( $options ) );
				$options = wc_get_text_attributes( $options );
			}

			if ( empty( $options ) ) {
				continue;
			}

			$attribute = new \WC_Product_Attribute();
			$attribute->set_id( $attribute_id );
			$attribute->set_name( $attribute_name );
			$attribute->set_options( $options );
			$attribute->set_position( $attr['position'] ?? 0 );
			$attribute->set_visible( $attr['visible'] ?? '' );
			$attribute->set_variation( $attr['variation'] ?? '' );
			$result[] = $attribute;
		}

		return $result;
	}

}
