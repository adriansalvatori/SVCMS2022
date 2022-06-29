<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Frontend_Loop_Product_Swatches {
	protected $settings;
	protected static $language;
	public static $is_loop;
	protected $position;
	protected $theme;
	protected $theme_swatches_pos;
	protected $add_to_cart;
	protected $attr_name_enable;
	protected $max_attr_items, $link_more_enable;
	protected $slider_enable, $slider_show;

	public function __construct() {
		$this->settings = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DATA::get_instance();
		if ( ! $this->settings->get_params( 'product_list_enable' ) ) {
			return;
		}
		$this->position = $this->settings->get_params( 'product_list_position' );
		/*variation swatches position*/
		add_action( 'init', array( $this, 'init' ) );
		if ( 'custom_only' !== $this->position ) {
			add_action( 'woocommerce_shop_loop_item_title', array(
				$this,
				'wpvs_before_loop_item_title'
			), PHP_INT_MIN );
			add_action( 'woocommerce_shop_loop_item_title', array( $this, 'wpvs_after_loop_item_title' ), PHP_INT_MAX );
			add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'wpvs_before_loop_item_price' ), 9 );
			add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'wpvs_after_loop_item_price' ), 11 );
			add_action( 'woocommerce_before_template_part', array( $this, 'wpvs_before_template_loop' ) );
			add_action( 'woocommerce_after_template_part', array( $this, 'wpvs_after_template_loop' ) );
		}
		$product_list_custom_hook = $this->settings->get_params( 'product_list_custom_hook' );
		if ( $product_list_custom_hook ) {
			add_action( $product_list_custom_hook, array( $this, 'product_list_custom_hook' ) );
		}

		add_filter( 'woocommerce_loop_add_to_cart_link', array(
			$this,
			'wpvs_woocommerce_loop_add_to_cart_link',
		), 99, 2 );
		self::add_ajax_events();
		/*Do not apply swatches to Upsells of WooCommerce Boost Sales*/
		add_action( 'woocommerce_boost_sales_single_product_summary', array(
			$this,
			'remove_filter_woocommerce_loop_add_to_cart_link'
		), 1 );
		add_action( 'woocommerce_boost_sales_single_product_summary', array(
			$this,
			'add_filter_woocommerce_loop_add_to_cart_link'
		), 99 );
		/*Elementor may apply wp_kses to its elements on single product hence form class/data are not allowed*/
		add_filter( 'elementor/frontend/section/before_render', array( $this, 'elementor_before_render' ) );
		add_filter( 'elementor/frontend/section/after_render', array( $this, 'elementor_after_render' ) );
	}

	public function elementor_before_render() {
		add_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses_allowed_html' ) );
	}

	public function elementor_after_render() {
		remove_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses_allowed_html' ) );
	}

	public function wp_kses_allowed_html( $tags ) {
		$tags['form']['class']  = 1;
		$tags['form']['data-*'] = 1;

		return $tags;
	}

	public function product_list_custom_hook() {
		global $product;
		if ( ( ! $product || ! $product->is_in_stock() && $this->settings->get_params( 'out_of_stock_variation_disable_archive' ) ) || ! $product->is_type( 'variable' ) ) {
			return;
		}
		$this->get_loop_attribute_option_html( $product );
		self::variation_price_html();
	}

	public function remove_filter_woocommerce_loop_add_to_cart_link() {
		remove_filter( 'woocommerce_loop_add_to_cart_link', array(
			$this,
			'wpvs_woocommerce_loop_add_to_cart_link',
		), 99 );
	}

	public function add_filter_woocommerce_loop_add_to_cart_link() {
		add_filter( 'woocommerce_loop_add_to_cart_link', array(
			$this,
			'wpvs_woocommerce_loop_add_to_cart_link',
		), 99, 2 );
	}

	public static function get_language() {
		if ( self::$language === null ) {
			self::$language = '';
			if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
				$default_lang     = apply_filters( 'wpml_default_language', null );
				$current_language = apply_filters( 'wpml_current_language', null );

				if ( $current_language && $current_language !== $default_lang ) {
					self::$language = $current_language;
				}
			} else if ( class_exists( 'Polylang' ) ) {
				$default_lang     = pll_default_language( 'slug' );
				$current_language = pll_current_language( 'slug' );
				if ( $current_language && $current_language !== $default_lang ) {
					self::$language = $current_language;
				}
			}
		}

		return self::$language;
	}

	public static function add_ajax_events() {
		$ajax_events = array(
			'wpvs_add_to_cart' => true,
		);
		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
				// WC AJAX can be used for frontend ajax requests
				add_action( 'wc_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	public static function wpvs_add_to_cart() {
		$notices = WC()->session->get( 'wc_notices', array() );
		if ( ! empty( $notices['error'] ) ) {
			wp_send_json( array( 'error' => true ) );
		}
		WC_AJAX::get_refreshed_fragments();
		die();
	}

	public function wpvs_before_loop_item_title() {
		$position = $this->position;
		if ( $position !== 'before_title' ) {
			return;
		}
		global $product;
		if ( ( ! $product->is_in_stock() && $this->settings->get_params( 'out_of_stock_variation_disable_archive' ) ) || ! $product->is_type( 'variable' ) ) {
			return;
		}
		$this->get_loop_attribute_option_html( $product );
		self::variation_price_html();
	}

	public function wpvs_after_loop_item_title() {
		$position = $this->position;
		if ( $position !== 'after_title' ) {
			return;
		}
		global $product;
		if ( ( ! $product->is_in_stock() && $this->settings->get_params( 'out_of_stock_variation_disable_archive' ) ) || ! $product->is_type( 'variable' ) ) {
			return;
		}
		$this->get_loop_attribute_option_html( $product );
		self::variation_price_html();
	}

	public function wpvs_before_loop_item_price() {
		$position = $this->position;
		if ( $position !== 'before_price' ) {
			return;
		}
		global $product;
		if ( ( ! $product->is_in_stock() && $this->settings->get_params( 'out_of_stock_variation_disable_archive' ) ) || ! $product->is_type( 'variable' ) ) {
			return;
		}
		$this->get_loop_attribute_option_html( $product );
		self::variation_price_html();
	}

	public function wpvs_after_loop_item_price() {
		global $product;
		if ( ( ! $product->is_in_stock() && $this->settings->get_params( 'out_of_stock_variation_disable_archive' ) ) || ! $product->is_type( 'variable' ) ) {
			return;
		}
		$position = $this->position;
		if ( $position ) {
			if ( $position !== 'after_price' ) {
				return;
			}
			self::variation_price_html();
			$this->get_loop_attribute_option_html( $product );
		}
	}

	public function wpvs_before_template_loop( $template_name ) {
		$position = $this->position;
		if ( ! in_array( $position, apply_filters( 'viwpvs_supported_position_before_template_loop', array( 'before_price_1' ) ) ) ) {
			return;
		}
		if ( ! in_array( $template_name, apply_filters( 'viwpvs_supported_position_before_template_loop_name', array( 'loop/price.php' ) )
		) ) {
			return;
		}
		global $product;
		if ( ( ! $product->is_in_stock() && $this->settings->get_params( 'out_of_stock_variation_disable_archive' ) ) || ! $product->is_type( 'variable' ) ) {
			return;
		}
		$this->get_loop_attribute_option_html( $product );
		self::variation_price_html();
	}

	public function wpvs_after_template_loop( $template_name ) {
		$position = $this->position;
		if ( $position !== 'after_price_1' ) {
			return;
		}
		if ( ! in_array( $template_name,
			array(
				'loop/price.php',
			) ) ) {
			return;
		}
		global $product;
		if ( ( ! $product->is_in_stock() && $this->settings->get_params( 'out_of_stock_variation_disable_archive' ) ) || ! $product->is_type( 'variable' ) ) {
			return;
		}
		self::variation_price_html();
		$this->get_loop_attribute_option_html( $product );
	}

	/**
	 * @param $html
	 * @param $product WC_Product
	 *
	 * @return string
	 */
	public function wpvs_woocommerce_loop_add_to_cart_link( $html, $product ) {
		$position = $this->position;
		if ( ( ! $product->is_in_stock() && $this->settings->get_params( 'out_of_stock_variation_disable_archive' ) ) || ! $product->is_type( 'variable' ) || ! $position ) {
			return $html;
		}
		$add_to_cart = $this->add_to_cart ?? $this->settings->get_params( 'product_list_add_to_cart' );
		if ( $add_to_cart && $position ) {
			if ( $this->settings->get_params( 'product_list_qty' ) ) {
				$min_qty = apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product );
				$min_qty = $min_qty < 0 ? '1' : $min_qty;
				$max_qty = apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product );
				$max_qty = $max_qty < 0 ? '' : $max_qty;
				$step    = apply_filters( 'woocommerce_quantity_input_step', 1, $product );
				ob_start();
				?>
                <div class="vi_wpvs_loop_action vi_wpvs_loop_variation_hidden">
                    <div class="vi_wpvs_loop_action_qty">
                        <span class="viwcuf_product_change_qty viwcuf_product_minus">-</span>
                        <input type="number" name="quantity" min="<?php echo esc_attr( $min_qty ); ?>"
                               max="<?php echo esc_attr( $max_qty ); ?>" step="<?php echo esc_attr( $step ); ?>"
                               value="<?php echo esc_attr( $min_qty ); ?>" class="viwcuf_product_qty" tabindex="0">
                        <span class="viwcuf_product_change_qty viwcuf_product_plus">+</span>
                        <div class="viwcuf_product_qty_tooltip vi_wpvs_loop_variation_hidden"></div>
                    </div>
					<?php echo $this->add_to_cart_button_html(); ?>
                </div>
				<?php
				$swatches_atc = ob_get_clean();
			} else {
				$swatches_atc = $this->add_to_cart_button_html();
			}
			$html = $swatches_atc . $html;
		}
		if ( ! in_array( $position,
			array(
				'before_cart',
				'after_cart',
			) ) ) {
			return $html;
		}
		ob_start();
		self::variation_price_html();
		$this->get_loop_attribute_option_html( $product );
		$swatches = ob_get_clean();
		if ( $position === 'before_cart' ) {
			return $swatches . $html;
		} else {
			return $html . $swatches;
		}
	}

	protected function add_to_cart_button_html() {
		$atc_text = $this->settings->get_params( 'product_list_add_to_cart_text', self::get_language() );

		return apply_filters( 'viwpvs_loop_add_to_cart_button_html', '<button class="button is-small add_to_cart_button vi_wpvs_loop_atc_button vi_wpvs_loop_variation_hidden">' . wp_kses_post( $atc_text ) . ' </button>', $atc_text );
	}

	/**
	 * @param $product WC_Product
	 */
	protected function get_loop_attribute_option_html( $product ) {
		self::$is_loop = true;
		$attributes    = $product->get_variation_attributes();
		if ( $attributes && $count_attrs = count( $attributes ) ) {
			$variation_threshold              = $this->settings->get_params( 'variation_threshold_archive_page' );
			$variation_threshold              = $variation_threshold ?: 30;
			$add_to_cart                      = $this->add_to_cart ?? $this->settings->get_params( 'product_list_add_to_cart' );
			$attr_name_enable                 = $this->attr_name_enable ?? $this->settings->get_params( 'product_list_attr_name_enable' );
			$max_attr_items                   = $this->max_attr_items ?? $this->settings->get_params( 'product_list_maximum_attr_item' );
			$link_more_enable                 = $this->link_more_enable ?? $this->settings->get_params( 'product_list_more_link_enable' );
			$product_list_double_click_enable = $this->settings->get_params( 'product_list_double_click_enable' );
			$product_id                       = $product->get_id();
			$variation_count                  = count( $product->get_children() );
			if ( $variation_count <= $variation_threshold ) {
				add_filter( 'sctv_get_countdown_on_available_variation', function ( $result ) {
					return false;
				} );
				$available_variations = $product->get_available_variations();
				$variations_json      = wp_json_encode( $available_variations );
				$variations_attr      = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );
			} else {
				$variations_attr = false;
			}
			$class                            = $attr_name_enable ? 'vi_wpvs_loop_variation_attr_name_enable' : 'vi_wpvs_loop_variation_attr_name_disable';
			$product_list_double_click_enable = $product_list_double_click_enable ? 'true' : 'false';
			$form_class                       = $this->position ? array( 'vi_wpvs_loop_variation_form' ) : array( 'vi_wpvs_loop_variation_form vi-wpvs-hidden' );
			$slider_enable                    = $this->slider_enable ?? $this->settings->get_params( 'product_list_slider' );
			$slider_show                      = $this->slider_show ?? $this->settings->get_params( 'product_list_slider_min' );
			$slider_show                      = (int) $slider_show ?: 5;
			$slider_type                      = $this->settings->get_params( 'product_list_slider_type' ) ?: array();
			$slider_type                      = empty( $slider_type ) ? array(
				'color',
				'image',
				'variation_img'
			) : $slider_type;
			$form_class[]                     = $slider_enable ? 'vi_wpvs_loop_variation_slider' : '';
			$form_class                       = implode( ' ', $form_class );
			if ( $add_to_cart ) {
				$find_variation = 'true';
				?>
                <form action=""
                      class="<?php echo esc_attr( $form_class ); ?>"
                      data-product_id="<?php echo esc_attr( $product_id ); ?>"
                      data-variation_count="<?php echo esc_attr( $variation_count ); ?>"
                      data-vpvs_find_variation="<?php echo esc_attr( $find_variation ) ?>"
                      data-wpvs_double_click="<?php echo esc_attr( $product_list_double_click_enable ) ?>"
                      data-product_variations="<?php echo esc_attr( $variations_attr ); ?>">
                    <table class="variations" cellspacing="0" cellpadding="0">
                        <tbody>
						<?php
						if ( $slider_enable ) {
							$vi_attribute_settings = get_post_meta( $product_id, '_vi_woo_product_variation_swatches_product_attribute', true );
							$vi_attribute_settings = $vi_attribute_settings ? json_decode( $vi_attribute_settings, true ) : array();
							$vi_attribute_types    = $vi_attribute_settings['attribute_type'] ?? array();
							$taxonomy_custom_cats  = $this->settings->get_params( 'taxonomy_custom_cats' ) ?: array();

							$custom_attribute_id       = $this->settings->get_params( 'custom_attribute_id' ) ?: array();
							$custom_attribute_name     = $this->settings->get_params( 'custom_attribute_name' ) ?: array();
							$custom_attribute_type     = $this->settings->get_params( 'custom_attribute_type' ) ?: array();
							$custom_attribute_category = $this->settings->get_params( 'custom_attribute_category' ) ?: array();
							$count_custom_rule         = count( $custom_attribute_id );

							$product_cats = wc_get_product_cat_ids( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id() );
							foreach ( $attributes as $attribute_name => $options ) {
								$count_attr_items  = count( $options );
								$vi_attribute_type = $vi_attribute_types[ $attribute_name ] ?? '';
								if ( ! $vi_attribute_type ) {
									if ( 'pa_' === substr( $attribute_name, 0, 3 ) ) {
										$term_custom_cats = $taxonomy_custom_cats[ $attribute_name ] ?? '';
										$index            = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Frontend_Frontend::get_term_cats_index( $term_custom_cats, $product_cats );
										if ( $index !== false ) {
											$vi_attribute_type = $term_custom_cats[ $index ]['type'] ?? '';
										} else {
											$vi_attribute_type = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Frontend_Frontend::get_attribute_taxonomy_type( $attribute_name );
										}
									} else {
										$index = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Frontend_Frontend::get_custom_cats_index( $attribute_name, $count_custom_rule, $custom_attribute_id, $custom_attribute_name, $custom_attribute_category, $product_cats );
										if ( $index !== false ) {
											$vi_attribute_type = $custom_attribute_type[ $index ] ?? '';
										}
									}
								}
								$has_slider         = false;
								$vi_variation_class = 'vi-wpvs-variation-wrap-loop';
								if ( in_array( $vi_attribute_type, $slider_type ) ) {
									if ( $count_attr_items > $slider_show ) {
										$vi_variation_class .= ' vi-wpvs-variation-wrap-slider';
										$has_slider         = true;
									}
									$link_more = '';
								} else {
									$options   = $max_attr_items && $count_attr_items > $max_attr_items ? array_slice( $options, 0, $max_attr_items ) : $options;
									$link_more = $max_attr_items && $count_attr_items > $max_attr_items && $link_more_enable ? 1 : '';
								}
								if ( $has_slider ) {
									$vi_variation_class .= ' vi-wpvs-variation-wrap-slider';
								}
								?>
                                <tr class="<?php echo esc_attr( $class ); ?>">
									<?php
									if ( $attr_name_enable ) {
										?>
                                        <td class="vi_variation_container">
                                            <label class="vi_variation_attr_name">
												<?php echo esc_html( wc_attribute_label( $attribute_name ) ); ?>
                                            </label>
                                        </td>
										<?php
									}
									?>
                                    <td class="value vi_variation_container">
										<?php
										do_action( 'viwpvs_before_loop_variation_swatches', $attribute_name, $options, $has_slider, $product_id );
										wc_dropdown_variation_attribute_options( array(
											'options'              => $options,
											'attribute'            => $attribute_name,
											'product'              => $product,
											'vi_variation_class'   => $vi_variation_class,
											'viwpvs_link_more'     => $link_more,
											'viwpvs_attr_title'    => $attr_name_enable,
											'viwpvs_attr_selected' => '',
											'id'                   => $product_id . '_' . sanitize_title( $attribute_name ),
										) );
										do_action( 'viwpvs_after_loop_variation_swatches', $attribute_name, $options, $has_slider, $product_id );
										?>
                                    </td>
                                </tr>
								<?php
							}
						} else {
							foreach ( $attributes as $attribute_name => $options ) {
								$count_attr_items = count( $options );
								$options          = $max_attr_items && $count_attr_items > $max_attr_items ? array_slice( $options, 0, $max_attr_items ) : $options;
								$link_more        = $max_attr_items && $count_attr_items > $max_attr_items && $link_more_enable ? 1 : '';
								?>
                                <tr class="<?php echo esc_attr( $class ); ?>">
									<?php
									if ( $attr_name_enable ) {
										?>
                                        <td class="vi_variation_container">
                                            <label class="vi_variation_attr_name">
												<?php echo esc_html( wc_attribute_label( $attribute_name ) ); ?>
                                            </label>
                                        </td>
										<?php
									}
									?>
                                    <td class="value vi_variation_container">
										<?php
										do_action( 'viwpvs_before_loop_variation_swatches', $attribute_name, $options, false, $product_id );
										wc_dropdown_variation_attribute_options( array(
											'options'              => $options,
											'attribute'            => $attribute_name,
											'product'              => $product,
											'vi_variation_class'   => 'vi-wpvs-variation-wrap-loop',
											'viwpvs_link_more'     => $link_more,
											'viwpvs_attr_title'    => $attr_name_enable,
											'viwpvs_attr_selected' => '',
											'id'                   => $product_id . '_' . sanitize_title( $attribute_name ),
										) );
										do_action( 'viwpvs_after_loop_variation_swatches', $attribute_name, $options, false, $product_id );
										?>
                                    </td>
                                </tr>
								<?php
							}
						}
						?>
                        </tbody>
                    </table>
                    <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product_id ); ?>"/>
                    <input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>"/>
                    <input type="hidden" name="variation_id" class="variation_id" value="0"/>
                    <div class="vi_wpvs_loop_variation_form_loading vi_wpvs_loop_variation_form_loading_hidden"></div>
                </form>
				<?php
			} else {
				$vi_attribute_settings     = get_post_meta( $product_id, '_vi_woo_product_variation_swatches_product_attribute', true );
				$vi_attribute_settings     = $vi_attribute_settings ? json_decode( $vi_attribute_settings, true ) : array();
				$vi_attribute_loop_enables = $vi_attribute_settings['vi_attribute_loop_enable'] ?? array();
				$vi_attribute_types        = $vi_attribute_settings['attribute_type'] ?? array();
				$taxonomy_loop_enable      = $this->settings->get_params( 'taxonomy_loop_enable' ) ?: array();
				$taxonomy_custom_cats      = $this->settings->get_params( 'taxonomy_custom_cats' ) ?: array();

				$custom_attribute_id          = $this->settings->get_params( 'custom_attribute_id' ) ?: array();
				$custom_attribute_name        = $this->settings->get_params( 'custom_attribute_name' ) ?: array();
				$custom_attribute_category    = $this->settings->get_params( 'custom_attribute_category' ) ?: array();
				$custom_attribute_type        = $this->settings->get_params( 'custom_attribute_type' ) ?: array();
				$custom_attribute_loop_enable = $this->settings->get_params( 'custom_attribute_loop_enable' ) ?: array();
				$count_custom_rule            = count( $custom_attribute_id );

				$product_cats = wc_get_product_cat_ids( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id() );
				$attrs_enable = $attrs_enable_type = $attrs_disable = array();
				foreach ( $attributes as $attribute_name => $options ) {
					$vi_attribute_type        = $vi_attribute_types[ $attribute_name ] ?? '';
					$vi_attribute_loop_enable = $vi_attribute_loop_enables[ $attribute_name ] ?? '1';
					$vi_attribute_loop_enable = $vi_attribute_loop_enable ?: '1';
					$vi_attribute_loop_enable = $vi_attribute_loop_enable == '1' ? false : $vi_attribute_loop_enable;
					if ( $vi_attribute_loop_enable == 3 ) {
						continue;
					}
					if ( ! $vi_attribute_type || $vi_attribute_loop_enable === false ) {
						if ( 'pa_' === substr( $attribute_name, 0, 3 ) ) {
							$term_custom_cats = $taxonomy_custom_cats[ $attribute_name ] ?? '';
							$index            = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Frontend_Frontend::get_term_cats_index( $term_custom_cats, $product_cats );
							if ( $index !== false ) {
								$vi_attribute_type        = $vi_attribute_type ?: $term_custom_cats[ $index ]['type'] ?? '';
								$vi_attribute_loop_enable = $vi_attribute_loop_enable ?: $term_custom_cats[ $index ]['loop_enable'] ?? '';
							} else {
								$vi_attribute_type        = $vi_attribute_type ?: VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Frontend_Frontend::get_attribute_taxonomy_type( $attribute_name );
								$vi_attribute_loop_enable = $vi_attribute_loop_enable ?: $taxonomy_loop_enable[ $attribute_name ] ?? '';
							}
						} else {
							$index = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Frontend_Frontend::get_custom_cats_index( $attribute_name, $count_custom_rule, $custom_attribute_id, $custom_attribute_name, $custom_attribute_category, $product_cats );
							if ( $index !== false ) {
								$vi_attribute_type        = $vi_attribute_type ?: $custom_attribute_type[ $index ] ?? '';
								$vi_attribute_loop_enable = $vi_attribute_loop_enable ?: $custom_attribute_loop_enable[ $index ] ?? '';
							}
						}
					}
					if ( ! $vi_attribute_loop_enable ) {
						$attrs_disable[] = $attribute_name;
						continue;
					}
					$attrs_enable[ $attribute_name ] = array(
						'type'    => $vi_attribute_type,
						'options' => $options,
//						'show'    => $vi_attribute_loop_enable,
					);
				}
				if ( count( $attrs_enable ) ) {
					$find_variation = count( $attrs_enable ) < count( $attributes ) ? 'false' : 'true';
					?>
                    <div class="<?php echo esc_attr( $form_class ); ?>"
                         data-product_id="<?php echo esc_attr( $product_id ); ?>"
                         data-variation_count="<?php echo esc_attr( $variation_count ); ?>"
                         data-vpvs_find_variation="<?php echo esc_attr( $find_variation ) ?>"
                         data-wpvs_double_click="<?php echo esc_attr( $product_list_double_click_enable ); ?>"
                         data-product_variations="<?php echo esc_attr( $variations_attr ); ?>">
                        <table cellspacing="0" cellpadding="0">
                            <tbody>
							<?php
							foreach ( $attrs_enable as $attribute_name => $item ) {
								$selected = false;
//								if ( ! $item['show'] ) {
//									$class    .= ' vi-wpvs-hidden';
//									$selected = '';
//								}
								$item_type          = $item['type'] ?? '';
								$options            = $item['options'] ?? array();
								$count_attr_items   = count( $options );
								$has_slider         = false;
								$vi_variation_class = 'vi-wpvs-variation-wrap-loop';
								if ( $slider_enable && in_array( $item_type, $slider_type ) ) {
									if ( $count_attr_items > $slider_show ) {
										$vi_variation_class .= ' vi-wpvs-variation-wrap-slider';
										$has_slider         = true;
									}
									$link_more = '';
								} else {
									$options   = $max_attr_items && $count_attr_items > $max_attr_items ? array_slice( $options, 0, $max_attr_items ) : $options;
									$link_more = $max_attr_items && $count_attr_items > $max_attr_items && $link_more_enable ? 1 : '';
								}
								?>
                                <tr class="<?php echo esc_attr( $class ); ?>">
									<?php
									if ( $attr_name_enable ) {
										?>
                                        <td class="vi_variation_container">
                                            <label class="vi_variation_attr_name">
												<?php echo esc_html( wc_attribute_label( $attribute_name ) ); ?>
                                            </label>
                                        </td>
										<?php
									}
									?>
                                    <td class="value vi_variation_container">
										<?php
										do_action( 'viwpvs_before_loop_variation_swatches', $attribute_name, $options, $has_slider, $product_id );
										wc_dropdown_variation_attribute_options( array(
											'options'              => $options,
											'selected'             => $selected,
											'attribute'            => $attribute_name,
											'product'              => $product,
											'vi_variation_class'   => $vi_variation_class,
											'viwpvs_link_more'     => $link_more,
											'viwpvs_attr_title'    => $attr_name_enable,
											'viwpvs_attr_selected' => '',
											'id'                   => $product_id . '_' . sanitize_title( $attribute_name ),
										) );
										do_action( 'viwpvs_after_loop_variation_swatches', $attribute_name, $options, $has_slider, $product_id );
										?>
                                    </td>
                                </tr>
								<?php
							}
							?>
                            </tbody>
                        </table>
                        <div class="vi_wpvs_loop_variation_form_loading vi_wpvs_loop_variation_form_loading_hidden"></div>
                    </div>
					<?php
				}
			}
		}
		self::$is_loop = false;
	}

	protected function get_position_swatches() {
		if ( get_query_var( 'viwpvs_position', '' ) ) {
			$position = get_query_var( 'viwpvs_position', '' );
		} elseif ( isset( $_REQUEST['viwpvs_position'] ) ) {
			$position = sanitize_text_field( $_REQUEST['viwpvs_position'] );
		} else {
			$position = false;
		}

		return $position;
	}

	public function init() {
		if ( is_admin() ) {
			return;
		}

		$this->add_to_cart        = $this->settings->get_params( 'product_list_add_to_cart' );
		$this->attr_name_enable   = $this->settings->get_params( 'product_list_attr_name_enable' );
		$this->max_attr_items     = $this->settings->get_params( 'product_list_maximum_attr_item' );
		$this->link_more_enable   = $this->settings->get_params( 'product_list_more_link_enable' );
		$this->slider_enable      = $this->settings->get_params( 'product_list_slider' );
		$this->slider_show        = $this->settings->get_params( 'product_list_slider_min' ) ?: 5;
		$this->theme              = is_child_theme() ? wp_get_theme()->template : wp_get_theme()->get_stylesheet();
		$this->theme_swatches_pos = '';
		if ( in_array( $this->theme, array( 'woostify' ) ) ) {
			if ( in_array( $this->position, array( 'before_price', 'after_price' ) ) ) {
				$this->theme_swatches_pos = $this->position;
				$this->position           = 'after_cart';
			}
		}
		add_action( 'wp_enqueue_scripts', array( $this, 'wvps_wp_enqueue_scripts' ), PHP_INT_MAX );
	}

	public function remove_hooks() {
		remove_action( 'woocommerce_shop_loop_item_title', array(
			$this,
			'wpvs_before_loop_item_title'
		), PHP_INT_MIN );
		remove_action( 'woocommerce_shop_loop_item_title', array(
			$this,
			'wpvs_after_loop_item_title'
		), PHP_INT_MAX );
		remove_action( 'woocommerce_after_shop_loop_item_title', array(
			$this,
			'wpvs_before_loop_item_price'
		), 9 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array(
			$this,
			'wpvs_after_loop_item_price'
		), 11 );
		remove_action( 'woocommerce_before_template_part', array( $this, 'wpvs_before_template_loop' ) );
		remove_action( 'woocommerce_after_template_part', array( $this, 'wpvs_after_template_loop' ) );
		remove_filter( 'woocommerce_loop_add_to_cart_link', array(
			$this,
			'wpvs_woocommerce_loop_add_to_cart_link',
		), 99 );
		$product_list_custom_hook = $this->settings->get_params( 'product_list_custom_hook' );
		if ( $product_list_custom_hook ) {
			remove_action( $product_list_custom_hook, array( $this, 'product_list_custom_hook' ) );
		}
	}

	public function wvps_wp_enqueue_scripts() {
		$assign_page = $this->settings->get_params( 'product_list_assign' );
		if ( $assign_page ) {
			if ( stristr( $assign_page, "return" ) === false ) {
				$assign_page = "return (" . $assign_page . ");";
			}
			try {
				if ( ! eval( $assign_page ) ) {
					$this->remove_hooks();

					return;
				}
			} catch ( Error $e ) {
				trigger_error( $e->getMessage(), E_USER_WARNING );
				$this->remove_hooks();

				return;
			} catch ( Exception $e ) {
				trigger_error( $e->getMessage(), E_USER_WARNING );
				$this->remove_hooks();

				return;
			}
		}
//		$this->add_to_cart      = $this->settings->get_params( 'product_list_add_to_cart' );
//		$this->attr_name_enable = $this->settings->get_params( 'product_list_attr_name_enable' );
//		$this->max_attr_items   = $this->settings->get_params( 'product_list_maximum_attr_item' );
//		$this->link_more_enable = $this->settings->get_params( 'product_list_more_link_enable' );
//		$this->slider_enable    = $this->settings->get_params( 'product_list_slider' );
//		$this->slider_show      = $this->settings->get_params( 'product_list_slider_min' ) ?: 5;
//		$theme                  = is_child_theme() ? wp_get_theme()->template : wp_get_theme()->get_stylesheet();
//		$theme_swatches_pos     = '';
//		if ( in_array( $theme, array( 'woostify' ) ) ) {
//			if ( in_array( $this->position, array( 'before_price', 'after_price' ) ) ) {
//				$theme_swatches_pos = $this->position;
//				$this->position     = 'after_cart';
//			}
//		}
//		set_query_var( 'viwpvs_position', $this->position );
		if ( ! wp_style_is( 'vi-wpvs-frontend-loop-product-style', 'registered' ) ) {
			if ( $this->link_more_enable ) {
				wp_enqueue_style( 'vi-wpvs-frontend-loop-product-linkmore',
					VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'linkmore-icons.css',
					array(),
					VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			}
			if ( WP_DEBUG ) {
				wp_enqueue_style( 'vi-wpvs-frontend-loop-product-style',
					VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'frontend-loop-product-style.css',
					array(),
					VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			} else {
				wp_enqueue_style( 'vi-wpvs-frontend-loop-product-style',
					VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'frontend-loop-product-style.min.css',
					array(),
					VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			}
			$css = '';
			if ( ! $this->settings->get_params( 'product_list_tooltip_enable' ) ) {
				$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-loop .vi-wpvs-option-wrap .vi-wpvs-option-tooltip{';
				$css .= 'display: none;';
				$css .= '}';
			}
			$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-slider .vi-wpvs-option-wrap .vi-wpvs-option-tooltip{';
			$css .= 'display: none !important;';
			$css .= '}';
			if ( $this->position === 'after_cart' ) {
				$css .= '.vi_wpvs_loop_variation_form{';
				$css .= 'padding-bottom: 0;';
				$css .= '}';
			}
			$css .= '.vi_wpvs_loop_action,';
			$css .= '.vi_wpvs_loop_variation_form,';
			$css .= '.vi_wpvs_loop_variation_form .vi-wpvs-variation-style,';
			$css .= '.vi_wpvs_loop_variation_form .vi_variation_container,';
			$css .= '.vi_wpvs_loop_variation_form .vi_variation_container .vi-wpvs-variation-wrap-wrap,';
			$css .= '.vi_wpvs_loop_variation_form .vi_variation_container .vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap{';
			switch ( $this->settings->get_params( 'product_list_align' ) ) {
				case 'left':
					$css .= 'justify-content: flex-start;';
					$css .= 'text-align: left;';
					break;
				case 'right':
					$css .= 'justify-content: flex-end;';
					$css .= 'text-align: right;';
					break;
				default:
					$css .= 'justify-content: center;';
					$css .= 'text-align: center;';
			}
			$css .= '}';
			wp_add_inline_style( 'vi-wpvs-frontend-loop-product-style', $css );
		}
		if ( ! wp_script_is( 'vi-wpvs-frontend-loop-product-script', 'registered' ) ) {
			if ( WP_DEBUG ) {
				wp_enqueue_script( 'vi-wpvs-frontend-loop-product-script',
					VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'frontend-loop-product-script.js',
					array( 'jquery' ),
					VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION,
					true );
			} else {
				wp_enqueue_script( 'vi-wpvs-frontend-loop-product-script',
					VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'frontend-loop-product-script.min.js',
					array( 'jquery' ),
					VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION,
					true );
			}
			$args = array(
				'ajax_url'                            => admin_url( 'admin-ajax.php' ),
				'wc_ajax_url'                         => WC_AJAX::get_endpoint( "%%endpoint%%" ),
				'is_atc'                              => $this->add_to_cart ? '1' : '',
				'viwpvs_position'                     => $this->position ?: '',
				'theme_swatches_pos'                  => $this->theme_swatches_pos,
				'theme'                               => $this->theme,
				'greater_max_qty'                     => __( 'Value must be less than or equal to', 'woocommerce-product-variations-swatches' ),
				'less_min_qty'                        => __( 'Value must be greater than or equal to', 'woocommerce-product-variations-swatches' ),
				'cart_url'                            => apply_filters( 'woocommerce_add_to_cart_redirect', wc_get_cart_url(), null ),
				'cart_redirect_after_add'             => get_option( 'woocommerce_cart_redirect_after_add' ),
				'woocommerce_enable_ajax_add_to_cart' => 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' ) ? 1 : ''
			);
			wp_localize_script( 'vi-wpvs-frontend-loop-product-script', 'viwpvs_frontend_loop_product_params', $args );
		}
		if ( $this->slider_enable ) {
			wp_enqueue_style( 'dashicons' );
			wp_enqueue_script( 'flexslider' );
			if ( ! wp_style_is( 'vi-wpvs-flexslider', 'registered' ) ) {
				wp_enqueue_style( 'vi-wpvs-flexslider', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'vi_flexslider.min.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			}
		}
	}

	private static function variation_price_html() {
		do_action( 'viwpvs_before_loop_variation_price' );
		?><span class="vi_wpvs_loop_variation_price vi_wpvs_loop_variation_hidden"></span><?php
		do_action( 'viwpvs_after_loop_variation_price' );
	}
}