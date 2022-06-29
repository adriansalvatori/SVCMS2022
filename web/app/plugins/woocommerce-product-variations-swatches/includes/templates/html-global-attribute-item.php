<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$term_class                        = 'vi-wpvs-attribute-value-wrap vi-wpvs-attribute-taxonomy-value-wrap vi-wpvs-attribute-taxonomy-value-wrap-' . $term->term_id;
$term_class                        .= $selected ? '' : ' vi-wpvs-hidden';
$attribute_values_name             = 'attribute_values[' . $i . '][]';
$vi_attribute_color_separator_name = 'vi_attribute_color_separator[' . $i . '][' . $term->term_id . ']';
$terms_color_name                  = 'vi_attribute_colors[' . $i . '][' . $term->term_id . '][]';
$vi_attribute_images               = 'vi_attribute_images[' . $i . '][' . $term->term_id . ']';
$can_edit                          = in_array( $vi_attribute_type, [ 'image', 'color' ] );
$term_img_src                      = $terms_img_id ? wp_get_attachment_image_url( $terms_img_id, 'woocommerce_thumbnail', true ) : wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' );
?>
<div class="<?php echo esc_attr( $term_class ); ?>"
     data-attribute_number="<?php echo esc_attr( $i ); ?>"
     data-term_id="<?php echo esc_attr( $term->term_id ); ?>">
    <input type="hidden" class="vi_wpvs_attribute_values"
           name="<?php echo $selected ? esc_attr( $attribute_values_name ) : ''; ?>"
           data-name="<?php echo esc_attr( $attribute_values_name ); ?>"
           value="<?php echo esc_attr( $term->term_id ); ?>">
    <div class="vi-wpvs-attribute-value-title-wrap vi-wpvs-attribute-value-title-toggle">
        <div class="vi-wpvs-attribute-value-action-wrap">
            <span class="vi-wpvs-attribute-value-action-icon vi-wpvs-attribute-value-action-icon-down dashicons dashicons-arrow-down"></span>
            <span class="vi-wpvs-attribute-value-action-icon vi-wpvs-attribute-value-action-icon-up dashicons dashicons-arrow-up vi-wpvs-hidden"></span>
            <span class="vi-wpvs-attribute-value-action-remove button button-small"><?php esc_html_e( 'Remove', 'woocommerce-product-variations-swatches' ); ?></span>
        </div>
        <span class="vi-wpvs-attribute-value-name">
            <?php echo esc_html( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) ); ?>
        </span>
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
                    <input type="hidden" name="<?php echo $selected ? esc_attr( $vi_attribute_images ) : ''; ?>"
                           data-name="<?php echo esc_attr( $vi_attribute_images ); ?>" class="vi_attribute_image"
                           value="<?php echo esc_attr( $terms_img_id ); ?>">
                    <div class="vi-attribute-image-wrap vi-attribute-edit-image-wrap vi-wpvs-term-image-upload-img">
                            <span class="vi-attribute-edit-image-preview vi-attribute-image-preview">
                                 <img src="<?php echo esc_attr( esc_url( $term_img_src ) ); ?>"
                                      data-src_placeholder="<?php echo esc_attr( wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' ) ); ?>">
                            </span>
                        <span class="vi-attribute-image-remove dashicons dashicons-dismiss<?php echo $terms_img_id ? '' : esc_attr( ' vi-wpvs-hidden' ); ?>"></span>
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
                        <select name="<?php echo $selected ? esc_attr( $vi_attribute_color_separator_name ) : ''; ?>"
                                data-name="<?php echo esc_attr( $vi_attribute_color_separator_name ); ?>"
                                class="vi_attribute_color_separator">
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
                    </p>
                    <table cellspacing="0" cellpadding="0" class="vi-wpvs-attribute-value-content-color-table">
                        <tr>
                            <th><?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?></th>
                            <th><?php esc_html_e( 'Action', 'woocommerce-product-variations-swatches' ); ?></th>
                        </tr>
						<?php
						if ( $terms_colors && is_array( $terms_colors ) && count( $terms_colors ) ) {
							foreach ( $terms_colors as $terms_color ) {
								?>
                                <tr>
                                    <td>
                                        <input type="text" class="vi-wpvs-color vi_attribute_colors"
                                               name="<?php echo $selected ? esc_attr( $terms_color_name ) : ''; ?>"
                                               data-name="<?php echo esc_attr( $terms_color_name ); ?>"
                                               value="<?php echo esc_attr( $terms_color ) ?>">
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
							$terms_color = $vi_default_colors[ strtolower( $term->name ) ] ?? '';
							?>
                            <tr>
                                <td>
                                    <input type="text"
                                           class="vi-wpvs-color vi_attribute_colors"
                                           name="<?php echo $selected ? esc_attr( $terms_color_name ) : ''; ?>"
                                           data-name="<?php echo esc_attr( $terms_color_name ); ?>"
                                           value="<?php echo esc_attr( $terms_color ) ?>">
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
