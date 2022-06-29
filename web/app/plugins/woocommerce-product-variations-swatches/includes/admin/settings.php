<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Admin_Settings {
	protected $settings;
	protected $error;
	protected $language;
	protected $languages;
	protected $default_language;
	protected $languages_data;

	public function __construct() {
		$this->settings         = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DATA::get_instance();
		$this->languages        = array();
		$this->languages_data   = array();
		$this->default_language = '';
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 10 );
		add_action( 'admin_init', array( $this, 'check_update' ) );
		add_action( 'admin_init', array( $this, 'save_settings' ), 100 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), PHP_INT_MAX );
		add_action( 'wp_ajax_viwpvs_search_cate', array( $this, 'viwpvs_search_cate' ) );
	}

	public function check_update() {
		/**
		 * Check update
		 */
		if ( class_exists( 'VillaTheme_Plugin_Check_Update' ) ) {
			$setting_url = admin_url( 'admin.php?page=woocommerce-product-variations-swatches' );
			$key         = $this->settings->get_params( 'purchased_code' );
			new VillaTheme_Plugin_Check_Update (
				VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION,                    // current version
				'https://villatheme.com/wp-json/downloads/v3',  // update path
				'woocommerce-product-variations-swatches/woocommerce-product-variations-swatches.php',                  // plugin file slug
				'woocommerce-product-variations-swatches', '54441', $key, $setting_url
			);
			new VillaTheme_Plugin_Updater( 'woocommerce-product-variations-swatches/woocommerce-product-variations-swatches.php', 'woocommerce-product-variations-swatches', $setting_url );
		}
	}

	public function admin_menu() {
		add_menu_page(
			esc_html__( 'Variation Swatches', 'woocommerce-product-variations-swatches' ),
			esc_html__( 'Variation Swatches', 'woocommerce-product-variations-swatches' ),
			'manage_woocommerce',
			'woocommerce-product-variations-swatches',
			array( $this, 'settings_callback' ),
			'dashicons-image-filter',
			2 );
		add_submenu_page(
			'woocommerce-product-variations-swatches',
			esc_html__( 'Variation Swatches', 'woocommerce-product-variations-swatches' ),
			esc_html__( 'Variation Swatches', 'woocommerce-product-variations-swatches' ),
			'manage_woocommerce',
			'woocommerce-product-variations-swatches',
			array( $this, 'settings_callback' )
		);

	}

	public function settings_callback() {
		$this->settings                         = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DATA::get_instance( true );
		$custom_css                             = $this->settings->get_params( 'custom_css' );
		$variation_threshold_single_page        = $this->settings->get_params( 'variation_threshold_single_page' );
		$variation_threshold_archive_page       = $this->settings->get_params( 'variation_threshold_archive_page' );
		$out_of_stock_variation_disable         = $this->settings->get_params( 'out_of_stock_variation_disable' );
		$out_of_stock_variation_disable_archive = $this->settings->get_params( 'out_of_stock_variation_disable_archive' );
		$product_list_enable                    = $this->settings->get_params( 'product_list_enable' );
		$product_list_add_to_cart               = $this->settings->get_params( 'product_list_add_to_cart' );
		$product_list_add_to_cart_text          = $this->settings->get_params( 'product_list_add_to_cart_text' );
		$product_list_qty                       = $this->settings->get_params( 'product_list_qty' );
		$product_list_tooltip_enable            = $this->settings->get_params( 'product_list_tooltip_enable' );
		$product_list_attr_name_enable          = $this->settings->get_params( 'product_list_attr_name_enable' );
		$product_list_assign                    = $this->settings->get_params( 'product_list_assign' );
		$product_list_align                     = $this->settings->get_params( 'product_list_align' );
		$single_align                           = $this->settings->get_params( 'single_align' );
		$product_list_position                  = $this->settings->get_params( 'product_list_position' );
		$product_list_maximum_attr_item         = $this->settings->get_params( 'product_list_maximum_attr_item' );
		$product_list_double_click_enable       = $this->settings->get_params( 'product_list_double_click_enable' );
		$product_list_more_link_enable          = $this->settings->get_params( 'product_list_more_link_enable' );
		$product_list_maximum_more_link_text    = $this->settings->get_params( 'product_list_maximum_more_link_text' );
		$product_list_slider                    = $this->settings->get_params( 'product_list_slider' );
		$product_list_slider_type               = $this->settings->get_params( 'product_list_slider_type' ) ?: array();
		$product_list_slider_min                = $this->settings->get_params( 'product_list_slider_min' ) ?: array();
		$attribute_display_default              = $this->settings->get_params( 'attribute_display_default' );
		$attribute_profile_default              = $this->settings->get_params( 'attribute_profile_default' );
		$attribute_double_click                 = $this->settings->get_params( 'attribute_double_click' );
		$single_attr_title                      = $this->settings->get_params( 'single_attr_title' );
		$single_attr_selected                   = $this->settings->get_params( 'single_attr_selected' );
		$single_swatches_on_des                 = $this->settings->get_params( 'single_swatches_on_des' );
		$ids                                    = $this->settings->get_params( 'ids' );
		$count_ids                              = is_array( $ids ) ? count( $ids ) : 0;
		$custom_attribute_id                    = $this->settings->get_params( 'custom_attribute_id' ) ?: array();
		$attribute_profile_default              = $attribute_profile_default ?: $ids[0];
		?>
        <div id="vi-wpvs-message" class="error <?php echo $this->error ? '' : esc_attr( 'hidden' ); ?>">
            <p><?php echo esc_html( $this->error ); ?></p>
        </div>
        <div class="wrap">
            <h2 class=""><?php esc_html_e( 'WooCommerce Product Variations Swatches', 'woocommerce-product-variations-swatches' ) ?></h2>
            <div class="vi-ui raised">
                <form class="vi-ui form" method="post" enctype="multipart/form-data">
					<?php
					wp_nonce_field( '_vi_woo_product_variation_swatches_settings_action', '_vi_woo_product_variation_swatches_settings' );
					?>
                    <div class="vi-ui vi-ui-main top tabular attached menu">
                        <a class="item"
                           data-tab="general"><?php esc_html_e( 'General Settings', 'woocommerce-product-variations-swatches' ); ?></a>
                        <a class="item active"
                           data-tab="swatches_profile"><?php esc_html_e( 'Swatches Profile', 'woocommerce-product-variations-swatches' ); ?></a>
                        <a class="item"
                           data-tab="single_page"><?php esc_html_e( 'Swatches on Single page', 'woocommerce-product-variations-swatches' ); ?></a>
                        <a class="item"
                           data-tab="product_list"><?php esc_html_e( 'Swatches on Product List', 'woocommerce-product-variations-swatches' ); ?></a>
                        <a class="item"
                           data-tab="custom_attrs"><?php esc_html_e( 'Custom Attributes', 'woocommerce-product-variations-swatches' ); ?></a>
                        <a class="item"
                           data-tab="update"><?php esc_html_e( 'Update', 'woocommerce-product-variations-swatches' ); ?></a>
                    </div>
                    <div class="vi-ui bottom attached tab segment vi-wpvs-tab-general" data-tab="general">
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-attribute_display_default">
										<?php esc_html_e( 'Default display type', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <select name="attribute_display_default" id="vi-wpvs-attribute_display_default"
                                            class="vi-ui fluid dropdown vi-wpvs-attribute_display_default">
                                        <option value="none" <?php selected( $attribute_display_default, 'none' ) ?>>
											<?php esc_html_e( 'No change', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                        <option value="button" <?php selected( $attribute_display_default, 'button' ) ?>>
											<?php esc_html_e( 'Button', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                        <option value="radio" <?php selected( $attribute_display_default, 'radio' ) ?>>
											<?php esc_html_e( 'Radio', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                    </select>
                                    <p class="description">
										<?php esc_html_e( 'This is used if an attribute is not config yet or no rules are applied', 'woocommerce-product-variations-swatches' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-attribute_display_default">
										<?php esc_html_e( 'Blur out backorders', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox" name="attribute_blur_out_backorders"
                                               value="1" <?php checked( $this->settings->get_params( 'attribute_blur_out_backorders' ), '1' ) ?>><label></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-custom_css">
										<?php esc_html_e( 'Custom css', 'woocommerce-product-variations-swatches' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <textarea name="custom_css" id="vi-wpvs-custom_css" class="vi-wpvs-custom_css"
                                              rows="10"><?php echo wp_kses_post( $custom_css ) ?></textarea>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment active vi-wpvs-tab-swatches_profile"
                         data-tab="swatches_profile">
                        <div class="vi-ui positive message">
                            <ul class="list">
                                <li><?php esc_html_e( 'Tooltip content can now be editable. Useful shortcodes can be used in tooltip content are: ', 'woocommerce-product-variations-swatches' ); ?>
                                    <span data-tooltip="<?php esc_attr_e( 'Click to copy', 'woocommerce-product-variations-swatches' ) ?>"><input
                                                type="text" readonly class="vi-wpvs-input-shortcode-field"
                                                value="{attribute_value}"></span>, <span
                                            data-tooltip="<?php esc_attr_e( 'Click to copy', 'woocommerce-product-variations-swatches' ) ?>"><input
                                                type="text" readonly class="vi-wpvs-input-shortcode-field"
                                                value="{attribute_name}"></span>, <span
                                            data-tooltip="<?php esc_attr_e( 'Click to copy', 'woocommerce-product-variations-swatches' ) ?>"><input
                                                type="text" readonly class="vi-wpvs-input-shortcode-field"
                                                value="{attribute_value_desc}"></span>, <span
                                            data-tooltip="<?php esc_attr_e( 'Click to copy', 'woocommerce-product-variations-swatches' ) ?>"><input
                                                type="text" readonly class="vi-wpvs-input-shortcode-field"
                                                value="{attribute_image}"></span> <?php esc_html_e( 'and', 'woocommerce-product-variations-swatches' ); ?>
                                    <span data-tooltip="<?php esc_attr_e( 'Click to copy', 'woocommerce-product-variations-swatches' ) ?>"><input
                                                type="text" readonly class="vi-wpvs-input-shortcode-field"
                                                value="{variation_image}"></span></li>
                                <li><?php esc_html_e( 'For Color/Image type, you are now be able to display the attribute value along with color or image', 'woocommerce-product-variations-swatches' ); ?></li>
                            </ul>
                        </div>
						<?php
						if ( $count_ids ) {
							$wp_img_size = array();
							foreach ( wp_get_registered_image_subsizes() as $type => $type_info ) {
								$wp_img_size[ $type ] = sprintf( '%s - %sx%s', str_replace( array(
									'_',
									'-'
								), ' ', $type ), $type_info['width'] ?? 0, $type_info['height'] ?? 0 );
							}
							for ( $i = 0; $i < $count_ids; $i ++ ) {
								$name                               = $this->settings->get_current_setting( 'names', $i );
								$attribute_reduce_size_list_product = $this->settings->get_current_setting( 'attribute_reduce_size_list_product', $i );
								$attribute_reduce_size_mobile       = $this->settings->get_current_setting( 'attribute_reduce_size_mobile', $i );
								$attribute_height                   = $this->settings->get_current_setting( 'attribute_height', $i );
								$attribute_width                    = $this->settings->get_current_setting( 'attribute_width', $i );
								$attribute_fontsize                 = $this->settings->get_current_setting( 'attribute_fontsize', $i );
								$attribute_padding                  = $this->settings->get_current_setting( 'attribute_padding', $i );
								$attribute_transition               = $this->settings->get_current_setting( 'attribute_transition', $i );
								$attribute_image_size               = $this->settings->get_current_setting( 'attribute_image_size', $i );

								$attribute_default_box_shadow_color = $this->settings->get_current_setting( 'attribute_default_box_shadow_color', $i );
								$attribute_default_color            = $this->settings->get_current_setting( 'attribute_default_color', $i );
								$attribute_default_bg_color         = $this->settings->get_current_setting( 'attribute_default_bg_color', $i );
								$attribute_default_border_color     = $this->settings->get_current_setting( 'attribute_default_border_color', $i );
								$attribute_default_border_radius    = $this->settings->get_current_setting( 'attribute_default_border_radius', $i );
								$attribute_default_border_width     = $this->settings->get_current_setting( 'attribute_default_border_width', $i );

								$attribute_out_of_stock = $this->settings->get_current_setting( 'attribute_out_of_stock', $i );

								$attribute_hover_scale            = $this->settings->get_current_setting( 'attribute_hover_scale', $i );
								$attribute_hover_box_shadow_color = $this->settings->get_current_setting( 'attribute_hover_box_shadow_color', $i );
								$attribute_hover_color            = $this->settings->get_current_setting( 'attribute_hover_color', $i );
								$attribute_hover_bg_color         = $this->settings->get_current_setting( 'attribute_hover_bg_color', $i );
								$attribute_hover_border_color     = $this->settings->get_current_setting( 'attribute_hover_border_color', $i );
								$attribute_hover_border_radius    = $this->settings->get_current_setting( 'attribute_hover_border_radius', $i );
								$attribute_hover_border_width     = $this->settings->get_current_setting( 'attribute_hover_border_width', $i );

								$attribute_selected_scale            = $this->settings->get_current_setting( 'attribute_selected_scale', $i );
								$attribute_selected_box_shadow_color = $this->settings->get_current_setting( 'attribute_selected_box_shadow_color', $i );
								$attribute_selected_color            = $this->settings->get_current_setting( 'attribute_selected_color', $i );
								$attribute_selected_bg_color         = $this->settings->get_current_setting( 'attribute_selected_bg_color', $i );
								$attribute_selected_border_color     = $this->settings->get_current_setting( 'attribute_selected_border_color', $i );
								$attribute_selected_border_radius    = $this->settings->get_current_setting( 'attribute_selected_border_radius', $i );
								$attribute_selected_border_width     = $this->settings->get_current_setting( 'attribute_selected_border_width', $i );

								$attribute_value_enable      = $this->settings->get_current_setting( 'attribute_value_enable', $i );
								$attribute_value_cutoff_text = $this->settings->get_current_setting( 'attribute_value_cutoff_text', $i );
								$attribute_value_position    = $this->settings->get_current_setting( 'attribute_value_position', $i );
								$attribute_value_offset      = $this->settings->get_current_setting( 'attribute_value_offset', $i );
								$attribute_value_font_scale  = $this->settings->get_current_setting( 'attribute_value_font_scale', $i );
								$attribute_value_color       = $this->settings->get_current_setting( 'attribute_value_color', $i );
								$attribute_value_bg_color    = $this->settings->get_current_setting( 'attribute_value_bg_color', $i );

								$attribute_tooltip_enable        = $this->settings->get_current_setting( 'attribute_tooltip_enable', $i );
								$attribute_tooltip_content       = $this->settings->get_current_setting( 'attribute_tooltip_content', $i );
								$attribute_tooltip_position      = $this->settings->get_current_setting( 'attribute_tooltip_position', $i );
								$attribute_tooltip_border_radius = $this->settings->get_current_setting( 'attribute_tooltip_border_radius', $i );
								$attribute_tooltip_fontsize      = $this->settings->get_current_setting( 'attribute_tooltip_fontsize', $i );
								$attribute_tooltip_image_width   = $this->settings->get_current_setting( 'attribute_tooltip_image_width', $i );
								$attribute_tooltip_color         = $this->settings->get_current_setting( 'attribute_tooltip_color', $i );
								$attribute_tooltip_bg_color      = $this->settings->get_current_setting( 'attribute_tooltip_bg_color', $i );
								$attribute_tooltip_border_color  = $this->settings->get_current_setting( 'attribute_tooltip_border_color', $i );
								?>
                                <div class="vi-ui styled fluid accordion vi-wpvs-accordion-wrap vi-wpvs-accordion-wrap-swatches_profile vi-wpvs-accordion-wrap-<?php echo esc_attr( $i ); ?>"
                                     data-accordion_id="<?php echo esc_attr( $i ); ?>">
                                    <div class="woo-wpvs-accordion-info">
                                        <div class="vi-ui toggle checkbox checked"
                                             data-tooltip="<?php esc_attr_e( 'Default profile', 'woocommerce-product-variations-swatches' ); ?>">
                                            <input type="radio" name="attribute_profile_default"
                                                   id="vi-wpvs-attribute_profile_default-<?php echo esc_attr( $ids[ $i ] ); ?>"
                                                   class="vi-wpvs-attribute_profile_default"
                                                   value="<?php echo esc_attr( $ids[ $i ] ); ?>" <?php checked( $attribute_profile_default, $ids[ $i ] ) ?>>
                                            <label for="vi-wpvs-attribute_profile_default-<?php echo esc_attr( $ids[ $i ] ); ?>"></label>
                                        </div>
                                        <span>
						                    <h4><span class="vi-wpvs-accordion-name"><?php echo esc_html( $name ); ?></span></h4>
					                    </span>
                                        <span class="vi-wpvs-accordion-action">
						                    <span class="vi-wpvs-accordion-clone vi-ui mini positive button"><?php esc_html_e( 'Clone', 'woocommerce-product-variations-swatches' ); ?></span>
						                    <span class="vi-wpvs-accordion-remove vi-ui mini negative button"><?php esc_html_e( 'Remove', 'woocommerce-product-variations-swatches' ); ?></span>
					                    </span>
                                    </div>
                                    <div class="title">
                                        <i class="dropdown icon"></i>
										<?php esc_html_e( 'Default styling', 'woocommerce-product-variations-swatches' ); ?>
                                    </div>
                                    <div class="content">
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Name', 'woocommerce-product-variations-swatches' ); ?></label>
                                            <input type="hidden" name="ids[]" class="vi-wpvs-ids"
                                                   value="<?php echo esc_attr( $ids[ $i ] ); ?>">
                                            <input type="text" name="names[]" class="vi-wpvs-names"
                                                   value="<?php echo esc_attr( $name ); ?>">
                                        </div>
                                        <div class="equal width fields">
                                            <div class="field">
                                                <label><?php esc_html_e( 'Transition Duration', 'woocommerce-product-variations-swatches' ); ?></label>
                                                <div class="vi-ui right labeled fluid input">
                                                    <input type="number"
                                                           class="vi-wpvs-attribute_transition"
                                                           name="attribute_transition[]"
                                                           min="0"
                                                           max="1000"
                                                           value="<?php echo esc_attr( $attribute_transition ) ?>">
                                                    <div class="vi-ui label vi-wpvs-basic-label">
														<?php esc_html_e( 'Millisecond', 'woocommerce-product-variations-swatches' ); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="field vi-wpvs-field-min-width">
                                                <label><?php esc_html_e( 'Padding', 'woocommerce-product-variations-swatches' ); ?></label>
                                                <input type="text" class="vi-wpvs-attribute_padding"
                                                       name="attribute_padding[]"
                                                       placeholder="<?php esc_attr_e( 'eg: 3px 5px', 'woocommerce-product-variations-swatches' ); ?>"
                                                       value="<?php echo esc_attr( $attribute_padding ) ?>">
                                            </div>
                                            <div class="field vi-wpvs-field-max-width">
                                                <label><?php esc_html_e( 'Height', 'woocommerce-product-variations-swatches' ); ?></label>
                                                <div class="vi-ui right labeled fluid input">
                                                    <input type="number"
                                                           class="vi-wpvs-attribute_height"
                                                           name="attribute_height[]"
                                                           min="0"
                                                           value="<?php echo esc_attr( $attribute_height ) ?>">
                                                    <div class="vi-ui label vi-wpvs-basic-label">
														<?php esc_html_e( 'Px', 'woocommerce-product-variations-swatches' ); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="field vi-wpvs-field-max-width">
                                                <label><?php esc_html_e( 'Width', 'woocommerce-product-variations-swatches' ); ?></label>
                                                <div class="vi-ui right labeled fluid input">
                                                    <input type="number"
                                                           class="vi-wpvs-attribute_width"
                                                           name="attribute_width[]"
                                                           min="0"
                                                           value="<?php echo esc_attr( $attribute_width ) ?>">
                                                    <div class="vi-ui label vi-wpvs-basic-label">
														<?php esc_html_e( 'Px', 'woocommerce-product-variations-swatches' ); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="field vi-wpvs-field-max-width">
                                                <label><?php esc_html_e( 'Font size', 'woocommerce-product-variations-swatches' ); ?></label>
                                                <div class="vi-ui right labeled fluid input">
                                                    <input type="number"
                                                           class="vi-wpvs-attribute_fontsize"
                                                           name="attribute_fontsize[]"
                                                           min="0"
                                                           value="<?php echo esc_attr( $attribute_fontsize ) ?>">
                                                    <div class="vi-ui label vi-wpvs-basic-label">
														<?php esc_html_e( 'Px', 'woocommerce-product-variations-swatches' ); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="equal width fields">
                                            <div class="field">
                                                <label>
													<?php esc_html_e( 'Attribute image size', 'woocommerce-product-variations-swatches' ); ?>
                                                </label>
                                                <select name="attribute_image_size[]"
                                                        class="vi-ui fluid dropdown vi-wpvs-attribute_image_size">
													<?php
													foreach ( $wp_img_size as $k => $v ) {
														echo sprintf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $k, $attribute_image_size ), esc_attr( $v ) );
													}
													?>
                                                </select>
                                            </div>
                                            <div class="field">
                                                <label>
													<?php esc_html_e( 'Change the size of attribute items on', 'woocommerce-product-variations-swatches' ); ?>
                                                </label>
                                                <div class="equal width fields">
                                                    <div class="field">
                                                        <div class="vi-ui right labeled fluid input">
                                                            <div class="vi-ui label vi-wpvs-basic-label">
																<?php esc_html_e( 'Product list', 'woocommerce-product-variations-swatches' ) ?>
                                                            </div>
                                                            <input type="number"
                                                                   name="attribute_reduce_size_list_product[]"
                                                                   min="30"
                                                                   max="100"
                                                                   class="vi-wpvs-attribute_reduce_size_list_product"
                                                                   value="<?php echo esc_attr( $attribute_reduce_size_list_product ); ?>">
                                                            <div class="vi-ui label">
																<?php esc_html_e( '%', 'woocommerce-product-variations-swatches' ); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="field">
                                                        <div class="vi-ui right labeled fluid input">
                                                            <div class="vi-ui label vi-wpvs-basic-label">
																<?php esc_html_e( 'Mobile', 'woocommerce-product-variations-swatches' ) ?>
                                                            </div>
                                                            <input type="number"
                                                                   name="attribute_reduce_size_mobile[]"
                                                                   min="30"
                                                                   max="100"
                                                                   class="vi-wpvs-attribute_reduce_size_mobile"
                                                                   value="<?php echo esc_attr( $attribute_reduce_size_mobile ); ?>">
                                                            <div class="vi-ui label">
																<?php esc_html_e( '%', 'woocommerce-product-variations-swatches' ); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="equal width fields">
                                                <div class="field">
                                                    <label>
														<?php esc_html_e( 'Text color', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="text"
                                                           class="vi-wpvs-color vi-wpvs-attribute_default_color"
                                                           name="attribute_default_color[]"
                                                           value="<?php echo esc_attr( $attribute_default_color ) ?>">
                                                </div>
                                                <div class="field">
                                                    <label>
														<?php esc_html_e( 'Background color', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="text"
                                                           class="vi-wpvs-color vi-wpvs-attribute_default_bg_color"
                                                           name="attribute_default_bg_color[]"
                                                           value="<?php echo esc_attr( $attribute_default_bg_color ) ?>"">
                                                </div>
                                                <div class="field">
                                                    <label>
														<?php esc_html_e( 'Border color', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="text"
                                                           class="vi-wpvs-color vi-wpvs-attribute_default_border_color"
                                                           name="attribute_default_border_color[]"
                                                           value="<?php echo esc_attr( $attribute_default_border_color ) ?>">
                                                </div>
                                            </div>
                                            <div class="equal width fields">
                                                <div class="field">
                                                    <div class="equal width fields">
                                                        <div class="field vi-wpvs-field-max-width-number">
                                                            <label>
																<?php esc_html_e( 'Border radius', 'woocommerce-product-variations-swatches' ); ?>
                                                            </label>
                                                            <div class="vi-ui right labeled fluid input">
                                                                <input type="number"
                                                                       min="0"
                                                                       class="vi-wpvs-attribute_default_border_radius"
                                                                       name="attribute_default_border_radius[]"
                                                                       value="<?php echo esc_attr( $attribute_default_border_radius ) ?>">
                                                                <div class="vi-ui label vi-wpvs-basic-label">
																	<?php esc_html_e( 'Px', 'woocommerce-product-variations-swatches' ); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="field vi-wpvs-field-max-width-number">
                                                            <label>
																<?php esc_html_e( 'Border width', 'woocommerce-product-variations-swatches' ); ?>
                                                            </label>
                                                            <div class="vi-ui right labeled fluid input">
                                                                <input type="number"
                                                                       min="0"
                                                                       class="vi-wpvs-attribute_default_border_width"
                                                                       name="attribute_default_border_width[]"
                                                                       value="<?php echo esc_attr( $attribute_default_border_width ) ?>">
                                                                <div class="vi-ui label vi-wpvs-basic-label">
																	<?php esc_html_e( 'Px', 'woocommerce-product-variations-swatches' ); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="field">
                                                    <label>
														<?php esc_html_e( 'Box shadow color', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="text"
                                                           class="vi-wpvs-color vi-wpvs-attribute_default_box_shadow_color"
                                                           name="attribute_default_box_shadow_color[]"
                                                           value="<?php echo esc_attr( $attribute_default_box_shadow_color ) ?>">
                                                </div>
                                                <div class="field">
                                                    <label>
														<?php esc_html_e( 'Out of stock', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <select name="attribute_out_of_stock[]"
                                                            class="vi-ui fluid dropdown vi-wpvs-attribute_out_of_stock">
                                                        <option value="not_change" <?php selected( $attribute_out_of_stock, 'not_change' ) ?>>
															<?php esc_html_e( 'Do not change', 'woocommerce-product-variations-swatches' ); ?>
                                                        </option>
                                                        <option value="hide" <?php selected( $attribute_out_of_stock, 'hide' ) ?>>
															<?php esc_html_e( 'Hide', 'woocommerce-product-variations-swatches' ); ?>
                                                        </option>
                                                        <option value="blur" <?php selected( $attribute_out_of_stock, 'blur' ) ?>>
															<?php esc_html_e( 'Blur', 'woocommerce-product-variations-swatches' ); ?>
                                                        </option>
                                                        <option value="blur_icon" <?php selected( $attribute_out_of_stock, 'blur_icon' ) ?>>
															<?php esc_html_e( 'Blur with icon', 'woocommerce-product-variations-swatches' ); ?>
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="title">
                                        <i class="dropdown icon"></i>
										<?php esc_html_e( 'Hover styling', 'woocommerce-product-variations-swatches' ); ?>
                                    </div>
                                    <div class="content">
                                        <div class="field">
                                            <div class="equal width fields">
                                                <div class="field">
                                                    <label>
														<?php esc_html_e( 'Text color', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="text"
                                                           class="vi-wpvs-color vi-wpvs-attribute_hover_color"
                                                           name="attribute_hover_color[]"
                                                           value="<?php echo esc_attr( $attribute_hover_color ) ?>">
                                                </div>
                                                <div class="field">
                                                    <label for="">
														<?php esc_html_e( 'Background color', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="text"
                                                           class="vi-wpvs-color vi-wpvs-attribute_hover_bg_color"
                                                           name="attribute_hover_bg_color[]"
                                                           value="<?php echo esc_attr( $attribute_hover_bg_color ) ?>">
                                                </div>
                                                <div class="field">
                                                    <label>
														<?php esc_html_e( 'Border color', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="text"
                                                           class="vi-wpvs-color vi-wpvs-attribute_hover_border_color"
                                                           name="attribute_hover_border_color[]"
                                                           value="<?php echo esc_attr( $attribute_hover_border_color ) ?>">
                                                </div>
                                            </div>
                                            <div class="equal width fields">
                                                <div class="field">
                                                    <div class="equal width fields">
                                                        <div class="field vi-wpvs-field-max-width-number">
                                                            <label>
																<?php esc_html_e( 'Border radius', 'woocommerce-product-variations-swatches' ); ?>
                                                            </label>
                                                            <div class="vi-ui right labeled fluid input">
                                                                <input type="number"
                                                                       min="0"
                                                                       class="vi-wpvs-attribute_hover_border_radius"
                                                                       name="attribute_hover_border_radius[]"
                                                                       value="<?php echo esc_attr( $attribute_hover_border_radius ) ?>">
                                                                <div class="vi-ui label vi-wpvs-basic-label">
																	<?php esc_html_e( 'Px', 'woocommerce-product-variations-swatches' ); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="field vi-wpvs-field-max-width-number">
                                                            <label>
																<?php esc_html_e( 'Border width', 'woocommerce-product-variations-swatches' ); ?>
                                                            </label>
                                                            <div class="vi-ui right labeled fluid input">
                                                                <input type="number"
                                                                       min="0"
                                                                       class="vi-wpvs-attribute_hover_border_width"
                                                                       name="attribute_hover_border_width[]"
                                                                       value="<?php echo esc_attr( $attribute_hover_border_width ) ?>">
                                                                <div class="vi-ui label vi-wpvs-basic-label">
																	<?php esc_html_e( 'Px', 'woocommerce-product-variations-swatches' ); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="field">
                                                    <label for="">
														<?php esc_html_e( 'Box shadow color', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="text"
                                                           class="vi-wpvs-color vi-wpvs-attribute_hover_box_shadow_color"
                                                           name="attribute_hover_box_shadow_color[]"
                                                           value="<?php echo esc_attr( $attribute_hover_box_shadow_color ) ?>">
                                                </div>
                                                <div class="field">
                                                    <label for="">
														<?php esc_html_e( 'Change the size of attribute items', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="number" min="0.5" max="2" step="0.01"
                                                           class="vi-wpvs-attribute_hover_scale vi-wpvs-attribute-scale"
                                                           name="attribute_hover_scale[]"
                                                           value="<?php echo esc_attr( $attribute_hover_scale ) ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="title">
                                        <i class="dropdown icon"></i>
										<?php esc_html_e( 'Selected styling', 'woocommerce-product-variations-swatches' ); ?>
                                    </div>
                                    <div class="content">
                                        <div class="field">
                                            <div class="equal width fields">
                                                <div class="field">
                                                    <label>
														<?php esc_html_e( 'Text color', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="text"
                                                           class="vi-wpvs-color vi-wpvs-attribute_selected_color"
                                                           name="attribute_selected_color[]"
                                                           value="<?php echo esc_attr( $attribute_selected_color ) ?>">
                                                </div>
                                                <div class="field">
                                                    <label>
														<?php esc_html_e( 'Background', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="text"
                                                           class="vi-wpvs-color vi-wpvs-attribute_selected_bg_color"
                                                           name="attribute_selected_bg_color[]"
                                                           value="<?php echo esc_attr( $attribute_selected_bg_color ) ?>">
                                                </div>
                                                <div class="field">
                                                    <label>
														<?php esc_html_e( 'Border color', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="text"
                                                           class="vi-wpvs-color vi-wpvs-attribute_selected_border_color"
                                                           name="attribute_selected_border_color[]"
                                                           value="<?php echo esc_attr( $attribute_selected_border_color ) ?>">
                                                </div>
                                            </div>
                                            <div class="equal width fields">
                                                <div class="field">
                                                    <div class="equal width fields">
                                                        <div class="field vi-wpvs-field-max-width-number">
                                                            <label>
																<?php esc_html_e( 'Border radius', 'woocommerce-product-variations-swatches' ); ?>
                                                            </label>
                                                            <div class="vi-ui right labeled fluid input">
                                                                <input type="number"
                                                                       min="0"
                                                                       class="vi-wpvs-attribute_selected_border_radius"
                                                                       name="attribute_selected_border_radius[]"
                                                                       value="<?php echo esc_attr( $attribute_selected_border_radius ) ?>">
                                                                <div class="vi-ui label vi-wpvs-basic-label">
																	<?php esc_html_e( 'Px', 'woocommerce-product-variations-swatches' ); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="field vi-wpvs-field-max-width-number">
                                                            <label>
																<?php esc_html_e( 'Border width', 'woocommerce-product-variations-swatches' ); ?>
                                                            </label>
                                                            <div class="vi-ui right labeled fluid input">
                                                                <input type="number"
                                                                       min="0"
                                                                       class="vi-wpvs-attribute_selected_border_width"
                                                                       name="attribute_selected_border_width[]"
                                                                       value="<?php echo esc_attr( $attribute_selected_border_width ) ?>">
                                                                <div class="vi-ui label vi-wpvs-basic-label">
																	<?php esc_html_e( 'Px', 'woocommerce-product-variations-swatches' ); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="field">
                                                    <label>
														<?php esc_html_e( 'Box shadow color', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="text"
                                                           class="vi-wpvs-color vi-wpvs-attribute_selected_box_shadow_color"
                                                           name="attribute_selected_box_shadow_color[]"
                                                           value="<?php echo esc_attr( $attribute_selected_box_shadow_color ) ?>">
                                                </div>
                                                <div class="field">
                                                    <label>
														<?php esc_html_e( 'Change the size of attribute items', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="number" min="0.5" max="2" step="0.01"
                                                           class="vi-wpvs-attribute_selected_scale vi-wpvs-attribute-scale"
                                                           name="attribute_selected_scale[]"
                                                           value="<?php echo esc_attr( $attribute_selected_scale ) ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="title">
                                        <i class="dropdown icon"></i>
										<?php esc_html_e( 'Tooltip styling', 'woocommerce-product-variations-swatches' ); ?>
                                    </div>
                                    <div class="content">
                                        <div class="field">
                                            <div class="equal width fields">
                                                <div class="field">
                                                    <label for="">
														<?php esc_html_e( 'Enable', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <div class="vi-ui toggle checkbox">
                                                        <input type="hidden" name="attribute_tooltip_enable[]"
                                                               class="vi-wpvs-attribute_tooltip_enable"
                                                               value="<?php echo esc_attr( $attribute_tooltip_enable ); ?>">
                                                        <input type="checkbox"
                                                               class="vi-wpvs-attribute_default_box_shadow-checkbox" <?php checked( $attribute_tooltip_enable, '1' ) ?>><label>
                                                    </div>
                                                </div>
                                                <div class="field">
                                                    <label for="">
														<?php esc_html_e( 'Text color', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="text"
                                                           class="vi-wpvs-color vi-wpvs-attribute_tooltip_color"
                                                           name="attribute_tooltip_color[]"
                                                           value="<?php echo esc_attr( $attribute_tooltip_color ) ?>">
                                                </div>
                                                <div class="field">
                                                    <label for="">
														<?php esc_html_e( 'Background', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="text"
                                                           class="vi-wpvs-color vi-wpvs-attribute_tooltip_bg_color"
                                                           name="attribute_tooltip_bg_color[]"
                                                           value="<?php echo esc_attr( $attribute_tooltip_bg_color ) ?>">
                                                </div>
                                                <div class="field">
                                                    <label for="">
														<?php esc_html_e( 'Border color', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="text"
                                                           class="vi-wpvs-color vi-wpvs-attribute_tooltip_border_color"
                                                           name="attribute_tooltip_border_color[]"
                                                           value="<?php echo esc_attr( $attribute_tooltip_border_color ) ?>">
                                                </div>
                                            </div>
                                            <div class="fields">
                                                <div class="four wide field">
                                                    <div class="field">
                                                        <label for="">
															<?php esc_html_e( 'Tooltip content', 'woocommerce-product-variations-swatches' ); ?>
                                                        </label>
                                                        <input type="text"
                                                               class="vi-wpvs-attribute_tooltip_content"
                                                               name="attribute_tooltip_content[]"
                                                               value="<?php echo esc_attr( $attribute_tooltip_content ) ?>">
                                                    </div>
                                                </div>
                                                <div class="twelve wide field">
                                                    <div class="equal width fields">
                                                        <div class="field">
                                                            <label for="">
																<?php esc_html_e( 'Position', 'woocommerce-product-variations-swatches' ); ?>
                                                            </label>
                                                            <select name="attribute_tooltip_position[]"
                                                                    class="vi-ui fluid dropdown vi-wpvs-attribute_tooltip_position">
                                                                <option value="bottom" <?php selected( $attribute_tooltip_position, 'bottom' ) ?>>
																	<?php esc_html_e( 'Bottom', 'woocommerce-product-variations-swatches' ); ?>
                                                                </option>
                                                                <option value="left" <?php selected( $attribute_tooltip_position, 'left' ) ?>>
																	<?php esc_html_e( 'Left', 'woocommerce-product-variations-swatches' ); ?>
                                                                </option>
                                                                <option value="right" <?php selected( $attribute_tooltip_position, 'right' ) ?>>
																	<?php esc_html_e( 'Right', 'woocommerce-product-variations-swatches' ); ?>
                                                                </option>
                                                                <option value="top" <?php selected( $attribute_tooltip_position, 'top' ) ?>>
																	<?php esc_html_e( 'Top', 'woocommerce-product-variations-swatches' ); ?>
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div class="field">
                                                            <label for="">
																<?php esc_html_e( 'Border radius', 'woocommerce-product-variations-swatches' ); ?>
                                                            </label>
                                                            <div class="vi-ui right labeled fluid input">
                                                                <input type="number"
                                                                       class="vi-wpvs-attribute_tooltip_border_radius"
                                                                       name="attribute_tooltip_border_radius[]"
                                                                       min="0"
                                                                       value="<?php echo esc_attr( $attribute_tooltip_border_radius ) ?>">
                                                                <div class="vi-ui label vi-wpvs-basic-label">
																	<?php esc_html_e( 'Px', 'woocommerce-product-variations-swatches' ); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="field">
                                                            <label for="">
																<?php esc_html_e( 'Font size', 'woocommerce-product-variations-swatches' ); ?>
                                                            </label>
                                                            <div class="vi-ui right labeled fluid input">
                                                                <input type="number"
                                                                       class="vi-wpvs-attribute_tooltip_fontsize"
                                                                       name="attribute_tooltip_fontsize[]"
                                                                       min="0"
                                                                       value="<?php echo esc_attr( $attribute_tooltip_fontsize ) ?>">
                                                                <div class="vi-ui label vi-wpvs-basic-label">
																	<?php esc_html_e( 'Px', 'woocommerce-product-variations-swatches' ); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="field">
                                                            <label for="">
																<?php esc_html_e( 'Tooltip image width', 'woocommerce-product-variations-swatches' ); ?>
                                                            </label>
                                                            <div class="vi-ui right labeled fluid input">
                                                                <input type="number"
                                                                       class="vi-wpvs-attribute_tooltip_image_width"
                                                                       name="attribute_tooltip_image_width[]"
                                                                       min="0"
                                                                       value="<?php echo esc_attr( $attribute_tooltip_image_width ) ?>">
                                                                <div class="vi-ui label vi-wpvs-basic-label">
																	<?php esc_html_e( 'Px', 'woocommerce-product-variations-swatches' ); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="title">
                                        <i class="dropdown icon"></i>
										<?php esc_html_e( 'Attribute value for Color/Image type', 'woocommerce-product-variations-swatches' ); ?>
                                    </div>
                                    <div class="content">
                                        <div class="field">
                                            <div class="equal width fields">
                                                <div class="field">
                                                    <label for="">
														<?php esc_html_e( 'Enable', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <div class="vi-ui toggle checkbox">
                                                        <input type="hidden" name="attribute_value_enable[]"
                                                               class="vi-wpvs-attribute_value_enable"
                                                               value="<?php echo esc_attr( $attribute_value_enable ); ?>">
                                                        <input type="checkbox"
                                                               class="vi-wpvs-attribute_default_box_shadow-checkbox" <?php checked( $attribute_value_enable, '1' ) ?>><label>
                                                    </div>
                                                </div>
                                                <div class="field">
                                                    <label>
														<?php esc_html_e( 'Vertical position', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <select name="attribute_value_position[]"
                                                            class="vi-ui fluid dropdown vi-wpvs-attribute_value_position">
                                                        <option value="top" <?php selected( $attribute_value_position, 'top' ) ?>>
															<?php esc_html_e( 'Top', 'woocommerce-product-variations-swatches' ); ?>
                                                        </option>
                                                        <option value="bottom" <?php selected( $attribute_value_position, 'bottom' ) ?>>
															<?php esc_html_e( 'Bottom', 'woocommerce-product-variations-swatches' ); ?>
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="field">
                                                    <label for="">
														<?php esc_html_e( 'Vertical offset', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <div class="vi-ui right labeled fluid input">
                                                        <input type="number"
                                                               class="vi-wpvs-attribute_value_offset"
                                                               name="attribute_value_offset[]"
                                                               step="1"
                                                               value="<?php echo esc_attr( $attribute_value_offset ) ?>">
                                                        <div class="vi-ui label vi-wpvs-basic-label">
															<?php esc_html_e( 'Px', 'woocommerce-product-variations-swatches' ); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="field">
                                                    <label for="">
														<?php esc_html_e( 'Cut off long text', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <div class="vi-ui toggle checkbox">
                                                        <input type="hidden" name="attribute_value_cutoff_text[]"
                                                               class="vi-wpvs-attribute_value_cutoff_text"
                                                               value="<?php echo esc_attr( $attribute_value_cutoff_text ); ?>">
                                                        <input type="checkbox"
                                                               class="vi-wpvs-attribute_default_box_shadow-checkbox" <?php checked( $attribute_value_cutoff_text, '1' ) ?>><label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="equal width fields">
                                                <div class="field"></div>
                                                <div class="field">
                                                    <label for="">
														<?php esc_html_e( 'Font scale', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <div class="vi-ui fluid input">
                                                        <input type="number"
                                                               class="vi-wpvs-attribute_value_font_scale"
                                                               name="attribute_value_font_scale[]"
                                                               min="0.5"
                                                               max="1.5"
                                                               step="0.01"
                                                               value="<?php echo esc_attr( $attribute_value_font_scale ) ?>">
                                                    </div>
                                                </div>
                                                <div class="field">
                                                    <label for="">
														<?php esc_html_e( 'Text color', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="text"
                                                           class="vi-wpvs-color vi-wpvs-attribute_value_color"
                                                           name="attribute_value_color[]"
                                                           value="<?php echo esc_attr( $attribute_value_color ) ?>">
                                                </div>
                                                <div class="field">
                                                    <label for="">
														<?php esc_html_e( 'Background', 'woocommerce-product-variations-swatches' ); ?>
                                                    </label>
                                                    <input type="text"
                                                           class="vi-wpvs-color vi-wpvs-attribute_value_bg_color"
                                                           name="attribute_value_bg_color[]"
                                                           value="<?php echo esc_attr( $attribute_value_bg_color ) ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
								<?php
							}
						}
						?>
                    </div>
                    <div class="vi-ui bottom attached tab segment vi-wpvs-tab-single_page" data-tab="single_page">
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-variation_threshold_single_page">
										<?php esc_html_e( 'Ajax variation threshold', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="number" min="1" name="variation_threshold_single_page"
                                           class="vi-wpvs-variation_threshold_single_page"
                                           id="vi-wvps-variation_threshold_single_page"
                                           value="<?php echo esc_attr( $variation_threshold_single_page ); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-out_of_stock_variation_disable-checkbox">
										<?php esc_html_e( 'Disable \'out of stock\' variation items', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" name="out_of_stock_variation_disable"
                                               class="vi-wpvs-out_of_stock_variation_disable"
                                               value="<?php echo esc_attr( $out_of_stock_variation_disable ); ?>">
                                        <input type="checkbox" id="vi-wpvs-out_of_stock_variation_disable-checkbox"
                                               class="vi-wpvs-out_of_stock_variation_disable-checkbox" <?php checked( $out_of_stock_variation_disable, '1' ); ?>><label>
                                    </div>
                                    <p class="description">
										<?php esc_html_e( 'This function does not work for products whose number of variations is greater than the "Ajax variation threshold"', 'woocommerce-product-variations-swatches' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-attribute_double_click-checkbox">
										<?php esc_html_e( 'Clear on Reselect', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" name="attribute_double_click"
                                               class="vi-wpvs-attribute_double_click"
                                               value="<?php echo esc_attr( $attribute_double_click ); ?>">
                                        <input type="checkbox" id="vi-wpvs-attribute_double_click-checkbox"
                                               class="vi-wpvs-attribute_double_click-checkbox" <?php checked( $attribute_double_click, '1' ); ?>><label>
                                    </div>
                                    <p class="description">
										<?php esc_html_e( 'On single product page, clicking on a selected attribute will deselect it', 'woocommerce-product-variations-swatches' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-single_attr_title-checkbox">
										<?php esc_html_e( 'Enable attribute title', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" name="single_attr_title"
                                               class="vi-wpvs-single_attr_title"
                                               value="<?php echo esc_attr( $single_attr_title ); ?>">
                                        <input type="checkbox" id="vi-wpvs-single_attr_title-checkbox"
                                               class="vi-wpvs-single_attr_title-checkbox" <?php checked( $single_attr_title, '1' ); ?>><label>
                                    </div>
                                    <p class="description">
										<?php esc_html_e( 'Show attribute title on single product page', 'woocommerce-product-variations-swatches' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr class="vi-wpvs-single_attr_title-enable <?php echo $single_attr_title ? '' : ' vi-wpvs-hidden' ?>">
                                <th>
                                    <label for="vi-wpvs-single_attr_selected-checkbox">
										<?php esc_html_e( 'Show selected attribute item', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" name="single_attr_selected"
                                               class="vi-wpvs-single_attr_selected"
                                               value="<?php echo esc_attr( $single_attr_selected ); ?>">
                                        <input type="checkbox" id="vi-wpvs-single_attr_selected-checkbox"
                                               class="vi-wpvs-single_attr_selected-checkbox" <?php checked( $single_attr_selected, '1' ); ?>><label>
                                    </div>
                                    <p class="description">
										<?php esc_html_e( 'Display the selected item beside attribute title on single product page', 'woocommerce-product-variations-swatches' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-single-product-align">
										<?php esc_html_e( 'Swatches align', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <select name="single_align"
                                            class="vi-ui fluid dropdown vi-wpvs-single-product-align-select"
                                            id="vi-wpvs-single-product-align">
                                        <option value="center" <?php selected( $single_align, 'center' ) ?>>
											<?php esc_html_e( 'Center', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                        <option value="left" <?php selected( $single_align, 'left' ) ?>>
											<?php esc_html_e( 'Left', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                        <option value="right" <?php selected( $single_align, 'right' ) ?>>
											<?php esc_html_e( 'Right', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                    </select>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment vi-wpvs-tab-product_list" data-tab="product_list">
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-product_list_enable-checkbox">
										<?php esc_html_e( 'Enable', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" name="product_list_enable"
                                               class="vi-wpvs-product_list_enable"
                                               value="<?php echo esc_attr( $product_list_enable ); ?>">
                                        <input type="checkbox" id="vi-wpvs-product_list_enable-checkbox"
                                               class="vi-wpvs-product_list_enable-checkbox" <?php checked( $product_list_enable, '1' ); ?>><label>
                                    </div>
                                    <p class="description"><?php esc_html_e( 'Show variation swatches on the product list', 'woocommerce-product-variations-swatches' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-variation_threshold_archive_page">
										<?php esc_html_e( 'Ajax variation threshold', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="number" min="1" name="variation_threshold_archive_page"
                                           class="vi-wpvs-variation_threshold_archive_page"
                                           id="vi-wvps-variation_threshold_archive_page"
                                           value="<?php echo esc_attr( $variation_threshold_archive_page ); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-out_of_stock_variation_disable_archive-checkbox">
										<?php esc_html_e( 'Disable \'out of stock\' variation items', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" name="out_of_stock_variation_disable_archive"
                                               class="vi-wpvs-out_of_stock_variation_disable_archive"
                                               value="<?php echo esc_attr( $out_of_stock_variation_disable_archive ); ?>">
                                        <input type="checkbox"
                                               id="vi-wpvs-out_of_stock_variation_disable_archive-checkbox"
                                               class="vi-wpvs-out_of_stock_variation_disable_archive-checkbox" <?php checked( $out_of_stock_variation_disable_archive, '1' ); ?>><label>
                                    </div>
                                    <p class="description"><?php esc_html_e( 'This function does not work for products whose number of variations is greater than the "Ajax variation threshold"', 'woocommerce-product-variations-swatches' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-product-list-add-to-cart-checkbox">
										<?php esc_html_e( 'Enable add to cart', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" name="product_list_add_to_cart"
                                               class="vi-wpvs-product-list-add-to-cart"
                                               value="<?php echo esc_attr( $product_list_add_to_cart ); ?>">
                                        <input type="checkbox" id="vi-wpvs-product-list-add-to-cart-checkbox"
                                               class="vi-wpvs-product-list-add-to-cart-checkbox" <?php checked( $product_list_add_to_cart, '1' ); ?>><label>
                                    </div>
                                    <p class="description">
										<?php esc_html_e( 'Show the Add to cart button after selecting variation swatches on the product list. All attributes of a product are displayed on Product List page when enabling this option', 'woocommerce-product-variations-swatches' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr class="vi-wpvs-product-list-atc-enable <?php echo $product_list_add_to_cart ? '' : esc_attr( 'vi-wpvs-hidden' ); ?>">
                                <th>
                                    <label for="vi-wpvs-product-list-add-to-cart-text">
										<?php esc_html_e( 'Text of \'add to cart\' button', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
									<?php
									$this->default_language_flag_html( 'vi-wpvs-product-list-add-to-cart-text' );
									?>
                                    <input type="text" name="product_list_add_to_cart_text"
                                           id="vi-wpvs-product-list-add-to-cart-text"
                                           placeholder="<?php esc_attr_e( 'Add To Cart', 'woocommerce-product-variations-swatches' ); ?>"
                                           value="<?php echo esc_attr( $product_list_add_to_cart_text ); ?>">
									<?php
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											?>
                                            <p>
                                                <label for="<?php echo esc_attr( "vi-wpvs-product-list-add-to-cart-text_{$value}" ) ?>"><?php
													if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
														?>
                                                        <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
														<?php
													}
													echo $value;
													if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
														echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
													}
													?>:</label>
                                            </p>
                                            <input id="<?php echo esc_attr( "vi-wpvs-product-list-add-to-cart-text_{$value}" ) ?>"
                                                   type="text"
                                                   name="<?php echo esc_attr( "product_list_add_to_cart_text_{$value}" ) ?>"
                                                   value="<?php echo esc_attr( $this->settings->get_params( 'product_list_add_to_cart_text', $value ) ); ?>">
											<?php
										}
									}
									?>
                                </td>
                            </tr>
                            <tr class="vi-wpvs-product-list-atc-enable <?php echo $product_list_add_to_cart ? '' : esc_attr( 'vi-wpvs-hidden' ); ?>">
                                <th>
                                    <label for="vi-wpvs-product_list_qty-checkbox">
										<?php esc_html_e( 'Enable product quantity', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" name="product_list_qty"
                                               class="vi-wpvs-product_list_qty"
                                               value="<?php echo esc_attr( $product_list_qty ); ?>">
                                        <input type="checkbox" id="vi-wpvs-product_list_qty-checkbox"
                                               class="vi-wpvs-product_list_qty-checkbox" <?php checked( $product_list_qty, '1' ); ?>><label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-product-list-attr-name-enable-checkbox">
										<?php esc_html_e( 'Show attribute name', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" name="product_list_attr_name_enable"
                                               class="vi-wpvs-product-list-attr-name-enable"
                                               value="<?php echo esc_attr( $product_list_attr_name_enable ); ?>">
                                        <input type="checkbox" id="vi-wpvs-product-list-attr-name-enable-checkbox"
                                               class="vi-wpvs-product-list-tooltip-attr-name-checkbox" <?php checked( $product_list_attr_name_enable, '1' ); ?>><label>
                                    </div>
                                    <p class="description"><?php esc_html_e( 'Enable to show the attribute name on the product list', 'woocommerce-product-variations-swatches' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-product_list_double_click_enable-checkbox">
										<?php esc_html_e( 'Clear on Reselect', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" name="product_list_double_click_enable"
                                               class="vi-wpvs-product_list_double_click_enable"
                                               value="<?php echo esc_attr( $product_list_double_click_enable ); ?>">
                                        <input type="checkbox" id="vi-wpvs-product_list_double_click_enable-checkbox"
                                               class="vi-wpvs-product_list_double_click_enable-checkbox" <?php checked( $product_list_double_click_enable, '1' ); ?>><label>
                                    </div>
                                    <p class="description">
										<?php esc_html_e( 'On Product list, clicking on a selected attribute will deselect it', 'woocommerce-product-variations-swatches' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-product-list-tooltip-enable-checkbox">
										<?php esc_html_e( 'Enable tooltip', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" name="product_list_tooltip_enable"
                                               class="vi-wpvs-product-list-tooltip-enable"
                                               value="<?php echo esc_attr( $product_list_tooltip_enable ); ?>">
                                        <input type="checkbox" id="vi-wpvs-product-list-tooltip-enable-checkbox"
                                               class="vi-wpvs-product-list-tooltip-enable-checkbox" <?php checked( $product_list_tooltip_enable, '1' ); ?>><label>
                                    </div>
                                    <p class="description"><?php esc_html_e( 'Show tooltip on the product list if this tooltip is enabled on swatches profile', 'woocommerce-product-variations-swatches' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-product-list-tooltip-enable">
										<?php esc_html_e( 'Assign page', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input placeholder="<?php esc_attr_e( 'eg: !is_page(array(34,98,73))', 'woocommerce-product-variations-swatches' ) ?>"
                                           type="text"
                                           value="<?php echo esc_attr( htmlentities( $product_list_assign ) ); ?>"
                                           name="product_list_assign"/>

                                    <p class="description"><?php echo wp_kses_post( __( 'You can use WP\'s <a href="https://villatheme.com/knowledge-base/conditional-tags/" target="_blank">Conditional tags</a> to enable/disable swatches of product list on specific pages.', 'woocommerce-product-variations-swatches' ) ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-product-list-align">
										<?php esc_html_e( 'Swatches align', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <select name="product_list_align"
                                            class="vi-ui fluid dropdown vi-wpvs-product-list-align-select"
                                            id="vi-wpvs-product-list-align">
                                        <option value="center" <?php selected( $product_list_align, 'center' ) ?>>
											<?php esc_html_e( 'Center', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                        <option value="left" <?php selected( $product_list_align, 'left' ) ?>>
											<?php esc_html_e( 'Left', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                        <option value="right" <?php selected( $product_list_align, 'right' ) ?>>
											<?php esc_html_e( 'Right', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-product-list-position">
										<?php esc_html_e( 'Position', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <select name="product_list_position"
                                            class="vi-ui fluid dropdown vi-wpvs-product-list-position-select"
                                            id="vi-wpvs-product-list-position">
                                        <option value="before_title" <?php selected( $product_list_position, 'before_title' ) ?>>
											<?php esc_html_e( 'Before title', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                        <option value="after_title" <?php selected( $product_list_position, 'after_title' ) ?>>
											<?php esc_html_e( 'After title', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                        <option value="before_price" <?php selected( $product_list_position, 'before_price' ) ?>>
											<?php esc_html_e( 'Before price', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                        <option value="before_price_1" <?php selected( $product_list_position, 'before_price_1' ) ?>>
											<?php esc_html_e( 'Before price(alternative)', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                        <option value="after_price" <?php selected( $product_list_position, 'after_price' ) ?>>
											<?php esc_html_e( 'After price', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                        <option value="after_price_1" <?php selected( $product_list_position, 'after_price_1' ) ?>>
											<?php esc_html_e( 'After price(alternative)', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                        <option value="before_cart" <?php selected( $product_list_position, 'before_cart' ) ?>>
											<?php esc_html_e( 'Before cart', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                        <option value="after_cart" <?php selected( $product_list_position, 'after_cart' ) ?>>
											<?php esc_html_e( 'After cart', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                        <option value="custom_only" <?php selected( $product_list_position, 'custom_only' ) ?>>
											<?php esc_html_e( 'Custom position only', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                    </select>
                                    <p class="description"><?php esc_html_e( 'The position of variation on shop page, category page and other product list pages', 'woocommerce-product-variations-swatches' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-product-list-custom-hook">
										<?php esc_html_e( 'Custom position', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="product_list_custom_hook"
                                           value="<?php echo esc_attr( $this->settings->get_params( 'product_list_custom_hook' ) ); ?>">
                                    <p class="description">
										<?php esc_html_e( 'If none of the above positions work, you can enter a specific hook here. It\'s technical so you should ask a theme or plugin developer about the needed hook.', 'woocommerce-product-variations-swatches' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-product-list-maximum-attr-item">
										<?php esc_html_e( 'Maximum attribute items', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="number" min="0" step="1" name="product_list_maximum_attr_item"
                                           value="<?php echo esc_attr( $product_list_maximum_attr_item ); ?>">
                                    <p class="description">
										<?php esc_html_e( 'The maximum number of items of an attribute can be displayed. Set to 0 to not limit this.', 'woocommerce-product-variations-swatches' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-product-list-more-link-enable-checkbox">
										<?php esc_html_e( 'Show more link', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" name="product_list_more_link_enable"
                                               class="vi-wpvs-product_list_more_link_enable"
                                               value="<?php echo esc_attr( $product_list_more_link_enable ); ?>">
                                        <input type="checkbox" id="vi-wpvs-product-list-more-link-enable-checkbox"
                                               class="vi-wpvs-product-list-more-link-enable-checkbox" <?php checked( $product_list_more_link_enable, '1' ); ?>>

                                        <label for="vi-wpvs-product-list-more-link-enable-checkbox">
											<?php esc_html_e( 'Enable', 'woocommerce-product-variations-swatches' ); ?>
                                        </label>
                                    </div>
                                    <p class="description">
										<?php esc_html_e( 'This option is used when total items of an attribute is greater than the Maximum attribute items above', 'woocommerce-product-variations-swatches' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr class="vi-wpvs-product-list-more-link-enable <?php echo $product_list_more_link_enable ? '' : esc_attr( 'vi-wpvs-hidden' ); ?>">
                                <th>
                                    <label for="vi-wpvs-product-list-more-link-text">
										<?php esc_html_e( 'Text of more link', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="product_list_maximum_more_link_text"
                                           id="vi-wpvs-product-list-more-link-text"
                                           value="<?php echo esc_attr( $product_list_maximum_more_link_text ); ?>">
                                    <p class="description">
										<?php
										echo esc_html( '{link_more_icon} - ' );
										echo esc_html__( 'The icon of more link', 'woocommerce-product-variations-swatches' );
										?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wpvs-product_list_slider-checkbox">
										<?php esc_html_e( 'Swatches slider', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" name="product_list_slider" id="vi-wpvs-product_list_slider"
                                               value="<?php echo esc_attr( $product_list_slider ); ?>">
                                        <input type="checkbox" class="vi-wpvs-product_list_slider-checkbox"
                                               id="vi-wpvs-product_list_slider-checkbox" <?php checked( $product_list_slider, '1' ); ?>>
                                        <label for="vi-wpvs-product_list_slider-checkbox"><?php esc_html_e( 'Enable', 'woocommerce-product-variations-swatches' ); ?></label>
                                    </div>
                                    <p class="description">
										<?php esc_html_e( 'Show all items of the attribute in a slider. The tooltip will hide on slider.', 'woocommerce-product-variations-swatches' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr class="vi-wpvs-product_list_slider-enable <?php echo $product_list_slider ? '' : esc_attr( 'vi-wpvs-hidden' ); ?>">
                                <th>
                                    <label for="vi-wpvs-product_list_slider_type">
										<?php esc_html_e( 'Swatches slider for attribute type', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <select name="product_list_slider_type[]" id="vi-wpvs-product_list_slider_type"
                                            class="vi-ui fluid dropdown vi-wpvs-product_list_slider_type" multiple>
                                        <option value="color" <?php selected( in_array( 'color', $product_list_slider_type ), true ) ?>>
											<?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                        <option value="image" <?php selected( in_array( 'image', $product_list_slider_type ), true ) ?>>
											<?php esc_html_e( 'Image', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                        <option value="variation_img" <?php selected( in_array( 'variation_img', $product_list_slider_type ), true ) ?>>
											<?php esc_html_e( 'Variation Image', 'woocommerce-product-variations-swatches' ); ?>
                                        </option>
                                    </select>
                                    <p class="description">
										<?php
										esc_html_e( 'Choose type of the attribute to convert to slider. Leave blank to apply for Image,Variation Image, Color type', 'woocommerce-product-variations-swatches' );
										?>
                                    </p>
                                </td>
                            </tr>
                            <tr class="vi-wpvs-product_list_slider-enable <?php echo $product_list_slider ? '' : esc_attr( 'vi-wpvs-hidden' ); ?>">
                                <th>
                                    <label for="vi-wpvs-product_list_slider_min">
										<?php esc_html_e( 'Minimum attribute items', 'woocommerce-product-variations-swatches' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="number" name="product_list_slider_min" min="3" step="1"
                                           id="vi-wpvs-product_list_slider_min"
                                           value="<?php echo esc_attr( $product_list_slider_min ); ?>">
                                    <p class="description">
										<?php esc_html_e( 'The maximum number of items of an attribute to convert to slider', 'woocommerce-product-variations-swatches' ); ?>
                                    </p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment vi-wpvs-tab-custom_attrs" data-tab="custom_attrs">
                        <div class="vi-ui blue message">
							<?php esc_html_e( 'Rules for Custom Attributes', 'woocommerce-product-variations-swatches' ); ?>
                            <ul class="list">
                                <li><?php esc_html_e( 'Rules are checked from top to bottom and will stop if the attribute matches a rule', 'woocommerce-product-variations-swatches' ); ?></li>
                                <li><?php esc_html_e( 'For each rule, if a custom attribute has the same name(case-insensitive) as field "Attribute name" and products that contain this custom attribute belongs to one of selected "Product category", the swatches settings of current rule will be applied to that custom attribute', 'woocommerce-product-variations-swatches' ); ?></li>
                                <li><?php esc_html_e( 'If Product category of a rule is empty, this rule will be applied to products from all categories', 'woocommerce-product-variations-swatches' ); ?></li>
                                <li><?php esc_html_e( 'If "Enable add to cart" option(Swatches on Product List) is ON, "Show in product list" option cannot be changed', 'woocommerce-product-variations-swatches' ); ?></li>
                            </ul>
                        </div>
                        <table class="form-table vi-wpvs-table">
                            <thead>
                            <tr>
                                <th colspan="2"><?php esc_html_e( 'Conditions(AND)', 'woocommerce-product-variations-swatches' ); ?></th>
                                <th colspan="6"><?php esc_html_e( 'Apply these settings for attributes that match conditions', 'woocommerce-product-variations-swatches' ); ?></th>
                            </tr>
                            <tr>
                                <td>
                                    <label><?php esc_html_e( 'Attribute name', 'woocommerce-product-variations-swatches' ); ?></label>
                                </td>
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
                                    <label><?php esc_html_e( 'Display style', 'woocommerce-product-variations-swatches' ); ?></label>
                                </td>
                                <td>
                                    <label><?php esc_html_e( 'Show in product list', 'woocommerce-product-variations-swatches' ); ?></label>
                                </td>
                                <td>
                                    <label><?php esc_html_e( 'Change product image', 'woocommerce-product-variations-swatches' ); ?></label>
                                </td>
                                <td>
                                    <label><?php esc_html_e( 'Action', 'woocommerce-product-variations-swatches' ); ?></label>
                                </td>
                            </tr>
                            </thead>
                            <tbody>
							<?php
							if ( empty( $custom_attribute_id ) ) {
								$custom_attribute_id[] = current_time( 'timestamp' );
							}
							$attribute_types = wc_get_attribute_types();
							for ( $i = 0; $i < count( $custom_attribute_id ); $i ++ ) {
								$custom_attribute_id_t           = $custom_attribute_id[ $i ];
								$custom_attribute_name_t         = $this->settings->get_current_setting( 'custom_attribute_name', $i, '' );
								$custom_attribute_type_t         = $this->settings->get_current_setting( 'custom_attribute_type', $i, '' );
								$custom_attribute_profiles_t     = $this->settings->get_current_setting( 'custom_attribute_profiles', $i, '' );
								$custom_attribute_loop_enable_t  = $this->settings->get_current_setting( 'custom_attribute_loop_enable', $i, '' );
								$change_product_image            = $this->settings->get_current_setting( 'custom_attribute_change_product_image', $i, '' );
								$custom_attribute_display_type_t = $this->settings->get_current_setting( 'custom_attribute_display_type', $i, 'vertical' );
								?>
                                <tr class="vi-wpvs-rule-custom-attrs-container">
                                    <td>
                                        <input type="hidden" name="custom_attribute_id[]"
                                               class="vi-wpvs-custom_attribute_id"
                                               value="<?php echo esc_attr( $custom_attribute_id_t ); ?>">
                                        <input type="text" name="custom_attribute_name[]"
                                               class="vi-wpvs-custom_attribute_name"
                                               value="<?php echo esc_attr( $custom_attribute_name_t ); ?>"
                                               placeholder="<?php esc_attr_e( 'Custom attribute name', 'woocommerce-product-variations-swatches' ); ?>">
                                    </td>
                                    <td>
                                        <div class="vi-ui field">
                                            <select multiple="multiple"
                                                    name="custom_attribute_category[<?php echo esc_attr( $custom_attribute_id_t ); ?>][]"
                                                    class="vi-wpvs-category-search">
												<?php
												$selected_cate = $this->settings->get_current_setting( 'custom_attribute_category', $custom_attribute_id_t );
												if ( $selected_cate && is_array( $selected_cate ) && count( $selected_cate ) ) {
													foreach ( $selected_cate as $category_id ) {
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
                                        <select name="custom_attribute_type[]"
                                                class="vi-ui fluid dropdown vi-wpvs-custom_attribute_type">
											<?php
											foreach ( $attribute_types as $k => $v ) {
												?>
                                                <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $custom_attribute_type_t, $k ) ?>><?php echo esc_html( $v ); ?></option>
												<?php
											}
											?>
                                        </select>
                                    </td>
                                    <td class="vi-wpvs-table-swatches-profile">
                                        <select name="custom_attribute_profiles[]"
                                                class="vi-ui fluid dropdown vi-wpvs-custom_attribute_profiles">
											<?php
											if ( $count_ids ) {
												for ( $j = 0; $j < $count_ids; $j ++ ) {
													?>
                                                    <option value="<?php echo esc_attr( $ids[ $j ] ); ?>" <?php selected( $ids[ $j ], $custom_attribute_profiles_t ) ?> >
														<?php echo esc_html( $this->settings->get_current_setting( 'names', $j ) ) ?>
                                                    </option>
													<?php
												}
											}
											?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="custom_attribute_display_type[]"
                                                class="vi-ui fluid dropdown vi-wpvs-custom_attribute_display_type">
                                            <option value="vertical" <?php selected( $custom_attribute_display_type_t, 'vertical' ) ?> >
												<?php esc_html_e( 'Vertical', 'woocommerce-product-variations-swatches' ); ?>
                                            </option>
                                            <option value="horizontal" <?php selected( $custom_attribute_display_type_t, 'horizontal' ) ?> >
												<?php esc_html_e( 'Horizontal', 'woocommerce-product-variations-swatches' ); ?>
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="vi-ui toggle checkbox vi-wpvs-custom-attr-loop-enable<?php echo $product_list_add_to_cart ? esc_attr( ' disabled' ) : ''; ?>">
                                            <input type="hidden" name="custom_attribute_loop_enable[]"
                                                   class="vi-wpvs-custom_attribute_loop_enable"
                                                   value="<?php echo esc_attr( $custom_attribute_loop_enable_t ); ?>">
                                            <input type="checkbox"
                                                   class="vi-wpvs-custom_attribute_loop_enable-checkbox" <?php checked( $custom_attribute_loop_enable_t, '1' ); ?>><label></label>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="vi-ui dropdown" name="custom_attribute_change_product_image[]">
                                            <option value="not_change" <?php selected( $change_product_image, 'not_change' ) ?>><?php esc_html_e( 'Not change', 'woocommerce-product-variations-swatches' ); ?></option>
                                            <option value="attribute_image" <?php selected( $change_product_image, 'attribute_image' ) ?>><?php esc_html_e( 'Change to image set for attribute', 'woocommerce-product-variations-swatches' ); ?></option>
                                            <option value="variation_image" <?php selected( $change_product_image, 'variation_image' ) ?>><?php esc_html_e( 'Auto detect variation image', 'woocommerce-product-variations-swatches' ); ?></option>
                                        </select>
                                    </td>
                                    <td>
                                        <!--                                        <span class="vi-wpvs-rule-custom-attrs-edit vi-ui orange mini button"-->
                                        <!--                                              data-tooltip="-->
										<?php //esc_attr_e( 'Edit', 'woocommerce-product-variations-swatches' ); ?><!--">-->
                                        <!--                                            <i class="edit icon"></i>-->
                                        <!--                                        </span>-->
                                        <span class="vi-wpvs-rule-custom-attrs-clone vi-ui positive mini button"
                                              data-tooltip="<?php esc_attr_e( 'Clone', 'woocommerce-product-variations-swatches' ); ?>">
                                            <i class="clone icon"></i>
                                        </span>
                                        <span class="vi-wpvs-rule-custom-attrs-remove vi-ui negative mini button"
                                              data-tooltip="<?php esc_attr_e( 'Remove', 'woocommerce-product-variations-swatches' ); ?>">
                                            <i class="trash icon"></i>
                                        </span>
                                    </td>
                                </tr>
								<?php
							}
							?>
                            </tbody>
                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment vi-wpvs-tab-update" data-tab="update">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="auto-update-key"><?php esc_html_e( 'Auto Update Key', 'woocommerce-product-variations-swatches' ) ?></label>
                                </th>
                                <td>
                                    <div class="fields">
                                        <div class="ten wide field">
                                            <input type="text" name="purchased_code" id="auto-update-key"
                                                   class="villatheme-autoupdate-key-field"
                                                   value="<?php echo esc_attr( htmlentities( $this->settings->get_params( 'purchased_code' ) ) ); ?>">
                                        </div>
                                        <div class="six wide field">
                                        <span class="vi-ui button green villatheme-get-key-button"
                                              data-href="https://api.envato.com/authorization?response_type=code&client_id=villatheme-download-keys-6wzzaeue&redirect_uri=https://villatheme.com/update-key"
                                              data-id="26235745"><?php echo esc_html__( 'Get Key', 'woocommerce-product-variations-swatches' ) ?></span>
                                        </div>
                                    </div>
									<?php do_action( 'woocommerce-product-variations-swatches_key' ) ?>
                                    <p class="description"><?php echo wp_kses_post( __( 'Please fill your key what you get from <a target="_blank" href="https://villatheme.com/my-download">https://villatheme.com/my-download</a>. You can auto update WooCommerce Product Variations Swatches plugin. See <a target="_blank" href="https://villatheme.com/knowledge-base/how-to-use-auto-update-feature/">guide</a>', 'woocommerce-product-variations-swatches' ) ); ?></p>
                                </td>
                            </tr>

                        </table>
                    </div>
                    <p class="vi-wpvs-save-wrap">
                        <button type="submit" class="vi-wpvs-save vi-ui primary button labeled icon"
                                name="vi-wpvs-save">
                            <i class="save icon"></i>
							<?php esc_html_e( 'Save', 'woocommerce-product-variations-swatches' ); ?>
                        </button>
                        <button class="vi-ui button labeled icon vi-wpvs-save" type="submit"
                                name="vi-wpvs-check_key">
                            <i class="send icon"></i> <?php esc_html_e( 'Save & Check Key', 'woocommerce-product-variations-swatches' ) ?>
                        </button>
                        <button type="button" class="vi-ui button labeled icon vi-wpvs-import">
                            <i class="download icon"></i> <?php esc_html_e( 'Import Settings', 'woocommerce-product-variations-swatches' ) ?>
                        </button>
                        <button class="vi-ui button labeled icon"
                                name="vi-wpvs-export">
                            <i class="upload icon"></i> <?php esc_html_e( 'Export Settings', 'woocommerce-product-variations-swatches' ) ?>
                        </button>
                        <button type="button" class="vi-ui button labeled icon vi-wpvs-reset red" name="vi-wpvs-reset">
                            <i class="undo icon"></i> <?php esc_html_e( 'Reset Settings', 'woocommerce-product-variations-swatches' ) ?>
                        </button>
                    </p>
                    <div class="vi-ui vi-wpvs-import-wrap-wrap segment vi-wpvs-hidden">
                        <div class="vi-wpvs-import-wrap">
                            <input type="file" name="vi_wpvs_import_file" id="vi-wpvs-import-file"
                                   class="vi-wpvs-import-file" accept=".csv">
                            <button type="submit" class="vi-ui button green icon vi-wpvs-import"
                                    name="vi-wpvs-import-choose_file">
								<?php esc_html_e( 'Import', 'woocommerce-product-variations-swatches' ) ?>
                            </button>
                        </div>
                    </div>
                </form>
				<?php
				do_action( 'villatheme_support_woocommerce-product-variations-swatches' );
				?>
            </div>
        </div>
		<?php

	}

	public function save_settings() {
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
		if ( $page !== 'woocommerce-product-variations-swatches' ) {
			return;
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			global $sitepress;
			$default_lang           = $sitepress->get_default_language();
			$this->default_language = $default_lang;
			$languages              = icl_get_languages( 'skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str' );
			$this->languages_data   = $languages;
			if ( count( $languages ) ) {
				foreach ( $languages as $key => $language ) {
					if ( $key != $default_lang ) {
						$this->languages[] = $key;
					}
				}
			}
		} elseif ( class_exists( 'Polylang' ) ) {
			/*Polylang*/
			$languages    = pll_languages_list();
			$default_lang = pll_default_language( 'slug' );
			foreach ( $languages as $language ) {
				if ( $language == $default_lang ) {
					continue;
				}
				$this->languages[] = $language;
			}
		}


		if ( ! isset( $_POST['_vi_woo_product_variation_swatches_settings'] ) || ! wp_verify_nonce( $_POST['_vi_woo_product_variation_swatches_settings'],
				'_vi_woo_product_variation_swatches_settings_action' ) ) {
			return;
		}
		global $vi_wpvs_settings;
		if ( isset( $_POST['vi-wpvs-reset'] ) ) {
			$args = json_decode( $this->settings->get_reset_data(), true );
			update_option( 'vi_woo_product_variation_swatches_params', $args );
			$vi_wpvs_settings = $args;

			return;
		}
		if ( isset( $_POST['vi-wpvs-export'] ) ) {
			$filename     = 'wpvs_swatches_settings.csv';
			$export_value = json_encode( get_option( 'vi_woo_product_variation_swatches_params', $vi_wpvs_settings ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			preg_match_all( '/rgba\((.*)\)/im', $export_value, $matches );
			if ( count( $matches ) === 2 && count( $matches[0] ) ) {
				$export_value = str_replace( $matches[0], array_map( 'viwpvs_rgba2hex', $matches[0] ), $export_value );
			}
			$fh = @fopen( 'php://output', 'w' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: application/json;charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=' . $filename );
			header( 'Expires: 0' );
			header( 'Pragma: public' );
			fwrite( $fh, $export_value );
			fclose( $fh );
			die;
		}
		if ( isset( $_POST['vi-wpvs-import-choose_file'] ) ) {
			if ( ! isset( $_FILES['vi_wpvs_import_file'] ) ) {
				$this->error = __( 'File is empty. Please upload something more substantial. This error could also be caused by uploads being disabled in your php.ini or by post_max_size being defined as smaller than upload_max_filesize in php.ini.', 'woocommerce-product-variations-swatches' );

				return;
			}
			if ( ! empty( $_FILES['vi_wpvs_import_file']['error'] ) ) {
				$this->error = __( 'File is error.', 'woocommerce-product-variations-swatches' );

				return;
			}
			$import      = $_FILES['vi_wpvs_import_file'];
			$import_type = strtolower( pathinfo( $import['name'], PATHINFO_EXTENSION ) );
			if ( $import_type !== 'csv' ) {
				$this->error = __( 'Please select the csv file', 'woocommerce-product-variations-swatches' );

				return;
			}
			$file_content = file_get_contents( $import['tmp_name'] );
			if ( ! $file_content ) {
				$this->error = __( 'File is empty.', 'woocommerce-product-variations-swatches' );

				return;
			}
			if ( strpos( $file_content, 'check_swatches_settings' ) === false ) {
				$this->error = __( 'There isn\'t Swatches Settings. Please select the another', 'woocommerce-product-variations-swatches' );

				return;
			}
			$vi_wpvs_settings = json_decode( $file_content, true );
			update_option( 'vi_woo_product_variation_swatches_params', $vi_wpvs_settings );

			return;
		}
		if ( isset( $_POST['vi-wpvs-save'] ) || isset( $_POST['vi-wpvs-check_key'] ) ) {
			$map_args_1 = array(
				'ids',
				'names',
				'attribute_reduce_size_mobile',
				'attribute_reduce_size_list_product',
				'attribute_width',
				'attribute_height',
				'attribute_fontsize',
				'attribute_padding',
				'attribute_transition',
				'attribute_image_size',

				'attribute_default_box_shadow_color',
				'attribute_default_color',
				'attribute_default_bg_color',
				'attribute_default_border_color',
				'attribute_default_border_radius',
				'attribute_default_border_width',

				'attribute_hover_scale',
				'attribute_hover_box_shadow_color',
				'attribute_hover_color',
				'attribute_hover_bg_color',
				'attribute_hover_border_color',
				'attribute_hover_border_radius',
				'attribute_hover_border_width',

				'attribute_selected_scale',
				'attribute_selected_icon_enable',
				'attribute_selected_icon_type',
				'attribute_selected_icon_color',
				'attribute_selected_box_shadow_color',
				'attribute_selected_color',
				'attribute_selected_bg_color',
				'attribute_selected_border_color',
				'attribute_selected_border_radius',
				'attribute_selected_border_width',

				'attribute_out_of_stock',

				'attribute_value_enable',
				'attribute_value_cutoff_text',
				'attribute_value_position',
				'attribute_value_offset',
				'attribute_value_font_scale',
				'attribute_value_color',
				'attribute_value_bg_color',

				'attribute_tooltip_enable',
				'attribute_tooltip_content',
				'attribute_tooltip_type',
				'attribute_tooltip_position',
				'attribute_tooltip_width',
				'attribute_tooltip_image_width',
				'attribute_tooltip_height',
				'attribute_tooltip_fontsize',
				'attribute_tooltip_border_radius',
				'attribute_tooltip_bg_color',
				'attribute_tooltip_color',
				'attribute_tooltip_border_color',

				'custom_attribute_id',
				'custom_attribute_name',
				'custom_attribute_category',
				'custom_attribute_type',
				'custom_attribute_profiles',
				'custom_attribute_loop_enable',
				'custom_attribute_change_product_image',
				'custom_attribute_display_type',

				'product_list_slider_type',
			);
			$map_args_2 = array(
				'attribute_display_default',
				'attribute_blur_out_backorders',
				'attribute_profile_default',
				'out_of_stock_variation_disable',
				'out_of_stock_variation_disable_archive',
				'attribute_double_click',
				'single_attr_title',
				'single_align',
				'single_attr_selected',
				'single_swatches_on_des',
				'variation_threshold_single_page',
				'product_list_enable',
				'product_list_add_to_cart',
				'product_list_qty',
				'product_list_tooltip_enable',
				'product_list_double_click_enable',
				'product_list_attr_name_enable',
				'product_list_assign',
				'product_list_align',
				'product_list_position',
				'product_list_maximum_attr_item',
				'product_list_custom_hook',
				'product_list_more_link_enable',
				'product_list_maximum_more_link_text',
				'product_list_slider',
				'product_list_slider_min',
				'variation_threshold_archive_page',
				'purchased_code',
			);
			$map_args_3 = array(
				'custom_css',
				'product_list_add_to_cart_text',
			);
			$args       = array();
			foreach ( $map_args_1 as $item ) {
				$args[ $item ] = isset( $_POST[ $item ] ) ? viwpvs_sanitize_fields( $_POST[ $item ] ) : array();
			}
			foreach ( $map_args_2 as $item ) {
				$args[ $item ] = isset( $_POST[ $item ] ) ? sanitize_text_field( wp_unslash( $_POST[ $item ] ) ) : '';
			}
			foreach ( $map_args_3 as $item ) {
				$args[ $item ] = isset( $_POST[ $item ] ) ? wp_kses_post( wp_unslash( $_POST[ $item ] ) ) : '';
			}
			if ( count( $this->languages ) ) {
				foreach ( $this->languages as $key => $value ) {
					$args[ 'product_list_add_to_cart_text_' . $value ] = isset( $_POST[ 'product_list_add_to_cart_text_' . $value ] ) ? wp_kses_post( wp_unslash( $_POST[ 'product_list_add_to_cart_text_' . $value ] ) ) : '';
				}
			}
			if ( ! count( $args['names'] ) ) {
				$this->error = esc_html__( 'Can not remove all Countdown timer settings.', 'woocommerce-product-variations-swatches' );

				return;
			} else {
				if ( count( $args['names'] ) != count( array_unique( $args['names'] ) ) ) {
					$this->error = esc_html__( 'Names are unique.', 'woocommerce-product-variations-swatches' );

					return;
				}
				foreach ( $args['names'] as $key => $name ) {
					if ( ! $name ) {
						$this->error = esc_html__( 'Names can not be empty.', 'woocommerce-product-variations-swatches' );

						return;
					}
				}
			}
			$args = wp_parse_args( $args, get_option( 'vi_woo_product_variation_swatches_params', $vi_wpvs_settings ) );
			if ( isset( $_POST['vi-wpvs-check_key'] ) ) {
				delete_site_transient( 'update_plugins' );
				delete_transient( 'villatheme_item_54441' );
				delete_option( 'woocommerce-product-variations-swatches_messages' );
				do_action( 'villatheme_save_and_check_key_woocommerce-product-variations-swatches', $args['purchased_code'] );
			}
			update_option( 'vi_woo_product_variation_swatches_params', $args );
			$vi_wpvs_settings = $args;
		}

	}

	public function viwpvs_search_cate() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$keyword = filter_input( INPUT_GET, 'keyword', FILTER_SANITIZE_STRING );
		if ( ! $keyword ) {
			$keyword = filter_input( INPUT_POST, 'keyword', FILTER_SANITIZE_STRING );
		}
		if ( empty( $keyword ) ) {
			die();
		}
		$categories = get_terms(
			array(
				'taxonomy' => 'product_cat',
				'orderby'  => 'name',
				'order'    => 'ASC',
				'search'   => $keyword,
				'number'   => 100
			)
		);
		$items      = array();
		if ( count( $categories ) ) {
			foreach ( $categories as $category ) {
				$item    = array(
					'id'   => $category->term_id,
					'text' => $category->name
				);
				$items[] = $item;
			}
		}
		wp_send_json( $items );
		die;
	}

	public function admin_enqueue_scripts() {
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		if ( $page === 'woocommerce-product-variations-swatches' ) {
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
			/*Stylesheet*/
			wp_enqueue_style( 'semantic-ui-accordion', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'accordion.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-button', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'button.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-checkbox', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'checkbox.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-dropdown', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'dropdown.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-form', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'form.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-header', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'header.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-icon', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'icon.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-input', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'input.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-label', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'label.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-menu', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'menu.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-message', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'message.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-popup', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'popup.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-segment', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'segment.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'select2', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'select2.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'transition', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'transition.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-tab', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'tab.min.css', '', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'vi-wpvs-admin', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'admin-settings.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'vi-wpvs-admin-minicolors', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'minicolors.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'semantic-ui-accordion', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'accordion.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'semantic-ui-address', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'address.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'semantic-ui-checkbox', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'checkbox.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'semantic-ui-dropdown', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'dropdown.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'semantic-ui-form', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'form.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'select2', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'select2.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'semantic-ui-tab', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'tab.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'transition', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'transition.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'vi-wpvs-admin', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'admin-settings.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'vi-wpvs-admin-minicolors', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'minicolors.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_localize_script( 'vi-wpvs-admin', 'vi_wpvs_admin_settings', array(
				'i18n_copy_shortcode'   => esc_html__( 'Click to copy', 'woocommerce-product-variations-swatches' ),
				'i18n_shortcode_copied' => esc_html__( 'Copied to clipboard!', 'woocommerce-product-variations-swatches' ),
			) );
		}
	}

	public function default_language_flag_html( $name = '' ) {
		if ( $this->default_language ) {
			?>
            <p>
                <label for="<?php echo esc_attr( $name ) ?>"><?php
					if ( isset( $this->languages_data[ $this->default_language ]['country_flag_url'] ) && $this->languages_data[ $this->default_language ]['country_flag_url'] ) {
						?>
                        <img src="<?php echo esc_url( $this->languages_data[ $this->default_language ]['country_flag_url'] ); ?>">
						<?php
					}
					echo $this->default_language;
					if ( isset( $this->languages_data[ $this->default_language ]['translated_name'] ) ) {
						echo '(' . $this->languages_data[ $this->default_language ]['translated_name'] . '):';
					}
					?></label>
            </p>
			<?php
		}
	}

}