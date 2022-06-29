<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Frontend_Frontend {
	protected $settings;
	protected static $language;
	protected static $attachment_props;
	protected static $hide_outofstock;

	public function __construct() {
		$this->settings         = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DATA::get_instance();
		self::$language         = '';
		self::$attachment_props = array();
		add_filter( 'woocommerce_available_variation', array(
			$this,
			'wvps_woocommerce_available_variation'
		), PHP_INT_MAX, 3 );

		add_action( 'wp_enqueue_scripts', array( $this, 'wvps_wp_enqueue_scripts' ), 99 );
		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array(
			$this,
			'variation_attribute_options_html'
		), PHP_INT_MAX, 2 );
		add_filter( 'woocommerce_ajax_variation_threshold', array(
			$this,
			'viwpvs_ajax_variation_threshold'
		), PHP_INT_MAX, 2 );
	}

	/**
	 * @param $attribute_tooltip_content
	 * @param $attribute_tooltip_position
	 * @param $term_name
	 * @param $term
	 * @param $attribute
	 * @param $variations
	 * @param $product WC_Product
	 * @param string $attr_img_id
	 * @param string $variation_img_id
	 */
	public static function tooltip_html( $attribute_tooltip_content, $attribute_tooltip_position, $term_name, $term, $attribute, $variations, $product, $attr_img_id = '', $variation_img_id = '' ) {
		if ( $attribute_tooltip_content ) {
			$attribute_name = wc_attribute_label( $attribute );
			$description    = $attribute_image = $variation_image = '';
			if ( $term && isset( $term->description ) ) {
				$description = $term->description;
			}
			if ( $attr_img_id ) {
				$attribute_image = '<img class="vi-wpvs-option-tooltip-image" src="' . esc_url( wp_get_attachment_image_url( $attr_img_id, 'woocommerce_thumbnail', true ) ) . '" alt="' . esc_attr( $term_name ) . '"/>';
			}
			if ( $variation_img_id ) {
				$variation_image = '<img class="vi-wpvs-option-tooltip-image" src="' . esc_url( wp_get_attachment_image_url( $variation_img_id, 'woocommerce_thumbnail', true ) ) . '" alt="' . esc_attr( $term_name ) . '"/>';
			}

			$term_tooltip = apply_filters( 'viwpvs_variation_option_tooltip', str_replace( array(
				'{attribute_value_desc}',
				'{attribute_value}',
				'{attribute_name}',
				'{attribute_image}',
				'{variation_image}',
			), array(
				$description,
				$term_name,
				$attribute_name,
				$attribute_image,
				$variation_image,
			), $attribute_tooltip_content ), $term, $attribute, $variations, $product );
			if ( $term_tooltip ) {
				?>
                <div class="vi-wpvs-option-tooltip vi-wpvs-option-tooltip-<?php echo esc_attr( $attribute_tooltip_position ); ?>"
                     data-attribute_label="<?php echo esc_attr( $term_name ); ?>">
                    <span>
                        <?php echo wp_kses_post( $term_tooltip ); ?>
                    </span>
                </div>
				<?php
			}
		}
	}

	private static function out_of_stock_icon_html() {
		?>
        <div class="vi-wpvs-option-out-of-stock-attribute-icon"></div>
		<?php
	}

	/**
	 * @param $result
	 * @param $object
	 * @param $variation WC_Product_Variation
	 *
	 * @return bool
	 */
	public function wvps_woocommerce_available_variation( $result, $object, $variation ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $result;
		}
		global $wp_query;
		$is_product = false;
		if ( is_product() && $wp_query->post && ( method_exists( $object, 'get_id' ) ? $object->get_id() : $object->ID ) === $wp_query->post->ID ) {
			$is_product = true;
		}
		self::$hide_outofstock = false;
		if ( ( $is_product && $this->settings->get_params( 'out_of_stock_variation_disable' ) ) || ( ! $is_product && $this->settings->get_params( 'out_of_stock_variation_disable_archive' ) ) ) {
			self::$hide_outofstock = true;
		}
		if ( $variation->get_status() !== 'publish' ) {
			if ( self::$hide_outofstock ) {
				$result = false;
			} else {
				$result['viwpvs_not_available'] = 1;
			}
		} elseif ( ! $variation->is_in_stock() || ( $variation->managing_stock() && $variation->get_stock_quantity() <= get_option( 'woocommerce_notify_no_stock_amount', 0 ) ) ) {
			$get_backorders = $variation->get_backorders();
			if ( $get_backorders && 'no' !== $get_backorders ) {
				$result['viwpvs_on_backorders'] = 1;
			} else {
				if ( self::$hide_outofstock ) {
					$result = false;
				} else {
					$result['viwpvs_not_available'] = 1;
				}
			}
		}
		if ( $result && ! $variation->get_image_id( 'edit' ) ) {
			$result['viwpvs_no_image'] = 1;
//			unset($result['image']);
		}

		return $result;
	}

	public function viwpvs_ajax_variation_threshold( $limit, $product ) {
		$result = $this->settings->get_params( 'variation_threshold_single_page' );
		$result = $result ?: 30;

		return $result;
	}

	/**
	 * @param $args
	 *
	 * @return false|string
	 */
	private function get_select_dropdown( $args ) {
		$args                  = wp_parse_args( $args, array(
			'options'          => false,
			'attribute'        => false,
			'product'          => false,
			'selected'         => false,
			'name'             => '',
			'id'               => '',
			'class'            => '',
			'type'             => '',
			'assigned'         => '',
			'show_option_none' => esc_html__( 'Choose an option', 'woocommerce-product-variations-swatches' )
		) );
		$options               = $args['options'] ?: array();
		$product               = $args['product'] ?: null;
		$attribute             = $args['attribute'] ?: '';
		$name                  = $args['name'] ?: 'attribute_' . sanitize_title( $attribute );
		$id                    = $args['id'] ?: sanitize_title( $attribute );
		$class                 = $args['class'] ? $args['class'] . ' vi-wpvs-select-option' : 'vi-wpvs-select-option';
		$show_option_none      = (bool) $args['show_option_none'];
		$show_option_none_text = $args['show_option_none'] ?: __( 'Choose an option', 'woocommerce-product-variations-swatches' );

		if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
			$attributes = $product->get_variation_attributes();
			$options    = $attributes[ $attribute ];
		}
		ob_start();
		?>
        <select name="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $class ); ?>"
                data-attribute_name="attribute_<?php echo esc_attr( sanitize_title( $attribute ) ) ?>"
                data-show_option_none="attribute_<?php echo $show_option_none ? esc_attr( 'yes' ) : esc_attr( 'no' ); ?>">
            <option value=""><?php echo esc_html( $show_option_none_text ); ?></option>
			<?php
			if ( $product && ! empty( $options ) ) {
				if ( taxonomy_exists( $attribute ) ) {
					// Get terms if this is a taxonomy - ordered. We need the names too.
					$terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );
					foreach ( $terms as $term ) {
						if ( in_array( $term->slug, $options, true ) ) {
							$term_name = apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product );
							echo sprintf( '<option value="%s" %s>%s</option>',
								esc_attr( $term->slug ),
								selected( sanitize_title( $args['selected'] ), $term->slug, false ),
								esc_html( $term_name )
							);
						}
					}
				} else {
					foreach ( $options as $option ) {
						$selected    = sanitize_title( $args['selected'] ) === $args['selected'] ? selected( $args['selected'], sanitize_title( $option ), false ) : selected( $args['selected'], $option, false );
						$option_name = apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product );
						echo sprintf( '<option value="%s" %s>%s</option>',
							esc_attr( $option ),
							$selected,
							esc_html( $option_name )
						);
					}
				}
			}
			?>
        </select>
		<?php
		$html = ob_get_clean();

		return $html;
	}

	public static function get_term_cats_index( $term_custom_cats, $product_cats ) {
		if ( ! $term_custom_cats || ! is_array( $term_custom_cats ) || ! $count_rule = count( $term_custom_cats ) ) {
			return false;
		}
		$index = false;
		for ( $i = 0; $i < $count_rule; $i ++ ) {
			$rule_attr_category = $term_custom_cats[ $i ]['category'] ?? array();
			if ( ! empty( $rule_attr_category ) && count( $product_cats ) ) {
				if ( count( array_intersect( $product_cats, $rule_attr_category ) ) ) {
					$index = $i;
					break;
				}
			}
		}

		return $index;
	}

	public static function get_custom_cats_index( $attribute_name, $count_custom_rule, $custom_attribute_id, $custom_attribute_name, $custom_attribute_category, $product_cats ) {
		if ( ! $count_custom_rule || ! is_array( $custom_attribute_id ) ) {
			return false;
		}
		$index          = false;
		$attribute_name = strtolower( trim( $attribute_name ) );
		for ( $i = 0; $i < $count_custom_rule; $i ++ ) {
			$rule_attr_id       = $custom_attribute_id[ $i ];
			$rule_attr_name     = strtolower( trim( $custom_attribute_name[ $i ] ?? '' ) );
			$rule_attr_category = $custom_attribute_category[ $rule_attr_id ] ?? array();
			if ( empty( $rule_attr_category ) ) {
				if ( $attribute_name === $rule_attr_name && $index === false ) {
					$index = $i;
				}
			} elseif ( count( $product_cats ) ) {
				if ( $attribute_name === $rule_attr_name && count( array_intersect( $product_cats, $rule_attr_category ) ) ) {
					$index = $i;
					break;
				}
			}
		}

		return $index;
	}

	/**
	 * @param $html
	 * @param $args
	 *
	 * @return string
	 */
	public function variation_attribute_options_html( $html, $args ) {
		$args       = wp_parse_args( $args, array(
			'options'          => false,
			'attribute'        => false,
			'product'          => false,
			'selected'         => false,
			'name'             => '',
			'id'               => '',
			'class'            => '',
			'type'             => '',
			'assigned'         => '',
			'show_option_none' => esc_html__( 'Choose an option', 'woocommerce-product-variations-swatches' )
		) );
		$check_null = strpos( $html, '<select' );
		if ( $check_null === false ) {
			$html = $this->get_select_dropdown( $args );
		}
		if ( ! empty( $args['viwpvs_swatches_disable'] ) ) {
			return $html;
		}
//		$attribute = $args['attribute'];
		$attribute = html_entity_decode( $args['attribute'], ENT_NOQUOTES, 'UTF-8' );
		if ( ! $attribute ) {
			return $html;
		}
		$product    = $args['product'];
		$product_id = method_exists( $product, 'get_id' ) ? $product->get_id() : $product->id;
		/*Custom settings on product edit/attributes*/
		$vi_attribute_settings     = get_post_meta( $product_id, '_vi_woo_product_variation_swatches_product_attribute', true );
		$vi_attribute_settings     = $vi_attribute_settings ? json_decode( $vi_attribute_settings, true ) : array();
		$vi_attribute_type         = $vi_attribute_settings['attribute_type'][ $attribute ] ?? null;
		$vi_change_product_image   = $vi_attribute_settings['change_product_image'][ $attribute ] ?? 'global';
		$vi_attribute_profile      = $vi_attribute_settings['attribute_profile'][ $attribute ] ?? null;
		$vi_attribute_display_type = $vi_attribute_settings['attribute_display_type'][ $attribute ] ?? null;
		$is_taxonomy               = ( 'pa_' === substr( $attribute, 0, 3 ) ) ? 1 : 0;
		$product_cats              = wc_get_product_cat_ids( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product_id );
		$index                     = $use_taxonomy_type = false;
		if ( $is_taxonomy ) {
			$use_taxonomy_type    = $vi_attribute_type ? false : true;
			$taxonomy_custom_cats = $this->settings->get_params( 'taxonomy_custom_cats' )[ $attribute ] ?? '';
			$index                = self::get_term_cats_index( $taxonomy_custom_cats, $product_cats );
			/*Settings from Global attributes/Design with Product category*/
			if ( $index !== false ) {
				$vi_attribute_profile      = $vi_attribute_profile ?: $taxonomy_custom_cats[ $index ]['profile'] ?? '';
				$vi_attribute_display_type = $vi_attribute_display_type ?: $taxonomy_custom_cats[ $index ]['display_type'] ?? '';
				$vi_attribute_type         = $vi_attribute_type ?: $taxonomy_custom_cats[ $index ]['type'] ?? '';
				if ( $vi_change_product_image === 'global' ) {
					$vi_change_product_image = $taxonomy_custom_cats[ $index ]['change_product_image'] ?? '';
				}
			}
			/*Settings from Global attributes*/
			if ( ! $vi_attribute_profile ) {
				$vi_attribute_profile = $this->settings->get_params( 'taxonomy_profiles' )[ $attribute ] ?? '';
			}
			if ( ! $vi_attribute_display_type ) {
				$vi_attribute_display_type = $this->settings->get_params( 'taxonomy_display_type' )[ $attribute ] ?? '';
			}
			if ( $vi_change_product_image === 'global' ) {
				$vi_change_product_image = $this->settings->get_params( 'change_product_image' )[ $attribute ] ?? '';
			}
			if ( ! $vi_attribute_type ) {
				$use_taxonomy_type = true;
				$vi_attribute_type = self::get_attribute_taxonomy_type( $attribute );
				if ( ! in_array( $vi_attribute_type, array(
						'button',
						'color',
						'image',
						'variation_img',
						'radio',
						'viwpvs_default'
					) ) && ! isset( $this->settings->get_params( 'taxonomy_profiles' )[ $attribute ] ) ) {
					if ( $this->settings->get_params( 'attribute_display_default' ) !== 'none' ) {
						$vi_attribute_type = $this->settings->get_params( 'attribute_display_default' );
					}
				}
			}
		} else {
			$custom_attribute_id = $this->settings->get_params( 'custom_attribute_id' ) ?: array();
			$index               = self::get_custom_cats_index( $attribute, count( $custom_attribute_id ), $custom_attribute_id, $this->settings->get_params( 'custom_attribute_name' ), $this->settings->get_params( 'custom_attribute_category' ), $product_cats );
			if ( $index === false ) {
				if ( ! $vi_attribute_type && $this->settings->get_params( 'attribute_display_default' ) !== 'none' ) {
					$vi_attribute_type = $this->settings->get_params( 'attribute_display_default' );
				}
			} else {
				/*Settings from Variation swatches/Custom attributes*/
				$vi_attribute_type                     = $vi_attribute_type ?: $this->settings->get_current_setting( 'custom_attribute_type', $index );
				$vi_attribute_profile                  = $vi_attribute_profile ?: $this->settings->get_current_setting( 'custom_attribute_profiles', $index );
				$vi_attribute_display_type             = $vi_attribute_display_type ?: $this->settings->get_current_setting( 'custom_attribute_display_type', $index );
				$custom_attribute_change_product_image = $this->settings->get_current_setting( 'custom_attribute_change_product_image', $index );
				if ( $vi_change_product_image === 'global' ) {
					$vi_change_product_image = $custom_attribute_change_product_image;
				}
			}
		}
		$options = $args['options'];
		if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
			$attributes = $product->get_variation_attributes();
			$options    = $attributes[ $attribute ];
		}
		if ( empty( $options ) ) {
			return $html;
		}
		$vi_args                       = array();
		$vi_args['vi_variation_class'] = $args['vi_variation_class'] ?? '';
		$vi_args['viwpvs_link_more']   = $args['viwpvs_link_more'] ?? '';
		$vi_args['selected']           = $args['selected'] ?? '';
		$vi_args['show_option_none']   = $args['show_option_none'] ?? esc_html__( 'Choose an option', 'woocommerce-product-variations-swatches' );
		$attribute_double_click        = $args['viwpvs_double_click'] ?? $this->settings->get_params( 'attribute_double_click' );
		$attribute_double_click        = $attribute_double_click ? 1 : '';
		$attribute_title_enable        = $args['viwpvs_attr_title'] ?? $this->settings->get_params( 'single_attr_title' );
		$attribute_title_enable        = $attribute_title_enable ? 1 : '';
		$attribute_attr_selected       = $args['viwpvs_attr_selected'] ?? $this->settings->get_params( 'single_attr_selected' );
		$attribute_attr_selected       = $attribute_attr_selected ? 1 : '';
		$vi_attribute_display_type     = $attribute_title_enable ? $vi_attribute_display_type : '';
		$display_type_class            = array(
			'vi-wpvs-variation-style',
			'vi-wpvs-variation-attribute-type-' . $vi_attribute_type,
			'vi-wpvs-variation-style-' . $vi_attribute_display_type ?: 'vertical'
		);
		$display_type_class[]          = is_rtl() ? 'vi-wpvs-variation-style-rtl' : '';
		$display_type_class            = trim( implode( ' ', $display_type_class ) );
		$new_html                      = '<div class="vi-wpvs-variation-wrap-wrap vi-wpvs-hidden' . ( $vi_args['selected'] ? ' vi-wpvs-variation-wrap-wrap-hasdefault' : '' ) . '" data-wpvs_double_click="' . $attribute_double_click . '" data-wpvs_attr_title="' . $attribute_title_enable . '" ';
		$new_html                      .= 'data-selected="' . $vi_args['selected'] . '" data-swatch_type="' . $vi_attribute_type . '" data-display_type="' . $display_type_class . '" data-show_selected_item="' . $attribute_attr_selected . '"  data-hide_outofstock="' . ( self::$hide_outofstock ? 1 : '' ) . '"  data-blur_out_backorders="' . $this->settings->get_params( 'attribute_blur_out_backorders' ) . '" data-wpvs_attribute_name="attribute_' . esc_attr( sanitize_title( $attribute ) ) . '" ';
		$new_html                      .= '>';
		if ( $vi_attribute_type === 'viwpvs_default' ) {
			$new_html .= $html;
		} else {
			$new_html .= '<div class="vi-wpvs-select-attribute vi-wpvs-select-attribute-attribute_' . esc_attr( sanitize_title( $attribute ) ) . '">';
			$new_html .= $html;
			$new_html .= '</div>';
			$new_html .= self::get_attribute_option_html( $attribute, $product, $options, $vi_attribute_settings, $vi_args, $vi_attribute_type,
				$vi_attribute_profile, $use_taxonomy_type, $vi_change_product_image );
		}
		$new_html .= '</div>';

		return $new_html;
	}

	public static function get_attribute_option_color( $option, $colors = array(), $color_separator = '1' ) {
		if ( empty( $option ) ) {
			return '';
		}
		$settings = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DATA::get_instance();
		if ( empty( $colors ) ) {
			$result = $settings->get_default_color( strtolower( $option ) );
		} else {
			if ( ( $count_colors = count( $colors ) ) === 1 ) {
				$result = $colors[0];
				$result = $result ?: $settings->get_default_color( strtolower( $option ) );
			} else {
				$temp = (int) floor( 100 / $count_colors );
				switch ( $color_separator ) {
					case '2':
						$result = 'linear-gradient( ' . implode( ',', $colors ) . ' )';
						break;
					case '3':
						$result = 'linear-gradient(to bottom left, ' . implode( ',', $colors ) . ' )';
						break;
					case '4':
						$result = 'linear-gradient( to bottom right, ' . implode( ',', $colors ) . ' )';
						break;
					case '5':
						$result = 'linear-gradient(to right,' . $colors[0] . ' ' . $temp . '%';
						for ( $i = 1; $i < $count_colors; $i ++ ) {
							$result .= ' , ' . $colors[ $i ] . ' ' . ( $i * $temp ) . '% ' . ( ( $i + 1 ) * $temp ) . '%';
						}
						$result .= ' )';
						break;
					case '6':
						$result = 'linear-gradient(' . $colors[0] . ' ' . $temp . '%';
						for ( $i = 1; $i < $count_colors; $i ++ ) {
							$result .= ' , ' . $colors[ $i ] . ' ' . ( $i * $temp ) . '% ' . ( ( $i + 1 ) * $temp ) . '%';
						}
						$result .= ' )';
						break;
					case '7':
						$result = 'linear-gradient(to bottom left, ' . $colors[0] . ' ' . $temp . '%';
						for ( $i = 1; $i < $count_colors; $i ++ ) {
							$result .= ' , ' . $colors[ $i ] . ' ' . ( $i * $temp ) . '% ' . ( ( $i + 1 ) * $temp ) . '%';
						}
						$result .= ' )';
						break;
					case '8':
						$result = 'linear-gradient(to bottom right, ' . $colors[0] . ' ' . $temp . '%';
						for ( $i = 1; $i < $count_colors; $i ++ ) {
							$result .= ' , ' . $colors[ $i ] . ' ' . ( $i * $temp ) . '% ' . ( ( $i + 1 ) * $temp ) . '%';
						}
						$result .= ' )';
						break;
					default:
						$result = 'linear-gradient( to right, ' . implode( ',', $colors ) . ' )';
				}
			}
		}

		return $result;
	}

	/**
	 * @param $attribute
	 * @param $product WC_Product
	 * @param $options
	 * @param $vi_attribute_settings
	 * @param $vi_args
	 * @param $type
	 * @param $profile
	 * @param string $use_taxonomy_type
	 * @param string $change_product_image
	 *
	 * @return false|string
	 */
	public static function get_attribute_option_html( $attribute, $product, $options, $vi_attribute_settings, $vi_args, $type, $profile, $use_taxonomy_type = '', $change_product_image = '' ) {
		if ( empty( $attribute ) || empty( $product ) || empty( $options ) ) {
			return false;
		}
		global $wpvs_count;
		if ( $wpvs_count === null ) {
			$wpvs_count = 0;
		}
		$wpvs_count ++;
		$settings                    = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DATA::get_instance();
		$profile_default             = $settings->get_params( 'attribute_profile_default' );
		$profile_ids                 = $settings->get_params( 'ids' );
		$profile_default_index       = array_search( $profile_default, $profile_ids ) ? array_search( $profile_default, $profile_ids ) : 0;
		$profile_index               = array_search( $profile, $profile_ids ) !== false ? array_search( $profile, $profile_ids ) : $profile_default_index;
		$profile                     = $profile_ids[ $profile_index ];
		$attribute_value_enable      = $settings->get_current_setting( 'attribute_value_enable', $profile_index );
		$attribute_value_cutoff_text = $settings->get_current_setting( 'attribute_value_cutoff_text', $profile_index );
		$tooltip_enable              = $settings->get_current_setting( 'attribute_tooltip_enable', $profile_index );
		$attribute_tooltip_content   = $settings->get_current_setting( 'attribute_tooltip_content', $profile_index );
		if ( ! $tooltip_enable ) {
			$attribute_tooltip_content = '';
		}
		$attribute_tooltip_position = $settings->get_current_setting( 'attribute_tooltip_position', $profile_index );
		$attribute_image_size       = $settings->get_current_setting( 'attribute_image_size', $profile_index );
		$type                       = $type ?: 'select';
		$colors                     = $vi_attribute_settings['attribute_colors'][ $attribute ] ?? array();
		$color_separator            = $vi_attribute_settings['attribute_color_separator'][ $attribute ] ?? array();
		$img_ids                    = $vi_attribute_settings['attribute_img_ids'][ $attribute ] ?? array();
		$option_selected            = $vi_args['selected'] ?? '';
		$div_class                  = array(
			'vi-wpvs-variation-wrap',
			'vi-wpvs-variation-wrap-' . $profile,
			'vi-wpvs-variation-wrap-' . $type,
		);
		if ( $attribute_value_enable ) {
			$div_class[] = 'vi-wpvs-variation-wrap-show-attribute-value';
			if ( $attribute_value_cutoff_text ) {
				$div_class[] = 'vi-wpvs-variation-wrap-show-attribute-value-cutoff-text';
			}
		}
		if ( taxonomy_exists( $attribute ) ) {
			$div_class[] = 'vi-wpvs-variation-wrap-taxonomy';
		}
		if ( $vi_args['vi_variation_class'] ) {
			$div_class[] = $vi_args['vi_variation_class'];
		}
		if ( is_rtl() ) {
			$div_class[] = 'vi-wpvs-variation-wrap-rtl';
		}
		ob_start();
		?>
        <div class="<?php echo esc_attr( implode( ' ', $div_class ) ); ?>"
             data-out_of_stock="<?php echo esc_attr( $settings->get_current_setting( 'attribute_out_of_stock', $profile_index ) ) ?>"
             data-wpvs_id="<?php echo esc_attr( $wpvs_count ) ?>"
             data-attribute="attribute_<?php echo esc_attr( sanitize_title( $attribute ) ); ?>">
			<?php
			$variations = $product->get_children();
			if ( taxonomy_exists( $attribute ) ) {
				$terms = wc_get_product_terms(
					$product->get_id(),
					$attribute,
					array(
						'fields' => 'all',
					)
				);
				$terms = apply_filters( 'viwpvs_frontend_get_product_terms', $terms, $product, $terms );
				switch ( $type ) {
					case 'button':
						foreach ( $terms as $term ) {
							if ( ! in_array( $term->slug, $options ) ) {
								continue;
							}
							$term_name    = apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product );
							$term_class   = $option_selected === $term->slug ? 'vi-wpvs-option-wrap vi-wpvs-option-wrap-selected' : 'vi-wpvs-option-wrap vi-wpvs-option-wrap-default';
							$terms_img_id = $attr_img_id = $variation_img_id = '';
							if ( $change_product_image === 'attribute_image' || strpos( $attribute_tooltip_content, '{attribute_image}' ) !== false ) {
								if ( $use_taxonomy_type ) {
									$attr_img_id = $vi_wpvs_terms_settings['img_id'] ?? '';
								} else {
									$attr_img_id = $img_ids[ $term->term_id ] ?? $vi_wpvs_terms_settings['img_id'] ?? '';
								}
							}
							if ( $change_product_image === 'variation_image' || strpos( $attribute_tooltip_content, '{variation_image}' ) !== false ) {
								$variation_img_id = self::get_variation_image_id( $variations, $term, $attribute, '' );
							}
							if ( $change_product_image === 'attribute_image' ) {
								$terms_img_id = $attr_img_id;
							} elseif ( $change_product_image === 'variation_image' ) {
								$terms_img_id = $variation_img_id;
							}
							$img_loop_src  = '';
							$img_loop_data = '';
							if ( $terms_img_id ) {
								$img_loop_src  = wp_get_attachment_image_url( $terms_img_id, 'woocommerce_thumbnail', true );
								$img_loop_data = json_encode( self::get_attachment_props( $terms_img_id ) );
							}
							?>
                            <div class="<?php echo esc_attr( $term_class ); ?>"
                                 data-attribute_label="<?php echo esc_attr( $term_name ); ?>"
                                 data-attribute_value="<?php echo esc_attr( $term->slug ); ?>"
                                 data-loop_source="<?php echo esc_url( $img_loop_src ); ?>"
                                 data-loop_data="<?php echo esc_attr( $img_loop_data ); ?>"
                                 data-change_product_image="<?php echo esc_attr( $change_product_image ); ?>">
                                <span class="vi-wpvs-option vi-wpvs-option-button">
						            <?php echo wp_kses_post( $term_name ); ?>
					            </span>
								<?php
								self::tooltip_html( $attribute_tooltip_content, $attribute_tooltip_position, $term_name, $term, $attribute, $variations, $product, $attr_img_id, $variation_img_id );
								self::out_of_stock_icon_html();
								?>
                            </div>
							<?php
						}
						if ( ! empty( $vi_args['viwpvs_link_more'] ) ) {
							$link_more_text = $settings->get_params( 'product_list_maximum_more_link_text' );
							$link_more_text = str_replace( '{link_more_icon}', '<i class="viwpvs_linkmore-plus"></i>', $link_more_text );
							$product_link   = $product->get_permalink();
							?>
                            <a href="<?php echo esc_attr( esc_url( $product_link ) ) ?>"
                               class="vi-wpvs-option-wrap vi-wpvs-option-wrap-default vi-wpvs-product-link"
                               data-product_link="<?php echo esc_attr( esc_url( $product_link ) ) ?>">
                                <span class="vi-wpvs-option vi-wpvs-option-button">
						            <?php echo wp_kses_post( $link_more_text ); ?>
					            </span>
                                <div class="vi-wpvs-option-tooltip vi-wpvs-option-tooltip-<?php echo esc_attr( $attribute_tooltip_position ); ?>"
                                     data-attribute_label="<?php esc_attr_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>">
                                    <span>
                                        <?php esc_html_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>
                                    </span>
                                </div>
                            </a>
							<?php
						}
						break;
					case 'color':
						foreach ( $terms as $term ) {
							if ( ! in_array( $term->slug, $options ) ) {
								continue;
							}
							$term_name              = apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product );
							$vi_wpvs_terms_settings = get_term_meta( $term->term_id, 'vi_wpvs_terms_params', true );
							$term_class             = $option_selected === $term->slug ? 'vi-wpvs-option-wrap vi-wpvs-option-wrap-selected' : 'vi-wpvs-option-wrap vi-wpvs-option-wrap-default';
							$terms_img_id           = $attr_img_id = $variation_img_id = '';

							if ( $use_taxonomy_type ) {
								$term_colors          = $vi_wpvs_terms_settings['color'] ?? array();
								$term_color_separator = $vi_wpvs_terms_settings['color_separator'] ?? '1';
								if ( $change_product_image === 'attribute_image' || strpos( $attribute_tooltip_content, '{attribute_image}' ) !== false ) {
									$attr_img_id = $vi_wpvs_terms_settings['img_id'] ?? '';
								}
							} else {
								$term_colors          = $colors[ $term->term_id ] ?? $vi_wpvs_terms_settings['color'] ?? array();
								$term_color_separator = $color_separator[ $term->term_id ] ?? $vi_wpvs_terms_settings['color_separator'] ?? '1';
								if ( $change_product_image === 'attribute_image' || strpos( $attribute_tooltip_content, '{attribute_image}' ) !== false ) {
									$attr_img_id = $img_ids[ $term->term_id ] ?? $vi_wpvs_terms_settings['img_id'] ?? '';
								}
							}
							$term_color = self::get_attribute_option_color( $term->slug, $term_colors, $term_color_separator );
							if ( $change_product_image === 'variation_image' || strpos( $attribute_tooltip_content, '{variation_image}' ) !== false ) {
								$variation_img_id = self::get_variation_image_id( $variations, $term, $attribute, '' );
							}
							if ( $change_product_image === 'attribute_image' ) {
								$terms_img_id = $attr_img_id;
							} elseif ( $change_product_image === 'variation_image' ) {
								$terms_img_id = $variation_img_id;
							}
							$img_loop_src  = '';
							$img_loop_data = '';
							if ( $terms_img_id ) {
								$img_loop_src  = wp_get_attachment_image_url( $terms_img_id, 'woocommerce_thumbnail', true );
								$img_loop_data = json_encode( self::get_attachment_props( $terms_img_id ) );
							}
							?>
                            <div class="<?php echo esc_attr( $term_class ); ?>"
                                 data-attribute_label="<?php echo esc_attr( $term_name ); ?>"
                                 data-attribute_value="<?php echo esc_attr( $term->slug ); ?>"
                                 data-loop_source="<?php echo esc_url( $img_loop_src ); ?>"
                                 data-loop_data="<?php echo esc_attr( $img_loop_data ); ?>"
                                 data-change_product_image="<?php echo esc_attr( $change_product_image ); ?>">
                                <span class="vi-wpvs-option vi-wpvs-option-color"
                                      data-option_color="<?php echo esc_attr( $term_color ); ?>"></span>
								<?php
								self::tooltip_html( $attribute_tooltip_content, $attribute_tooltip_position, $term_name, $term, $attribute, $variations, $product, $attr_img_id, $variation_img_id );
								self::out_of_stock_icon_html();
								?>
                            </div>
							<?php
						}
						if ( ! empty( $vi_args['viwpvs_link_more'] ) ) {
							$link_more_text = $settings->get_params( 'product_list_maximum_more_link_text' );
							$link_more_text = str_replace( '{link_more_icon}', '<i class="viwpvs_linkmore-plus"></i>', $link_more_text );
							$product_link   = $product->get_permalink();
							?>
                            <a href="<?php echo esc_attr( esc_url( $product_link ) ) ?>"
                               class="vi-wpvs-option-wrap vi-wpvs-option-wrap-default vi-wpvs-product-link"
                               data-product_link="<?php echo esc_attr( esc_url( $product_link ) ) ?>">
                                <span class="vi-wpvs-option vi-wpvs-option-color"
                                      data-option_color="<?php echo esc_attr( 'transparent' ); ?>"
                                      data-option_separator="">
						            <?php echo wp_kses_post( $link_more_text ); ?>
					            </span>
                                <div class="vi-wpvs-option-tooltip vi-wpvs-option-tooltip-<?php echo esc_attr( $attribute_tooltip_position ); ?>"
                                     data-attribute_label="<?php esc_attr_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>">
                                    <span>
                                        <?php esc_html_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>
                                    </span>
                                </div>
                            </a>
							<?php
						}
						break;
					case 'image':
						foreach ( $terms as $term ) {
							if ( ! in_array( $term->slug, $options ) ) {
								continue;
							}
							$term_name              = apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product );
							$vi_wpvs_terms_settings = get_term_meta( $term->term_id, 'vi_wpvs_terms_params', true );
							$term_class             = $option_selected === $term->slug ? 'vi-wpvs-option-wrap vi-wpvs-option-wrap-selected' : 'vi-wpvs-option-wrap vi-wpvs-option-wrap-default';
							if ( $use_taxonomy_type ) {
								$terms_img_id = $vi_wpvs_terms_settings['img_id'] ?? '';
							} else {
								$terms_img_id = $img_ids[ $term->term_id ] ?? $vi_wpvs_terms_settings['img_id'] ?? '';
							}
							$variation_img_id = '';
							$img_loop_src     = '';
							$img_loop_data    = '';
							if ( $terms_img_id ) {
								$img_url = wp_get_attachment_image_url( $terms_img_id, $attribute_image_size );
								if ( $change_product_image === 'attribute_image' ) {
									$img_loop_src  = wp_get_attachment_image_url( $terms_img_id, 'woocommerce_thumbnail', true );
									$img_loop_data = json_encode( self::get_attachment_props( $terms_img_id ) );
								}
							} else {
								/*WPML - use image of original attribute if not set for the translated one*/
								$source_term = self::wpml_get_original_global_term( $term );
								if ( $source_term ) {
									$vi_wpvs_terms_settings = get_term_meta( $source_term->term_id, 'vi_wpvs_terms_params', true );
									if ( $use_taxonomy_type ) {
										$terms_img_id = $vi_wpvs_terms_settings['img_id'] ?? '';
									} else {
										$terms_img_id = $img_ids[ $term->term_id ] ?? $vi_wpvs_terms_settings['img_id'] ?? '';
									}
								}
								if ( $terms_img_id ) {
									$img_url = wp_get_attachment_image_url( $terms_img_id, $attribute_image_size );
									if ( $change_product_image === 'attribute_image' ) {
										$img_loop_src  = wp_get_attachment_image_url( $terms_img_id, 'woocommerce_thumbnail', true );
										$img_loop_data = json_encode( self::get_attachment_props( $terms_img_id ) );
									}
								} else {
									$img_url = wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' );
								}
							}
							$attr_img_id = $terms_img_id;
							if ( $change_product_image === 'variation_image' || strpos( $attribute_tooltip_content, '{variation_image}' ) !== false ) {
								$variation_img_id = self::get_variation_image_id( $variations, $term, $attribute, '' );
								if ( $variation_img_id ) {
									$img_loop_src  = wp_get_attachment_image_url( $variation_img_id, 'woocommerce_thumbnail', true );
									$img_loop_data = json_encode( self::get_attachment_props( $variation_img_id ) );
								}
							}
							?>
                            <div class="<?php echo esc_attr( $term_class ); ?>"
                                 data-attribute_label="<?php echo esc_attr( $term_name ); ?>"
                                 data-attribute_value="<?php echo esc_attr( $term->slug ); ?>"
                                 data-loop_source="<?php echo esc_url( $img_loop_src ); ?>"
                                 data-loop_data="<?php echo esc_attr( $img_loop_data ); ?>"
                                 data-change_product_image="<?php echo esc_attr( $change_product_image ); ?>">
                                <img src="<?php echo esc_url( $img_url ); ?>"
                                     srcset="<?php echo esc_url( $img_url ); ?>"
                                     alt="<?php echo esc_attr( $term->slug ); ?>"
                                     class="vi-wpvs-option vi-wpvs-option-image">
								<?php
								self::tooltip_html( $attribute_tooltip_content, $attribute_tooltip_position, $term_name, $term, $attribute, $variations, $product, $attr_img_id, $variation_img_id );
								self::out_of_stock_icon_html();
								?>
                            </div>
							<?php
						}
						if ( ! empty( $vi_args['viwpvs_link_more'] ) ) {
							$link_more_text = $settings->get_params( 'product_list_maximum_more_link_text' );
							$link_more_text = str_replace( '{link_more_icon}', '<i class="viwpvs_linkmore-plus"></i>', $link_more_text );
							$product_link   = $product->get_permalink();
							?>
                            <a href="<?php echo esc_attr( esc_url( $product_link ) ) ?>"
                               class="vi-wpvs-option-wrap vi-wpvs-option-wrap-default vi-wpvs-product-link"
                               data-product_link="<?php echo esc_attr( esc_url( $product_link ) ) ?>">
                                <span class="vi-wpvs-option vi-wpvs-option-image">
						            <?php echo wp_kses_post( $link_more_text ); ?>
					            </span>
                                <div class="vi-wpvs-option-tooltip vi-wpvs-option-tooltip-<?php echo esc_attr( $attribute_tooltip_position ); ?>"
                                     data-attribute_label="<?php esc_attr_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>">
                                    <span>
                                        <?php esc_html_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>
                                    </span>
                                </div>
                            </a>
							<?php
						}
						break;
					case 'variation_img':
						foreach ( $terms as $term ) {
							if ( ! in_array( $term->slug, $options ) ) {
								continue;
							}
							$option_tr     = self::get_translated_attribute_term( $term );
							$term_name     = apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product );
							$term_class    = $option_selected === $term->slug ? 'vi-wpvs-option-wrap vi-wpvs-option-wrap-selected' : 'vi-wpvs-option-wrap vi-wpvs-option-wrap-default';
							$terms_img_id  = self::get_variation_image_id( $variations, $term, $attribute, $option_tr );
							$img_loop_src  = '';
							$img_loop_data = '';
							if ( $terms_img_id ) {
								$img_url = wp_get_attachment_image_url( $terms_img_id, $attribute_image_size );
								if ( $change_product_image !== 'not_change' ) {
									$img_loop_src  = wp_get_attachment_image_url( $terms_img_id, 'woocommerce_thumbnail', true );
									$img_loop_data = json_encode( self::get_attachment_props( $terms_img_id ) );
								}
							} else {
								$img_url = wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' );
							}
							?>
                            <div class="<?php echo esc_attr( $term_class ); ?>"
                                 data-attribute_label="<?php echo esc_attr( $term_name ); ?>"
                                 data-attribute_value="<?php echo esc_attr( $term->slug ); ?>"
                                 data-loop_source="<?php echo esc_url( $img_loop_src ); ?>"
                                 data-loop_data="<?php echo esc_attr( $img_loop_data ); ?>"
                                 data-change_product_image="<?php echo esc_attr( $change_product_image ); ?>">
                                <img src="<?php echo esc_url( $img_url ); ?>"
                                     srcset="<?php echo esc_url( $img_url ); ?>"
                                     alt="<?php echo esc_attr( $term->slug ); ?>"
                                     class="vi-wpvs-option vi-wpvs-option-image">
								<?php
								self::tooltip_html( $attribute_tooltip_content, $attribute_tooltip_position, $term_name, $term, $attribute, $variations, $product, $terms_img_id, $terms_img_id );
								self::out_of_stock_icon_html();
								?>
                            </div>
							<?php
						}
						if ( ! empty( $vi_args['viwpvs_link_more'] ) ) {
							$link_more_text = $settings->get_params( 'product_list_maximum_more_link_text' );
							$link_more_text = str_replace( '{link_more_icon}', '<i class="viwpvs_linkmore-plus"></i>', $link_more_text );
							$product_link   = $product->get_permalink();
							?>
                            <a href="<?php echo esc_attr( esc_url( $product_link ) ) ?>"
                               class="vi-wpvs-option-wrap vi-wpvs-option-wrap-default vi-wpvs-product-link"
                               data-product_link="<?php echo esc_attr( esc_url( $product_link ) ) ?>">
                                <span class="vi-wpvs-option vi-wpvs-option-image">
						            <?php echo wp_kses_post( $link_more_text ); ?>
					            </span>
                                <div class="vi-wpvs-option-tooltip vi-wpvs-option-tooltip-<?php echo esc_attr( $attribute_tooltip_position ); ?>"
                                     data-attribute_label="<?php esc_attr_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>">
                                    <span>
                                        <?php esc_html_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>
                                    </span>
                                </div>
                            </a>
							<?php
						}
						break;
					case 'radio':
						foreach ( $terms as $term ) {
							if ( ! in_array( $term->slug, $options ) ) {
								continue;
							}
							$term_name    = apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product );
							$term_class   = $option_selected === $term->slug ? 'vi-wpvs-option-wrap vi-wpvs-option-wrap-selected' : 'vi-wpvs-option-wrap vi-wpvs-option-wrap-default';
							$terms_img_id = $attr_img_id = $variation_img_id = '';
							if ( $change_product_image === 'attribute_image' || strpos( $attribute_tooltip_content, '{attribute_image}' ) !== false ) {
								if ( $use_taxonomy_type ) {
									$attr_img_id = $vi_wpvs_terms_settings['img_id'] ?? '';
								} else {
									$attr_img_id = $img_ids[ $term->term_id ] ?? $vi_wpvs_terms_settings['img_id'] ?? '';
								}
							}
							if ( $change_product_image === 'variation_image' || strpos( $attribute_tooltip_content, '{variation_image}' ) !== false ) {
								$variation_img_id = self::get_variation_image_id( $variations, $term, $attribute, '' );
							}
							if ( $change_product_image === 'attribute_image' ) {
								$terms_img_id = $attr_img_id;
							} elseif ( $change_product_image === 'variation_image' ) {
								$terms_img_id = $variation_img_id;
							}
							$img_loop_src  = '';
							$img_loop_data = '';
							if ( $terms_img_id ) {
								$img_loop_src  = wp_get_attachment_image_url( $terms_img_id, 'woocommerce_thumbnail', true );
								$img_loop_data = json_encode( self::get_attachment_props( $terms_img_id ) );
							}
							?>
                            <div class="<?php echo esc_attr( $term_class ); ?>"
                                 data-attribute_label="<?php echo esc_attr( $term_name ); ?>"
                                 data-attribute_value="<?php echo esc_attr( $term->slug ); ?>"
                                 data-loop_source="<?php echo esc_url( $img_loop_src ); ?>"
                                 data-loop_data="<?php echo esc_attr( $img_loop_data ); ?>"
                                 data-change_product_image="<?php echo esc_attr( $change_product_image ); ?>">
								<?php
								$option_radio_id = '"vi-wpvs-option-radio-' . $product->get_id() . '-' . $term->slug;
								?>
                                <label for="<?php echo esc_attr( $option_radio_id ); ?>" class="vi-wpvs-option">
                                    <input type="radio" value="<?php echo esc_attr( $term->slug ); ?>"
                                           class="vi-wpvs-option-radio" id="<?php echo esc_attr( $option_radio_id ); ?>"
										<?php checked( $term->slug, $option_selected ); ?> >
									<?php echo wp_kses_post( $term_name ); ?>
                                </label>
								<?php
								self::tooltip_html( $attribute_tooltip_content, $attribute_tooltip_position, $term_name, $term, $attribute, $variations, $product, $attr_img_id, $variation_img_id );
								self::out_of_stock_icon_html();
								?>
                            </div>
							<?php
						}
						if ( ! empty( $vi_args['viwpvs_link_more'] ) ) {
							$link_more_text  = $settings->get_params( 'product_list_maximum_more_link_text' );
							$link_more_text  = str_replace( '{link_more_icon}', '<i class="viwpvs_linkmore-plus"></i>', $link_more_text );
							$product_link    = $product->get_permalink();
							$option_radio_id = '"vi-wpvs-option-radio-' . $product->get_id() . '-linkmore';
							?>
                            <a href="<?php echo esc_attr( esc_url( $product_link ) ) ?>"
                               class="vi-wpvs-option-wrap vi-wpvs-option-wrap-default vi-wpvs-product-link"
                               data-product_link="<?php echo esc_attr( esc_url( $product_link ) ) ?>">
                                <label for="<?php echo esc_attr( $option_radio_id ); ?>" class="vi-wpvs-option">
                                    <input type="radio" value=""
                                           class="vi-wpvs-option vi-wpvs-option-radio"
                                           id="<?php echo esc_attr( $option_radio_id ); ?>">
									<?php echo wp_kses_post( $link_more_text ); ?>
                                </label>
                                <div class="vi-wpvs-option-tooltip vi-wpvs-option-tooltip-<?php echo esc_attr( $attribute_tooltip_position ); ?>"
                                     data-attribute_label="<?php esc_attr_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>">
                                    <span>
                                        <?php esc_html_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>
                                    </span>
                                </div>
                            </a>
							<?php
						}
						break;
					default:
						$show_option_none_text = empty( $vi_args['show_option_none'] ) ? esc_html__( 'Choose an option', 'woocommerce-product-variations-swatches' ) : $vi_args['show_option_none'];
						?>
                        <div class="vi-wpvs-variation-wrap-select-wrap">
                            <div class="vi-wpvs-variation-button-select">
                        <span>
                            <?php
                            echo esc_html( $show_option_none_text );
                            ?>
                        </span>
                            </div>
                            <div class="vi-wpvs-variation-wrap-option vi-wpvs-select-hidden">
								<?php
								if ( ! empty( $vi_args['show_option_none'] ) ) {
									?>
                                    <div class="vi-wpvs-option-wrap vi-wpvs-option-wrap-default" data-attribute_value=""
                                         data-attribute_label="">
                                        <span class="vi-wpvs-option vi-wpvs-option-select">
                                            <?php echo esc_html( $show_option_none_text ); ?>
                                        </span>
                                    </div>
									<?php
								}
								foreach ( $terms as $term ) {
									if ( ! in_array( $term->slug, $options ) ) {
										continue;
									}
									$term_name    = apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product );
									$term_class   = $option_selected === $term->slug ? 'vi-wpvs-option-wrap vi-wpvs-option-wrap-selected' : 'vi-wpvs-option-wrap vi-wpvs-option-wrap-default';
									$terms_img_id = '';
									if ( $change_product_image === 'attribute_image' ) {
										if ( $use_taxonomy_type ) {
											$terms_img_id = $vi_wpvs_terms_settings['img_id'] ?? '';
										} else {
											$terms_img_id = $img_ids[ $term->term_id ] ?? $vi_wpvs_terms_settings['img_id'] ?? '';
										}
									} elseif ( $change_product_image === 'variation_image' ) {
										$terms_img_id = self::get_variation_image_id( $variations, $term, $attribute, '' );
									}
									$img_loop_src  = '';
									$img_loop_data = '';
									if ( $terms_img_id ) {
										$img_loop_src  = wp_get_attachment_image_url( $terms_img_id, 'woocommerce_thumbnail', true );
										$img_loop_data = json_encode( self::get_attachment_props( $terms_img_id ) );
									}
									?>
                                    <div class="<?php echo esc_attr( $term_class ); ?>"
                                         data-attribute_label="<?php echo esc_attr( $term_name ); ?>"
                                         data-attribute_value="<?php echo esc_attr( $term->slug ); ?>"
                                         data-loop_source="<?php echo esc_url( $img_loop_src ); ?>"
                                         data-loop_data="<?php echo esc_attr( $img_loop_data ); ?>"
                                         data-change_product_image="<?php echo esc_attr( $change_product_image ); ?>">
                                        <span class="vi-wpvs-option vi-wpvs-option-select">
                                            <?php echo wp_kses_post( $term_name ); ?>
                                        </span>
										<?php self::out_of_stock_icon_html(); ?>
                                    </div>
									<?php
								}
								if ( ! empty( $vi_args['viwpvs_link_more'] ) ) {
									$link_more_text = $settings->get_params( 'product_list_maximum_more_link_text' );
									$link_more_text = str_replace( '{link_more_icon}', '<i class="viwpvs_linkmore-plus"></i>', $link_more_text );
									$product_link   = $product->get_permalink();
									?>
                                    <a href="<?php echo esc_attr( esc_url( $product_link ) ) ?>"
                                       class="vi-wpvs-option-wrap vi-wpvs-option-wrap-default vi-wpvs-product-link"
                                       data-product_link="<?php echo esc_attr( esc_url( $product_link ) ) ?>"
                                       title="<?php esc_attr_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>">
                                        <span class="vi-wpvs-option vi-wpvs-option-select"><?php echo wp_kses_post( $link_more_text ); ?></span>
                                    </a>
									<?php
								}
								?>
                            </div>
                        </div>
					<?php
				}
			} else {
				$attribute_options = $product->get_attribute( $attribute );
				$attribute_options = explode( '|', $attribute_options );
				$attribute_options = array_map( 'trim', $attribute_options );
				switch ( $type ) {
					case 'button':
						foreach ( $options as $k => $option ) {
							$option_name  = apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product );
							$option_class = ( $option_selected === $option || $option_selected === sanitize_title( $option ) ) ? 'vi-wpvs-option-wrap vi-wpvs-option-wrap-selected' : 'vi-wpvs-option-wrap vi-wpvs-option-wrap-default';
							$option_img   = $attr_img_id = $variation_img_id = '';
							if ( $change_product_image === 'attribute_image' || strpos( $attribute_tooltip_content, '{attribute_image}' ) !== false ) {
								$key = array_search( $option, $attribute_options );
								if ( $key !== false && isset( $img_ids[ $key ] ) ) {
									$attr_img_id = $img_ids[ $key ];
								}
							}
							if ( $change_product_image === 'variation_image' || strpos( $attribute_tooltip_content, '{variation_image}' ) !== false ) {
								$variation_img_id = self::get_variation_image_id( $variations, $option, $attribute, '' );
							}
							if ( $change_product_image === 'attribute_image' ) {
								$option_img = $attr_img_id;
							} elseif ( $change_product_image === 'variation_image' ) {
								$option_img = $variation_img_id;
							}
							$img_loop_src  = '';
							$img_loop_data = '';
							if ( $option_img ) {
								$img_loop_src  = wp_get_attachment_image_url( $option_img, 'woocommerce_thumbnail', true );
								$img_loop_data = json_encode( self::get_attachment_props( $option_img ) );
							}
							?>
                            <div class="<?php echo esc_attr( $option_class ); ?>"
                                 data-attribute_value="<?php echo esc_attr( $option ); ?>"
                                 data-attribute_label="<?php echo esc_attr( $option_name ); ?>"
                                 data-loop_source="<?php echo esc_url( $img_loop_src ); ?>"
                                 data-loop_data="<?php echo esc_attr( $img_loop_data ); ?>"
                                 data-change_product_image="<?php echo esc_attr( $change_product_image ); ?>">
                                <span class="vi-wpvs-option vi-wpvs-option-button">
						            <?php echo wp_kses_post( $option_name ); ?>
					            </span>
								<?php
								self::tooltip_html( $attribute_tooltip_content, $attribute_tooltip_position, $option_name, null, $attribute, $variations, $product );
								self::out_of_stock_icon_html();
								?>
                            </div>
							<?php
						}
						if ( ! empty( $vi_args['viwpvs_link_more'] ) ) {
							$link_more_text = $settings->get_params( 'product_list_maximum_more_link_text' );
							$link_more_text = str_replace( '{link_more_icon}', '<i class="viwpvs_linkmore-plus"></i>', $link_more_text );
							$product_link   = $product->get_permalink();
							?>
                            <a href="<?php echo esc_attr( esc_url( $product_link ) ) ?>"
                               class="vi-wpvs-option-wrap vi-wpvs-option-wrap-default vi-wpvs-product-link"
                               data-product_link="<?php echo esc_attr( esc_url( $product_link ) ) ?>">
                                <span class="vi-wpvs-option vi-wpvs-option-button">
						            <?php echo wp_kses_post( $link_more_text ); ?>
					            </span>
                                <div class="vi-wpvs-option-tooltip vi-wpvs-option-tooltip-<?php echo esc_attr( $attribute_tooltip_position ); ?>"
                                     data-attribute_label="<?php esc_attr_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>">
                                    <span>
                                        <?php esc_html_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>
                                    </span>
                                </div>
                            </a>
							<?php
						}
						break;
					case 'color':
						foreach ( $options as $k => $option ) {
							$option_class = ( $option_selected === $option || $option_selected === sanitize_title( $option ) ) ? 'vi-wpvs-option-wrap vi-wpvs-option-wrap-selected' : 'vi-wpvs-option-wrap vi-wpvs-option-wrap-default';
							$option_name  = apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product );
							$key          = array_search( $option, $attribute_options );
							$option_img   = $attr_img_id = $variation_img_id = '';
							if ( $key !== false ) {
								$option_colors          = $colors[ $key ] ?? array();
								$option_color_separator = $color_separator[ $key ] ?? '1';
								if ( ( $change_product_image === 'attribute_image' || strpos( $attribute_tooltip_content, '{attribute_image}' ) !== false ) && isset( $img_ids[ $key ] ) ) {
									$attr_img_id = $img_ids[ $key ];
								}
							} else {
								$option_colors          = array();
								$option_color_separator = '1';
							}
							if ( $change_product_image === 'variation_image' || strpos( $attribute_tooltip_content, '{variation_image}' ) !== false ) {
								$variation_img_id = self::get_variation_image_id( $variations, $option, $attribute, '' );
							}
							if ( $change_product_image === 'attribute_image' ) {
								$option_img = $attr_img_id;
							} elseif ( $change_product_image === 'variation_image' ) {
								$option_img = $variation_img_id;
							}
							$img_loop_src  = '';
							$img_loop_data = '';
							if ( $option_img ) {
								$img_loop_src  = wp_get_attachment_image_url( $option_img, 'woocommerce_thumbnail', true );
								$img_loop_data = json_encode( self::get_attachment_props( $option_img ) );
							}
							$option_color = self::get_attribute_option_color( $option, $option_colors, $option_color_separator );
							?>
                            <div class="<?php echo esc_attr( $option_class ); ?>"
                                 data-attribute_value="<?php echo esc_attr( $option ); ?>"
                                 data-attribute_label="<?php echo esc_attr( $option_name ); ?>"
                                 data-loop_source="<?php echo esc_url( $img_loop_src ); ?>"
                                 data-loop_data="<?php echo esc_attr( $img_loop_data ); ?>"
                                 data-change_product_image="<?php echo esc_attr( $change_product_image ); ?>">
                                <span class="vi-wpvs-option vi-wpvs-option-color"
                                      data-option_color="<?php echo esc_attr( $option_color ); ?>"></span>
								<?php
								self::tooltip_html( $attribute_tooltip_content, $attribute_tooltip_position, $option_name, null, $attribute, $variations, $product );
								self::out_of_stock_icon_html();
								?>
                            </div>
							<?php
						}
						if ( ! empty( $vi_args['viwpvs_link_more'] ) ) {
							$link_more_text = $settings->get_params( 'product_list_maximum_more_link_text' );
							$link_more_text = str_replace( '{link_more_icon}', '<i class="viwpvs_linkmore-plus"></i>', $link_more_text );
							$product_link   = $product->get_permalink();
							?>
                            <a href="<?php echo esc_attr( esc_url( $product_link ) ) ?>"
                               class="vi-wpvs-option-wrap vi-wpvs-option-wrap-default vi-wpvs-product-link"
                               data-product_link="<?php echo esc_attr( esc_url( $product_link ) ) ?>">
                                <span class="vi-wpvs-option vi-wpvs-option-color"
                                      data-option_color="<?php echo esc_attr( 'transparent' ); ?>"
                                      data-option_separator="">
						            <?php echo wp_kses_post( $link_more_text ); ?>
					            </span>
                                <div class="vi-wpvs-option-tooltip vi-wpvs-option-tooltip-<?php echo esc_attr( $attribute_tooltip_position ); ?>"
                                     data-attribute_label="<?php esc_attr_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>">
                                    <span>
                                        <?php esc_html_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>
                                    </span>
                                </div>
                            </a>
							<?php
						}
						break;
					case 'image':
						foreach ( $options as $k => $option ) {
							$option_class  = ( $option_selected === $option || $option_selected === sanitize_title( $option ) ) ? 'vi-wpvs-option-wrap vi-wpvs-option-wrap-selected' : 'vi-wpvs-option-wrap vi-wpvs-option-wrap-default';
							$option_name   = apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product );
							$key           = array_search( $option, $attribute_options );
							$option_img    = '';
							$img_loop_src  = '';
							$img_loop_data = '';
							$img_url       = wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' );
							if ( $key !== false && isset( $img_ids[ $key ] ) ) {
								$option_img = $img_ids[ $key ];
								if ( $option_img ) {
									$img_url = wp_get_attachment_image_url( $option_img, $attribute_image_size );
									if ( $change_product_image === 'attribute_image' ) {
										$img_loop_src  = wp_get_attachment_image_url( $option_img, 'woocommerce_thumbnail', true );
										$img_loop_data = json_encode( self::get_attachment_props( $option_img ) );
									}
								}
							}
							$attr_img_id      = $option_img;
							$variation_img_id = '';
							if ( $change_product_image === 'variation_image' || strpos( $attribute_tooltip_content, '{variation_image}' ) !== false ) {
								$variation_img_id = self::get_variation_image_id( $variations, $option, $attribute, '' );
								if ( $variation_img_id ) {
									$img_loop_src  = wp_get_attachment_image_url( $variation_img_id, 'woocommerce_thumbnail', true );
									$img_loop_data = json_encode( self::get_attachment_props( $variation_img_id ) );
								}
							}
							?>
                            <div class="<?php echo esc_attr( $option_class ); ?>"
                                 data-attribute_value="<?php echo esc_attr( $option ); ?>"
                                 data-attribute_label="<?php echo esc_attr( $option_name ); ?>"
                                 data-loop_source="<?php echo esc_url( $img_loop_src ); ?>"
                                 data-loop_data="<?php echo esc_attr( $img_loop_data ); ?>"
                                 data-change_product_image="<?php echo esc_attr( $change_product_image ); ?>">
                                <img src="<?php echo esc_url( $img_url ); ?>"
                                     srcset="<?php echo esc_url( $img_url ); ?>"
                                     alt="<?php echo esc_attr( $option ); ?>"
                                     class="vi-wpvs-option vi-wpvs-option-image">
								<?php
								self::tooltip_html( $attribute_tooltip_content, $attribute_tooltip_position, $option_name, null, $attribute, $variations, $product, $attr_img_id, $variation_img_id );
								self::out_of_stock_icon_html();
								?>
                            </div>
							<?php
						}
						if ( ! empty( $vi_args['viwpvs_link_more'] ) ) {
							$link_more_text = $settings->get_params( 'product_list_maximum_more_link_text' );
							$link_more_text = str_replace( '{link_more_icon}', '<i class="viwpvs_linkmore-plus"></i>', $link_more_text );
							$product_link   = $product->get_permalink();
							?>
                            <a href="<?php echo esc_attr( esc_url( $product_link ) ) ?>"
                               class="vi-wpvs-option-wrap vi-wpvs-option-wrap-default vi-wpvs-product-link"
                               data-product_link="<?php echo esc_attr( esc_url( $product_link ) ) ?>">
                                <span class="vi-wpvs-option vi-wpvs-option-image">
						            <?php echo wp_kses_post( $link_more_text ); ?>
					            </span>
                                <div class="vi-wpvs-option-tooltip vi-wpvs-option-tooltip-<?php echo esc_attr( $attribute_tooltip_position ); ?>"
                                     data-attribute_label="<?php esc_attr_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>">
                                    <span>
                                        <?php esc_html_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>
                                    </span>
                                </div>
                            </a>
							<?php
						}
						break;
					case 'variation_img':
						foreach ( $options as $k => $option ) {
							$option_tr     = self::get_translated_attribute_term( $option );
							$option_class  = ( $option_selected === $option || $option_selected === sanitize_title( $option ) ) ? 'vi-wpvs-option-wrap vi-wpvs-option-wrap-selected' : 'vi-wpvs-option-wrap vi-wpvs-option-wrap-default';
							$option_name   = apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product );
							$option_img    = self::get_variation_image_id( $variations, $option, $attribute, $option_tr );
							$img_loop_src  = '';
							$img_loop_data = '';
							if ( $option_img ) {
								$img_url = wp_get_attachment_image_url( $option_img, $attribute_image_size );
								if ( $change_product_image !== 'not_change' ) {
									$img_loop_src  = wp_get_attachment_image_url( $option_img, 'woocommerce_thumbnail', true );
									$img_loop_data = json_encode( self::get_attachment_props( $option_img ) );
								}
							} else {
								$img_url = wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' );
							}
							?>
                            <div class="<?php echo esc_attr( $option_class ); ?>"
                                 data-attribute_value="<?php echo esc_attr( $option ); ?>"
                                 data-attribute_label="<?php echo esc_attr( $option_name ); ?>"
                                 data-loop_source="<?php echo esc_url( $img_loop_src ); ?>"
                                 data-loop_data="<?php echo esc_attr( $img_loop_data ); ?>"
                                 data-change_product_image="<?php echo esc_attr( $change_product_image ); ?>">
                                <img src="<?php echo esc_url( $img_url ); ?>"
                                     srcset="<?php echo esc_url( $img_url ); ?>"
                                     alt="<?php echo esc_attr( $option ); ?>"
                                     class="vi-wpvs-option vi-wpvs-option-image">
								<?php
								self::tooltip_html( $attribute_tooltip_content, $attribute_tooltip_position, $option_name, null, $attribute, $variations, $product, $option_img, $option_img );
								self::out_of_stock_icon_html();
								?>
                            </div>
							<?php
						}
						if ( ! empty( $vi_args['viwpvs_link_more'] ) ) {
							$link_more_text = $settings->get_params( 'product_list_maximum_more_link_text' );
							$link_more_text = str_replace( '{link_more_icon}', '<i class="viwpvs_linkmore-plus"></i>', $link_more_text );
							$product_link   = $product->get_permalink();
							?>
                            <a href="<?php echo esc_attr( esc_url( $product_link ) ) ?>"
                               class="vi-wpvs-option-wrap vi-wpvs-option-wrap-default vi-wpvs-product-link"
                               data-product_link="<?php echo esc_attr( esc_url( $product_link ) ) ?>">
                                <span class="vi-wpvs-option vi-wpvs-option-image">
						            <?php echo wp_kses_post( $link_more_text ); ?>
					            </span>
                                <div class="vi-wpvs-option-tooltip vi-wpvs-option-tooltip-<?php echo esc_attr( $attribute_tooltip_position ); ?>"
                                     data-attribute_label="<?php esc_attr_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>">
                                    <span>
                                        <?php esc_html_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>
                                    </span>
                                </div>
                            </a>
							<?php
						}
						break;
					case 'radio':
						foreach ( $options as $k => $option ) {
							$option_name  = apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product );
							$option_class = ( $option_selected === $option || $option_selected === sanitize_title( $option ) ) ? 'vi-wpvs-option-wrap vi-wpvs-option-wrap-selected' : 'vi-wpvs-option-wrap vi-wpvs-option-wrap-default';
							$option_img   = $attr_img_id = $variation_img_id = '';
							if ( $change_product_image === 'attribute_image' || strpos( $attribute_tooltip_content, '{attribute_image}' ) !== false ) {
								$key = array_search( $option, $attribute_options );
								if ( $key !== false && isset( $img_ids[ $key ] ) ) {
									$attr_img_id = $img_ids[ $key ];
								}
							}
							if ( $change_product_image === 'variation_image' || strpos( $attribute_tooltip_content, '{variation_image}' ) !== false ) {
								$variation_img_id = self::get_variation_image_id( $variations, $option, $attribute, '' );
							}
							if ( $change_product_image === 'attribute_image' ) {
								$option_img = $attr_img_id;
							} elseif ( $change_product_image === 'variation_image' ) {
								$option_img = $variation_img_id;
							}
							$img_loop_src  = '';
							$img_loop_data = '';
							if ( $option_img ) {
								$img_loop_src  = wp_get_attachment_image_url( $option_img, 'woocommerce_thumbnail', true );
								$img_loop_data = json_encode( self::get_attachment_props( $option_img ) );
							}
							?>
                            <div class="<?php echo esc_attr( $option_class ); ?>"
                                 data-attribute_value="<?php echo esc_attr( $option ); ?>"
                                 data-attribute_label="<?php echo esc_attr( $option_name ); ?>"
                                 data-loop_source="<?php echo esc_url( $img_loop_src ); ?>"
                                 data-loop_data="<?php echo esc_attr( $img_loop_data ); ?>"
                                 data-change_product_image="<?php echo esc_attr( $change_product_image ); ?>">
								<?php
								$option_radio_id = '"vi-wpvs-option-radio-' . $product->get_id() . '-' . $option;
								?>
                                <label for="<?php echo esc_attr( $option_radio_id ); ?>" class="vi-wpvs-option">
                                    <input type="radio" value="<?php echo esc_attr( $option ); ?>"
                                           class="vi-wpvs-option vi-wpvs-option-radio"
                                           id="<?php echo esc_attr( $option_radio_id ); ?>"
										<?php echo $option_selected === $option || $option_selected === sanitize_title( $option ) ? esc_attr( 'checked' ) : ''; ?>>
									<?php echo wp_kses_post( $option_name ); ?>
                                </label>
								<?php
								self::tooltip_html( $attribute_tooltip_content, $attribute_tooltip_position, $option_name, null, $attribute, $variations, $product, $attr_img_id, $variation_img_id );
								self::out_of_stock_icon_html();
								?>
                            </div>
							<?php
						}
						if ( ! empty( $vi_args['viwpvs_link_more'] ) ) {
							$link_more_text  = $settings->get_params( 'product_list_maximum_more_link_text' );
							$link_more_text  = str_replace( '{link_more_icon}', '<i class="viwpvs_linkmore-plus"></i>', $link_more_text );
							$product_link    = $product->get_permalink();
							$option_radio_id = '"vi-wpvs-option-radio-' . $product->get_id() . '-linkmore';
							?>
                            <a href="<?php echo esc_attr( esc_url( $product_link ) ) ?>"
                               class="vi-wpvs-option-wrap vi-wpvs-option-wrap-default vi-wpvs-product-link"
                               data-product_link="<?php echo esc_attr( esc_url( $product_link ) ) ?>">
                                <label for="<?php echo esc_attr( $option_radio_id ); ?>" class="vi-wpvs-option">
                                    <input type="radio" value=""
                                           class="vi-wpvs-option vi-wpvs-option-radio"
                                           id="<?php echo esc_attr( $option_radio_id ); ?>">
									<?php echo wp_kses_post( $link_more_text ); ?>
                                </label>
                                <div class="vi-wpvs-option-tooltip vi-wpvs-option-tooltip-<?php echo esc_attr( $attribute_tooltip_position ); ?>"
                                     data-attribute_label="<?php esc_attr_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>">
                                    <span>
                                        <?php esc_html_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>
                                    </span>
                                </div>
                            </a>
							<?php
						}
						break;
					default:
						$show_option_none_text = empty( $vi_args['show_option_none'] ) ? esc_html__( 'Choose an option', 'woocommerce-product-variations-swatches' ) : $vi_args['show_option_none'];
						?>
                        <div class="vi-wpvs-variation-wrap-select-wrap">
                            <div class="vi-wpvs-variation-button-select">
                                <span>
                                    <?php echo esc_html( $show_option_none_text ); ?>
                                </span>
                            </div>
                            <div class="vi-wpvs-variation-wrap-option vi-wpvs-select-hidden">
								<?php
								if ( ! empty( $show_option_none_text ) ) {
									?>
                                    <div class="vi-wpvs-option-wrap vi-wpvs-option-wrap-default"
                                         data-attribute_value=""
                                         data-attribute_label="">
                                        <span class="vi-wpvs-option vi-wpvs-option-select">
                                            <?php echo esc_html( $show_option_none_text ); ?>
                                        </span>
                                    </div>
									<?php
								}
								foreach ( $options as $k => $option ) {
									$option_class = ( $option_selected === $option || $option_selected === sanitize_title( $option ) ) ? 'vi-wpvs-option-wrap vi-wpvs-option-wrap-selected' : 'vi-wpvs-option-wrap vi-wpvs-option-wrap-default';
									$option_name  = apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product );
									$option_img   = '';
									if ( $change_product_image === 'attribute_image' ) {
										$key = array_search( $option, $attribute_options );
										if ( $key !== false && isset( $img_ids[ $key ] ) ) {
											$option_img = $img_ids[ $key ];
										}
									} elseif ( $change_product_image === 'variation_image' ) {
										$option_img = self::get_variation_image_id( $variations, $option, $attribute, '' );
									}
									$img_loop_src  = '';
									$img_loop_data = '';
									if ( $option_img ) {
										$img_loop_src  = wp_get_attachment_image_url( $option_img, 'woocommerce_thumbnail', true );
										$img_loop_data = json_encode( self::get_attachment_props( $option_img ) );
									}
									?>
                                    <div class="<?php echo esc_attr( $option_class ); ?>"
                                         data-attribute_value="<?php echo esc_attr( $option ); ?>"
                                         data-attribute_label="<?php echo esc_attr( $option_name ); ?>"
                                         value="<?php echo esc_attr( $option ); ?>"
                                         data-loop_source="<?php echo esc_url( $img_loop_src ); ?>"
                                         data-loop_data="<?php echo esc_attr( $img_loop_data ); ?>"
                                         data-change_product_image="<?php echo esc_attr( $change_product_image ); ?>">
										<span class="vi-wpvs-option vi-wpvs-option-select">
                                        <?php echo wp_kses_post( $option_name ); ?>
                                        </span>
										<?php self::out_of_stock_icon_html(); ?>
                                    </div>
									<?php
								}
								if ( ! empty( $vi_args['viwpvs_link_more'] ) ) {
									$link_more_text = $settings->get_params( 'product_list_maximum_more_link_text' );
									$link_more_text = str_replace( '{link_more_icon}', '<i class="viwpvs_linkmore-plus"></i>', $link_more_text );
									$product_link   = $product->get_permalink();
									?>
                                    <a href="<?php echo esc_attr( esc_url( $product_link ) ) ?>"
                                       class="vi-wpvs-option-wrap vi-wpvs-option-wrap-default vi-wpvs-product-link"
                                       data-product_link="<?php echo esc_attr( esc_url( $product_link ) ) ?>"
                                       title="<?php esc_attr_e( 'See More', 'woocommerce-product-variations-swatches' ); ?>">
                                        <span class="vi-wpvs-option vi-wpvs-option-select"><?php echo wp_kses_post( $link_more_text ); ?></span>
                                    </a>
									<?php
								}
								?>
                            </div>
                        </div>
					<?php
				}
			}
			?>
        </div>
		<?php
		$html = ob_get_clean();

		return $html;
	}

	public static function get_variation_image_id( $variations, $term, $attribute, $option_tr ) {
		$terms_img_id = '';
		if ( is_string( $term ) ) {
			foreach ( $variations as $variation_id ) {
				$attribute_ = get_post_meta( $variation_id, 'attribute_' . sanitize_title( $attribute ), true );
				if ( $term === $attribute_ || ( $option_tr && $option_tr === $attribute_ ) ) {
					$terms_img_id = get_post_thumbnail_id( $variation_id );
					break;
				}
			}
		} else {
			foreach ( $variations as $variation_id ) {
				$attribute_ = get_post_meta( $variation_id, 'attribute_' . sanitize_title( $attribute ), true );
				if ( $term->slug === $attribute_ || ( $option_tr && $option_tr === $attribute_ ) ) {
					$terms_img_id = get_post_thumbnail_id( $variation_id );
					break;
				}
			}
		}

		return $terms_img_id;
	}

	public static function wpml_get_original_global_term( $term ) {
		$trid = apply_filters( 'wpml_element_trid', null, $term->term_id, 'tax_' . $term->taxonomy );
		if ( $trid ) {
			return self::wpml_get_source_attribute_term( $trid );
		}

		return false;
	}

	public static function get_translated_attribute_term( $term ) {
		$option_tr = '';
		if ( self::$language ) {
			if ( is_string( $term ) ) {
				$option_l = strlen( $term );
				if ( $option_l > 3 ) {
					if ( substr( $term, ( $option_l - 3 ) ) === '-' . self::$language ) {
						$option_tr = substr( $term, 0, ( $option_l - 3 ) );
					}
				}
			} else {
				$trid = apply_filters( 'wpml_element_trid', null, $term->term_id, 'tax_' . $term->taxonomy );
				if ( $trid ) {
					$src_term = self::wpml_get_source_attribute_term( $trid );
					if ( $src_term ) {
						$option_tr = $src_term->slug;
					}
				}
			}
		}

		return $option_tr;
	}

	public static function get_attribute_taxonomy_type( $attribute = '' ) {
		if ( ! $attribute ) {
			return 'select';
		}
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		$attribute_type       = 'select';
		foreach ( $attribute_taxonomies as $item ) {
			if ( $attribute === 'pa_' . $item->attribute_name ) {
				$attribute_type = $item->attribute_type;
				break;
			}
		}

		return $attribute_type;
	}

	public function wvps_wp_enqueue_scripts() {
		if ( is_admin() ) {
			return;
		}
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			$default_lang     = apply_filters( 'wpml_default_language', null );
			$current_language = apply_filters( 'wpml_current_language', null );

			if ( $current_language && $current_language !== $default_lang ) {
				self::$language = $current_language;
			}
		}
		if ( WP_DEBUG ) {
			wp_enqueue_style( 'vi-wpvs-frontend-style',
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'frontend-style.css',
				array(),
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'vi-wpvs-frontend-script',
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'frontend-script.js',
				array( 'jquery' ),
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION,
				true );
		} else {
			wp_enqueue_style( 'vi-wpvs-frontend-style',
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'frontend-style.min.css',
				array(),
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'vi-wpvs-frontend-script',
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'frontend-script.min.js',
				array( 'jquery' ),
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION,
				true );
		}
		$ids = $this->settings->get_params( 'ids' );
		if ( $ids && is_array( $ids ) && $count_ids = count( $ids ) ) {
			$css = '';
			for ( $i = 0; $i < $count_ids; $i ++ ) {
				$id                   = $ids[ $i ];
				$reduce_mobile        = $this->settings->get_current_setting( 'attribute_reduce_size_mobile', $i );
				$reduce_loop          = $this->settings->get_current_setting( 'attribute_reduce_size_list_product', $i );
				$attribute_height     = $this->settings->get_current_setting( 'attribute_height', $i );
				$attribute_width      = $this->settings->get_current_setting( 'attribute_width', $i );
				$attribute_transition = $this->settings->get_current_setting( 'attribute_transition', $i );

				$default_box_shadow_color = $this->settings->get_current_setting( 'attribute_default_box_shadow_color', $i );
				$default_border_color     = $this->settings->get_current_setting( 'attribute_default_border_color', $i );
				$default_border_width     = $this->settings->get_current_setting( 'attribute_default_border_width', $i );

				$hover_scale            = $this->settings->get_current_setting( 'attribute_hover_scale', $i );
				$hover_box_shadow_color = $this->settings->get_current_setting( 'attribute_hover_box_shadow_color', $i );
				$hover_border_color     = $this->settings->get_current_setting( 'attribute_hover_border_color', $i );
				$hover_border_width     = $this->settings->get_current_setting( 'attribute_hover_border_width', $i );

				$selected_scale            = $this->settings->get_current_setting( 'attribute_selected_scale', $i );
				$selected_box_shadow_color = $this->settings->get_current_setting( 'attribute_selected_box_shadow_color', $i );
				$selected_border_color     = $this->settings->get_current_setting( 'attribute_selected_border_color', $i );
				$selected_border_width     = $this->settings->get_current_setting( 'attribute_selected_border_width', $i );
				$tooltip_enable            = $this->settings->get_current_setting( 'attribute_tooltip_enable', $i );
				$tooltip_type              = $this->settings->get_current_setting( 'attribute_tooltip_type', $i );
				$tooltip_position          = $this->settings->get_current_setting( 'attribute_tooltip_position', $i );
				$tooltip_border_color      = $this->settings->get_current_setting( 'attribute_tooltip_border_color', $i );
				$tooltip_bg_color          = $this->settings->get_current_setting( 'attribute_tooltip_bg_color', $i );

				$attribute_value_enable   = $this->settings->get_current_setting( 'attribute_value_enable', $i );
				$attribute_value_position = $this->settings->get_current_setting( 'attribute_value_position', $i );

				if ( $attribute_transition ) {
					$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap{';
					$css .= 'transition: all ' . $attribute_transition . 'ms ease-in-out;';
					$css .= '}';
				}

				//apply css for .vi-wpvs-option-wrap for all swatches types
				//with select type, we need to implement the same for .vi-wpvs-variation-button-select
				$css .= $this->add_inline_style(
					'.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-variation-button-select,.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap',
					$i,
					array(
						'attribute_height',
						'attribute_width',
						'attribute_padding',
						'attribute_fontsize',
						'attribute_default_border_radius'
					),
					array( 'height', 'width', 'padding', 'font-size', 'border-radius' ),
					array( 'px', 'px', '', 'px', 'px' )
				);
				$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap .vi-wpvs-option:not(.vi-wpvs-option-select){';
				$css .= 'border-radius: inherit;';
				$css .= '}';
				if ( ! $attribute_width || ! $attribute_height ) {
					$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-image.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap ,';
					$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-variation_img.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap ,';
					$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-color.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap{';
					if ( ! $attribute_width ) {
						$attribute_width_t1 = $attribute_height ?: 48;
						$css                .= 'width: ' . $attribute_width_t1 . 'px;';
					}
					if ( ! $attribute_height ) {
						$attribute_height_t1 = $attribute_width ?: 48;
						$css                 .= 'height:' . $attribute_height_t1 . 'px;';
					}
					$css .= '}';
				}

				$attribute_width_t  = $attribute_width ? $attribute_width : ( $attribute_height ?: 48 );
				$attribute_height_t = $attribute_height ? $attribute_height : ( $attribute_width ?: 48 );
				if ( $default_border_width ) {
					$css                 .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-image.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-default .vi-wpvs-option,';
					$css                 .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-variation_img.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-default .vi-wpvs-option,';
					$css                 .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-color.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-default .vi-wpvs-option{';
					$miss_border         = $default_border_width * 2;
					$attribute_width_t1  = $attribute_width_t - $miss_border;
					$attribute_height_t1 = $attribute_height_t - $miss_border;
					$css                 .= 'width: ' . $attribute_width_t1 . 'px;';
					$css                 .= 'height:' . $attribute_height_t1 . 'px;';
					$css                 .= '}';
				}
				if ( $hover_border_width ) {
					$css                 .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-image.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-hover .vi-wpvs-option,';
					$css                 .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-variation_img.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-hover .vi-wpvs-option,';
					$css                 .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-color.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-hover .vi-wpvs-option{';
					$miss_border         = $hover_border_width * 2;
					$attribute_width_t1  = $attribute_width_t - $miss_border;
					$attribute_height_t1 = $attribute_height_t - $miss_border;
					$css                 .= 'width: ' . $attribute_width_t1 . 'px;';
					$css                 .= 'height:' . $attribute_height_t1 . 'px;';
					$css                 .= '}';
				}
				if ( $selected_border_width ) {
					$css                 .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-image.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected .vi-wpvs-option,';
					$css                 .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-variation_img.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected .vi-wpvs-option,';
					$css                 .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-color.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected .vi-wpvs-option{';
					$miss_border         = $selected_border_width * 2;
					$attribute_width_t1  = $attribute_width_t - $miss_border;
					$attribute_height_t1 = $attribute_height_t - $miss_border;
					$css                 .= 'width: ' . $attribute_width_t1 . 'px;';
					$css                 .= 'height:' . $attribute_height_t1 . 'px;';
					$css                 .= '}';
				}
				//selected styling
				$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected{';
				if ( $selected_border_width ) {
					if ( $selected_box_shadow_color ) {
						$css .= 'box-shadow:  0 0 0 ' . $selected_border_width . 'px ' . $selected_border_color . ' inset, 0px 4px 2px -2px ' . $selected_box_shadow_color . ';';
					} else {
						$css .= 'box-shadow:  0 0 0 ' . $selected_border_width . 'px ' . $selected_border_color . ' inset;';
					}
				} elseif ( $selected_box_shadow_color ) {
					$css .= 'box-shadow:  0px 4px 2px -2px ' . $selected_box_shadow_color . ';';
				}
				$css .= '}';
				$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-variation-wrap-select-wrap .vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected{';
				if ( $selected_border_width ) {
					if ( $selected_box_shadow_color ) {
						$css .= 'box-shadow:  0 0 0 ' . $selected_border_width . 'px ' . $selected_border_color . 'inset, 0px 4px 2px -2px ' . $selected_box_shadow_color . ';';
					} else {
						$css .= 'box-shadow:  0 0 0 ' . $selected_border_width . 'px ' . $selected_border_color . 'inset;';
					}
				} elseif ( $selected_box_shadow_color ) {
					$css .= 'box-shadow:  0px 4px 2px -2px ' . $selected_box_shadow_color . ';';
				}
				$css .= '}';

				if ( $selected_scale && $selected_scale !== '1' ) {
					$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected{';
					$css .= 'transform: perspective(1px)  scale(' . $selected_scale . ') translateZ(0);';
					$css .= 'backface-visibility: hidden;';
					$css .= 'transform-style: preserve-3d;';
					$css .= '-webkit-font-smoothing: antialiased !important;';
					$css .= '-moz-osx-font-smoothing: grayscale !important;';
					$css .= '}';
					if ( intval( $selected_scale ) > 0 ) {
						switch ( $tooltip_position ) {
							case 'left':
							case 'right':
								$translate = 'translate(0, -50%)';
								break;
							case 'bottom':
							default:
								$translate = 'translate(-50%, 0)';
						}
						$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected .vi-wpvs-option-tooltip{transform: ' . $translate . ' perspective(1px)  scale(calc(1/' . $selected_scale . ')) translateZ(0);}';
					}
				}
				$css .= $this->add_inline_style(
					'.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected',
					$i,
					array(
						'attribute_selected_color',
						'attribute_selected_bg_color',
						'attribute_selected_border_radius'
					),
					array( 'color', 'background', 'border-radius' ),
					array( '', '', 'px' )
				);
				$css .= $this->add_inline_style(
					'.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected .vi-wpvs-option',
					$i,
					array( 'attribute_selected_color' ),
					array( 'color' ),
					array( '' )
				);

				//hover styling
				$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-hover{';
				if ( $hover_border_width ) {
					if ( $hover_box_shadow_color ) {
						$css .= 'box-shadow:  0 0 0 ' . $hover_border_width . 'px ' . $hover_border_color . ' inset , 0px 4px 2px -2px ' . $hover_box_shadow_color . ';';
					} else {
						$css .= 'box-shadow:  0 0 0 ' . $hover_border_width . 'px ' . $hover_border_color . ' inset;';
					}
				} elseif ( $hover_box_shadow_color ) {
					$css .= 'box-shadow:  0px 4px 2px -2px ' . $hover_box_shadow_color . ';';
				}
				$css .= '}';
				$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-variation-wrap-select-wrap .vi-wpvs-option-wrap.vi-wpvs-option-wrap-hover{';
				if ( $hover_border_width ) {
					if ( $hover_box_shadow_color ) {
						$css .= 'box-shadow:  0 0 0 ' . $hover_border_width . 'px ' . $hover_border_color . ' inset, 0px 4px 2px -2px ' . $hover_box_shadow_color . ';';
					} else {
						$css .= 'box-shadow:  0 0 0 ' . $hover_border_width . 'px ' . $hover_border_color . ' inset;';
					}
				} elseif ( $hover_box_shadow_color ) {
					$css .= 'box-shadow:  0px 4px 2px -2px ' . $hover_box_shadow_color . ';';
				}
				$css .= '}';

				if ( $hover_scale && $hover_scale !== '1' ) {
					$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-hover{';
					$css .= 'transform: perspective(1px)  scale(' . $hover_scale . ') translateZ(0);';
					$css .= 'backface-visibility: hidden;';
					$css .= 'transform-style: preserve-3d;';
					$css .= '-webkit-font-smoothing: antialiased !important;';
					$css .= '-moz-osx-font-smoothing: grayscale !important;';
					$css .= '}';
					if ( intval( $hover_scale ) > 0 ) {
						switch ( $tooltip_position ) {
							case 'left':
							case 'right':
								$translate = 'translate(0, -50%)';
								break;
							case 'bottom':
							default:
								$translate = 'translate(-50%, 0)';
						}
						$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-hover .vi-wpvs-option-tooltip{transform: ' . $translate . ' perspective(1px)  scale(calc(1/' . $hover_scale . ')) translateZ(0);}';
					}
				}
				$css .= $this->add_inline_style(
					'.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-hover',
					$i,
					array( 'attribute_hover_color', 'attribute_hover_bg_color', 'attribute_hover_border_radius' ),
					array( 'color', 'background', 'border-radius' ),
					array( '', '', 'px' )
				);
				$css .= $this->add_inline_style(
					'.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-hover .vi-wpvs-option',
					$i,
					array( 'attribute_hover_color' ),
					array( 'color' ),
					array( '' )
				);

				//default styling
				$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-default{';
				if ( $default_border_width ) {
					if ( $default_box_shadow_color ) {
						$css .= 'box-shadow:  0 0 0 ' . $default_border_width . 'px ' . $default_border_color . ' inset, 0px 4px 2px -2px ' . $default_box_shadow_color . ';';
					} else {
						$css .= 'box-shadow:  0 0 0 ' . $default_border_width . 'px ' . $default_border_color . ' inset;';
					}
				} elseif ( $default_box_shadow_color ) {
					$css .= 'box-shadow:  0px 4px 2px -2px ' . $default_box_shadow_color . ';';
				}
				$css .= '}';
				$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-variation-wrap-select-wrap .vi-wpvs-option-wrap.vi-wpvs-option-wrap-default{';
				if ( $default_border_width ) {
					if ( $default_box_shadow_color ) {
						$css .= 'box-shadow:  0 0 0 ' . $default_border_width . 'px ' . $default_border_color . ', 0px 4px 2px -2px ' . $default_box_shadow_color . ';';
					} else {
						$css .= 'box-shadow:  0 0 0 ' . $default_border_width . 'px ' . $default_border_color . ' ;';
					}
				} elseif ( $default_box_shadow_color ) {
					$css .= 'box-shadow:  0px 4px 2px -2px ' . $default_box_shadow_color . ';';
				}
				$css .= '}';

				$css .= $this->add_inline_style(
					'.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-default',
					$i,
					array(
						'attribute_default_color',
						'attribute_default_bg_color',
						'attribute_default_border_radius'
					),
					array( 'color', 'background', 'border-radius' ),
					array( '', '', 'px' )
				);
				$css .= $this->add_inline_style(
					'.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-default .vi-wpvs-option',
					$i,
					array( 'attribute_default_color' ),
					array( 'color' ),
					array( '' )
				);

				// tooltip styling
				if ( $attribute_value_enable ) {
					$css .= $this->add_inline_style(
						".vi-wpvs-variation-wrap-wrap[data-swatch_type=\"image\"] .vi-wpvs-variation-wrap-show-attribute-value.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-{$id} .vi-wpvs-option-wrap:after,.vi-wpvs-variation-wrap-wrap[data-swatch_type=\"variation_img\"] .vi-wpvs-variation-wrap-show-attribute-value.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-{$id} .vi-wpvs-option-wrap:after,.vi-wpvs-variation-wrap-wrap[data-swatch_type=\"color\"] .vi-wpvs-variation-wrap-show-attribute-value.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-{$id} .vi-wpvs-option-wrap:after",
						$i,
						array(
							'attribute_value_color',
							'attribute_value_bg_color',
							'attribute_value_font_scale',
							'attribute_value_offset',
						),
						array( 'color', 'background-color', 'font-size', $attribute_value_position ),
						array( '', '', 'em', 'px' )
					);
				}
				if ( $tooltip_enable ) {
					switch ( $tooltip_type ) {
						case 'image':
							$css .= $this->add_inline_style(
								'.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap .vi-wpvs-option-tooltip',
								$i,
								array(
									'attribute_tooltip_width',
									'attribute_tooltip_height',
									'attribute_tooltip_fontsize',
									'attribute_tooltip_border_radius'
								),
								array( 'width', 'height', 'font-size', 'border-radius' ),
								array( 'px', 'px', 'px', 'px' )
							);
							break;
						default:
							$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap .vi-wpvs-option-tooltip{';
							$css .= 'min-width: 100px;';
							$css .= 'height: auto;';
							$css .= 'padding: 5px 8px;';
							$css .= '}';
							$css .= $this->add_inline_style(
								'.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap .vi-wpvs-option-tooltip',
								$i,
								array( 'attribute_tooltip_fontsize', 'attribute_tooltip_border_radius' ),
								array( 'font-size', 'border-radius' ),
								array( 'px', 'px' )
							);
							$css .= $this->add_inline_style(
								'.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap .vi-wpvs-option-tooltip img.vi-wpvs-option-tooltip-image',
								$i,
								array( 'attribute_tooltip_image_width' ),
								array( 'width' ),
								array( 'px' )
							);
							if ( $this->settings->get_current_setting( 'attribute_tooltip_image_width', $i ) ) {
								$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap .vi-wpvs-option-tooltip img.vi-wpvs-option-tooltip-image{max-width:unset;}';
							}
							$css .= $this->add_inline_style(
								'.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap .vi-wpvs-option-tooltip,.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap .vi-wpvs-option-tooltip span',
								$i,
								array( 'attribute_tooltip_color', 'attribute_tooltip_bg_color' ),
								array( 'color', 'background' ),
								array( ' !important', ' !important' )
							);
					}
					if ( $tooltip_bg_color ) {
						$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap .vi-wpvs-option-tooltip::after{';
						$css .= 'border-width: 5px;';
						$css .= 'border-style: solid;';
						switch ( $tooltip_position ) {
							case 'bottom':
								$css .= 'margin-left: -5px;';
								$css .= 'margin-top: -1px;';
								$css .= 'border-color:  transparent transparent ' . $tooltip_bg_color . ' transparent;';
								break;
							case 'left':
								$css .= 'margin-left: -1px;';
								$css .= 'margin-top: -5px;';
								$css .= 'border-color:  transparent transparent transparent ' . $tooltip_bg_color . ' ;';
								break;
							case 'right':
								$css .= 'margin-left: -1px;';
								$css .= 'margin-top: -5px;';
								$css .= 'border-color:  transparent ' . $tooltip_bg_color . ' transparent  transparent;';
								break;
							default:
								$css .= 'margin-left: -5px;';
								$css .= 'margin-top: -1px;';
								$css .= 'border-color: ' . $tooltip_bg_color . ' transparent transparent transparent;';
						}
						$css .= '}';
					}
					if ( $tooltip_border_color ) {
						$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap .vi-wpvs-option-tooltip{';
						$css .= 'border: 1px solid ' . $tooltip_border_color . ';';
						$css .= '}';
						$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap .vi-wpvs-option-tooltip::before{';
						$css .= 'border-width: 6px;';
						$css .= 'border-style: solid;';
						switch ( $tooltip_position ) {
							case 'bottom':
								$css .= 'margin-left: -6px;';
								$css .= 'border-color:  transparent transparent ' . $tooltip_border_color . ' transparent;';
								break;
							case 'left':
								$css .= 'margin-top: -6px;';
								$css .= 'border-color:  transparent transparent transparent ' . $tooltip_border_color . ' ;';
								break;
							case 'right':
								$css .= 'margin-top: -6px;';
								$css .= 'border-color:  transparent ' . $tooltip_border_color . ' transparent  transparent;';
								break;
							default:
								$css .= 'margin-left: -6px;';
								$css .= 'border-color: ' . $tooltip_border_color . ' transparent transparent transparent;';
						}
						$css .= '}';
					}
				} else {
					$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap .vi-wpvs-option-tooltip{';
					$css .= 'display: none;';
					$css .= '}';
				}

				// reduce the size
				if ( $reduce_loop ) {
					$css .= $this->add_inline_style_reduce(
						'.vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-variation-button-select,.vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap',
						$i,
						array( 'attribute_height', 'attribute_width', 'attribute_fontsize' ),
						array( 'height', 'width', 'font-size' ),
						array( 'px', 'px', 'px' ),
						$reduce_loop
					);
					$css .= $this->add_inline_style_reduce(
						'.vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap-slider.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap',
						$i,
						array( 'attribute_height', 'attribute_width' ),
						array( 'height', 'width' ),
						array( 'px !important', 'px !important' ),
						$reduce_loop
					);
					$css .= '.vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-image.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap ,';
					$css .= '.vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-variation_img.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap ,';
					$css .= '.vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-color.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap{';
					if ( ! $attribute_width ) {
						$attribute_width_t1 = $attribute_height ? ( $attribute_height * $reduce_loop / 100 ) : 48 * $reduce_loop / 100;
						$css                .= 'width: ' . $attribute_width_t1 . 'px !important;';
					}
					if ( ! $attribute_height ) {
						$attribute_height_t1 = $attribute_width ? ( $attribute_width * $reduce_loop / 100 ) : 48 * $reduce_loop / 100;
						$css                 .= 'height:' . $attribute_height_t1 . 'px !important;';
					}
					$css .= '}';
					if ( $default_border_width ) {
						$css                 .= '.vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-image.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-default .vi-wpvs-option,';
						$css                 .= '.vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-variation_img.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-default .vi-wpvs-option,';
						$css                 .= '.vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-color.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-default .vi-wpvs-option{';
						$miss_border         = $default_border_width * 2;
						$attribute_width_t1  = $attribute_width_t * $reduce_loop / 100 - $miss_border;
						$attribute_height_t1 = $attribute_height_t * $reduce_loop / 100 - $miss_border;
						$css                 .= 'width: ' . $attribute_width_t1 . 'px;';
						$css                 .= 'height:' . $attribute_height_t1 . 'px;';
						$css                 .= '}';
					}
					if ( $hover_border_width ) {
						$css                 .= '.vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-image.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-hover .vi-wpvs-option,';
						$css                 .= '.vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-variation_img.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-hover .vi-wpvs-option,';
						$css                 .= '.vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-color.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-hover .vi-wpvs-option{';
						$miss_border         = $hover_border_width * 2;
						$attribute_width_t1  = $attribute_width_t * $reduce_loop / 100 - $miss_border;
						$attribute_height_t1 = $attribute_height_t * $reduce_loop / 100 - $miss_border;
						$css                 .= 'width: ' . $attribute_width_t1 . 'px;';
						$css                 .= 'height:' . $attribute_height_t1 . 'px;';
						$css                 .= '}';
					}
					if ( $selected_border_width ) {
						$css                 .= '.vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-image.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected .vi-wpvs-option,';
						$css                 .= '.vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-variation_img.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected .vi-wpvs-option,';
						$css                 .= '.vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-color.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected .vi-wpvs-option{';
						$miss_border         = $selected_border_width * 2;
						$attribute_width_t1  = $attribute_width_t * $reduce_loop / 100 - $miss_border;
						$attribute_height_t1 = $attribute_height_t * $reduce_loop / 100 - $miss_border;
						$css                 .= 'width: ' . $attribute_width_t1 . 'px;';
						$css                 .= 'height:' . $attribute_height_t1 . 'px;';
						$css                 .= '}';
					}
					$css .= $this->add_inline_style_reduce(
						'.vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected',
						$i,
						array( 'attribute_selected_border_radius' ),
						array( 'border-radius' ),
						array( 'px' ),
						$reduce_loop
					);
					$css .= $this->add_inline_style_reduce(
						'.vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-hover',
						$i,
						array( 'attribute_hover_border_radius' ),
						array( 'border-radius' ),
						array( 'px' ),
						$reduce_loop
					);
					$css .= $this->add_inline_style_reduce(
						'.vi-wpvs-variation-wrap-loop.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-default',
						$i,
						array( 'attribute_default_border_radius' ),
						array( 'border-radius' ),
						array( 'px' ),
						$reduce_loop
					);
				}
				if ( $reduce_mobile ) {
					$css .= '@media screen and (max-width:600px){';
					$css .= $this->add_inline_style_reduce(
						'.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-variation-button-select,.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap',
						$i,
						array( 'attribute_width', 'attribute_height', 'attribute_fontsize' ),
						array( 'width', 'height', 'font-size' ),
						array( 'px', 'px', 'px' ),
						$reduce_mobile
					);

					$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-image.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap ,';
					$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-variation_img.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap ,';
					$css .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-color.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap{';
					if ( ! $attribute_width ) {
						$attribute_width_t = $attribute_height ? ( $attribute_height * $reduce_mobile / 100 ) : 48 * $reduce_mobile / 100;
						$css               .= 'width: ' . $attribute_width_t . 'px;';
					}
					if ( ! $attribute_height ) {
						$attribute_height_t = $attribute_width ? ( $attribute_width * $reduce_mobile / 100 ) : 48 * $reduce_mobile / 100;
						$css                .= 'height:' . $attribute_height_t . 'px;';
					}
					$css .= '}';
					if ( $default_border_width ) {
						$css                 .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-image.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-default .vi-wpvs-option,';
						$css                 .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-variation_img.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-default .vi-wpvs-option,';
						$css                 .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-color.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-default .vi-wpvs-option{';
						$miss_border         = $default_border_width * 2;
						$attribute_width_t1  = $attribute_width_t * $reduce_mobile / 100 - $miss_border;
						$attribute_height_t1 = $attribute_height_t * $reduce_mobile / 100 - $miss_border;
						$css                 .= 'width: ' . $attribute_width_t1 . 'px;';
						$css                 .= 'height:' . $attribute_height_t1 . 'px;';
						$css                 .= '}';
					}
					if ( $hover_border_width ) {
						$css                 .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-image.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-hover .vi-wpvs-option,';
						$css                 .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-variation_img.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-hover .vi-wpvs-option,';
						$css                 .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-color.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-hover .vi-wpvs-option{';
						$miss_border         = $hover_border_width * 2;
						$attribute_width_t1  = $attribute_width_t * $reduce_mobile / 100 - $miss_border;
						$attribute_height_t1 = $attribute_height_t * $reduce_mobile / 100 - $miss_border;
						$css                 .= 'width: ' . $attribute_width_t1 . 'px;';
						$css                 .= 'height:' . $attribute_height_t1 . 'px;';
						$css                 .= '}';
					}
					if ( $selected_border_width ) {
						$css                 .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-image.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected .vi-wpvs-option,';
						$css                 .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-variation_img.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected .vi-wpvs-option,';
						$css                 .= '.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-color.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected .vi-wpvs-option{';
						$miss_border         = $selected_border_width * 2;
						$attribute_width_t1  = $attribute_width_t * $reduce_mobile / 100 - $miss_border;
						$attribute_height_t1 = $attribute_height_t * $reduce_mobile / 100 - $miss_border;
						$css                 .= 'width: ' . $attribute_width_t1 . 'px;';
						$css                 .= 'height:' . $attribute_height_t1 . 'px;';
						$css                 .= '}';
					}
					$css .= $this->add_inline_style_reduce(
						'.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected',
						$i,
						array( 'attribute_selected_border_radius' ),
						array( 'border-radius' ),
						array( 'px' ),
						$reduce_mobile
					);
					$css .= $this->add_inline_style_reduce(
						'.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-hover',
						$i,
						array( 'attribute_hover_border_radius' ),
						array( 'border-radius' ),
						array( 'px' ),
						$reduce_mobile
					);
					$css .= $this->add_inline_style_reduce(
						'.vi-wpvs-variation-wrap.vi-wpvs-variation-wrap-' . $id . ' .vi-wpvs-option-wrap.vi-wpvs-option-wrap-default',
						$i,
						array( 'attribute_default_border_radius' ),
						array( 'border-radius' ),
						array( 'px' ),
						$reduce_mobile
					);
					$css .= '}';
				}
			}
			$css .= '.vi_wpvs_variation_form:not(.vi_wpvs_loop_variation_form) .vi-wpvs-variation-wrap-wrap,';
			$css .= '.vi_wpvs_variation_form:not(.vi_wpvs_loop_variation_form) .vi-wpvs-variation-wrap-wrap .vi-wpvs-variation-wrap{';
			switch ( $this->settings->get_params( 'single_align' ) ) {
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
			$css .= $this->settings->get_params( 'custom_css' );
			wp_add_inline_style( 'vi-wpvs-frontend-style', wp_unslash( $css ) );
		}
	}

	private function add_inline_style( $element, $i, $name, $style, $suffix = '' ) {
		$return = '';
		if ( is_array( $name ) && count( $name ) ) {
			foreach ( $name as $key => $value ) {
				$get_value  = $this->settings->get_current_setting( $name[ $key ], $i );
				$get_suffix = isset( $suffix[ $key ] ) ? $suffix[ $key ] : '';
				if ( in_array( $style[ $key ], array( 'top', 'bottom', 'left', 'right' ), true ) ) {
					if ( ! in_array( $get_value, array( '', false ), true ) ) {
						$get_value = intval( $get_value );
						$return    .= $style[ $key ] . ':' . $get_value . $get_suffix . ';';
					}
				} else {
					if ( $get_value ) {
						$return .= $style[ $key ] . ':' . $get_value . $get_suffix . ';';
					}
				}
			}
		}
		if ( $return ) {
			$return = "{$element}{{$return}}";
		}

		return $return;
	}

	private function add_inline_style_reduce( $element, $i, $name, $style, $suffix = '', $reduce = 0, $default = 0 ) {
		$return = '';
		if ( is_array( $name ) && count( $name ) ) {
			foreach ( $name as $key => $value ) {
				$get_value = $this->settings->get_current_setting( $name[ $key ], $i );
				$skip      = false;
				if ( ! $get_value ) {
					$skip = true;
				} elseif ( $reduce > 0 ) {
					if ( $default > 0 ) {
						$get_value = $get_value * $default / 100;
					}
					if ( in_array( $name[ $key ], array(
						'attribute_default_border_radius',
						'attribute_hover_border_radius',
						'attribute_selected_border_color'
					) ) ) {
						$min_size = min( $this->settings->get_current_setting( 'attribute_width', $i ), $this->settings->get_current_setting( 'attribute_height', $i ) );
						if ( $get_value >= $min_size / 2 ) {
							$skip = true;
						}
					}

					$get_value = $get_value * $reduce / 100;
					if ( $suffix[ $key ] === 'px' && in_array( $name[ $key ], array(
							'attribute_width',
							'attribute_height',
							'attribute_fontsize'
						) ) ) {
						$get_value = floor( floatval( $get_value ) );
					}
				}
				if ( ! $skip ) {
					$return .= $style[ $key ] . ':' . $get_value . $suffix[ $key ] . ';';
				}
			}
		}
		if ( $return ) {
			$return = "{$element}{{$return}}";
		}

		return $return;
	}

	public static function wpml_get_source_attribute_term( $trid ) {
		global $wpdb;
		$icl_translations = "{$wpdb->prefix}icl_translations";
		$terms            = "{$wpdb->prefix}terms";
		$query            = "SELECT * FROM {$icl_translations} join {$terms} on {$terms}.term_id={$icl_translations}.element_id where {$icl_translations}.source_language_code is null and {$icl_translations}.trid = %s";

		return $wpdb->get_row( $wpdb->prepare( $query, $trid ) );
	}

	public static function get_attachment_props( $id ) {
		if ( ! isset( self::$attachment_props[ $id ] ) ) {
			self::$attachment_props[ $id ] = wc_get_product_attachment_props( $id );
		}

		return self::$attachment_props[ $id ];
	}
}