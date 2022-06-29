<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Frontend_Woo_Widget {
	protected $settings;

	public function __construct() {
		$this->settings = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DATA::get_instance();
		if ( $this->settings->get_params( 'woo_widget_enable' ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'wvps_wp_enqueue_scripts' ), 999 );
			add_filter( 'viwpvs_woocommerce_layered_nav_term_html', array(
				$this,
				'viwpvs_woocommerce_layered_nav_term_html'
			), PHP_INT_MAX, 5 );
//			add_filter( 'widget_update_callback', array( $this, 'viwpvs_widget_update_callback' ), PHP_INT_MAX, 4 );
//			add_action( 'in_widget_form', array( $this, 'viwpvs_in_widget_form' ), PHP_INT_MAX, 3 );
			add_action( 'widgets_init', array( $this, 'viwpvs_override_woo_widgets' ), 15 );
		}
	}

	public function viwpvs_woocommerce_layered_nav_term_html( $term_html, $term, $link, $count, $instance ) {
		$term_name = $term->name ?? '';
		$taxonomy  = $term->taxonomy ?? '';
		if ( empty( $term_name ) || empty( $taxonomy ) || empty( $term->term_id ) ) {
			return $term_html;
		}
		$type = $instance['viwpvs_display_type'] ?? '';
		if ( $type === 'theme_default' ) {
			return $term_html;
		}
		if ( ! wp_style_is( 'dashicons' ) ) {
			wp_enqueue_style( 'dashicons' );
		}
		if ( ! wp_style_is( 'vi-wpvs-frontend-widget' ) ) {
			wp_enqueue_style( 'vi-wpvs-frontend-widget' );
		}
		if ( ! wp_script_is( 'vi-wpvs-frontend-widget' ) ) {
			wp_enqueue_script( 'vi-wpvs-frontend-widget' );
		}
		$vi_wpvs_terms_settings     = get_term_meta( $term->term_id, 'vi_wpvs_terms_params', true );
		$term_tooltip               = apply_filters( 'viwpvs_woo_widget_tooltip', $term_name, $term, $count );
		$profile_default            = $this->settings->get_params( 'attribute_profile_default' );
		$profile_ids                = $this->settings->get_params( 'ids' );
		$type                       = $type ?: 'button';
		$profile                    = $instance['viwpvs_profile'] ?? '';
		$profile_default_index      = array_search( $profile_default, $profile_ids ) ?: 0;
		$profile_index              = array_search( $profile, $profile_ids ) ?: $profile_default_index;
		$profile                    = $profile_ids[ $profile_index ];
		$attribute_tooltip_position = $this->settings->get_current_setting( 'attribute_tooltip_position', $profile_index );
		$div_class                  = array(
			'vi-wpvs-variation-wrap vi-wpvs-variation-wrap-wc-widget vi-wpvs-variation-wrap-taxonomy',
			'vi-wpvs-variation-wrap-' . $profile,
			'vi-wpvs-variation-wrap-' . $type,
		);
		$div_class                  = implode( ' ', $div_class );
		$term_class                 = 'vi-wpvs-option-wrap vi-wpvs-option-wrap-default';
		if ( ! empty( $instance['viwpvs_design_attr'] ) ) {
			$term_name_enable = $instance['viwpvs_term_default']['name_enable'] ?? '';
		} else {
			$term_name_enable = $this->settings->get_params( 'woo_widget_term_default' )['name_enable'] ?? '';
		}
		if ( ! empty( $instance['viwpvs_design_pd_count'] ) ) {
			$pd_count = $instance['viwpvs_pd_count_enable'] ?? '';
		} else {
			$pd_count = $this->settings->get_params( 'woo_widget_pd_count_enable' );
		}
		ob_start();
		?>
        <a rel="nofollow" href="<?php echo esc_attr( esc_url( $link ) ); ?>">
            <div class="<?php echo esc_attr( $div_class ); ?>">
				<?php
				switch ( $type ) {
					case 'color':
						$check_name_enable = true;
						$term_colors = $vi_wpvs_terms_settings['color'] ?? array();
						$term_color_separator = $vi_wpvs_terms_settings['color_separator'] ?? '1';
						if ( ! $term_colors ) {
							/*WPML - use colors of original attribute if not set for the translated one*/
							$source_term = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Frontend_Frontend::wpml_get_original_global_term( $term );
							if ( $source_term ) {
								$vi_wpvs_terms_settings = get_term_meta( $source_term->term_id, 'vi_wpvs_terms_params', true );
								$term_colors            = $vi_wpvs_terms_settings['color'] ?? array();
								$term_color_separator   = $vi_wpvs_terms_settings['color_separator'] ?? '1';
							}
						}
						$term_color = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Frontend_Frontend::get_attribute_option_color( $term->slug, $term_colors, $term_color_separator );
						?>
                        <div class="<?php echo esc_attr( $term_class ); ?>"
                             data-attribute_value="<?php echo esc_attr( $term->slug ); ?>">
                            <span class="vi-wpvs-option vi-wpvs-option-color"
                                  data-option_color="<?php echo esc_attr( $term_color ); ?>"></span>
                            <div class="vi-wpvs-option-tooltip vi-wpvs-option-tooltip-<?php echo esc_attr( $attribute_tooltip_position ); ?>"
                                 data-attribute_label="<?php echo esc_attr( $term_name ); ?>">
                                    <span>
                                        <?php echo wp_kses_post( $term_tooltip ); ?>
                                    </span>
                            </div>
                        </div>
						<?php
						break;
					case 'image':
						$check_name_enable = true;
						$terms_img_id = $vi_wpvs_terms_settings['img_id'] ?? '';
						if ( ! $terms_img_id ) {
							/*WPML - use image of original attribute if not set for the translated one*/
							$source_term = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Frontend_Frontend::wpml_get_original_global_term( $term );
							if ( $source_term ) {
								$vi_wpvs_terms_settings = get_term_meta( $source_term->term_id, 'vi_wpvs_terms_params', true );
								$terms_img_id           = $vi_wpvs_terms_settings['img_id'] ?? '';
							}
						}

						$img_url = $terms_img_id ? wp_get_attachment_image_url( $terms_img_id, 'woocommerce_gallery_thumbnail' ) : wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' );
						?>
                        <div class="<?php echo esc_attr( $term_class ); ?>"
                             data-attribute_value="<?php echo esc_attr( $term->slug ); ?>">
                            <img src="<?php echo esc_url( $img_url ); ?>"
                                 alt="<?php echo esc_attr( $term->slug ); ?>"
                                 class="vi-wpvs-option vi-wpvs-option-image">
                            <div class="vi-wpvs-option-tooltip vi-wpvs-option-tooltip-<?php echo esc_attr( $attribute_tooltip_position ); ?>"
                                 data-attribute_label="<?php echo esc_attr( $term_name ); ?>">
                                    <span>
                                        <?php echo wp_kses_post( $term_tooltip ); ?>
                                    </span>
                            </div>
                        </div>
						<?php
						break;
					default:
						$check_name_enable = false;
						?>
                        <div class="<?php echo esc_attr( $term_class ); ?>"
                             data-attribute_value="<?php echo esc_attr( $term->slug ); ?>">
                                <span class="vi-wpvs-option vi-wpvs-option-button">
						            <?php echo esc_html( $term_name ); ?>
					            </span>
                            <div class="vi-wpvs-option-tooltip vi-wpvs-option-tooltip-<?php echo esc_attr( $attribute_tooltip_position ); ?>"
                                 data-attribute_label="<?php echo esc_attr( $term_name ); ?>">
                                    <span>
                                        <?php echo wp_kses_post( $term_tooltip ); ?>
                                    </span>
                            </div>
                        </div>
					<?php
				}
				if ( $term_name_enable && $check_name_enable ) {
					echo sprintf( '<span class="vi-wpvs-variation-wrap-wc-widget-title">%s</span>', $term_name );
				}
				?>
            </div>
        </a>
		<?php
		if ( $pd_count ) {
			echo sprintf( '<span class="count vi-wpvs-widget-count">%s</span>', esc_attr( $count ) );
		}
		$term_html = ob_get_clean();

		return apply_filters( 'vi_wpvs_layered_nav_term_html', $term_html, $term, $link, $count, $instance );
	}

	public function viwpvs_in_widget_form( $widget, $return, $instance ) {
		if ( get_class( $widget ) !== 'WC_Widget_Layered_Nav' ) {
			return;
		}
		echo sprintf( '<div class="viwpvs-woo-widget-fields-wrap" style="background: #fafafa; padding: 10px 0;"><strong>%s</strong></div>',
			__( 'Please change \'Display type\' to \'List\' to use swatches style', 'woocommerce-product-variations-swatches' ) );
	}

	public function viwpvs_widget_update_callback() {

	}

	public function viwpvs_override_woo_widgets() {
		if ( class_exists( 'WC_Widget_Layered_Nav' ) ) {
			unregister_widget( 'WC_Widget_Layered_Nav' );
			include_once( VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_INCLUDES . 'class-wc-widget-layered-nav.php' );
			register_widget( 'VIWPVS_WC_Widget_Layered_Nav' );
		}
	}

	public function wvps_wp_enqueue_scripts() {
		if ( ! is_shop() && ! is_product_taxonomy() ) {
			return;
		}
		if ( WP_DEBUG ) {
			wp_register_style( 'vi-wpvs-frontend-widget',
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'frontend-widget.css',
				array(),
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_register_script( 'vi-wpvs-frontend-widget',
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'frontend-widget.js',
				array( 'jquery' ),
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
		} else {
			wp_register_style( 'vi-wpvs-frontend-widget',
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'frontend-widget.min.css',
				array(),
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_register_script( 'vi-wpvs-frontend-widget',
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'frontend-widget.min.js',
				array( 'jquery' ),
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
		}
		$display_style                = $this->settings->get_params( 'woo_widget_display_style' );
		$woo_widget_pd_count_default  = $this->settings->get_params( 'woo_widget_pd_count_default' );
		$woo_widget_pd_count_hover    = $this->settings->get_params( 'woo_widget_pd_count_hover' );
		$woo_widget_pd_count_selected = $this->settings->get_params( 'woo_widget_pd_count_selected' );
		$woo_widget_term_default      = $this->settings->get_params( 'woo_widget_term_default' );
		$woo_widget_term_hover        = $this->settings->get_params( 'woo_widget_term_hover' );
		$woo_widget_term_selected     = $this->settings->get_params( 'woo_widget_term_selected' );
		$box_shadow_color             = false;
		$css                          = '';
		$css                          .= '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term .vi-wpvs-widget-count{';
		if ( ! empty( $woo_widget_pd_count_default['border_width'] ) ) {
			$css .= 'border-width: ' . $woo_widget_pd_count_default['border_width'] . ' ;';
			$css .= 'border-style: solid ;';
		} else {
			$css .= 'border: unset;';
		}
		$css .= '}';
		$css .= $this->add_inline_style(
			array( '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term .vi-wpvs-widget-count' ),
			array( 'color', 'bg_color', 'padding', 'border_radius', 'border_color' ),
			$woo_widget_pd_count_default,
			array( 'color', 'background', 'padding', 'border-radius', 'border-color' ),
			array( '', '', '', 'px', '' )
		);
		$css .= $this->add_inline_style(
			array( '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term.vi-wpvs-wc-layered-nav-term-hover .vi-wpvs-widget-count' ),
			array( 'color', 'bg_color', 'border_radius', 'border_color' ),
			$woo_widget_pd_count_hover,
			array( 'color', 'background', 'border-radius', 'border-color' ),
			array( '', '', 'px', '' )
		);
		$css .= $this->add_inline_style(
			array( '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term.chosen .vi-wpvs-widget-count' ),
			array( 'color', 'bg_color', 'border_radius', 'border_color' ),
			$woo_widget_pd_count_selected,
			array( 'color', 'background', 'border-radius', 'border-color' ),
			array( '', '', 'px', '' )
		);
		if ( ! empty( $woo_widget_term_default['box_shadow_color'] ) ) {
			$box_shadow_color = true;
			$css              .= '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term{';
			$css              .= 'box-shadow: 0 1px 0 0 ' . $woo_widget_term_default['box_shadow_color'] . ' ;';
			$css              .= '}';
		}
		$css .= $this->add_inline_style(
			array(
				'.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term',
				'.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term .vi-wpvs-variation-wrap-wc-widget-title'
			),
			array( 'color', 'bg_color', 'padding' ),
			$woo_widget_term_default,
			array( 'color', 'background', 'padding' ),
			array( '', '', '' )
		);
		if ( ! empty( $woo_widget_term_hover['box_shadow_color'] ) ) {
			$box_shadow_color = true;
			$css              .= '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term.vi-wpvs-wc-layered-nav-term-hover{';
			$css              .= 'box-shadow: 0 1px 0 0 ' . $woo_widget_term_hover['box_shadow_color'] . ' ;';
			$css              .= '}';
		}
		$css .= $this->add_inline_style(
			array(
				'.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term.vi-wpvs-wc-layered-nav-term-hover',
				'.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term.vi-wpvs-wc-layered-nav-term-hover .vi-wpvs-variation-wrap-wc-widget-title'
			),
			array( 'color', 'bg_color' ),
			$woo_widget_term_hover,
			array( 'color', 'background' ),
			array( '', '' )
		);
		if ( ! empty( $woo_widget_term_selected['box_shadow_color'] ) ) {
			$box_shadow_color = true;
			$css              .= '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term.chosen{';
			$css              .= 'box-shadow: 0 1px 0 0 ' . $woo_widget_term_selected['box_shadow_color'] . ' ;';
			$css              .= '}';
		}
		$css .= $this->add_inline_style(
			array(
				'.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term.chosen',
				'.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term.chosen .vi-wpvs-variation-wrap-wc-widget-title'
			),
			array( 'color', 'bg_color' ),
			$woo_widget_term_selected,
			array( 'color', 'background' ),
			array( '', '' )
		);
		$css .= '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term{';
		if ( $box_shadow_color ) {
			$css .= ' margin: 0 0 5px 0 ;';
		} else {
			$css .= ' margin: 0 ;';
		}
		$css .= '}';
		wp_add_inline_style( 'vi-wpvs-frontend-widget', $css );
	}

	private function add_inline_style( $element, $name, $settings, $style, $suffix = '' ) {
		if ( ! is_array( $element ) || empty( $element ) ) {
			return '';
		}
		$element = implode( ',', $element );
		$return  = $element . '{';
		if ( is_array( $name ) && count( $name ) ) {
			foreach ( $name as $key => $value ) {
				$get_value  = $settings[ $value ] ?? '';
				$get_suffix = $suffix[ $key ] ?? '';
				if ( $get_value ) {
					$return .= $style[ $key ] . ':' . $get_value . $get_suffix . ';';
				}
			}
		}
		$return .= '}';

		return $return;
	}
}