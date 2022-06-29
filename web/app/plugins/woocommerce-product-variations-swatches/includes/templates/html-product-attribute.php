<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$attribute_options  = $attribute->get_options();
$attribute_name     = $attribute->get_name();
$attribute_position = $attribute->get_position();
$metabox_class[]    = is_rtl() ? 'vi-wpvs-wrap-rtl' : '';
$metabox_class      = implode( ' ', $metabox_class );
?>
<div data-taxonomy="<?php echo esc_attr( $attribute->get_taxonomy() ); ?>"
     class="woocommerce_attribute vi-wpvs-attribute-wrap wc-metabox closed <?php echo esc_attr( $metabox_class ); ?>"
     rel="<?php echo esc_attr( $attribute_position ); ?>">
    <h3>
        <a href="#"
           class="remove_row delete vi-wpvs-attribute-row-remove"><?php esc_html_e( 'Remove', 'woocommerce-product-variations-swatches' ); ?></a>
        <div class="handlediv"
             title="<?php esc_attr_e( 'Click to toggle', 'woocommerce-product-variations-swatches' ); ?>"></div>
        <div class="tips sort"
             data-tip="<?php esc_attr_e( 'Drag and drop to set admin attribute order', 'woocommerce-product-variations-swatches' ); ?>"></div>
        <strong class="attribute_name"><?php echo wp_kses_post( wc_attribute_label( $attribute_name ) ); ?></strong>
    </h3>
    <div class="woocommerce_attribute_data vi-wpvs-attribute-content-wrap wc-metabox-content hidden">
		<?php
		if ( $attribute->is_taxonomy() ) {
			$vi_attribute_loop_enable     = $vi_attribute_settings['vi_attribute_loop_enable'][ $attribute_name ] ?? null;
			$vi_attribute_profile         = $vi_attribute_settings['attribute_profile'][ $attribute_name ] ?? null;
			$vi_attribute_type            = $vi_attribute_settings['attribute_type'][ $attribute_name ] ?? null;
			$vi_attribute_colors          = $vi_attribute_settings['attribute_colors'][ $attribute_name ] ?? array();
			$vi_attribute_color_separator = $vi_attribute_settings['attribute_color_separator'][ $attribute_name ] ?? array();
			$vi_attribute_img_ids         = $vi_attribute_settings['attribute_img_ids'][ $attribute_name ] ?? array();
			$vi_attribute_display_type    = $vi_attribute_settings['attribute_display_type'][ $attribute_name ] ?? null;
			$vi_change_product_image      = $vi_attribute_settings['change_product_image'][ $attribute_name ] ?? '0';
			$attribute_taxonomy           = $attribute->get_taxonomy_object();
			?>
            <div class="vi-wpvs-attribute-content vi-wpvs-attribute-content-taxonomy">
                <div class="vi-wpvs-attribute-info-wrap">
                    <div class="vi-wpvs-attribute-name">
                        <label>
							<?php esc_html_e( 'Name', 'woocommerce-product-variations-swatches' ) ?>:
                        </label>
                        <strong><?php echo wp_kses_post( wc_attribute_label( $attribute_name ) ); ?></strong>
                        <input type="hidden" name="attribute_names[<?php echo esc_attr( $i ); ?>]"
                               value="<?php echo esc_attr( $attribute_name ); ?>"/>
                        <input type="hidden" name="attribute_position[<?php echo esc_attr( $i ); ?>]"
                               class="attribute_position" value="<?php echo esc_attr( $attribute_position ); ?>"/>
                    </div>

                    <label>
                        <input type="checkbox" class="checkbox" <?php checked( $attribute->get_visible(), true ); ?>
                               name="attribute_visibility[<?php echo esc_attr( $i ); ?>]"
                               value="1"/>
						<?php esc_html_e( 'Visible on the product page', 'woocommerce-product-variations-swatches' ); ?>
                    </label>
                    <div class="enable_variation show_if_variable">
                        <label>
                            <input type="checkbox"
                                   class="checkbox" <?php checked( $attribute->get_variation(), true ); ?>
                                   name="attribute_variation[<?php echo esc_attr( $i ); ?>]" value="1"/>
							<?php esc_html_e( 'Used for variations', 'woocommerce-product-variations-swatches' ); ?>
                        </label>
                    </div>
                    <div class="vi-wpvs-attribute-info-custom-open button">
                        <span class=""><?php esc_html_e( 'Swatches settings', 'woocommerce-product-variations-swatches' ); ?></span>
                        <span class="vi-wpvs-attribute-value-action-icon vi-wpvs-attribute-value-action-icon-down dashicons dashicons-arrow-down"></span>
                        <span class="vi-wpvs-attribute-value-action-icon vi-wpvs-attribute-value-action-icon-up dashicons dashicons-arrow-up vi-wpvs-hidden"></span>
                    </div>
                </div>
                <div class="vi-wpvs-attribute-info-wrap vi-wpvs-attribute-info-custom-wrap vi-wpvs-hidden">
                    <div class="vi-wpvs-attribute-loop-enable">
						<?php
						if ( $product_list_add_to_cart ) {
							?>
                            <a class="button button-primary" disabled target="_blank"
                               title="<?php esc_attr_e( 'Disable option \'Enable add to cart\' in Swatches on Product List tab  to do display setting for the attribute on Product List pages', 'woocommerce-product-variations-swatches' ); ?>"
                               href="<?php echo esc_url( admin_url( 'admin.php?page=woocommerce-product-variations-swatches#product_list' ) ); ?>">
								<?php esc_html_e( 'Show in product list', 'woocommerce-product-variations-swatches' ); ?>
                            </a>
							<?php
						}
						?>
                        <select name="vi_attribute_loop_enable[<?php echo esc_attr( $i ); ?>]"
                                title="<?php esc_attr_e( 'Show in product list', 'woocommerce-product-variations-swatches' ); ?>"
                                class="<?php echo $product_list_add_to_cart ? esc_attr( 'vi-wpvs-hidden' ) : ''; ?>">
                            <option value="1" <?php selected( $vi_attribute_loop_enable, '1' ) ?>>
								<?php esc_html_e( 'Global Product list visibility', 'woocommerce-product-variations-swatches' ); ?>
                            </option>
                            <option value="2" <?php selected( $vi_attribute_loop_enable, '2' ) ?>>
								<?php esc_html_e( 'Show in product list', 'woocommerce-product-variations-swatches' ); ?>
                            </option>
                            <option value="3" <?php selected( $vi_attribute_loop_enable, '3' ) ?>>
								<?php esc_html_e( 'Hide in product list', 'woocommerce-product-variations-swatches' ); ?>
                            </option>
                        </select>
                    </div>
                    <div class="vi-wpvs-attribute-display-type">
                        <select name="vi_attribute_display_type[<?php echo esc_attr( $i ); ?>]"
                                title="<?php esc_attr_e( 'Choose display style', 'woocommerce-product-variations-swatches' ); ?>">
                            <option value="0" <?php selected( $vi_attribute_display_type, '0' ) ?>>
								<?php esc_html_e( 'Global Style', 'woocommerce-product-variations-swatches' ); ?>
                            </option>
                            <option value="vertical" <?php selected( $vi_attribute_display_type, 'vertical' ) ?>>
								<?php esc_html_e( 'Vertical', 'woocommerce-product-variations-swatches' ); ?>
                            </option>
                            <option value="horizontal" <?php selected( $vi_attribute_display_type, 'horizontal' ) ?>>
								<?php esc_html_e( 'Horizontal', 'woocommerce-product-variations-swatches' ); ?>
                            </option>
                        </select>
                    </div>
                    <div class="vi-wpvs-attribute-type">
                        <select name="vi_attribute_type[<?php echo esc_attr( $i ); ?>]"
                                title="<?php esc_attr_e( 'Choose display type', 'woocommerce-product-variations-swatches' ); ?>">
                            <option value="0" <?php selected( $vi_attribute_type, '0' ) ?>>
								<?php esc_html_e( 'Global Type', 'woocommerce-product-variations-swatches' ); ?>
                            </option>
							<?php
							foreach ( $attribute_types as $k => $v ) {
								?>
                                <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $vi_attribute_type, $k ) ?>><?php echo esc_html( $v ); ?></option>
								<?php
							}
							?>
                        </select>
                    </div>
                    <div class="vi-wpvs-attribute-profile">
                        <select name="vi_attribute_profile[<?php echo esc_attr( $i ); ?>]"
                                title="<?php esc_attr_e( 'Choose swatches profile', 'woocommerce-product-variations-swatches' ); ?>">
                            <option value="0" <?php selected( $vi_attribute_profile, '0' ) ?>>
								<?php esc_html_e( 'Global Profile', 'woocommerce-product-variations-swatches' ); ?>
                            </option>
							<?php
							foreach ( $vi_wpvs_ids as $k => $id ) {
								?>
                                <option value="<?php echo esc_attr( $id ) ?>" <?php selected( $vi_attribute_profile, $id ) ?>><?php echo esc_html( $vi_wpvs_name[ $k ] ); ?></option>
								<?php
							}
							?>
                        </select>
                    </div>
                    <div class="vi-wpvs-change-product-image">
                        <select class="vi-wpvs-change-product-image-select"
                                name="vi_change_product_image[<?php echo esc_attr( $i ); ?>]">
                            <option value="global" <?php selected( $vi_change_product_image, 'global' ) ?>><?php esc_html_e( 'Global Change product image', 'woocommerce-product-variations-swatches' ); ?></option>
                            <option value="attribute_image" <?php selected( $vi_change_product_image, 'attribute_image' ) ?>><?php esc_html_e( 'Change to image set for attribute', 'woocommerce-product-variations-swatches' ); ?></option>
                            <option value="variation_image" <?php selected( $vi_change_product_image, 'variation_image' ) ?>><?php esc_html_e( 'Auto detect variation image', 'woocommerce-product-variations-swatches' ); ?></option>
                            <option value="not_change" <?php selected( $vi_change_product_image, 'not_change' ) ?>><?php esc_html_e( 'Not change', 'woocommerce-product-variations-swatches' ); ?></option>
                        </select>
						<?php
						echo wc_help_tip( esc_html__( 'When selecting an attribute value, change product image according to attribute/variation image(Only use for Image/Variation image type)', 'woocommerce-product-variations-swatches' ) );
						?>
                    </div>
                </div>
                <div class="vi-wpvs-attribute-value-wrap-wrap" data-index="<?php echo esc_attr( $i ); ?>"
                     data-attribute_name="<?php echo esc_attr( $attribute_name ); ?>">
					<?php
					$args      = array(
						'orderby'    => isset( $attribute_taxonomy->attribute_orderby ) ? $attribute_taxonomy->attribute_orderby : 'name',
						'hide_empty' => 0,
					);
					$all_terms = get_terms( $attribute->get_taxonomy(), apply_filters( 'woocommerce_product_attribute_terms', $args ) );
					if ( $all_terms ) {
						if ( count( $all_terms ) > 30 ) {
							foreach ( $attribute_options as $option ) {
								$term = get_term( $option );
								if ( ! $term ) {
									continue;
								}
								$vi_wpvs_terms_settings = ! empty( get_term_meta( $option, 'vi_wpvs_terms_params', true ) ) ? get_term_meta( $option, 'vi_wpvs_terms_params', true ) : array();
								$terms_color_separator  = $vi_attribute_color_separator[ $option ] ?? $vi_wpvs_terms_settings['color_separator'] ?? '1';
								$terms_colors           = $vi_attribute_colors[ $option ] ?? $vi_wpvs_terms_settings['color'] ?? '';
								$terms_img_id           = $vi_attribute_img_ids[ $option ] ?? $vi_wpvs_terms_settings['img_id'] ?? '';
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
						} else {
							foreach ( $all_terms as $k => $term ) {
								$selected               = in_array( $term->term_id, $attribute_options );
								$vi_wpvs_terms_settings = ! empty( get_term_meta( $term->term_id, 'vi_wpvs_terms_params', true ) ) ? get_term_meta( $term->term_id, 'vi_wpvs_terms_params', true ) : array();
								$terms_color_separator  = $vi_attribute_color_separator[ $term->term_id ] ?? $vi_wpvs_terms_settings['color_separator'] ?? '1';
								$terms_colors           = $vi_attribute_colors[ $term->term_id ] ?? $vi_wpvs_terms_settings['color'] ?? '';
								$terms_img_id           = $vi_attribute_img_ids[ $term->term_id ] ?? $vi_wpvs_terms_settings['img_id'] ?? '';
								wc_get_template( 'html-global-attribute-item.php',
									array(
										'selected'              => $selected,
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
						}
					}
					?>
                    <p class="vi-wpvs-attribute-taxonomy-action">
                        <span class="vi-wpvs-attribute-taxonomy-select-all button button-small"
                              data-total_term="<?php echo esc_attr( count( $all_terms ) ); ?>">
                            <?php esc_html_e( 'Select all', 'woocommerce-product-variations-swatches' ) ?>
                        </span>
                        <span class="vi-wpvs-attribute-taxonomy-select-none button button-small">
                            <?php esc_html_e( 'Select none', 'woocommerce-product-variations-swatches' ) ?></span>
                        <span class="vi-wpvs-attribute-taxonomy-add-new button button-small <?php echo count( $all_terms ) ? '' : esc_attr( 'disabled' ); ?>">
                            <?php esc_html_e( 'Add', 'woocommerce-product-variations-swatches' ); ?></span>
                        <span class="vi-wpvs-attribute-taxonomy-create button button-small">
                            <?php esc_html_e( 'Add new', 'woocommerce-product-variations-swatches' ); ?></span>
                    </p>
                    <div class="vi-wpvs-attribute-taxonomy-add-new-term-wrap vi-wpvs-attribute-edit-wrap-wrap vi-wpvs-hidden">
                        <div class="vi-wpvs-attribute-edit-overlay"></div>
                        <div class="vi-wpvs-attribute-edit-wrap">
                            <div class="vi-wpvs-attribute-edit-content-wrap">
                                <div class="vi-wpvs-attribute-edit-content">
                                    <div class="vi-wpvs-attribute-edit-content-row-wrap vi-wpvs-attribute-edit-taxonomy-add-new-term">
                                        <label for="vi-wpvs-attribute-edit-profile"><?php esc_html_e( 'Select term', 'woocommerce-product-variations-swatches' ); ?></label>
                                        <select class="vi-wpvs-taxonomy-add-new-term" multiple>
											<?php
											if ( $all_terms ) {
												foreach ( $all_terms as $term ) {
													?>
                                                    <option value="<?php echo esc_attr( $term->term_id ); ?>">
														<?php echo esc_html( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) ) ?>
                                                    </option>
													<?php
												}
											}
											?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="vi-wpvs-attribute-edit-buttons">
                                <div class="vi-wpvs-attribute-edit-button vi-wpvs-attribute-edit-button-ok primary button"><?php esc_html_e( 'OK', 'woocommerce-product-variations-swatches' ) ?></div>
                                <div class="vi-wpvs-attribute-edit-button vi-wpvs-attribute-edit-button-cancel  button"><?php esc_html_e( 'Cancel', 'woocommerce-product-variations-swatches' ) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
				do_action( 'woocommerce_product_option_terms', $attribute_taxonomy, $i, $attribute );
				?>
            </div>
			<?php
		} else {
			$attribute_name_              = html_entity_decode( $attribute_name, ENT_NOQUOTES, 'UTF-8' );
			$vi_attribute_loop_enable     = $vi_attribute_settings['vi_attribute_loop_enable'][ $attribute_name_ ] ?? null;
			$vi_attribute_profile         = $vi_attribute_settings['attribute_profile'][ $attribute_name_ ] ?? null;
			$vi_attribute_type            = $vi_attribute_settings['attribute_type'][ $attribute_name_ ] ?? null;
			$vi_attribute_colors          = $vi_attribute_settings['attribute_colors'][ $attribute_name_ ] ?? array();
			$vi_attribute_color_separator = $vi_attribute_settings['attribute_color_separator'][ $attribute_name_ ] ?? array();
			$vi_attribute_img_ids         = $vi_attribute_settings['attribute_img_ids'][ $attribute_name_ ] ?? array();
			$vi_attribute_display_type    = $vi_attribute_settings['attribute_display_type'][ $attribute_name_ ] ?? null;
			$vi_change_product_image      = $vi_attribute_settings['change_product_image'][ $attribute_name_ ] ?? '0';
			?>
            <div class="vi-wpvs-attribute-content">
                <div class="vi-wpvs-attribute-info-wrap">
                    <div class="vi-wpvs-attribute-name">
                        <input type="text" class="attribute_name vi-attribute-name"
                               name="attribute_names[<?php echo esc_attr( $i ); ?>]"
                               value="<?php echo esc_attr( $attribute_name ); ?>"
                               placeholder="<?php esc_attr_e( 'Name', 'woocommerce-product-variations-swatches' ); ?>"/>
                        <input type="hidden" name="attribute_position[<?php echo esc_attr( $i ); ?>]"
                               class="attribute_position" value="<?php echo esc_attr( $attribute_position ); ?>"/>
                    </div>
                    <label>
                        <input type="checkbox" class="checkbox" <?php checked( $attribute->get_visible(), true ); ?>
                               name="attribute_visibility[<?php echo esc_attr( $i ); ?>]"
                               value="1"/>
						<?php esc_html_e( 'Visible on the product page', 'woocommerce-product-variations-swatches' ); ?>
                    </label>
                    <div class="enable_variation show_if_variable">
                        <label>
                            <input type="checkbox" class="checkbox" <?php checked( $attribute->get_variation(),
								true ); ?> name="attribute_variation[<?php echo esc_attr( $i ); ?>]" value="1"/>
							<?php esc_html_e( 'Used for variations', 'woocommerce-product-variations-swatches' ); ?>
                        </label>
                    </div>
                    <div class="vi-wpvs-attribute-info-custom-open button">
                        <span class=""><?php esc_html_e( 'Swatches settings', 'woocommerce-product-variations-swatches' ); ?></span>
                        <span class="vi-wpvs-attribute-value-action-icon vi-wpvs-attribute-value-action-icon-down dashicons dashicons-arrow-down"></span>
                        <span class="vi-wpvs-attribute-value-action-icon vi-wpvs-attribute-value-action-icon-up dashicons dashicons-arrow-up vi-wpvs-hidden"></span>
                    </div>
                </div>
                <div class="vi-wpvs-attribute-info-wrap vi-wpvs-attribute-info-custom-wrap vi-wpvs-hidden">
                    <div class="vi-wpvs-attribute-loop-enable">
						<?php
						if ( $product_list_add_to_cart ) {
							?>
                            <a class="button button-primary" disabled target="_blank"
                               title="<?php esc_attr_e( 'Disable option \'Enable add to cart\' in Swatches on Product List tab  to do display setting for the attribute on Product List pages', 'woocommerce-product-variations-swatches' ); ?>"
                               href="<?php echo esc_url( admin_url( 'admin.php?page=woocommerce-product-variations-swatches#product_list' ) ); ?>">
								<?php esc_html_e( 'Show in product list', 'woocommerce-product-variations-swatches' ); ?>
                            </a>
							<?php
						}
						?>
                        <select name="vi_attribute_loop_enable[<?php echo esc_attr( $i ); ?>]"
                                class="<?php echo $product_list_add_to_cart ? esc_attr( 'vi-wpvs-hidden' ) : ''; ?>"
                                title="<?php esc_attr_e( 'Show in product list', 'woocommerce-product-variations-swatches' ); ?>">
                            <option value="1" <?php selected( $vi_attribute_loop_enable, '1' ) ?>>
								<?php esc_html_e( 'Global Product list visibility', 'woocommerce-product-variations-swatches' ); ?>
                            </option>
                            <option value="2" <?php selected( $vi_attribute_loop_enable, '2' ) ?>>
								<?php esc_html_e( 'Show in product list', 'woocommerce-product-variations-swatches' ); ?>
                            </option>
                            <option value="3" <?php selected( $vi_attribute_loop_enable, '3' ) ?>>
								<?php esc_html_e( 'Hide in product list', 'woocommerce-product-variations-swatches' ); ?>
                            </option>
                        </select>
                    </div>
                    <div class="vi-wpvs-attribute-display-type">
                        <select name="vi_attribute_display_type[<?php echo esc_attr( $i ); ?>]"
                                title="<?php esc_attr_e( 'Choose display style', 'woocommerce-product-variations-swatches' ); ?>">
                            <option value="0" <?php selected( $vi_attribute_display_type, '0' ) ?>>
								<?php esc_html_e( 'Global Style', 'woocommerce-product-variations-swatches' ); ?>
                            </option>
                            <option value="vertical" <?php selected( $vi_attribute_display_type, 'vertical' ) ?>>
								<?php esc_html_e( 'Vertical', 'woocommerce-product-variations-swatches' ); ?>
                            </option>
                            <option value="horizontal" <?php selected( $vi_attribute_display_type, 'horizontal' ) ?>>
								<?php esc_html_e( 'Horizontal', 'woocommerce-product-variations-swatches' ); ?>
                            </option>
                        </select>
                    </div>
                    <div class="vi-wpvs-attribute-type">
                        <select name="vi_attribute_type[<?php echo esc_attr( $i ); ?>]"
                                title="<?php esc_attr_e( 'Choose display type', 'woocommerce-product-variations-swatches' ); ?>">
                            <option value="0" <?php selected( $vi_attribute_type, '0' ) ?>>
								<?php esc_html_e( 'Global Type', 'woocommerce-product-variations-swatches' ); ?>
                            </option>
							<?php
							foreach ( $attribute_types as $k => $v ) {
								?>
                                <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $vi_attribute_type, $k ) ?>><?php echo esc_html( $v ); ?></option>
								<?php
							}
							?>
                        </select>
                    </div>
                    <div class="vi-wpvs-attribute-profile">
                        <select name="vi_attribute_profile[<?php echo esc_attr( $i ); ?>]"
                                title="<?php esc_attr_e( 'Choose swatches profile', 'woocommerce-product-variations-swatches' ); ?>">
                            <option value="0" <?php selected( $vi_attribute_profile, '0' ) ?>>
								<?php esc_html_e( 'Global Profile', 'woocommerce-product-variations-swatches' ); ?>
                            </option>
							<?php
							foreach ( $vi_wpvs_ids as $k => $id ) {
								?>
                                <option value="<?php echo esc_attr( $id ) ?>" <?php selected( $vi_attribute_profile, $id ) ?>><?php echo esc_html( $vi_wpvs_name[ $k ] ); ?></option>
								<?php
							}
							?>
                        </select>
                    </div>
                    <div class="vi-wpvs-change-product-image">
                        <select class="vi-wpvs-change-product-image-select"
                                name="vi_change_product_image[<?php echo esc_attr( $i ); ?>]">
                            <option value="global" <?php selected( $vi_change_product_image, 'global' ) ?>><?php esc_html_e( 'Global Change product image', 'woocommerce-product-variations-swatches' ); ?></option>
                            <option value="attribute_image" <?php selected( $vi_change_product_image, 'attribute_image' ) ?>><?php esc_html_e( 'Change to image set for attribute', 'woocommerce-product-variations-swatches' ); ?></option>
                            <option value="variation_image" <?php selected( $vi_change_product_image, 'variation_image' ) ?>><?php esc_html_e( 'Auto detect variation image', 'woocommerce-product-variations-swatches' ); ?></option>
                            <option value="not_change" <?php selected( $vi_change_product_image, 'not_change' ) ?>><?php esc_html_e( 'Not change', 'woocommerce-product-variations-swatches' ); ?></option>
                        </select>
						<?php
						echo wc_help_tip( esc_html__( 'When selecting an attribute value, change product image according to attribute/variation image(Only use for Image/Variation image type)', 'woocommerce-product-variations-swatches' ) );
						?>
                    </div>
                </div>
                <div class="vi-wpvs-attribute-value-wrap-wrap">
					<?php
					if ( $attribute_options && is_array( $attribute_options ) && count( $attribute_options ) ) {
						foreach ( $attribute_options as $k => $attribute_option ) {
							$attribute_color_separator = $vi_attribute_color_separator[ $k ] ?? '1';
							$attribute_colors          = $vi_attribute_colors[ $k ] ?? array();
							$attribute_img_id          = $vi_attribute_img_ids[ $k ] ?? '';
							$attribute_colors_id       = current_time( 'timestamp' ) . '-' . $k;
							$attribute_img_src         = $attribute_img_id ? wp_get_attachment_image_url( $attribute_img_id, 'woocommerce_thumbnail', true ) : wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' );
							?>
                            <div class="vi-wpvs-attribute-value-wrap"
                                 data-attribute_number="<?php echo esc_attr( $i ); ?>">
                                <div class="vi-wpvs-attribute-value-title-wrap vi-wpvs-attribute-value-title-toggle">
                                    <div class="vi-wpvs-attribute-value-action-wrap">
                                        <span class="vi-wpvs-attribute-value-action-icon vi-wpvs-attribute-value-action-icon-down dashicons dashicons-arrow-down"></span>
                                        <span class="vi-wpvs-attribute-value-action-icon vi-wpvs-attribute-value-action-icon-up dashicons dashicons-arrow-up vi-wpvs-hidden"></span>
                                        <span class="vi-wpvs-attribute-value-action-clone button button-small"><?php esc_html_e( 'Clone', 'woocommerce-product-variations-swatches' ); ?></span>
                                        <span class="vi-wpvs-attribute-value-action-remove button button-small"><?php esc_html_e( 'Remove', 'woocommerce-product-variations-swatches' ); ?></span>
                                    </div>
                                    <input type="text" class="vi-wpvs-attribute-value-name"
                                           name="attribute_values[<?php echo esc_attr( $i ); ?>][]"
                                           value="<?php echo esc_attr( $attribute_option ); ?>"
                                           placeholder="<?php esc_attr_e( 'Name', 'woocommerce-product-variations-swatches' ); ?>"/>
                                    <div class="vi-wvps-clear-both"></div>
                                </div>
                                <div class="vi-wpvs-attribute-value-content-wrap vi-wpvs-attribute-value-content-close">
                                    <table cellpadding="0" cellspacing="0">
                                        <tbody>
                                        <tr class="vi-wpvs-attribute-value-content-image-wrap">
                                            <td>
												<?php
												esc_html_e( 'Image', 'woocommerce-product-variations-swatches' );
												echo wc_help_tip( esc_html__( 'Image can also be used for "Change product image" option if attribute type is not Image', 'woocommerce-product-variations-swatches' ) );
												?>
                                            </td>
                                            <td>
                                                <input type="hidden"
                                                       name="vi_attribute_images[<?php echo esc_attr( $i ); ?>][]"
                                                       class="vi_attribute_image"
                                                       value="<?php echo esc_attr( $attribute_img_id ); ?>">
                                                <div class="vi-attribute-image-wrap vi-attribute-edit-image-wrap vi-wpvs-term-image-upload-img">
                                                        <span class="vi-attribute-edit-image-preview vi-attribute-image-preview">
                                                            <img src="<?php echo esc_attr( esc_url( $attribute_img_src ) ); ?>"
                                                                 data-src_placeholder="<?php echo esc_attr( wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' ) ); ?>">
                                                        </span>
                                                    <span class="vi-attribute-image-remove dashicons dashicons-dismiss<?php echo $attribute_img_id ? '' : esc_attr( ' vi-wpvs-hidden' ); ?>"></span>
                                                    <div class="vi-attribute-image-add-new"><?php esc_html_e( 'Upload/Add an image', 'woocommerce-product-variations-swatches' ); ?></div>
                                                </div>
                                                <p class="description">
													<?php esc_html_e( 'Choose an image', 'woocommerce-product-variations-swatches' ); ?>
                                                </p>
                                            </td>
                                        </tr>
                                        <tr class="vi-wpvs-attribute-value-content-color-wrap">
                                            <td>
												<?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?>
                                            </td>
                                            <td>
                                                <p><?php esc_html_e( 'Color separator', 'woocommerce-product-variations-swatches' ); ?>
                                                    <select name="vi_attribute_color_separator[<?php echo esc_attr( $i ); ?>][]"
                                                            class="vi_attribute_color_separator">
                                                        <option value="1" <?php selected( $attribute_color_separator, '1' ) ?>>
															<?php esc_html_e( 'Basic horizontal', 'woocommerce-product-variations-swatches' ); ?>
                                                        </option>
                                                        <option value="2" <?php selected( $attribute_color_separator, '2' ) ?>>
															<?php esc_html_e( 'Basic vertical', 'woocommerce-product-variations-swatches' ); ?>
                                                        </option>
                                                        <option value="3" <?php selected( $attribute_color_separator, '3' ) ?>>
															<?php esc_html_e( 'Basic diagonal left', 'woocommerce-product-variations-swatches' ); ?>
                                                        </option>
                                                        <option value="4" <?php selected( $attribute_color_separator, '4' ) ?>>
															<?php esc_html_e( 'Basic diagonal right', 'woocommerce-product-variations-swatches' ); ?>
                                                        </option>
                                                        <option value="5" <?php selected( $attribute_color_separator, '5' ) ?>>
															<?php esc_html_e( 'Hard lines horizontal', 'woocommerce-product-variations-swatches' ); ?>
                                                        </option>
                                                        <option value="6" <?php selected( $attribute_color_separator, '6' ) ?>>
															<?php esc_html_e( 'Hard lines vertical', 'woocommerce-product-variations-swatches' ); ?>
                                                        </option>
                                                        <option value="7" <?php selected( $attribute_color_separator, '7' ) ?>>
															<?php esc_html_e( 'Hard lines diagonal left', 'woocommerce-product-variations-swatches' ); ?>
                                                        </option>
                                                        <option value="8" <?php selected( $attribute_color_separator, '8' ) ?>>
															<?php esc_html_e( 'Hard lines diagonal right', 'woocommerce-product-variations-swatches' ); ?>
                                                        </option>
                                                    </select>
                                                </p>
                                                <table cellspacing="0" cellpadding="0"
                                                       class="vi-wpvs-attribute-value-content-color-table">
                                                    <tr>
                                                        <th><?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?></th>
                                                        <th><?php esc_html_e( 'Action', 'woocommerce-product-variations-swatches' ); ?></th>
                                                    </tr>
													<?php
													if ( $attribute_colors && is_array( $attribute_colors ) && count( $attribute_colors ) ) {
														foreach ( $attribute_colors as $attribute_color ) {
															?>
                                                            <tr>
                                                                <td>
                                                                    <input type="text"
                                                                           class="vi-wpvs-color vi_attribute_colors"
                                                                           name="vi_attribute_colors[<?php echo esc_attr( $i ); ?>][<?php echo esc_attr( $attribute_colors_id ); ?>][]"
                                                                           value="<?php echo esc_attr( $attribute_color ) ?>">
                                                                </td>
                                                                <td>
                                                                    <span class="vi-wpvs-attribute-colors-action-clone button button-primary button-small"">
																	<?php esc_html_e( 'Clone', 'woocommerce-product-variations-swatches' ) ?>
                                                                    </span>
                                                                    <span class="vi-wpvs-attribute-colors-action-remove button button-secondary delete button-small">
                                                                             <?php esc_html_e( 'Remove', 'woocommerce-product-variations-swatches' ) ?>
                                                                        </span>
                                                                </td>
                                                            </tr>
															<?php
														}
													} else {
														$attribute_color = $vi_default_colors[ strtolower( $attribute_option ) ] ?? '';
														?>
                                                        <tr>
                                                            <td>
                                                                <input type="text"
                                                                       class="vi-wpvs-color vi_attribute_colors"
                                                                       name="vi_attribute_colors[<?php echo esc_attr( $i ); ?>][<?php echo esc_attr( $attribute_colors_id ); ?>][]"
                                                                       value="<?php echo esc_attr( $attribute_color ) ?>">
                                                            </td>
                                                            <td>
                                                                <span class="vi-wpvs-attribute-colors-action-clone button button-primary button-small"">
																<?php esc_html_e( 'Clone', 'woocommerce-product-variations-swatches' ) ?>
                                                                </span>
                                                                <span class="vi-wpvs-attribute-colors-action-remove button button-secondary delete button-small">
                                                                        <?php esc_html_e( 'Remove', 'woocommerce-product-variations-swatches' ) ?>
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
							<?php
						}
					} else {
						?>
                        <div class="vi-wpvs-attribute-value-wrap" data-attribute_number="<?php echo esc_attr( $i ); ?>">
                            <div class="vi-wpvs-attribute-value-title-wrap vi-wpvs-attribute-value-title-toggle">
                                <div class="vi-wpvs-attribute-value-action-wrap">
                                    <span class="vi-wpvs-attribute-value-action-icon vi-wpvs-attribute-value-action-icon-down dashicons dashicons-arrow-down vi-wpvs-hidden"></span>
                                    <span class="vi-wpvs-attribute-value-action-icon vi-wpvs-attribute-value-action-icon-up dashicons dashicons-arrow-up vi-wpvs-hidden"></span>
                                    <span class="vi-wpvs-attribute-value-action-clone button button-small"><?php esc_html_e( 'Clone', 'woocommerce-product-variations-swatches' ); ?></span>
                                    <span class="vi-wpvs-attribute-value-action-remove button button-small"><?php esc_html_e( 'Remove', 'woocommerce-product-variations-swatches' ); ?></span>
                                </div>
                                <input type="text" class="vi-wpvs-attribute-value-name"
                                       name="attribute_values[<?php echo esc_attr( $i ); ?>][]"
                                       placeholder="<?php esc_attr_e( 'Name', 'woocommerce-product-variations-swatches' ); ?>"/>
                                <div class="vi-wvps-clear-both"></div>
                            </div>
                            <div class="vi-wpvs-attribute-value-content-wrap vi-wpvs-attribute-value-content-close">
                                <table cellpadding="0" cellspacing="0">
                                    <tbody>
                                    <tr class="vi-wpvs-attribute-value-content-image-wrap">
                                        <td>
											<?php
											esc_html_e( 'Image', 'woocommerce-product-variations-swatches' );
											echo wc_help_tip( esc_html__( 'Image can also be used for "Change product image" option if attribute type is not Image', 'woocommerce-product-variations-swatches' ) );
											?>
                                        </td>
                                        <td>
                                            <input type="hidden"
                                                   name="vi_attribute_images[<?php echo esc_attr( $i ); ?>][]"
                                                   class="vi_attribute_image">
                                            <div class="vi-attribute-image-wrap vi-attribute-edit-image-wrap vi-wpvs-term-image-upload-img">
                                                        <span class="vi-attribute-edit-image-preview vi-attribute-image-preview">
                                                            <img src="<?php echo esc_attr( esc_url( wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' ) ) ); ?>"
                                                                 data-src_placeholder="<?php echo esc_attr( wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' ) ); ?>">
                                                        </span>
                                                <span class="vi-attribute-image-remove vi-wpvs-hidden dashicons dashicons-dismiss"></span>
                                                <div class="vi-attribute-image-add-new"><?php esc_html_e( 'Upload/Add an image', 'woocommerce-product-variations-swatches' ); ?></div>
                                            </div>
                                            <p class="description">
												<?php esc_html_e( 'Choose an image', 'woocommerce-product-variations-swatches' ); ?>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr class="vi-wpvs-attribute-value-content-color-wrap">
                                        <td>
											<?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?>
                                        </td>
                                        <td>
                                            <p>
												<?php esc_html_e( 'Color separator', 'woocommerce-product-variations-swatches' ); ?>
                                                <select name="vi_attribute_color_separator[<?php echo esc_attr( $i ); ?>][]"
                                                        class="vi_attribute_color_separator">
                                                    <option value="1">
														<?php esc_html_e( 'Basic horizontal', 'woocommerce-product-variations-swatches' ); ?>
                                                    </option>
                                                    <option value="2">
														<?php esc_html_e( 'Basic vertical', 'woocommerce-product-variations-swatches' ); ?>
                                                    </option>
                                                    <option value="3">
														<?php esc_html_e( 'Basic diagonal left', 'woocommerce-product-variations-swatches' ); ?>
                                                    </option>
                                                    <option value="4">
														<?php esc_html_e( 'Basic diagonal right', 'woocommerce-product-variations-swatches' ); ?>
                                                    </option>
                                                    <option value="5">
														<?php esc_html_e( 'Hard lines horizontal', 'woocommerce-product-variations-swatches' ); ?>
                                                    </option>
                                                    <option value="6">
														<?php esc_html_e( 'Hard lines vertical', 'woocommerce-product-variations-swatches' ); ?>
                                                    </option>
                                                    <option value="7">
														<?php esc_html_e( 'Hard lines diagonal left', 'woocommerce-product-variations-swatches' ); ?>
                                                    </option>
                                                    <option value="8">
														<?php esc_html_e( 'Hard lines diagonal right', 'woocommerce-product-variations-swatches' ); ?>
                                                    </option>
                                                </select>
                                            </p>
                                            <table cellspacing="0" cellpadding="0"
                                                   class="vi-wpvs-attribute-value-content-color-table">
                                                <tr>
                                                    <th><?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?></th>
                                                    <th><?php esc_html_e( 'Action', 'woocommerce-product-variations-swatches' ); ?></th>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="text"
                                                               class="vi-wpvs-color vi_attribute_colors"
                                                               name="vi_attribute_colors[<?php echo esc_attr( $i ); ?>][0][]"
                                                               value="">
                                                    </td>
                                                    <td>
                                                        <span class="vi-wpvs-attribute-colors-action-clone button button-primary button-small"">
														<?php esc_html_e( 'Clone', 'woocommerce-product-variations-swatches' ) ?>
                                                        </span>
                                                        <span class="vi-wpvs-attribute-colors-action-remove button button-secondary delete button-small">
                                                                        <?php esc_html_e( 'Remove', 'woocommerce-product-variations-swatches' ) ?>
                                                                    </span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
						<?php
					}
					?>
                </div>
            </div>
			<?php
		}
		?>
    </div>
	<?php do_action( 'woocommerce_after_product_attribute_settings', $attribute, $i ); ?>
</div>