<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Admin_Custom_Attribute {
	protected $settings;

	public function __construct() {
		$this->settings = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DATA::get_instance();
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 99 );
		add_action( 'init', array( $this, 'init' ), 99 );
		add_action( 'woocommerce_product_options_attributes', array( $this, 'custom_attribute_product' ) );
		add_filter( 'woocommerce_admin_meta_boxes_prepare_attribute', array(
			__CLASS__,
			'prepare_attribute'
		), 99999, 3 );
		add_action( 'wp_ajax_woocommerce_add_attribute', array( __CLASS__, 'add_attribute' ) );
		add_action( 'wp_ajax_woocommerce_save_attributes', array( __CLASS__, 'save_attributes' ) );
		add_action( 'wp_ajax_vi_wvps_get_html_global_attrs_item', array(
			__CLASS__,
			'vi_wvps_get_html_global_attrs_item'
		) );
		add_action( 'wp_ajax_vi_wvps_get_html_global_attrs_items', array(
			__CLASS__,
			'vi_wvps_get_html_global_attrs_items'
		) );
	}

	public static function vi_wvps_get_html_global_attrs_items() {
		$result = array(
			'status'  => '',
			'content' => '',
		);
		if ( ! current_user_can( 'edit_products' ) || ! isset( $_POST['attribute_name'], $_POST['i'] ) ) {
			$result['status']  = 'error';
			$result['content'] = 'can\'t edit';
		}
		$i                 = wc_clean( $_POST['i'] );
		$attribute_name    = wc_clean( $_POST['attribute_name'] );
		$vi_attribute_type = isset( $_POST['vi_attribute_type'] ) ? wc_clean( $_POST['vi_attribute_type'] ) : '';
		$available         = isset( $_POST['available'] ) ? wc_clean( $_POST['available'] ) : array();
		$args              = array(
			'orderby'    => 'name',
			'hide_empty' => 0,
		);
		$all_terms         = get_terms( $attribute_name, apply_filters( 'woocommerce_product_attribute_terms', $args ) );
		if ( $all_terms && count( $all_terms ) ) {
			ob_start();
			foreach ( $all_terms as $k => $term ) {
				if ( in_array( $term->term_id, $available ) ) {
					continue;
				}
				$vi_wpvs_terms_settings = ! empty( get_term_meta( $term->term_id, 'vi_wpvs_terms_params', true ) ) ? get_term_meta( $term->term_id, 'vi_wpvs_terms_params', true ) : array();
				$terms_color_separator  = $vi_wpvs_terms_settings['color_separator'] ?? '1';
				$terms_colors           = $vi_wpvs_terms_settings['color'] ?? '';
				$terms_img_id           = $vi_wpvs_terms_settings['img_id'] ?? '';
				wc_get_template( 'html-global-attribute-item.php',
					array(
						'selected'              => 1,
						'i'                     => $i,
						'term'                  => $term,
						'terms_img_id'          => $terms_img_id,
						'terms_colors'          => $terms_colors,
						'terms_color_separator' => $terms_color_separator,
						'vi_attribute_type'     => $vi_attribute_type,
					),
					'',
					VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_TEMPLATES );
			}
			$html              = ob_get_clean();
			$result['status']  = 'success';
			$result['content'] = $html;
		}
		wp_send_json( $result );
	}

	public static function vi_wvps_get_html_global_attrs_item() {
		$result = array(
			'status'  => '',
			'content' => '',
		);
		if ( ! current_user_can( 'edit_products' ) || ! isset( $_POST['product_id'], $_POST['attribute_name'], $_POST['i'], $_POST['term_id'] ) ) {
			$result['status']  = 'error';
			$result['content'] = 'can\'t edit';
		}
		$term_id = wc_clean( $_POST['term_id'] );
		$term    = get_term( $term_id );
		if ( ! $term ) {
			$result['status']  = 'error';
			$result['content'] = 'not term';
		} else {
			$product_id     = wc_clean( $_POST['product_id'] );
			$i              = wc_clean( $_POST['i'] );
			$attribute_name = wc_clean( $_POST['attribute_name'] );

			$vi_attribute_settings        = get_post_meta( $product_id, '_vi_woo_product_variation_swatches_product_attribute', true );
			$vi_attribute_settings        = $vi_attribute_settings ? json_decode( $vi_attribute_settings, true ) : array();
			$vi_attribute_color_separator = $vi_attribute_settings['attribute_color_separator'][ $attribute_name ] ?? array();
			$vi_attribute_colors          = $vi_attribute_settings['attribute_colors'][ $attribute_name ] ?? array();
			$vi_attribute_img_ids         = $vi_attribute_settings['attribute_img_ids'][ $attribute_name ] ?? array();
			$vi_attribute_type            = $vi_attribute_settings['attribute_type'][ $attribute_name ] ?? null;
			$vi_wpvs_terms_settings       = ! empty( get_term_meta( $term_id, 'vi_wpvs_terms_params', true ) ) ? get_term_meta( $term_id, 'vi_wpvs_terms_params', true ) : array();
			$terms_color_separator        = $vi_attribute_color_separator[ $term_id ] ?? $vi_wpvs_terms_settings['color_separator'] ?? '1';
			$terms_colors                 = $vi_attribute_colors[ $term_id ] ?? $vi_wpvs_terms_settings['color'] ?? '';
			$terms_img_id                 = $vi_attribute_img_ids[ $term_id ] ?? $vi_wpvs_terms_settings['img_id'] ?? '';
			ob_start();
			wc_get_template( 'html-global-attribute-item.php',
				array(
					'selected'              => 1,
					'i'                     => $i,
					'term'                  => $term,
					'terms_img_id'          => $terms_img_id,
					'terms_colors'          => $terms_colors,
					'terms_color_separator' => $terms_color_separator,
					'vi_attribute_type'     => $vi_attribute_type,
				),
				'',
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_TEMPLATES );
			$html              = ob_get_clean();
			$result['status']  = 'success';
			$result['content'] = $html;
		}
		wp_send_json( $result );
	}

	public function init() {
		remove_action( 'wp_ajax_woocommerce_add_attribute', array( 'WC_AJAX', 'add_attribute' ) );
		remove_action( 'wp_ajax_woocommerce_save_attributes', array( 'WC_AJAX', 'save_attributes' ) );
	}

	public static function add_attribute() {
		ob_start();

		check_ajax_referer( 'add-attribute', 'security' );

		if ( ! current_user_can( 'edit_products' ) || ! isset( $_POST['taxonomy'], $_POST['i'] ) ) {
			wp_die( - 1 );
		}

		$settings                 = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DATA::get_instance();
		$vi_wpvs_ids              = $settings->get_params( 'ids' );
		$vi_wpvs_name             = $settings->get_params( 'names' );
		$vi_default_colors        = $settings->get_default_color();
		$product_list_add_to_cart = $settings->get_params( 'product_list_add_to_cart' );
		$i                        = absint( $_POST['i'] );
		$metabox_class            = array();
		$attribute                = new WC_Product_Attribute();

		$attribute->set_id( wc_attribute_taxonomy_id_by_name( sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) ) ) );
		$attribute->set_name( sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) ) );
		$attribute->set_visible( apply_filters( 'woocommerce_attribute_default_visibility', 1 ) );
		$attribute->set_variation( apply_filters( 'woocommerce_attribute_default_is_variation', 0 ) );
		$attribute_types = wc_get_attribute_types();

		if ( $attribute->is_taxonomy() ) {
			$metabox_class[] = 'taxonomy';
			$metabox_class[] = $attribute->get_name();
		}

		include VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_TEMPLATES . 'html-product-attribute.php';
		wp_die();
	}

	public static function prepare_attribute( $attribute, $data, $i ) {
		if ( ! empty( $data['attribute_names'] ) && ! empty( $data['attribute_values'] ) ) {
			$attribute_name               = wc_clean( esc_html( $data['attribute_names'][ $i ] ) );
			$vi_attribute_type            = isset( $data['vi_attribute_type'][ $i ] ) ? wc_clean( $data['vi_attribute_type'][ $i ] ) : '';
			$vi_attribute_profile         = isset( $data['vi_attribute_profile'][ $i ] ) ? wc_clean( $data['vi_attribute_profile'][ $i ] ) : '';
			$vi_attribute_color_separator = isset( $data['vi_attribute_color_separator'][ $i ] ) ? wc_clean( $data['vi_attribute_color_separator'][ $i ] ) : '';
			$vi_attribute_colors          = isset( $data['vi_attribute_colors'][ $i ] ) ? wc_clean( $data['vi_attribute_colors'][ $i ] ) : '';
			$vi_attribute_images          = isset( $data['vi_attribute_images'][ $i ] ) ? wc_clean( $data['vi_attribute_images'][ $i ] ) : '';
			$vi_attribute_loop_enable     = isset( $data['vi_attribute_loop_enable'][ $i ] ) ? wc_clean( $data['vi_attribute_loop_enable'][ $i ] ) : '';
			$vi_attribute_display_type    = isset( $data['vi_attribute_display_type'][ $i ] ) ? wc_clean( $data['vi_attribute_display_type'][ $i ] ) : '';
			$vi_change_product_image      = isset( $data['vi_change_product_image'][ $i ] ) ? wc_clean( $data['vi_change_product_image'][ $i ] ) : '';
			if ( 'pa_' !== substr( $attribute_name, 0, 3 ) ) {
				$attribute_name = html_entity_decode( $attribute_name, ENT_NOQUOTES, 'UTF-8' );
				$options        = isset( $data['attribute_values'][ $i ] ) ? $data['attribute_values'][ $i ] : '';
				$new_option     = array();
				if ( is_array( $options ) ) {
					for ( $j = 0; $j < count( $options ); $j ++ ) {
						if ( $options[ $j ] !== '' ) {
							$new_option[] = $options[ $j ];
						} else {
							unset( $vi_attribute_color_separator[ $j ] );
							unset( $vi_attribute_colors[ $j ] );
							unset( $vi_attribute_images[ $j ] );
						}
					}
					$vi_attribute_color_separator = array_values( $vi_attribute_color_separator );
					$vi_attribute_colors          = array_values( $vi_attribute_colors );
					$vi_attribute_images          = array_values( $vi_attribute_images );
				} else {
					// Terms or text sent in textarea.
					$options    = 0 < $i ? wc_sanitize_textarea( wc_sanitize_term_text_based( $options ) ) : wc_sanitize_textarea( $options );
					$new_option = wc_get_text_attributes( $options );
				}
				if ( empty( $new_option ) ) {
					return false;
				}
				$attribute->set_options( $new_option );
			}
			global $post_ID;
			if ( $post_ID ) {
				$vi_attribute_settings                                                 = get_post_meta( $post_ID, '_vi_woo_product_variation_swatches_product_attribute', true );
				$vi_attribute_settings                                                 = $vi_attribute_settings ? json_decode( $vi_attribute_settings, true ) : array();
				$vi_attribute_settings['attribute_type'][ $attribute_name ]            = $vi_attribute_type;
				$vi_attribute_settings['attribute_profile'][ $attribute_name ]         = $vi_attribute_profile;
				$vi_attribute_settings['attribute_color_separator'][ $attribute_name ] = $vi_attribute_color_separator;
				$vi_attribute_settings['attribute_colors'][ $attribute_name ]          = $vi_attribute_colors;
				$vi_attribute_settings['attribute_img_ids'][ $attribute_name ]         = $vi_attribute_images;
				$vi_attribute_settings['vi_attribute_loop_enable'][ $attribute_name ]  = $vi_attribute_loop_enable;
				$vi_attribute_settings['attribute_display_type'][ $attribute_name ]    = $vi_attribute_display_type;
				$vi_attribute_settings['change_product_image'][ $attribute_name ]      = $vi_change_product_image;
				$vi_attribute_settings                                                 = wp_json_encode( $vi_attribute_settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
				update_post_meta( $post_ID, '_vi_woo_product_variation_swatches_product_attribute', $vi_attribute_settings );
			} else {
				$attribute->vi_attribute_name            = $attribute_name;
				$attribute->vi_attribute_type            = $vi_attribute_type;
				$attribute->vi_attribute_profile         = $vi_attribute_profile;
				$attribute->vi_attribute_color_separator = $vi_attribute_color_separator;
				$attribute->vi_attribute_colors          = $vi_attribute_colors;
				$attribute->vi_attribute_images          = $vi_attribute_images;
				$attribute->vi_attribute_loop_enable     = $vi_attribute_loop_enable;
				$attribute->vi_attribute_display_type    = $vi_attribute_display_type;
				$attribute->vi_change_product_image      = $vi_change_product_image;
			}
		}

		return $attribute;
	}

	public static function save_attributes() {
		check_ajax_referer( 'save-attributes', 'security' );

		if ( ! current_user_can( 'edit_products' ) || ! isset( $_POST['data'], $_POST['post_id'] ) ) {
			wp_die( - 1 );
		}

		$response = array();
		try {
			parse_str( wp_unslash( $_POST['data'] ),
				$data ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$attributes = WC_Meta_Box_Product_Data::prepare_attributes( $data );

			$product_id   = absint( wp_unslash( $_POST['post_id'] ) );
			$product_type = ! empty( $_POST['product_type'] ) ? wc_clean( wp_unslash( $_POST['product_type'] ) ) : 'simple';
			$classname    = WC_Product_Factory::get_product_classname( $product_id, $product_type );
			$product      = new $classname( $product_id );

			$product->set_attributes( $attributes );
			$product->save();
			$vi_attribute_settings = get_post_meta( $product_id, '_vi_woo_product_variation_swatches_product_attribute', true );
			$vi_attribute_settings = $vi_attribute_settings ? json_decode( $vi_attribute_settings, true ) : array();
			$args                  = array();
			foreach ( $attributes as $attribute ) {
				if ( ! $attribute->vi_attribute_name ) {
					continue;
				}
				$vi_attribute_name                                       = html_entity_decode( $attribute->vi_attribute_name, ENT_NOQUOTES, 'UTF-8' );//use html_entity_decode to handle special/accented characters in custom attribute name
				$args['attribute_type'][ $vi_attribute_name ]            = $attribute->vi_attribute_type;
				$args['attribute_profile'][ $vi_attribute_name ]         = $attribute->vi_attribute_profile;
				$args['attribute_color_separator'][ $vi_attribute_name ] = $attribute->vi_attribute_color_separator;
				$args['attribute_colors'][ $vi_attribute_name ]          = $attribute->vi_attribute_colors;
				$args['attribute_img_ids'][ $vi_attribute_name ]         = $attribute->vi_attribute_images;
				$args['vi_attribute_loop_enable'][ $vi_attribute_name ]  = $attribute->vi_attribute_loop_enable;
				$args['attribute_display_type'][ $vi_attribute_name ]    = $attribute->vi_attribute_display_type;
				$args['change_product_image'][ $vi_attribute_name ]      = $attribute->vi_change_product_image;
			}
			$args                  = wp_parse_args( $args, $vi_attribute_settings );
			$vi_attribute_settings = $args;
			$args                  = wp_json_encode( $args, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			update_post_meta( $product_id, '_vi_woo_product_variation_swatches_product_attribute', $args );
			$settings                 = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DATA::get_instance();
			$vi_wpvs_ids              = $settings->get_params( 'ids' );
			$vi_wpvs_name             = $settings->get_params( 'names' );
			$vi_default_colors        = $settings->get_default_color();
			$product_list_add_to_cart = $settings->get_params( 'product_list_add_to_cart' );
			$attribute_types          = wc_get_attribute_types();
			ob_start();
			$attributes = $product->get_attributes( 'edit' );
			$i          = - 1;
			if ( ! empty( $data['attribute_names'] ) ) {
				foreach ( $data['attribute_names'] as $attribute_name ) {
					$attribute = isset( $attributes[ sanitize_title( $attribute_name ) ] ) ? $attributes[ sanitize_title( $attribute_name ) ] : false;
					if ( ! $attribute ) {
						continue;
					}
					$i ++;
					$metabox_class = array();

					if ( $attribute->is_taxonomy() ) {
						$metabox_class[] = 'taxonomy';
						$metabox_class[] = $attribute->get_name();
					}
					include VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_TEMPLATES . 'html-product-attribute.php';
				}
			}

			$response['html'] = ob_get_clean();
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}

		// wp_send_json_success must be outside the try block not to break phpunit tests.
		wp_send_json_success( $response );
	}

	public function custom_attribute_product() {
		global $post, $thepostid, $product_object;
		$vi_attribute_settings    = get_post_meta( $thepostid, '_vi_woo_product_variation_swatches_product_attribute', true );
		$vi_attribute_settings    = $vi_attribute_settings ? json_decode( $vi_attribute_settings, true ) : array();
		$vi_wpvs_ids              = $this->settings->get_params( 'ids' );
		$vi_wpvs_name             = $this->settings->get_params( 'names' );
		$vi_default_colors        = $this->settings->get_default_color();
		$attribute_types          = wc_get_attribute_types();
		$product_list_add_to_cart = $this->settings->get_params( 'product_list_add_to_cart' );
		?>
        <div class="product_attributes vi-wpvs-attribute-wrap-wrap wc-metaboxes"
             data-product_id="<?php echo esc_attr( $thepostid ); ?>">
			<?php
			// Product attributes - taxonomies and custom, ordered, with visibility and variation attributes set.
			$attributes = $product_object->get_attributes( 'edit' );
			$i          = - 1;
			foreach ( $attributes as $attribute ) {
				$i ++;
				$metabox_class = array();
				if ( $attribute->is_taxonomy() ) {
					$metabox_class[] = 'taxonomy';
					$metabox_class[] = $attribute->get_name();
				}
				include VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_TEMPLATES . 'html-product-attribute.php';
			}
			?>
        </div>
        <div class="toolbar vi-wpvs-attribute-wrap-wrap">
			<span class="expand-close">
				<a href="#"
                   class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce-product-variations-swatches' ); ?></a> / <a
                        href="#"
                        class="close_all">
                    <?php esc_html_e( 'Close', 'woocommerce-product-variations-swatches' ); ?>
                </a>
			</span>
            <button type="button"
                    class="button save_attributes button-primary"><?php esc_html_e( 'Save attributes', 'woocommerce-product-variations-swatches' ); ?></button>
        </div>
		<?php
	}

	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( $screen->id == 'product' ) {
			wp_enqueue_style( 'woo-product-variations-swatches-admin-minicolors', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'minicolors.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'woo-product-variations-swatches-admin-custom-attribute', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'admin-custom-attribute.css', array(),
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'select2', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'select2.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'woo-product-variations-swatches-admin-custom-attribute',
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'admin-custom-attribute.js',
				array( 'jquery' ),
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'woo-product-variations-swatches-admin-minicolors', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'minicolors.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			$args = array(
				'ajax_url'               => admin_url( 'admin-ajax.php' ),
				'settings_default_color' => $this->settings->get_default_color(),
				'remove_attribute'       => esc_html__( 'Remove this attribute?', 'woocommerce-product-variations-swatches' ),
				'remove_item'            => esc_html__( 'Would you want to remove this?', 'woocommerce-product-variations-swatches' ),
				'remove_last_item'       => esc_html__( 'You can not remove the last item.', 'woocommerce-product-variations-swatches' ),
				'global_setting_url'     => admin_url( 'admin.php?page=woocommerce-product-variations-swatches-global-attrs' ),
				'global_setting_title'   => esc_html__( 'Swatches settings for global attributes', 'woocommerce-product-variations-swatches' ),
			);
			wp_localize_script( 'woo-product-variations-swatches-admin-custom-attribute', 'viwpvs_admin_custom_attribute', $args );
		}
	}
}