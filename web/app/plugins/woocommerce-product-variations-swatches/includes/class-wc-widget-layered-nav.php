<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VIWPVS_WC_Widget_Layered_Nav extends WC_Widget_Layered_Nav {
	/**
	 * Updates a particular instance of a widget.
	 *
	 * @param array $new_instance New Instance.
	 * @param array $old_instance Old Instance.
	 *
	 * @return array
	 * @see WP_Widget->update
	 *
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = parent::update( $new_instance, $old_instance );
		$arg1     = array(
			'viwpvs_display_style',
			'viwpvs_display_type',
			'viwpvs_profile',
			'viwpvs_design_attr',
			'viwpvs_design_pd_count',
		);
		foreach ( $arg1 as $key ) {
			$instance[ $key ] = $new_instance[ $key ] ? sanitize_text_field( $new_instance[ $key ] ) : '';
		}
		if ( ! empty( $instance['viwpvs_design_attr'] ) ) {
			$instance['viwpvs_term_default']  = isset( $new_instance['viwpvs_term_default'] ) ? viwpvs_sanitize_fields( $new_instance['viwpvs_term_default'] ) : array();
			$instance['viwpvs_term_hover']    = isset( $new_instance['viwpvs_term_hover'] ) ? viwpvs_sanitize_fields( $new_instance['viwpvs_term_hover'] ) : array();
			$instance['viwpvs_term_selected'] = isset( $new_instance['viwpvs_term_selected'] ) ? viwpvs_sanitize_fields( $new_instance['viwpvs_term_selected'] ) : array();
		}
		if ( ! empty( $instance['viwpvs_design_pd_count'] ) ) {
			$instance['viwpvs_pd_count_enable']   = isset( $new_instance['viwpvs_pd_count_enable'] ) ? sanitize_text_field( $new_instance['viwpvs_pd_count_enable'] ) : '';
			$instance['viwpvs_pd_count_default']  = isset( $new_instance['viwpvs_pd_count_default'] ) ? viwpvs_sanitize_fields( $new_instance['viwpvs_pd_count_default'] ) : array();
			$instance['viwpvs_pd_count_hover']    = isset( $new_instance['viwpvs_pd_count_hover'] ) ? viwpvs_sanitize_fields( $new_instance['viwpvs_pd_count_hover'] ) : array();
			$instance['viwpvs_pd_count_selected'] = isset( $new_instance['viwpvs_pd_count_selected'] ) ? viwpvs_sanitize_fields( $new_instance['viwpvs_pd_count_selected'] ) : array();
		}

		return $instance;
	}

	/**
	 * form function.
	 *
	 * @param array $instance
	 *
	 * @return void
	 * @see    WP_Widget->form
	 * @access public
	 *
	 */
	public function form( $instance ) {
		$wpvs_data = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DATA::get_instance();
		?>
        <div class="vi-wpvs-customize-woo-widget-wrap">
			<?php
			parent::form( $instance );
			$display_type = $instance['display_type'] ?? $this->settings['display_type']['std'] ?? 'list';
			?>
            <div class="vi-wpvs-customize-woo-widget-content-wrap<?php echo ( $display_type === 'list' ) ? '' : esc_attr( ' vi-wpvs-hidden' ); ?>">
                <button type="button" class="vi-wpvs-customize-woo-widget-btn-design button button-primary">
					<?php esc_html_e( 'Swatches Settings', 'woocommerce-product-variations-swatches' ); ?>
                </button>
                <div class="vi-wpvs-customize-woo-widget-design-wrap vi-wpvs-hidden">
                    <div class="vi-wpvs-customize-woo-widget-overlay vi-wpvs-customize-woo-widget-close"></div>
                    <div class="vi-wpvs-customize-woo-widget-design">
                        <div class="vi-wpvs-customize-woo-widget-design-header-wrap">
							<?php esc_html_e( 'Swatches Settings', 'woocommerce-product-variations-swatches' ); ?>
                            <span class="vi-wpvs-customize-woo-widget-close dashicons dashicons-no-alt"></span>
                        </div>
                        <div class="vi-wpvs-customize-woo-widget-design-content-wrap">
							<?php
							$vi_wpvs_ids            = $wpvs_data->get_params( 'ids' );
							$vi_wpvs_names          = $wpvs_data->get_params( 'names' );
							$viwpvs_display_style   = $instance['viwpvs_display_style'] ?? '';
							$viwpvs_display_type    = $instance['viwpvs_display_type'] ?? '';
							$viwpvs_profile         = $instance['viwpvs_profile'] ?? '';
							$viwpvs_design_attr     = $instance['viwpvs_design_attr'] ?? '';
							$viwpvs_design_pd_count = $instance['viwpvs_design_pd_count'] ?? '';
							?>
                            <table class="form-table">
                                <tr>
                                    <td>
                                        <label><?php esc_html_e( 'Display style', 'woocommerce-product-variations-swatches' ); ?></label>
                                    </td>
                                    <td>
                                        <select name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_display_style' ) ) ?>"
                                                class="vi-wpvs-customize-woo-widget-viwpvs_display_style">
                                            <option value="horizontal" <?php selected( $viwpvs_display_style, 'horizontal' ) ?>>
												<?php esc_html_e( 'Horizontal', 'woocommerce-product-variations-swatches' ); ?>
                                            </option>
                                            <option value="vertical" <?php selected( $viwpvs_display_style, 'vertical' ) ?>>
												<?php esc_html_e( 'Vertical', 'woocommerce-product-variations-swatches' ); ?>
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label><?php esc_html_e( 'Display type', 'woocommerce-product-variations-swatches' ); ?></label>
                                    </td>
                                    <td>
                                        <select name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_display_type' ) ) ?>"
                                                class="vi-wpvs-customize-woo-widget-viwpvs_display_type">
                                            <option value="button" <?php selected( $viwpvs_display_type, 'button' ) ?>>
												<?php esc_html_e( 'Button', 'woocommerce-product-variations-swatches' ); ?>
                                            </option>
                                            <option value="color" <?php selected( $viwpvs_display_type, 'color' ) ?>>
												<?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?>
                                            </option>
                                            <option value="image" <?php selected( $viwpvs_display_type, 'image' ) ?>>
												<?php esc_html_e( 'Image', 'woocommerce-product-variations-swatches' ); ?>
                                            </option>
                                            <option value="theme_default" <?php selected( $viwpvs_display_type, 'theme_default' ) ?>>
												<?php esc_html_e( 'Theme default', 'woocommerce-product-variations-swatches' ); ?>
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label><?php esc_html_e( 'Swatches profile', 'woocommerce-product-variations-swatches' ); ?></label>
                                    </td>
                                    <td>
                                        <select name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_profile' ) ) ?>"
                                                class="vi-wpvs-customize-woo-widget-viwpvs_profile">
											<?php
											foreach ( $vi_wpvs_ids as $k => $id ) {
												echo sprintf( '<option value="%s" %s>%s</option>', esc_attr( $id ), selected( $id, $viwpvs_profile ), esc_attr( $vi_wpvs_names[ $k ] ) );
											}
											?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label><?php esc_html_e( 'Design attribute details', 'woocommerce-product-variations-swatches' ); ?></label>
                                    </td>
                                    <td>
                                        <select name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_design_attr' ) ) ?>"
                                                class="vi-wpvs-customize-woo-widget-design-select vi-wpvs-customize-woo-widget-viwpvs_design_attr"
                                                data-design="attr">
                                            <option value=""><?php esc_html_e( 'Default', 'woocommerce-product-variations-swatches' ); ?></option>
                                            <option value="custom" <?php selected( $viwpvs_design_attr, 'custom' ) ?>>
												<?php esc_html_e( 'Custom', 'woocommerce-product-variations-swatches' ); ?>
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <div class="vi-wpvs-customize-woo-widget-design-content vi-wpvs-customize-woo-widget-design-attr<?php echo $viwpvs_design_attr ? '' : esc_attr( ' vi-wpvs-hidden' ); ?>">
								<?php
								$viwpvs_term_default  = $instance['viwpvs_term_default'] ?? $wpvs_data->get_params( 'woo_widget_term_default' ) ?? array();
								$viwpvs_term_hover    = $instance['viwpvs_term_hover'] ?? $wpvs_data->get_params( 'woo_widget_term_hover' ) ?? array();
								$viwpvs_term_selected = $instance['viwpvs_term_selected'] ?? $wpvs_data->get_params( 'woo_widget_term_selected' ) ?? array();
								?>
                                <div class="vi-wpvs-customize-woo-widget-heading1 active"><?php esc_html_e( 'Design attribute details', 'woocommerce-product-variations-swatches' ); ?></div>
                                <div class="vi-wpvs-customize-woo-widget-heading"><?php esc_html_e( 'Default styling', 'woocommerce-product-variations-swatches' ); ?></div>
                                <table class="form-table">
                                    <tr>
                                        <td>
                                            <label for="vi-wpvs-customize-woo-widget-viwpvs_term_default-name_enable"><?php esc_html_e( 'Show attribute label', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Padding', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="checkbox"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_term_default' ) ) ?>[name_enable]"
                                                   value="1"
                                                   id="vi-wpvs-customize-woo-widget-viwpvs_term_default-name_enable"
                                                   class="vi-wpvs-customize-woo-widget-viwpvs_term_default-name_enable" <?php checked( $viwpvs_term_default['name_enable'] ?? '', 1 ); ?>>
                                            <label for="vi-wpvs-customize-woo-widget-viwpvs_term_default-name_enable">
												<?php esc_html_e( 'Display attribute label for color and image type', 'woocommerce-product-variations-swatches' ); ?>
                                            </label>
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-customize-woo-widget-viwpvs_term_default-padding"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_term_default' ) ) ?>[padding]"
                                                   placeholder="<?php echo esc_attr( 'eg: 3px 5px' ); ?>"
                                                   value="<?php echo esc_attr( $viwpvs_term_default['padding'] ?? '' ); ?>">
                                        </td>
                                    </tr>
                                </table>
                                <table class="form-table">
                                    <tr>
                                        <td>
                                            <label><?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Background', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Box shadow color', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-color vi-wpvs-customize-woo-widget-viwpvs_term_default-color"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_term_default' ) ) ?>[color]"
                                                   value="<?php echo esc_attr( $viwpvs_term_default['color'] ?? '' ); ?>">
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-color vi-wpvs-customize-woo-widget-viwpvs_term_default-bg_color"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_term_default' ) ) ?>[bg_color]"
                                                   value="<?php echo esc_attr( $viwpvs_term_default['bg_color'] ?? '' ); ?>">
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-color vi-wpvs-customize-woo-widget-viwpvs_term_default-box_shadow_color"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_term_default' ) ) ?>[box_shadow_color]"
                                                   value="<?php echo esc_attr( $viwpvs_term_default['box_shadow_color'] ?? '' ); ?>">
                                        </td>
                                    </tr>
                                </table>
                                <div class="vi-wpvs-customize-woo-widget-heading"><?php esc_html_e( 'Hover styling', 'woocommerce-product-variations-swatches' ); ?></div>
                                <table class="form-table">
                                    <tr>
                                        <td>
                                            <label><?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Background', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Box shadow color', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-color vi-wpvs-customize-woo-widget-viwpvs_term_hover-color"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_term_hover' ) ) ?>[color]"
                                                   value="<?php echo esc_attr( $viwpvs_term_hover['color'] ?? '' ); ?>">
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-color vi-wpvs-customize-woo-widget-viwpvs_term_hover-bg_color"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_term_hover' ) ) ?>[bg_color]"
                                                   value="<?php echo esc_attr( $viwpvs_term_hover['bg_color'] ?? '' ); ?>">
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-color vi-wpvs-customize-woo-widget-viwpvs_term_hover-box_shadow_color"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_term_hover' ) ) ?>[box_shadow_color]"
                                                   value="<?php echo esc_attr( $viwpvs_term_hover['box_shadow_color'] ?? '' ); ?>">
                                        </td>
                                    </tr>
                                </table>
                                <div class="vi-wpvs-customize-woo-widget-heading"><?php esc_html_e( 'Selected styling', 'woocommerce-product-variations-swatches' ); ?></div>
                                <table class="form-table">
                                    <tr>
                                        <td>
                                            <label><?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Background', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Box shadow color', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-color vi-wpvs-customize-woo-widget-viwpvs_term_selected-color"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_term_selected' ) ) ?>[color]"
                                                   value="<?php echo esc_attr( $viwpvs_term_selected['color'] ?? '' ); ?>">
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-color vi-wpvs-customize-woo-widget-viwpvs_term_selected-bg_color"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_term_selected' ) ) ?>[bg_color]"
                                                   value="<?php echo esc_attr( $viwpvs_term_selected['bg_color'] ?? '' ); ?>">
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-color vi-wpvs-customize-woo-widget-viwpvs_term_selected-box_shadow_color"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_term_selected' ) ) ?>[box_shadow_color]"
                                                   value="<?php echo esc_attr( $viwpvs_term_selected['box_shadow_color'] ?? '' ); ?>">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <table class="form-table">
                                <tr>
                                    <td>
                                        <label><?php esc_html_e( 'Design product count', 'woocommerce-product-variations-swatches' ); ?></label>
                                    </td>
                                    <td>
                                        <select name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_design_pd_count' ) ) ?>"
                                                class="vi-wpvs-customize-woo-widget-design-select vi-wpvs-customize-woo-widget-viwpvs_design_pd_count"
                                                data-design="pd_count">
                                            <option value=""><?php esc_html_e( 'Default', 'woocommerce-product-variations-swatches' ); ?></option>
                                            <option value="custom" <?php selected( $viwpvs_design_pd_count, 'custom' ) ?>>
												<?php esc_html_e( 'Custom', 'woocommerce-product-variations-swatches' ); ?>
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <div class="vi-wpvs-customize-woo-widget-design-content vi-wpvs-customize-woo-widget-design-pd_count<?php echo $viwpvs_design_pd_count ? '' : esc_attr( ' vi-wpvs-hidden' ); ?>">
								<?php
								$viwpvs_pd_count_enable   = $instance['viwpvs_pd_count_enable'] ?? '';
								$viwpvs_pd_count_default  = $instance['viwpvs_pd_count_default'] ?? $wpvs_data->get_params( 'woo_widget_pd_count_default' ) ?? array();
								$viwpvs_pd_count_hover    = $instance['viwpvs_pd_count_hover'] ?? $wpvs_data->get_params( 'woo_widget_pd_count_hover' ) ?? array();
								$viwpvs_pd_count_selected = $instance['viwpvs_pd_count_selected'] ?? $wpvs_data->get_params( 'woo_widget_pd_count_selected' ) ?? array();
								?>
                                <div class="vi-wpvs-customize-woo-widget-heading1 active"><?php esc_html_e( 'Design product count', 'woocommerce-product-variations-swatches' ); ?></div>
                                <div class="vi-wpvs-customize-woo-widget-heading"><?php esc_html_e( 'Default styling', 'woocommerce-product-variations-swatches' ); ?></div>
                                <table class="form-table">
                                    <tr>
                                        <td>
                                            <label for="vi-wpvs-customize-woo-widget-viwpvs_pd_count_enable"><?php esc_html_e( 'Enable', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Background', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Border color', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="checkbox"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_pd_count_enable' ) ) ?>"
                                                   value="1"
                                                   id="vi-wpvs-customize-woo-widget-viwpvs_pd_count_enable"
                                                   class="vi-wpvs-customize-woo-widget-viwpvs_pd_count_enable" <?php checked( $viwpvs_pd_count_enable, 1 ); ?>>
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-color vi-wpvs-customize-woo-widget-viwpvs_pd_count_default-color"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_pd_count_default' ) ) ?>[color]"
                                                   value="<?php echo esc_attr( $viwpvs_pd_count_default['color'] ?? '' ); ?>">
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-color vi-wpvs-customize-woo-widget-viwpvs_pd_count_default-bg_color"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_pd_count_default' ) ) ?>[bg_color]"
                                                   value="<?php echo esc_attr( $viwpvs_pd_count_default['bg_color'] ?? '' ); ?>">
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-color vi-wpvs-customize-woo-widget-viwpvs_pd_count_default-border_color"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_pd_count_default' ) ) ?>[border_color]"
                                                   value="<?php echo esc_attr( $viwpvs_pd_count_default['border_color'] ?? '' ); ?>">
                                        </td>
                                    </tr>
                                </table>
                                <table class="form-table">
                                    <tr>
                                        <td>
                                            <label><?php esc_html_e( 'Padding', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Border width', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Border radius(px)', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-customize-woo-widget-viwpvs_pd_count_default-padding"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_pd_count_default' ) ) ?>[padding]"
                                                   placeholder="<?php echo esc_attr( 'eg: 3px 5px' ); ?>"
                                                   value="<?php echo esc_attr( $viwpvs_pd_count_default['padding'] ?? '' ); ?>">
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-customize-woo-widget-viwpvs_pd_count_default-border_width"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_pd_count_default' ) ) ?>[border_width]"
                                                   placeholder="<?php echo esc_attr( 'eg: 1px 1px 1px 1px ( top right bottom left)' ); ?>"
                                                   value="<?php echo esc_attr( $viwpvs_pd_count_default['border_width'] ?? '' ); ?>">
                                        </td>
                                        <td>
                                            <input type="number" min="0"
                                                   class="vi-wpvs-customize-woo-widget-viwpvs_pd_count_default-border_radius"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_pd_count_default' ) ) ?>[border_radius]"
                                                   value="<?php echo esc_attr( $viwpvs_pd_count_default['border_radius'] ?? 0 ); ?>">
                                        </td>
                                    </tr>
                                </table>
                                <div class="vi-wpvs-customize-woo-widget-heading"><?php esc_html_e( 'Hover styling', 'woocommerce-product-variations-swatches' ); ?></div>
                                <table class="form-table">
                                    <tr>
                                        <td>
                                            <label><?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Background', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Border color', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Border radius(px)', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-color vi-wpvs-customize-woo-widget-viwpvs_pd_count_hover-color"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_pd_count_hover' ) ) ?>[color]"
                                                   value="<?php echo esc_attr( $viwpvs_pd_count_hover['color'] ?? '' ); ?>">
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-color vi-wpvs-customize-woo-widget-viwpvs_pd_count_hover-bg_color"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_pd_count_hover' ) ) ?>[bg_color]"
                                                   value="<?php echo esc_attr( $viwpvs_pd_count_hover['bg_color'] ?? '' ); ?>">
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-color vi-wpvs-customize-woo-widget-viwpvs_pd_count_hover-border_color"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_pd_count_hover' ) ) ?>[border_color]"
                                                   value="<?php echo esc_attr( $viwpvs_pd_count_hover['border_color'] ?? '' ); ?>">
                                        </td>
                                        <td>
                                            <input type="number" min="0"
                                                   class="vi-wpvs-customize-woo-widget-viwpvs_pd_count_hover-border_radius"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_pd_count_hover' ) ) ?>[border_radius]"
                                                   value="<?php echo esc_attr( $viwpvs_pd_count_hover['border_radius'] ?? 0 ); ?>">
                                        </td>
                                    </tr>
                                </table>
                                <div class="vi-wpvs-customize-woo-widget-heading"><?php esc_html_e( 'Selected styling', 'woocommerce-product-variations-swatches' ); ?></div>
                                <table class="form-table">
                                    <tr>
                                        <td>
                                            <label><?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Background', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Border color', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                        <td>
                                            <label><?php esc_html_e( 'Border radius(px)', 'woocommerce-product-variations-swatches' ); ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-color vi-wpvs-customize-woo-widget-viwpvs_pd_count_selected-color"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_pd_count_selected' ) ) ?>[color]"
                                                   value="<?php echo esc_attr( $viwpvs_pd_count_selected['color'] ?? '' ); ?>">
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-color vi-wpvs-customize-woo-widget-viwpvs_pd_count_selected-bg_color"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_pd_count_selected' ) ) ?>[bg_color]"
                                                   value="<?php echo esc_attr( $viwpvs_pd_count_selected['bg_color'] ?? '' ); ?>">
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="vi-wpvs-color vi-wpvs-customize-woo-widget-viwpvs_pd_count_selected-border_color"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_pd_count_selected' ) ) ?>[border_color]"
                                                   value="<?php echo esc_attr( $viwpvs_pd_count_selected['border_color'] ?? '' ); ?>">
                                        </td>
                                        <td>
                                            <input type="number" min="0"
                                                   class="vi-wpvs-customize-woo-widget-viwpvs_pd_count_selected-border_radius"
                                                   name="<?php echo esc_attr( $this->get_field_name( 'viwpvs_pd_count_selected' ) ) ?>[border_radius]"
                                                   value="<?php echo esc_attr( $viwpvs_pd_count_selected['border_radius'] ?? 0 ); ?>">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="vi-wpvs-customize-woo-widget-design-footer-wrap">
                            <div class="vi-wpvs-customize-woo-widget-close vi-wpvs-customize-woo-widget-btn vi-wpvs-customize-woo-widget-btn-done">
								<?php esc_html_e( 'OK', 'woocommerce-product-variations-swatches' ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	public function init_settings() {
		parent::init_settings();
		if ( isset( $this->settings['display_type']['class'] ) ) {
			$this->settings['display_type']['class'] .= ' vi-wpvs-customize-woo-widget-display_type';
		} else {
			$this->settings['display_type']['class'] = 'vi-wpvs-customize-woo-widget-display_type';
		}
	}

	public function widget( $args, $instance ) {

		if ( ! is_shop() && ! is_product_taxonomy() ) {
			return;
		}

		$_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
		$taxonomy           = $this->get_instance_taxonomy( $instance );
		$query_type         = $this->get_instance_query_type( $instance );
		$display_type       = $this->get_instance_display_type( $instance );

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		$terms = get_terms( $taxonomy, array( 'hide_empty' => '1' ) );

		if ( 0 === count( $terms ) ) {
			return;
		}

		ob_start();

		$this->widget_start( $args, $instance );
		if ( 'dropdown' === $display_type ) {
			wp_enqueue_script( 'selectWoo' );
			wp_enqueue_style( 'select2' );
			$found = $this->layered_nav_dropdown( $terms, $taxonomy, $query_type );
		} else {
			$found = $this->layered_nav_list1( $terms, $taxonomy, $query_type, $instance, $args );
		}

		$this->widget_end( $args );

		// Force found when option is selected - do not force found on taxonomy attributes.
		if ( ! is_tax() && is_array( $_chosen_attributes ) && array_key_exists( $taxonomy, $_chosen_attributes ) ) {
			$found = true;
		}

		if ( ! $found ) {
			ob_end_clean();
		} else {
			echo ob_get_clean(); // @codingStandardsIgnoreLine
		}
	}

	public function layered_nav_list1( $terms, $taxonomy, $query_type, $instance, $args ) {
		$term_counts                = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );
		$_chosen_attributes         = WC_Query::get_layered_nav_chosen_attributes();
		$found                      = false;
		$base_link                  = $this->get_current_page_url();
		$wpvs_data                  = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DATA::get_instance();
		$viwpvs_display_style       = $instance['viwpvs_display_style'] ?? $wpvs_data->get_params( 'woo_widget_display_style' );
		$viwpvs_display_style_class = 'vi-wpvs-woocommerce-widget-layered-nav-list vi-wpvs-woocommerce-widget-layered-nav-list-' . $viwpvs_display_style ?: 'vertical';
		echo sprintf( '<ul class="woocommerce-widget-layered-nav-list %s">', $viwpvs_display_style_class );
		$max   = $wpvs_data->get_params( 'woo_widget_max_items' );
		$shown = 0;
		foreach ( $terms as $term ) {
			$current_values = isset( $_chosen_attributes[ $taxonomy ]['terms'] ) ? $_chosen_attributes[ $taxonomy ]['terms'] : array();
			$option_is_set  = in_array( $term->slug, $current_values, true );
			$count          = isset( $term_counts[ $term->term_id ] ) ? $term_counts[ $term->term_id ] : 0;
			// Skip the term for the current archive.
			if ( $this->get_current_term_id() === $term->term_id ) {
				continue;
			}
			// Only show options with count > 0.
			if ( 0 < $count ) {
				$found = true;
			} elseif ( 0 === $count && ! $option_is_set ) {
				continue;
			}
			$filter_name = 'filter_' . wc_attribute_taxonomy_slug( $taxonomy );
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$current_filter = isset( $_GET[ $filter_name ] ) ? explode( ',', wc_clean( wp_unslash( $_GET[ $filter_name ] ) ) ) : array();
			$current_filter = array_map( 'sanitize_title', $current_filter );

			if ( ! in_array( $term->slug, $current_filter, true ) ) {
				$current_filter[] = $term->slug;
			}

			$link = remove_query_arg( $filter_name, $base_link );

			// Add current filters to URL.
			foreach ( $current_filter as $key => $value ) {
				// Exclude query arg for current term archive term.
				if ( $value === $this->get_current_term_slug() ) {
					unset( $current_filter[ $key ] );
				}

				// Exclude self so filter can be unset on click.
				if ( $option_is_set && $value === $term->slug ) {
					unset( $current_filter[ $key ] );
				}
			}
			if ( ! empty( $current_filter ) ) {
				asort( $current_filter );
				$link = add_query_arg( $filter_name, implode( ',', $current_filter ), $link );

				// Add Query type Arg to URL.
				if ( 'or' === $query_type && ! ( 1 === count( $current_filter ) && $option_is_set ) ) {
					$link = add_query_arg( 'query_type_' . wc_attribute_taxonomy_slug( $taxonomy ), 'or', $link );
				}
				$link = str_replace( '%2C', ',', $link );
			}
			if ( $count > 0 || $option_is_set ) {
				$link      = apply_filters( 'woocommerce_layered_nav_link', $link, $term, $taxonomy );
				$term_html = '<a rel="nofollow" href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a>';
			} else {
				$link      = false;
				$term_html = '<span>' . esc_html( $term->name ) . '</span>';
			}
			$term_html  .= ' ' . apply_filters( 'woocommerce_layered_nav_count', '<span class="count">(' . absint( $count ) . ')</span>', $count, $term );
			$term_class = 'woocommerce-widget-layered-nav-list__item wc-layered-nav-term vi-wpvs-wc-layered-nav-term%s';
			if ( $max > 0 && $shown >= $max ) {
				$term_class .= ' vi-wpvs-hidden';
			}
			echo sprintf( '<li class="' . $term_class . '">',
				esc_attr( $option_is_set ? ' woocommerce-widget-layered-nav-list__item--chosen chosen' : '' )
			);
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.EscapeOutput.OutputNotEscaped
			echo apply_filters( 'viwpvs_woocommerce_layered_nav_term_html', $term_html, $term, $link, $count, $instance );
			echo sprintf( '</li>' );
			$shown ++;
		}
		echo sprintf( '</ul>' );
		if ( $max > 0 && $shown > $max ) {
			?>
            <div class="vi-wpvs-show-more-wrap"><span
                        class="vi-wpvs-show-more"><?php echo esc_html__( 'Show more', 'woocommerce-product-variations-swatches' ) ?></span>
            </div>
			<?php
		}
		$widget_id_1 = $args['widget_id'] ?? '';
		$widget_id   = $widget_id_1 ? '#' . $widget_id_1 : '';
		$css         = '';
		if ( ! empty( $instance['viwpvs_design_pd_count'] ) ) {
			$css = $widget_id . '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term .vi-wpvs-widget-count{';
			if ( ! empty( $instance['viwpvs_pd_count_default']['border_width'] ) ) {
				$css .= 'border-width: ' . $instance['viwpvs_pd_count_default']['border_width'] . ' ;';
				$css .= 'border-style: solid ;';
			} else {
				$css .= 'border: unset;';
			}
			$css .= '}';
			$css .= $this->viwpvs_add_inline_style(
				array( $widget_id . '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term .vi-wpvs-widget-count' ),
				array( 'color', 'bg_color', 'padding', 'border_radius', 'border_color' ),
				$instance['viwpvs_pd_count_default'] ?? array(),
				array( 'color', 'background', 'padding', 'border-radius', 'border-color' ),
				array( '', '', '', 'px', '' )
			);
			$css .= $this->viwpvs_add_inline_style(
				array( $widget_id . '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term.vi-wpvs-wc-layered-nav-term-hover .vi-wpvs-widget-count' ),
				array( 'color', 'bg_color', 'border_radius', 'border_color' ),
				$instance['viwpvs_pd_count_hover'] ?? array(),
				array( 'color', 'background', 'border-radius', 'border-color' ),
				array( '', '', 'px', '' )
			);
			$css .= $this->viwpvs_add_inline_style(
				array( $widget_id . '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term.chosen .vi-wpvs-widget-count' ),
				array( 'color', 'bg_color', 'border_radius', 'border_color' ),
				$instance['viwpvs_pd_count_selected'] ?? array(),
				array( 'color', 'background', 'border-radius', 'border-color' ),
				array( '', '', 'px', '' )
			);
		}
		if ( ! empty( $instance['viwpvs_design_attr'] ) ) {
			$box_shadow_color = false;
			if ( ! empty( $instance['viwpvs_term_default']['box_shadow_color'] ) ) {
				$box_shadow_color = true;
				$css              .= $widget_id . '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term{';
				$css              .= 'box-shadow: 0 1px 0 0 ' . $instance['viwpvs_term_default']['box_shadow_color'] . ' ;';
				$css              .= '}';
			}
			$css .= $this->viwpvs_add_inline_style(
				array(
					$widget_id . '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term',
					$widget_id . '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term .vi-wpvs-variation-wrap-wc-widget-title'
				),
				array( 'color', 'bg_color', 'padding' ),
				$instance['viwpvs_term_default'] ?? array(),
				array( 'color', 'background', 'padding' ),
				array( '', '', '' )
			);
			if ( ! empty( $instance['viwpvs_term_hover']['box_shadow_color'] ) ) {
				$box_shadow_color = true;
				$css              .= $widget_id . '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term.vi-wpvs-wc-layered-nav-term-hover{';
				$css              .= 'box-shadow: 0 1px 0 0 ' . $instance['viwpvs_term_hover']['box_shadow_color'] . ' ;';
				$css              .= '}';
			}
			$css .= $this->viwpvs_add_inline_style(
				array(
					$widget_id . '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term.vi-wpvs-wc-layered-nav-term-hover',
					$widget_id . '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term.vi-wpvs-wc-layered-nav-term-hover .vi-wpvs-variation-wrap-wc-widget-title'
				),
				array( 'color', 'bg_color' ),
				$instance['viwpvs_term_hover'] ?? array(),
				array( 'color', 'background' ),
				array( '', '' )
			);
			if ( ! empty( $instance['viwpvs_term_selected']['box_shadow_color'] ) ) {
				$box_shadow_color = true;
				$css              .= $widget_id . '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term.chosen{';
				$css              .= 'box-shadow: 0 1px 0 0 ' . $instance['viwpvs_term_selected']['box_shadow_color'] . ' ;';
				$css              .= '}';
			}
			$css .= $this->viwpvs_add_inline_style(
				array(
					$widget_id . '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term.chosen',
					$widget_id . '.widget_layered_nav .woocommerce-widget-layered-nav-list.vi-wpvs-woocommerce-widget-layered-nav-list .wc-layered-nav-term.vi-wpvs-wc-layered-nav-term.chosen .vi-wpvs-variation-wrap-wc-widget-title'
				),
				array( 'color', 'bg_color' ),
				$instance['viwpvs_term_selected'] ?? array(),
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
		}
		if ( $css ) {
			$add_inline_style = wp_add_inline_style( 'vi-wpvs-frontend-widget', $css );
			if ( ! $add_inline_style ) {
				?>
                <div class="vi-wpvs-customize-woo-widget-css vi-wpvs-hidden"
                     data-widget_id="<?php echo esc_attr( $widget_id_1 ); ?>"><?php echo wp_kses_post( $css ); ?></div>
				<?php
			}
		}

		return $found;
	}

	private function viwpvs_add_inline_style( $element, $name, $settings, $style, $suffix = '' ) {
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