<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Admin_Setting_Global_Attrs {
	protected $settings;
	protected $error;

	public function __construct() {
		$this->settings = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DATA::get_instance();
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 99999 );
		add_action( 'wp_ajax_vi_wvps_save_global_attrs', array( $this, 'save_attr' ) );
		add_filter( 'set-screen-option', array( $this, 'save_screen_options' ), 10, 3 );
	}

	public function save_attr() {
		$response = array(
			'status'  => 'failed',
			'message' => '',
		);
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'vi_wvps_global_attrs_action' ) ) {
			$response['message'] = 'wp verify nonce failed';
			wp_send_json( $response );
		}
		$data_changes = $_POST['viwpvs_admin_attrs_data'] ?? '';
		if ( empty( $data_changes ) ) {
			$response['message'] = 'Not found data';
			wp_send_json( $response );
		}
		$data_changes = str_replace( '\"', '"', $data_changes );
		$data_changes = json_decode( $data_changes, true );
		global $vi_wpvs_settings;
		$slug                 = isset( $data_changes['taxonomy_slug'] ) ? sanitize_text_field( $data_changes['taxonomy_slug'] ) : '';
		$profile              = isset( $data_changes['taxonomy_profile'] ) ? sanitize_text_field( $data_changes['taxonomy_profile'] ) : '';
		$change_product_image = isset( $data_changes['change_product_image'] ) ? sanitize_text_field( $data_changes['change_product_image'] ) : '';
		$type                 = isset( $data_changes['taxonomy_type'] ) ? sanitize_text_field( $data_changes['taxonomy_type'] ) : 'select';
		$loop_enable          = isset( $data_changes['taxonomy_loop_enable'] ) ? sanitize_text_field( $data_changes['taxonomy_loop_enable'] ) : '';
		$display_type         = isset( $data_changes['taxonomy_display_type'] ) ? sanitize_text_field( $data_changes['taxonomy_display_type'] ) : '';
		$term_data            = isset( $data_changes['term_data'] ) ? viwpvs_sanitize_fields( $data_changes['term_data'] ) : array();
		$term_cats_data       = isset( $data_changes['term_cats_data'] ) ? viwpvs_sanitize_fields( $data_changes['term_cats_data'] ) : array();
		if ( ! $slug ) {
			$response['message'] = 'not found taxonomy_slug';
			wp_send_json( $response );
		}
		//save option
		$args                                   = array();
		$taxonomy_profiles                      = $vi_wpvs_settings['taxonomy_profiles'] ?? array();
		$change_product_images                  = $vi_wpvs_settings['change_product_image'] ?? array();
		$taxonomy_loop_enable                   = $vi_wpvs_settings['taxonomy_loop_enable'] ?? array();
		$taxonomy_display_type                  = $vi_wpvs_settings['taxonomy_display_type'] ?? array();
		$taxonomy_custom_cats                   = $vi_wpvs_settings['taxonomy_custom_cats'] ?? array();
		$change_product_images[ 'pa_' . $slug ] = $change_product_image;
		$args ['change_product_image']          = $change_product_images;
		$taxonomy_profiles[ 'pa_' . $slug ]     = $profile;
		$args ['taxonomy_profiles']             = $taxonomy_profiles;
		$taxonomy_loop_enable[ 'pa_' . $slug ]  = $loop_enable;
		$args ['taxonomy_loop_enable']          = $taxonomy_loop_enable;
		$taxonomy_display_type[ 'pa_' . $slug ] = $display_type;
		$args ['taxonomy_display_type']         = $taxonomy_display_type;
		$taxonomy_custom_cats[ 'pa_' . $slug ]  = $term_cats_data;
		$args ['taxonomy_custom_cats']          = $taxonomy_custom_cats;
		$args                                   = wp_parse_args( $args, get_option( 'vi_woo_product_variation_swatches_params', $vi_wpvs_settings ) );

		update_option( 'vi_woo_product_variation_swatches_params', $args );
		$vi_wpvs_settings = $args;

		//save attribute type
		if ( $type ) {
			global $wpdb;
			$wpdb->update( "{$wpdb->prefix}woocommerce_attribute_taxonomies", array( 'attribute_type' => $type ), array( 'attribute_name' => $slug ), array( '%s' ), array( '%s' ) );
			// Clear cache and flush rewrite rules.
			wp_schedule_single_event( time(), 'woocommerce_flush_rewrite_rules' );
			delete_transient( 'wc_attribute_taxonomies' );
			WC_Cache_Helper::invalidate_cache_group( 'woocommerce-attributes' );
		}
		//save term
		if ( is_array( $term_data ) && count( $term_data ) ) {
			foreach ( $term_data as $term_id => $term_settings ) {
				$old_term_settings = get_term_meta( $term_id, 'vi_wpvs_terms_params', true );
				if ( $old_term_settings !== $term_settings ) {
					$term_settings = wp_parse_args( $term_settings, $old_term_settings );
					update_term_meta( $term_id, 'vi_wpvs_terms_params', $term_settings );
				}
			}
		}
		$response['status'] = $response['message'] ? 'failed' : 'successfully';
		wp_send_json( $response );
	}

	public function admin_menu() {

		$import_list = add_submenu_page(
			'woocommerce-product-variations-swatches',
			esc_html__( 'Swatches Settings for Global Attributes', 'woocommerce-product-variations-swatches' ),
			esc_html__( 'Global Attributes', 'woocommerce-product-variations-swatches' ),
			'manage_woocommerce',
			'woocommerce-product-variations-swatches-global-attrs',
			array( $this, 'settings_callback' )
		);
		add_action( "load-$import_list", array( $this, 'screen_options_page' ) );
	}

	/**
	 * @param $status
	 * @param $option
	 * @param $value
	 *
	 * @return mixed
	 */
	public function save_screen_options( $status, $option, $value ) {
		if ( 'vi_wvps_attrs_per_page' == $option ) {
			return $value;
		}

		return $status;
	}

	/**
	 * Add Screen Options
	 */
	public function screen_options_page() {
		$option = 'per_page';
		$args   = array(
			'label'   => esc_html__( 'Number of items per page', 'woocommerce-product-variations-swatches' ),
			'default' => 5,
			'option'  => 'vi_wvps_attrs_per_page'
		);

		add_screen_option( $option, $args );
	}

	public function get_pagination_html( $page, $keyword, $paged, $p_paged, $n_paged, $total_page ) {
		?>
        <form method="get">
            <input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>">
            <div class="tablenav top">
                <div class="vi-wvps-attrs-save">
                    <span class="vi-ui button primary vi-wvps-attrs-save-button vi-wvps-attrs-save-all-button"
                          title="<?php esc_attr_e( 'Save all', 'woocommerce-product-variations-swatches' ) ?>">
                        <?php esc_html_e( 'Save All', 'woocommerce-product-variations-swatches' ) ?>
                    </span>
                </div>
                <div class="tablenav-pages">
                    <div class="pagination-links">
						<?php
						if ( $paged > 2 ) {
							?>
                            <a class="prev-page button" href="<?php echo esc_url( add_query_arg(
								array(
									'page'           => $page,
									'paged'          => 1,
									'vi_wvps_search' => $keyword,
								), admin_url( 'admin.php' )
							) ) ?>"><span
                                        class="screen-reader-text"><?php esc_html_e( 'First Page', 'woocommerce-product-variations-swatches' ) ?></span><span
                                        aria-hidden="true">«</span></a>
							<?php
						} else {
							?>
                            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
							<?php
						}
						/*Previous button*/
						if ( $p_paged ) {
							$p_url = add_query_arg(
								array(
									'page'           => $page,
									'paged'          => $p_paged,
									'vi_wvps_search' => $keyword,
								), admin_url( 'admin.php' )
							);
							?>
                            <a class="prev-page button" href="<?php echo esc_url( $p_url ) ?>"><span
                                        class="screen-reader-text"><?php esc_html_e( 'Previous Page', 'woocommerce-product-variations-swatches' ) ?></span><span
                                        aria-hidden="true">‹</span></a>
							<?php
						} else {
							?>
                            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
							<?php
						}
						?>
                        <span class="screen-reader-text"><?php esc_html_e( 'Current Page', 'woocommerce-product-variations-swatches' ) ?></span>
                        <span id="table-paging" class="paging-input">
                            <input class="current-page" type="text" name="paged" size="1"
                                   value="<?php echo esc_attr( $paged ) ?>">
                            <span class="tablenav-paging-text">
                                <?php esc_html_e( 'of', 'woocommerce-product-variations-swatches' ) ?>
                                 <span class="total-pages"><?php echo esc_html( $total_page ) ?></span>
                            </span>
                        </span>
						<?php
						/*Next button*/
						if ( $n_paged ) {
							$n_url = add_query_arg(
								array(
									'page'           => $page,
									'paged'          => $n_paged,
									'vi_wvps_search' => $keyword,
								), admin_url( 'admin.php' )
							); ?>
                            <a class="next-page button" href="<?php echo esc_url( $n_url ) ?>"><span
                                        class="screen-reader-text"><?php esc_html_e( 'Next Page', 'woocommerce-product-variations-swatches' ) ?></span><span
                                        aria-hidden="true">›</span></a>
							<?php
						} else {
							?>
                            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
							<?php
						}
						if ( $total_page > $paged + 1 ) {
							?>
                            <a class="next-page button" href="<?php echo esc_url( add_query_arg(
								array(
									'page'           => $page,
									'paged'          => $total_page,
									'vi_wvps_search' => $keyword,
								), admin_url( 'admin.php' )
							) ) ?>"><span
                                        class="screen-reader-text"><?php esc_html_e( 'Last Page', 'woocommerce-product-variations-swatches' ) ?></span><span
                                        aria-hidden="true">»</span></a>
							<?php
						} else {
							?>
                            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>
							<?php
						}
						?>
                    </div>
                </div>
                <p class="search-box">
                    <input type="text" class="text short" name="vi_wvps_search"
                           value="<?php echo esc_attr( $keyword ) ?>">
                    <input type="submit" name="submit" class="button"
                           value="<?php esc_attr_e( 'Search attribute', 'woocommerce-product-variations-swatches' ) ?>">
                </p>
            </div>
        </form>
		<?php
	}

	public function settings_callback() {
		$user     = get_current_user_id();
		$screen   = get_current_screen();
		$option   = $screen->get_option( 'per_page', 'option' );
		$per_page = get_user_meta( $user, $option, true );
		if ( empty ( $per_page ) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}
		$paged                = isset( $_GET['paged'] ) ? sanitize_text_field( $_GET['paged'] ) : 1;
		$keyword              = isset( $_GET['vi_wvps_search'] ) ? strtolower( sanitize_text_field( $_GET['vi_wvps_search'] ) ) : '';
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		?>
        <div class="wrap<?php echo is_rtl() ? esc_attr( ' vi-wpvs-wrap-rtl' ) : ''; ?>">
            <h2><?php esc_html_e( 'Swatches Settings for Global Attributes', 'woocommerce-product-variations-swatches' ) ?></h2>
            <div class="vi-ui blue message">
				<?php esc_html_e( 'This page allows you to customize all WooCommerce global attributes rapidly', 'woocommerce-product-variations-swatches' ); ?>
            </div>
			<?php
			if ( $attribute_taxonomies ) {
				if ( $keyword ) {
					$attribute_taxonomies_t = array();
					foreach ( $attribute_taxonomies as $attr ) {
						$check = strtolower( $attr->attribute_label );
						if ( strlen( strstr( $check, $keyword ) ) ) {
							$attribute_taxonomies_t[] = $attr;
						}
					}
				} else {
					$attribute_taxonomies_t = $attribute_taxonomies;
				}
				$count_taxonomies = ! empty( $attribute_taxonomies_t ) ? count( $attribute_taxonomies_t ) : 1;
				$total_page       = ceil( $count_taxonomies / $per_page );
				/*Previous page*/
				$p_paged = $per_page * $paged > $per_page ? $paged - 1 : 0;
				/* next page */
				$n_paged = $per_page * $paged < $count_taxonomies ? $paged + 1 : 0;
				ob_start();
				$this->get_pagination_html( 'woocommerce-product-variations-swatches-global-attrs', $keyword, $paged, $p_paged, $n_paged, $total_page );
				$pagination_html = ob_get_clean();
				echo wp_kses( $pagination_html, VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DATA::extend_post_allowed_html() );
				wp_nonce_field( 'vi_wvps_global_attrs_action', '_vi_wvps_global_attrs_nonce' );
				$global_attrs              = array_slice( $attribute_taxonomies_t, $p_paged * $per_page, $paged * $per_page );
				$vi_wpvs_ids               = $this->settings->get_params( 'ids' );
				$vi_wpvs_names             = $this->settings->get_params( 'names' );
				$vi_attribute_profiles     = $this->settings->get_params( 'taxonomy_profiles' ) ?: array();
				$vi_change_product_image   = $this->settings->get_params( 'change_product_image' ) ?: array();
				$vi_attribute_loop_enable  = $this->settings->get_params( 'taxonomy_loop_enable' ) ?: array();
				$vi_attribute_display_type = $this->settings->get_params( 'taxonomy_display_type' ) ?: array();
				$taxonomy_custom_cats      = $this->settings->get_params( 'taxonomy_custom_cats' ) ?: array();
				$product_list_add_to_cart  = $this->settings->get_params( 'product_list_add_to_cart' );
				$attribute_types           = wc_get_attribute_types();
				echo sprintf( '<form  class="vi-ui form" method="post" >' );
				foreach ( $global_attrs as $attribute ) {
					$attribute_name         = wc_attribute_taxonomy_name( $attribute->attribute_name );
					$attribute_loop_enable  = $vi_attribute_loop_enable[ $attribute_name ] ?? '';
					$attribute_profile      = $vi_attribute_profiles[ $attribute_name ] ?? '';
					$attribute_display_type = $vi_attribute_display_type[ $attribute_name ] ?? 'vertical';
					$term_custom_settings   = $taxonomy_custom_cats[ $attribute_name ] ?? '';
					$change_product_image   = $vi_change_product_image[ $attribute_name ] ?? '';
					?>
                    <div class="vi-ui styled fluid accordion active vi-wpvs-accordion-wrap vi-wpvs-accordion-attr-wrap vi-wpvs-accordion-attr-wrap-<?php echo esc_attr( $attribute_name ); ?>"
                         data-attribute_name="<?php echo esc_attr( $attribute_name ); ?>"
                         data-attribute_id="<?php echo esc_attr( $attribute->attribute_id ); ?>">
                        <div class="vi-wpvs-accordion-info-wrap">
                            <div class="vi-wpvs-accordion-name">
								<?php echo esc_html( $attribute->attribute_label ); ?>
                            </div>
                            <div class="vi-wpvs-accordion-action">
                                <div class="vi-wvps-attrs-save">
									<span class="vi-ui mini button primary vi-wvps-attrs-save-button vi-wvps-attr-taxonomy-save-button">
										<?php esc_html_e( 'Save', 'woocommerce-product-variations-swatches' ); ?>
									</span>
                                </div>
                            </div>
                        </div>
                        <div class="title active">
                            <i class="dropdown icon"></i>
							<?php esc_html_e( 'Default design', 'woocommerce-product-variations-swatches' ); ?>
                        </div>
                        <div class="content active">
                            <input type="hidden" name="taxonomy_slug"
                                   value="<?php echo esc_attr( $attribute->attribute_name ); ?>">
                            <div class="equal width fields">
                                <div class="field">
                                    <label>
										<?php esc_html_e( 'Show in product list', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                    <div class="vi-ui toggle checkbox vi-wpvs-taxonomy-loop-enable<?php echo $product_list_add_to_cart ? esc_attr( ' disabled' ) : ''; ?>">
                                        <input type="hidden" name="taxonomy_loop_enable"
                                               class="vi-wpvs-accordion-taxonomy_loop_enable"
                                               value="<?php echo esc_attr( $attribute_loop_enable ); ?>">
                                        <input type="checkbox"
                                               class="vi-wpvs-accordion-taxonomy_loop_enable-checkbox" <?php checked( $attribute_loop_enable, '1' ) ?>>
                                    </div>
									<?php
									if ( $product_list_add_to_cart ) {
										echo sprintf( '<p class="description"><a href="%s" target="_blank">%s</a></p>',
											esc_url( admin_url( 'admin.php?page=woocommerce-product-variations-swatches#product_list' ) ),
											__( 'This option cannot be disabled because \'Enable add to cart\' option in Swatches on Product List tab is currently enabled', 'woocommerce-product-variations-swatches' )
										);
									}
									?>
                                </div>
                                <div class="field">
                                    <label>
										<?php esc_html_e( 'Display style', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                    <select name="taxonomy_display_type"
                                            class="vi-ui fluid dropdown vi-wpvs-accordion-taxonomy_display_type">
                                        <option value="vertical" <?php selected( $attribute_display_type, 'vertical' ) ?> >
											<?php esc_html_e( 'Vertical', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                        <option value="horizontal" <?php selected( $attribute_display_type, 'horizontal' ) ?> >
											<?php esc_html_e( 'Horizontal', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                    </select>
                                </div>
                                <div class="field">
                                    <label>
										<?php esc_html_e( 'Display type', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                    <select name="taxonomy_type"
                                            class="vi-ui fluid dropdown vi-wpvs-accordion-taxonomy_type">
										<?php
										foreach ( $attribute_types as $k => $v ) {
											?>
                                            <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $attribute->attribute_type, $k ) ?>><?php echo esc_html( $v ); ?></option>
											<?php
										}
										?>
                                    </select>
                                </div>
                                <div class="field">
                                    <label>
										<?php esc_html_e( 'Swatches profile', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                    <select name="taxonomy_profile"
                                            class="vi-ui fluid dropdown vi-wpvs-accordion-taxonomy_profile">
										<?php
										foreach ( $vi_wpvs_ids as $k => $id ) {
											?>
                                            <option value="<?php echo esc_attr( $id ) ?>" <?php selected( $attribute_profile,
												$id ) ?>><?php echo esc_html( $vi_wpvs_names[ $k ] ); ?></option>
											<?php
										}
										?>
                                    </select>
                                </div>
                                <div class="field">
                                    <label>
										<?php esc_html_e( 'Change product image', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                    <select class="vi-ui dropdown" name="change_product_image">
                                        <option value="not_change" <?php selected( $change_product_image, 'not_change' ) ?>><?php esc_html_e( 'Not change', 'woocommerce-product-variations-swatches' ); ?></option>
                                        <option value="attribute_image" <?php selected( $change_product_image, 'attribute_image' ) ?>><?php esc_html_e( 'Change to image set for attribute', 'woocommerce-product-variations-swatches' ); ?></option>
                                        <option value="variation_image" <?php selected( $change_product_image, 'variation_image' ) ?>><?php esc_html_e( 'Auto detect variation image', 'woocommerce-product-variations-swatches' ); ?></option>
                                    </select>
                                </div>
                            </div>
							<?php
							if ( taxonomy_exists( $attribute_name ) ) {
								$this->terms_settings_html( $attribute, $attribute_name );
							}
							?>
                        </div>
                        <div class="title">
                            <i class="dropdown icon"></i>
							<?php esc_html_e( 'Design with Product category', 'woocommerce-product-variations-swatches' ); ?>
                        </div>
                        <div class="content">
                            <div class="field">
                                <table class="form-table vi-wpvs-table">
                                    <thead>
                                    <tr>
                                        <td>
                                            <label><?php esc_html_e( 'Product category', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Display type', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Swatches profile', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Show in product list', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Change product image', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Display style', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Action', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                    </tr>
                                    </thead>
                                    <tbody>
									<?php
									if ( $term_custom_settings && is_array( $term_custom_settings ) && $count_rule = count( $term_custom_settings ) ) {
										for ( $i = 0; $i < $count_rule; $i ++ ) {
											$term_custom_cats         = $term_custom_settings[ $i ]['category'] ?? array();
											$term_custom_type         = $term_custom_settings[ $i ]['type'] ?? '';
											$term_custom_profile      = $term_custom_settings[ $i ]['profile'] ?? '';
											$term_custom_display_type = $term_custom_settings[ $i ]['display_type'] ?? '';
											$term_custom_loop_enable  = $term_custom_settings[ $i ]['loop_enable'] ?? '';
											$change_product_image     = $term_custom_settings[ $i ]['change_product_image'] ?? '';
											?>
                                            <tr class="vi-wpvs-term-custom-cats-wrap">
                                                <td>
                                                    <div class="vi-ui field">
                                                        <select multiple="multiple"
                                                                name="taxonomy_custom_cats[<?php echo esc_attr( $attribute_name ); ?>][category][]"
                                                                class="vi-wpvs-category-search vi-wpvs-term_custom_cats">
															<?php
															if ( $term_custom_cats && is_array( $term_custom_cats ) && count( $term_custom_cats ) ) {
																foreach ( $term_custom_cats as $category_id ) {
																	$category = get_term( $category_id );
																	if ( $category ) {
																		?>
                                                                        <option value="<?php echo esc_attr( $category_id ); ?>"
                                                                                selected><?php echo esc_html( $category->name ); ?></option>
																		<?php
																	}
																}
															}
															?>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <select name="taxonomy_custom_cats[<?php echo esc_attr( $attribute_name ); ?>][type][]"
                                                            class="vi-ui fluid dropdown vi-wpvs-term_custom_type">
														<?php
														foreach ( $attribute_types as $k => $v ) {
															?>
                                                            <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $term_custom_type, $k ) ?>><?php echo esc_html( $v ); ?></option>
															<?php
														}
														?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="taxonomy_custom_cats[<?php echo esc_attr( $attribute_name ); ?>][profile][]"
                                                            class="vi-ui fluid dropdown vi-wpvs-term_custom_profile">
														<?php
														foreach ( $vi_wpvs_ids as $k => $id ) {
															?>
                                                            <option value="<?php echo esc_attr( $id ) ?>" <?php selected( $term_custom_profile,
																$id ) ?>><?php echo esc_html( $vi_wpvs_names[ $k ] ); ?></option>
															<?php
														}
														?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="vi-ui toggle checkbox">
                                                        <input type="hidden"
                                                               name="taxonomy_custom_cats[<?php echo esc_attr( $attribute_name ); ?>][loop_enable][]"
                                                               class="vi-wpvs-term_custom_loop_enable"
                                                               value="<?php echo esc_attr( $term_custom_loop_enable ); ?>">
                                                        <input type="checkbox"
                                                               class="vi-wpvs-term_custom_loop_enable-checkbox" <?php checked( $term_custom_loop_enable, '1' ); ?>><label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <select class="vi-ui dropdown vi-wpvs-term_custom_change_product_image"
                                                            name="taxonomy_custom_cats[<?php echo esc_attr( $attribute_name ); ?>][change_product_image][]">
                                                        <option value="not_change" <?php selected( $change_product_image, 'not_change' ) ?>><?php esc_html_e( 'Not change', 'woocommerce-product-variations-swatches' ); ?></option>
                                                        <option value="attribute_image" <?php selected( $change_product_image, 'attribute_image' ) ?>><?php esc_html_e( 'Change to image set for attribute', 'woocommerce-product-variations-swatches' ); ?></option>
                                                        <option value="variation_image" <?php selected( $change_product_image, 'variation_image' ) ?>><?php esc_html_e( 'Auto detect variation image', 'woocommerce-product-variations-swatches' ); ?></option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="taxonomy_custom_cats[<?php echo esc_attr( $attribute_name ); ?>][display_type][]"
                                                            class="vi-ui fluid dropdown vi-wpvs-term_custom_display_type">
                                                        <option value="vertical" <?php selected( $term_custom_display_type, 'vertical' ) ?> >
															<?php esc_html_e( 'Vertical', 'woocommerce-product-variations-swatches' ); ?>
                                                        </option>
                                                        <option value="horizontal" <?php selected( $term_custom_display_type, 'horizontal' ) ?> >
															<?php esc_html_e( 'Horizontal', 'woocommerce-product-variations-swatches' ); ?>
                                                        </option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="vi-wpvs-term-custom-clone vi-ui positive mini  button">
                                                        <?php esc_html_e( 'Clone', 'woocommerce-product-variations-swatches' ); ?>
                                                    </span>
                                                    <span class="vi-wpvs-term-custom-remove vi-ui negative mini button">
                                                        <?php esc_html_e( 'Remove', 'woocommerce-product-variations-swatches' ); ?>
                                                    </span>
                                                </td>
                                            </tr>
											<?php
										}
									} else {
										?>
                                        <tr class="vi-wpvs-term-custom-cats-wrap">
                                            <td>
                                                <div class="vi-ui field">
                                                    <select multiple="multiple"
                                                            name="taxonomy_custom_cats[<?php echo esc_attr( $attribute_name ); ?>][category][]"
                                                            class="vi-wpvs-category-search vi-wpvs-term_custom_cats">
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <select name="taxonomy_custom_cats[<?php echo esc_attr( $attribute_name ); ?>][type][]"
                                                        class="vi-ui fluid dropdown vi-wpvs-term_custom_type">
													<?php
													foreach ( $attribute_types as $k => $v ) {
														?>
                                                        <option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $v ); ?></option>
														<?php
													}
													?>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="taxonomy_custom_cats[<?php echo esc_attr( $attribute_name ); ?>][profile][]"
                                                        class="vi-ui fluid dropdown vi-wpvs-term_custom_profile">
													<?php
													foreach ( $vi_wpvs_ids as $k => $id ) {
														?>
                                                        <option value="<?php echo esc_attr( $id ) ?>"><?php echo esc_html( $vi_wpvs_names[ $k ] ); ?></option>
														<?php
													}
													?>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="vi-ui toggle checkbox">
                                                    <input type="hidden"
                                                           name="taxonomy_custom_cats[<?php echo esc_attr( $attribute_name ); ?>][loop_enable][]"
                                                           class="vi-wpvs-term_custom_loop_enable"
                                                           value="">
                                                    <input type="checkbox"
                                                           class="vi-wpvs-term_custom_loop_enable-checkbox"><label>
                                                </div>
                                            </td>
                                            <td>
                                                <select class="vi-ui dropdown vi-wpvs-term_custom_change_product_image"
                                                        name="taxonomy_custom_cats[<?php echo esc_attr( $attribute_name ); ?>][change_product_image][]">
                                                    <option value="not_change" <?php selected( $change_product_image, 'not_change' ) ?>><?php esc_html_e( 'Not change', 'woocommerce-product-variations-swatches' ); ?></option>
                                                    <option value="attribute_image" <?php selected( $change_product_image, 'attribute_image' ) ?>><?php esc_html_e( 'Change to image set for attribute', 'woocommerce-product-variations-swatches' ); ?></option>
                                                    <option value="variation_image" <?php selected( $change_product_image, 'variation_image' ) ?>><?php esc_html_e( 'Auto detect variation image', 'woocommerce-product-variations-swatches' ); ?></option>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="taxonomy_custom_cats[<?php echo esc_attr( $attribute_name ); ?>][display_type][]"
                                                        class="vi-ui fluid dropdown vi-wpvs-term_custom_display_type">
                                                    <option value="vertical">
														<?php esc_html_e( 'Vertical', 'woocommerce-product-variations-swatches' ); ?>
                                                    </option>
                                                    <option value="horizontal">
														<?php esc_html_e( 'Horizontal', 'woocommerce-product-variations-swatches' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                            <td>
                                                    <span class="vi-wpvs-term-custom-clone vi-ui positive mini  button">
                                                        <?php esc_html_e( 'Clone', 'woocommerce-product-variations-swatches' ); ?>
                                                    </span>
                                                <span class="vi-wpvs-term-custom-remove vi-ui negative mini button">
                                                        <?php esc_html_e( 'Remove', 'woocommerce-product-variations-swatches' ); ?>
                                                    </span>
                                            </td>
                                        </tr>
										<?php
									}
									?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
					<?php
				}
				echo sprintf( '</form>' );
				echo wp_kses( $pagination_html, VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DATA::extend_post_allowed_html() );
			} else {
				?>
                <div class="vi-ui orange message">
					<?php esc_html_e( 'No attributes currently exist.', 'woocommerce-product-variations-swatches' ) ?>
                </div>
				<?php
			}
			?>
            <div class="vi-wvps-save-sucessful-popup">
				<?php esc_html_e( 'Settings saved', 'sales-countdown-timer' ); ?>
            </div>
        </div>
		<?php
	}

	/**
	 * @param $attribute
	 * @param $attribute_name
	 */
	public function terms_settings_html( $attribute, $attribute_name ) {
		$terms = get_terms( array(
			'taxonomy'   => $attribute_name,
			'hide_empty' => false,
//			'number'     => 100,//test
//			'offset'     => 0,
		) );
		$count = count( $terms );
		if ( $count ) {
			$vi_default_colors   = $this->settings->get_default_color();
			$placeholder_img_src = wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' );
			$taxonomy_term_class = array( 'vi-wpvs-accordion-term-wrap-wrap' );
			$loader              = '';
			$table_class         = array( 'vi-wpvs-attribute-terms-list-table' );
			if ( $count > 25 ) {
				/*Only paginate if an attribute has more than 25 values*/
				$table_class[] = 'vi-wpvs-attribute-terms-list-data-table';
				$table_class[] = 'vi-wpvs-hidden';
				$loader        = '<div class="vi-ui active inverted dimmer"><div class="vi-ui text loader">' . esc_html__( 'Loading', 'woocommerce-product-variations-swatches' ) . '</div></div>';
			}
			?>
            <div class="<?php echo esc_attr( implode( ' ', $taxonomy_term_class ) ) ?>">
				<?php echo $loader; ?>
                <table class="vi-ui celled center aligned table <?php echo esc_attr( implode( ' ', $table_class ) ) ?>">
                    <thead>
                    <tr>
                        <th style="width: 1%"><?php esc_html_e( 'Term', 'woocommerce-product-variations-swatches' ); ?></th>
                        <th><?php esc_html_e( 'Term Settings', 'woocommerce-product-variations-swatches' ); ?></th>
                    </tr>
                    </thead>
                    <tbody>
					<?php
					foreach ( $terms as $term ) {
						$this->terms_settings_html_row( $term, $placeholder_img_src, $vi_default_colors );
					}
					?>
                    </tbody>
                </table>
            </div>
			<?php
		}
	}

	private function terms_settings_html_row( $term, $placeholder_img_src, $vi_default_colors ) {
		$vi_wpvs_terms_settings = get_term_meta( $term->term_id, 'vi_wpvs_terms_params', true );
		$term_class             = 'vi-wpvs-accordion-wrap vi-wpvs-accordion-term-wrap vi-wpvs-accordion-term-wrap-' . $term->term_id;
		$terms_color_separator  = $vi_wpvs_terms_settings['color_separator'] ?? '1';
		$terms_colors           = $vi_wpvs_terms_settings['color'] ?? array();
		$terms_img_id           = $vi_wpvs_terms_settings['img_id'] ?? '';
		$terms_img_src          = $terms_img_id ? wp_get_attachment_image_url( $terms_img_id, 'woocommerce_thumbnail', true ) : $placeholder_img_src;
		$title_class            = array( 'title' );
		if ( is_rtl() ) {
			$title_class[] = 'right';
		} else {
			$title_class[] = 'left';
		}
		$title_class[] = 'aligned';
		?>
        <tr class="<?php echo esc_attr( $term_class ); ?>"
            data-term_id="<?php echo esc_attr( $term->term_id ); ?>">
            <td><?php echo esc_html( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) ); ?></td>
            <td>
                <div class="vi-ui styled fluid accordion ">
                    <input type="hidden" name="term_id"
                           value="<?php echo esc_attr( $term->term_id ); ?>">
                    <div class="<?php echo esc_attr( implode( ' ', $title_class ) ); ?>">
                        <i class="dropdown icon"></i>
                        <span class="vi-wpvs-attribute-value-image-config">
                            <?php esc_html_e( 'Image', 'woocommerce-product-variations-swatches' ) ?>
                            <span class="vi-wpvs-attribute-value-image-preview"></span>
                        </span>
                    </div>
                    <div class="content">
                        <div class="vi-wpvs-attribute-value-content-wrap vi-wpvs-attribute-value-content-image-wrap">
                            <input type="hidden"
                                   name="vi_attribute_images"
                                   class="vi_attribute_image"
                                   value="<?php echo esc_attr( $terms_img_id ); ?>">
                            <div class="vi-attribute-image-wrap vi-attribute-edit-image-wrap vi-wpvs-term-image-upload-img">
                                <span class="vi-attribute-edit-image-preview vi-attribute-image-preview">
                                    <img src="<?php echo esc_attr( esc_url( $terms_img_src ) ); ?>"
                                         data-src_placeholder="<?php echo esc_attr( $placeholder_img_src ); ?>">
                                </span>
                                <i class="vi-attribute-image-remove times circle outline icon<?php echo $terms_img_id ? '' : esc_attr( ' vi-wpvs-hidden' ); ?>"></i>
                                <div class="vi-attribute-image-add-new"><?php esc_html_e( 'Upload/Add an image', 'woocommerce-product-variations-swatches' ); ?></div>
                            </div>
                            <p class="description">
								<?php esc_html_e( 'Choose an image', 'woocommerce-product-variations-swatches' ); ?>
                            </p>
                        </div>
                    </div>
                    <div class="<?php echo esc_attr( implode( ' ', $title_class ) ); ?>">
                        <i class="dropdown icon"></i>
                        <span class="vi-wpvs-attribute-value-color-config">
                            <?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ) ?>
                            <span class="vi-wpvs-attribute-value-color-preview"></span>
                        </span>
                    </div>
                    <div class="content">
                        <div class="vi-wpvs-attribute-value-content-wrap vi-wpvs-attribute-value-content-color-wrap">
                            <table class="form-table">
                                <tbody>
                                <tr>
                                    <td>
										<?php esc_html_e( 'Color separator', 'woocommerce-product-variations-swatches' ); ?>
                                    </td>
                                    <td>
                                        <select name="vi_attribute_color_separator"
                                                id="vi_attribute_color_separator_<?php echo esc_attr( $term->term_id ); ?>"
                                                class="vi-ui fluid dropdown vi_attribute_color_separator">
                                            <option value="1" <?php selected( $terms_color_separator, '1' ) ?>>
												<?php esc_html_e( 'Basic horizontal', 'woocommerce-product-variations-swatches' ); ?>
                                            </option>
                                            <option value="2" <?php selected( $terms_color_separator, '2' ) ?>>
												<?php esc_html_e( 'Basic vertical', 'woocommerce-product-variations-swatches' ); ?>
                                            </option>
                                            <option value="3" <?php selected( $terms_color_separator, '3' ) ?>>
												<?php esc_html_e( 'Basic diagonal left', 'woocommerce-product-variations-swatches' ); ?>
                                            </option>
                                            <option value="4" <?php selected( $terms_color_separator, '4' ) ?>>
												<?php esc_html_e( 'Basic diagonal right', 'woocommerce-product-variations-swatches' ); ?>
                                            </option>
                                            <option value="5" <?php selected( $terms_color_separator, '5' ) ?>>
												<?php esc_html_e( 'Hard lines horizontal', 'woocommerce-product-variations-swatches' ); ?>
                                            </option>
                                            <option value="6" <?php selected( $terms_color_separator, '6' ) ?>>
												<?php esc_html_e( 'Hard lines vertical', 'woocommerce-product-variations-swatches' ); ?>
                                            </option>
                                            <option value="7" <?php selected( $terms_color_separator, '7' ) ?>>
												<?php esc_html_e( 'Hard lines diagonal left', 'woocommerce-product-variations-swatches' ); ?>
                                            </option>
                                            <option value="8" <?php selected( $terms_color_separator, '8' ) ?>>
												<?php esc_html_e( 'Hard lines diagonal right', 'woocommerce-product-variations-swatches' ); ?>
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
										<?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?>
                                    </td>
                                    <td>
                                        <table class="form-table vi-wpvs-table vi-wpvs-attribute-value-content-color-table">
                                            <tr class="vi-wpvs-table-head">
                                                <th><?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?></th>
                                                <th><?php esc_html_e( 'Action', 'woocommerce-product-variations-swatches' ); ?></th>
                                            </tr>
											<?php
											if ( $terms_colors && is_array( $terms_colors ) && count( $terms_colors ) ) {
												foreach ( $terms_colors as $terms_color ) {
													?>
                                                    <tr>
                                                        <td>
                                                            <input type="text"
                                                                   class="vi-wpvs-color vi_attribute_colors"
                                                                   name="vi_attribute_colors[]"
                                                                   value="<?php echo esc_attr( $terms_color ) ?>">
                                                        </td>
                                                        <td>
                                                            <span class="vi-wpvs-term-color-action-clone vi-ui positive button">
                                                                <?php esc_html_e( 'Clone', 'woocommerce-product-variations-swatches' ); ?>
                                                            </span>
                                                            <span class="vi-wpvs-term-color-action-remove vi-ui negative button">
                                                                <?php esc_html_e( 'Remove', 'woocommerce-product-variations-swatches' ); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
													<?php
												}
											} else {
												$terms_color = $vi_default_colors[ strtolower( $term->name ) ] ?? '';
												?>
                                                <tr>
                                                    <td>
                                                        <input type="text"
                                                               class="vi-wpvs-color vi_attribute_colors"
                                                               name="vi_attribute_colors[]"
                                                               value="<?php echo esc_attr( $terms_color ) ?>">
                                                    </td>
                                                    <td>
                                                        <span class="vi-wpvs-term-color-action-clone vi-ui positive button">
                                                            <?php esc_html_e( 'Clone', 'woocommerce-product-variations-swatches' ); ?>
                                                        </span>
                                                        <span class="vi-wpvs-term-color-action-remove vi-ui negative button">
                                                            <?php esc_html_e( 'Remove', 'woocommerce-product-variations-swatches' ); ?>
                                                        </span>
                                                    </td>
                                                </tr>
												<?php
											}
											?>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
		<?php
	}

	public function admin_enqueue_scripts() {
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		if ( $page === 'woocommerce-product-variations-swatches-global-attrs' ) {
			global $wp_scripts;
			if ( isset( $wp_scripts->registered['jquery-ui-accordion'] ) ) {
				unset( $wp_scripts->registered['jquery-ui-accordion'] );
				wp_dequeue_script( 'jquery-ui-accordion' );
			}
			if ( isset( $wp_scripts->registered['accordion'] ) ) {
				unset( $wp_scripts->registered['accordion'] );
				wp_dequeue_script( 'accordion' );
			}
			$scripts = $wp_scripts->registered;
			foreach ( $scripts as $k => $script ) {
				preg_match( '/^\/wp-/i', $script->src, $result );
				if ( count( array_filter( $result ) ) ) {
					preg_match( '/^(\/wp-content\/plugins|\/wp-content\/themes)/i', $script->src, $result1 );
					if ( count( array_filter( $result1 ) ) ) {
						wp_dequeue_script( $script->handle );
					}
				} else {
					if ( $script->handle != 'query-monitor' ) {
						wp_dequeue_script( $script->handle );
					}
				}
			}
			wp_dequeue_style( 'eopa-admin-css' );
			/*Stylesheet*/
			wp_enqueue_style( 'semantic-ui-accordion', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'accordion.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-button', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'button.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-checkbox', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'checkbox.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-dropdown', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'dropdown.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-form', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'form.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-header', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'header.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-icon', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'icon.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-input', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'input.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-table', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'table.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-menu', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'menu.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-grid', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'grid.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-label', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'label.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-message', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'message.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-popup', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'popup.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-segment', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'segment.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-dimmer', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'dimmer.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-loader', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'loader.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'select2', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'select2.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'transition', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'transition.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );

			wp_enqueue_style( 'woo-product-variations-swatches-admin-attrs-attrs-css', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'admin-setting-attrs.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'woo-product-variations-swatches-admin-minicolors', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'minicolors.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );

			wp_enqueue_script( 'jquery-data-table', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'jquery.dataTables.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'semantic-ui-data-table', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'dataTables.semanticui.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );

			wp_enqueue_media();
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'semantic-ui-accordion', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'accordion.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'semantic-ui-address', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'address.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'semantic-ui-checkbox', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'checkbox.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'semantic-ui-dropdown', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'dropdown.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'semantic-ui-form', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'form.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'select2', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'select2.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'transition', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'transition.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );

			wp_enqueue_script( 'woo-product-variations-swatches-admin-attrs-js', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'admin-setting-attrs.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'woo-product-variations-swatches-admin-minicolors', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'minicolors.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			$args = array(
				'ajax_url'               => admin_url( 'admin-ajax.php' ),
				'settings_default_color' => $this->settings->get_default_color(),
				'remove_item'            => esc_html__( 'Would you want to remove this?', 'woocommerce-product-variations-swatches' ),
				'remove_last_item'       => esc_html__( 'You can not remove the last item.', 'woocommerce-product-variations-swatches' ),
				'save_all_confirm'       => esc_html__( 'Save all settings of the attribute taxonomies on this page?', 'woocommerce-product-variations-swatches' ),
				'not_found_error'        => esc_html__( 'No taxonomy found.', 'woocommerce-product-variations-swatches' ),
			);
			wp_localize_script( 'woo-product-variations-swatches-admin-attrs-js', 'viwpvs_admin_attrs_js', $args );
		}
	}
}