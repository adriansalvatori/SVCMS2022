<?php

namespace WCBEditor\Includes\Products;

defined( 'ABSPATH' ) || exit;

class Filters {

	protected static $instance = null;
	public $filter;

	public function __construct() {
		add_filter( 'posts_where', [ $this, 'add_filter_to_posts_where' ] );
		add_filter( 'woocommerce_product_data_store_cpt_get_products_query', [ $this, 'product_data_store_cpt_get_products_query' ], 10, 2 );
	}

	public static function instance() {
		return self::$instance == null ? self::$instance = new self : self::$instance;
	}

	public function set_args( $args ) {
		$user_id = get_current_user_id();
		$filter  = get_transient( "vi_wbe_filter_data_{$user_id}" );

		if ( empty( $filter ) ) {
			return $args;
		}

		$this->filter = $filter;

		if ( ! empty( $filter['id'] ) && strpos( $filter['id'], '-' ) === false ) {
			$args['include'] = explode( ',', str_replace( ' ', '', $filter['id'] ) );
		}

		$string_type = [ 'type', 'status', 'stock_status', 'backorders', 'visibility' ];
		foreach ( $string_type as $key ) {
			if ( ! empty( $filter[ $key ] ) ) {
				$args[ $key ] = $filter[ $key ];
			}
		}

		$boolean_type = [ 'featured', 'downloadable', 'sold_individually' ];
		foreach ( $boolean_type as $key ) {
			if ( ! empty( $filter[ $key ] ) ) {
				$args[ $key ] = $filter[ $key ] == 'yes';
			}
		}

		return $args;
	}

	public function product_data_store_cpt_get_products_query( $wp_query_args, $query_vars ) {
		if ( empty( $this->filter ) ) {
			return $wp_query_args;
		}

		$taxonomies = $this->filter['taxonomies'] ?? '';
		if ( empty( $taxonomies ) || ! is_array( $taxonomies ) ) {
			return $wp_query_args;
		}

		$operators = $this->filter['operator'] ?? [];

		foreach ( $taxonomies as $taxonomy => $terms ) {
			if ( empty( array_filter( $terms ) ) ) {
				continue;
			}

			$field = 'pa_' === substr( $taxonomy, 0, 3 ) ? 'slug' : 'term_id';

			$operator = ! empty( $operators[ $taxonomy ] ) ? $operators[ $taxonomy ] : 'IN';
			switch ( $operator ) {
				case 'or':
					$operator = 'IN';
					break;
				case 'and':
					$operator = 'AND';
					break;
				case 'not_in':
					$operator = 'NOT IN';
					break;
			}

			$wp_query_args['tax_query'][] = [
				'taxonomy'         => $taxonomy,
				'field'            => $field,
				'terms'            => $terms,
				'include_children' => true,
				'operator'         => $operator
			];
		}

		if ( ! empty( $wp_query_args['tax_query'] ) ) {
			$wp_query_args['tax_query']['relation'] = 'AND';
		}

		return $wp_query_args;
	}

	public function add_filter_to_posts_where( $where ) {
		if ( empty( $this->filter ) ) {
			return $where;
		}

		global $wpdb;

		$filter = $this->filter;

		if ( ! empty( $filter['id'] ) && strpos( $filter['id'], '-' ) !== false ) {
			$ids      = array_filter( explode( '-', str_replace( ' ', '', $filter['id'] ) ) );
			$count_id = count( $ids );
			if ( $count_id == 1 ) {
				$id    = absint( $ids[0] );
				$where .= " AND {$wpdb->posts}.ID = {$id} ";
			} elseif ( $count_id == 2 ) {
				$start_id = absint( $ids[0] );
				$end_id   = absint( $ids[1] );

				if ( $start_id < $end_id ) {
					$where .= " AND {$wpdb->posts}.ID >= {$start_id} AND {$wpdb->posts}.ID <= {$end_id} ";
				} elseif ( $start_id > $end_id ) {
					$where .= " AND {$wpdb->posts}.ID >= {$end_id} AND {$wpdb->posts}.ID <= {$start_id} ";
				} else {
					$where .= " AND {$wpdb->posts}.ID = {$start_id} ";
				}
			}
		}

		if ( ! empty( $filter['post_date_from'] ) ) {
			$where .= " AND post_date >= '{$filter['post_date_from']}' ";
		}

		if ( ! empty( $filter['post_date_to'] ) ) {
			$where .= " AND post_date <= '{$filter['post_date_to']}' ";
		}

		$product_ids_from_range_type = $this->parse_type_range( $filter );
		if ( ! empty( $product_ids_from_range_type ) ) {
			$product_ids = $product_ids_from_range_type !== - 1 ? implode( ',', $product_ids_from_range_type ) : '';
			$where       .= $product_ids ? " AND ( $wpdb->posts.ID IN($product_ids) )" : " AND (1=2)";
		}

		if ( ! empty( $filter['sale_date_from'] ) || ! empty( $filter['sale_date_to'] ) ) {
			$product_ids = $this->get_product_ids_from_sale_schedule( $filter['sale_date_from'], $filter['sale_date_to'] );
			$product_ids = implode( ',', $product_ids );
			$where       .= ! empty( $product_ids ) ? " AND ( $wpdb->posts.ID IN($product_ids) )" : " AND (1=2)";
		}

		$where .= $this->text_search();
		$where .= $this->sku_search();

		unset( $this->filter );

		return $where;
	}

	public function parse_type_range( $filter ) {
		$product_ids = [];
		$flag        = 0;

		$fields = [
			[
				'from'    => 'regular_price_from',
				'to'      => 'regular_price_to',
				'metakey' => '_regular_price',
			],
			[
				'from'    => 'sale_price_from',
				'to'      => 'sale_price_to',
				'metakey' => '_sale_price',
			],
			[
				'from'    => 'stock_quantity_from',
				'to'      => 'stock_quantity_to',
				'metakey' => '_stock',
			],
			[
				'from'    => 'length_from',
				'to'      => 'length_to',
				'metakey' => '_length',
			],
			[
				'from'    => 'width_from',
				'to'      => 'width_to',
				'metakey' => '_width',
			],
			[
				'from'    => 'height_from',
				'to'      => 'height_to',
				'metakey' => '_height',
			],
			[
				'from'    => 'weight_from',
				'to'      => 'weight_to',
				'metakey' => '_weight',
			],
		];

		foreach ( $fields as $field ) {

			if ( $filter[ $field['from'] ] != '' || $filter[ $field['to'] ] != '' ) {
				$flag ++;
				$found_ids   = $this->get_product_ids( $filter[ $field['from'] ], $filter[ $field['to'] ], $field['metakey'] );
				$product_ids = empty( $product_ids ) ? $found_ids : array_intersect( $product_ids, $found_ids );
			}
		}

		if ( $flag && empty( $product_ids ) ) {
			return - 1;
		}

		return array_values( array_unique( $product_ids ) );
	}

	public function get_product_ids( $from, $to, $meta_key ) {
		global $wpdb;

		$from = $from != '' ? floatval( $from ) : 0;
		$to   = $to != '' ? floatval( $to ) : PHP_INT_MAX;

		if ( $from == $to ) {
			$query = "SELECT posts.ID FROM {$wpdb->posts} AS posts
                    LEFT JOIN {$wpdb->postmeta} AS postmeta ON ( posts.ID = postmeta.post_id )
                    WHERE posts.post_type IN ('product','product_variation')
                    AND postmeta.meta_key = '{$meta_key}' AND postmeta.meta_value ={$from}";
		} else {
			$query = "SELECT posts.ID FROM {$wpdb->posts} AS posts
                    LEFT JOIN {$wpdb->postmeta} AS postmeta ON ( posts.ID = postmeta.post_id )
                    WHERE posts.post_type IN ('product','product_variation')
                    AND postmeta.meta_key = '{$meta_key}' AND postmeta.meta_value BETWEEN {$from} AND {$to}";
		}

		return $this->get_parent_product_from_query( $query );
	}

	public function get_product_ids_from_sale_schedule( $from, $to ) {
		global $wpdb;

		$from = floatval( ! empty( $from ) ) ? strtotime( $from ) : 0;
		$to   = floatval( ! empty( $to ) ) ? strtotime( "tomorrow {$to}" ) - 1 : PHP_INT_MAX;

		$query = "SELECT posts.ID FROM {$wpdb->posts} AS posts INNER JOIN {$wpdb->postmeta} AS postmeta ON ( posts.ID = postmeta.post_id )  
					INNER JOIN {$wpdb->postmeta} AS mt1 ON ( posts.ID = mt1.post_id ) 
					WHERE 1=1  AND (( postmeta.meta_key = '_sale_price_dates_from' AND CAST(postmeta.meta_value AS SIGNED) BETWEEN {$from} AND {$to} )
       								 OR ( mt1.meta_key = '_sale_price_dates_to'  AND CAST(mt1.meta_value AS SIGNED) BETWEEN {$from} AND {$to})) 
    				AND posts.post_type  IN ('product','product_variation')  GROUP BY posts.ID ";

		return $this->get_parent_product_from_query( $query );
	}

	public function get_parent_product_from_query( $query ) {
		global $wpdb;
		$all_products = $wpdb->get_results( $query, ARRAY_N );
		$all_products = array_column( $all_products, 0 );

		return $this->get_final_parent_products( $all_products );
	}

	public function get_final_parent_products( $all_product_ids ) {
		if ( ! empty( $all_product_ids ) ) {
			global $wpdb;
			$string_all_products = implode( ',', $all_product_ids );
			$_query              = "SELECT posts.post_parent FROM {$wpdb->posts} AS posts WHERE posts.ID IN ({$string_all_products}) AND posts.post_parent > 0";
			$variable_products   = $wpdb->get_results( $_query, ARRAY_N );
			$variable_products   = array_column( $variable_products, 0 );
			$product_ids         = array_merge( $variable_products, $all_product_ids );
		}

		return $product_ids ?? [];
	}

	public function text_search() {
		$items = array_map( function ( $key ) {
			return [
				'type'     => $key,
				'value'    => $this->filter[ $key ] ?? '',
				'behavior' => $this->filter['behavior'][ $key ] ?? '',
			];
		}, [ 'post_title', 'post_content', 'post_excerpt', 'post_name' ] );

		if ( empty( $items ) || ! is_array( $items ) ) {
			return '';
		}

		$where = '';

		foreach ( $items as $item ) {
			if ( ! $item['value'] && $item['behavior'] !== 'empty' ) {
				continue;
			}

			$type  = $item['type'];
			$value = sanitize_text_field( wp_specialchars_decode( trim( urldecode( $item['value'] ) ) ) );

			$query = '';
			switch ( $item['behavior'] ) {
				case 'empty':
					$query .= "{$type} =''";
					break;

				case 'exact':
					$query .= "{$type} ='{$value}'";
					break;

				case 'not':
					$query .= "{$type} NOT LIKE '%{$value}%'";
					break;

				case 'begin':
					$query .= "{$type} REGEXP '^{$value}'";
					break;

				case 'end':
					$query .= "{$type} REGEXP '{$value}$'";
					break;

				default:
					$query .= "{$type} LIKE '%{$value}%'";
					break;
			}

			if ( $query ) {
				$where .= " AND ($query)";
			}
		}

		return $where;
	}

	public function sku_search() {
		global $wpdb;

		$sku          = $this->filter['sku'] ?? '';
		$sku_behavior = $this->filter['behavior']['sku'] ?? '';

		if ( $sku_behavior == 'empty' ) {
			$args = array(
				'post_type'  => array( 'product', 'product_variation' ),
				'fields'     => 'ids',
				'meta_query' => array(
					array(
						'key'     => '_sku',
						'compare' => 'NOT EXISTS',
					),
				)
			);

			$product_variations_ids = get_posts( $args ); //ids of simple + variation
			$found_ids              = $this->get_final_parent_products( $product_variations_ids );
		} else {
			if ( ! $sku ) {
				return '';
			}

			$sku           = sanitize_text_field( wp_specialchars_decode( trim( urldecode( $sku ) ) ) );
			$sku_condition = '';

			switch ( $sku_behavior ) {
				case 'exact':
					$sku_condition .= "postmeta.meta_value = '{$sku}'";
					break;

				case 'not':
					$sku_condition .= "postmeta.meta_value NOT LIKE '%{$sku}%'";
					break;

				case 'begin':
					$sku_condition .= "postmeta.meta_value REGEXP '^{$sku}'";
					break;

				case 'end':
					$sku_condition .= "postmeta.meta_value REGEXP '{$sku}$'";
					break;

				default:
					$sku_condition .= "postmeta.meta_value LIKE '%{$sku}%'";
					break;
			}

			$query = "SELECT posts.ID FROM {$wpdb->posts} AS posts
                        LEFT JOIN {$wpdb->postmeta} AS postmeta ON ( posts.ID = postmeta.post_id )
                        WHERE posts.post_type IN ('product','product_variation')
                        AND postmeta.meta_key = '_sku' AND ({$sku_condition})";

			$found_ids = $this->get_parent_product_from_query( $query );
		}

		$found_ids = implode( ',', array_unique( $found_ids ) );

		return $found_ids ? " AND ( $wpdb->posts.ID IN($found_ids) )" : " AND (1=2)";
	}
}