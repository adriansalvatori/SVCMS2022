<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Admin_Woo_Widget {
	protected $settings, $error;
	public function __construct() {
		$this->settings = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DATA::get_instance();
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 30 );
		add_action( 'admin_init', array( $this, 'save_settings' ), 99 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), PHP_INT_MAX );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_controls_enqueue_scripts' ) );
		add_action( 'customize_preview_init', array( $this, 'customize_preview_init' ) );
	}
	public function admin_menu() {
		add_submenu_page(
			'woocommerce-product-variations-swatches',
			esc_html__( 'Swatches Settings for WooCommerce Filter Widget', 'woocommerce-product-variations-swatches' ),
			esc_html__( 'Woo Filter Widget', 'woocommerce-product-variations-swatches' ),
			'manage_woocommerce',
			'woocommerce-product-variations-swatches-woo-widget',
			array( $this, 'settings_callback' )
		);
	}
	public function save_settings(){
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
		if ( $page !== 'woocommerce-product-variations-swatches-woo-widget' ) {
			return;
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		if ( ! isset( $_POST['_vi_woo_product_variation_swatches_settings'] ) || ! wp_verify_nonce( $_POST['_vi_woo_product_variation_swatches_settings'],
				'_vi_woo_product_variation_swatches_settings_action' ) ) {
			return;
		}
		global $vi_wpvs_settings;
		if ( isset( $_POST['vi-wpvs-save'] ) ){
			$map_args_1 = array(
				'woo_widget_pd_count_default',
				'woo_widget_pd_count_hover',
				'woo_widget_pd_count_selected',
				'woo_widget_term_default',
				'woo_widget_term_hover',
				'woo_widget_term_selected',
			);
			$map_args_2 = array(
				'woo_widget_enable',
				'woo_widget_max_items',
				'woo_widget_display_style',
				'woo_widget_pd_count_enable',
			);
			$args       = array();
			foreach ( $map_args_1 as $item ) {
				$args[ $item ] = isset( $_POST[ $item ] ) ? viwpvs_sanitize_fields( $_POST[ $item ] ) : array();
			}
			foreach ( $map_args_2 as $item ) {
				$args[ $item ] = isset( $_POST[ $item ] ) ? sanitize_text_field( stripslashes( $_POST[ $item ] ) ) : '';
			}
			$args = wp_parse_args( $args, get_option( 'vi_woo_product_variation_swatches_params', $vi_wpvs_settings ) );
			update_option( 'vi_woo_product_variation_swatches_params', $args );
			$vi_wpvs_settings = $args;
		}
	}
	public function settings_callback(){
		$this->settings                      = VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DATA::get_instance(true);
		?>
		<div id="vi-wpvs-message" class="error <?php echo $this->error ? '' : esc_attr( 'hidden' ); ?>">
			<p><?php echo esc_html( $this->error ); ?></p>
		</div>
		<div class="wrap">
			<h2 class=""><?php esc_html_e( 'Swatches Settings for WooCommerce Filter Widget', 'woocommerce-product-variations-swatches' ) ?></h2>
			<div class="vi-ui raised">
				<form class="vi-ui form" method="post">
					<?php
					wp_nonce_field( '_vi_woo_product_variation_swatches_settings_action', '_vi_woo_product_variation_swatches_settings' );
					?>
					<div class="vi-ui vi-ui-main top tabular attached menu">
						<a class="item active" data-tab="general"><?php esc_html_e( 'General Settings', 'woocommerce-product-variations-swatches' ); ?></a>
					</div>
					<div class="vi-ui bottom attached tab segment active" data-tab="general">
						<?php
						$woo_widget_enable            = $this->settings->get_params( 'woo_widget_enable' );
						$woo_widget_max_items            = $this->settings->get_params( 'woo_widget_max_items' );
						$woo_widget_display_style     = $this->settings->get_params( 'woo_widget_display_style' );
						$woo_widget_pd_count_enable   = $this->settings->get_params( 'woo_widget_pd_count_enable' );
						$woo_widget_pd_count_default  = $this->settings->get_params( 'woo_widget_pd_count_default' );
						$woo_widget_pd_count_hover    = $this->settings->get_params( 'woo_widget_pd_count_hover' );
						$woo_widget_pd_count_selected = $this->settings->get_params( 'woo_widget_pd_count_selected' );
						$woo_widget_term_default      = $this->settings->get_params( 'woo_widget_term_default' );
						$woo_widget_term_hover        = $this->settings->get_params( 'woo_widget_term_hover' );
						$woo_widget_term_selected     = $this->settings->get_params( 'woo_widget_term_selected' );
						?>
						<div class="vi-ui blue message">
							<?php esc_html_e( 'Settings the Swatches for \'Filter Products by Attribute\' WooCommerce: ', 'woocommerce-product-variations-swatches' ); ?>
							<ul class="list">
								<li><?php esc_html_e( 'Change  \'Display type\' on Widget settings to  \'List\' to use the below settings', 'woocommerce-product-variations-swatches' ); ?></li>
								<li><?php esc_html_e( 'For special taxonomy, please go to \'Global Attributes\' to set display type', 'woocommerce-product-variations-swatches' ); ?></li>
							</ul>
						</div>
						<table class="form-table">
							<tr>
								<th>
									<label for="vi-wpvs-woo_widget_enable-checkbox"><?php esc_html_e( 'Enable', 'woocommerce-product-variations-swatches' ); ?></label>
								</th>
								<td>
									<div class="vi-ui toggle checkbox">
										<input type="hidden" name="woo_widget_enable" id="vi-wpvs-woo_widget_enable" value="<?php echo esc_attr( $woo_widget_enable ) ?>">
										<input type="checkbox" id="vi-wpvs-woo_widget_enable-checkbox" <?php checked( $woo_widget_enable, 1 ); ?>><label></label>
									</div>
								</td>
							</tr>
							<tr>
								<th>
									<label for="vi-wpvs-woo_widget_max_items"><?php esc_html_e( 'Max items', 'woocommerce-product-variations-swatches' ); ?></label>
								</th>
								<td>
                                    <input type="number" name="woo_widget_max_items" min="0" id="vi-wpvs-woo_widget_max_items" value="<?php echo esc_attr( $woo_widget_max_items ) ?>">
                                    <p class="description"><?php esc_html_e( 'Maximum items shown by default. There will be a "Show more" button if the number of attributes is greater than this value.', 'woocommerce-product-variations-swatches' ); ?></p>
                                    <p class="description"><?php esc_html_e( 'If set to 0, all terms will be shown by default.', 'woocommerce-product-variations-swatches' ); ?></p>
								</td>
							</tr>
							<tr>
								<th>
									<label for="vi-wpvs-woo_widget_display_style"><?php esc_html_e( 'Display style', 'woocommerce-product-variations-swatches' ); ?></label>
								</th>
								<td>
									<select name="woo_widget_display_style" id="vi-wpvs-woo_widget_display_style"
									        class="vi-ui fluid dropdown vi-wpvs-woo_widget_display_style">
										<option value="horizontal" <?php selected( $woo_widget_display_style, 'horizontal' ) ?>>
											<?php esc_html_e( 'Horizontal', 'woocommerce-product-variations-swatches' ); ?>
										</option>
										<option value="vertical" <?php selected( $woo_widget_display_style, 'vertical' ) ?>>
											<?php esc_html_e( 'Vertical', 'woocommerce-product-variations-swatches' ); ?>
										</option>
									</select>
								</td>
							</tr>
						</table>
						<div class="vi-ui styled fluid accordion vi-wpvs-accordion-wrap">
							<div class="woo-wpvs-accordion-info">
                                <span>
                                    <h4><span class="vi-wpvs-accordion-name"><?php esc_html_e( 'Design attribute', 'woocommerce-product-variations-swatches' ); ?></span></h4>
					            </span>
							</div>
							<div class="title active">
								<i class="dropdown icon"></i>
								<?php esc_html_e( 'Default styling', 'woocommerce-product-variations-swatches' ); ?>
							</div>
							<div class="content active">
								<div class="equal width fields">
									<div class="field" data-tooltip="<?php esc_attr_e( 'Display attribute label for color and image type', 'woocommerce-product-variations-swatches' ); ?>">
										<label for="vi-wpvs-woo_widget_term_default-name_enable-checkbox"><?php esc_html_e( 'Show attribute label', 'woocommerce-product-variations-swatches' ); ?></label>
										<div class="vi-ui toggle checkbox">
											<input type="hidden" name="woo_widget_term_default[name_enable]"
											       id="vi-wpvs-woo_widget_term_default-name_enable"
											       value="<?php echo esc_attr( $woo_widget_term_default['name_enable'] ?? '' ); ?>">
											<input type="checkbox" id="vi-wpvs-woo_widget_term_default-name_enable-checkbox"
												<?php checked( $woo_widget_term_default['name_enable'], 1 ); ?>>
										</div>
									</div>
									<div class="field">
										<label><?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text"
										       class="vi-wpvs-color vi-wpvs-woo_widget_term_default-color"
										       name="woo_widget_term_default[color]"
										       value="<?php echo esc_attr( $woo_widget_term_default['color'] ?? '' ); ?>">
									</div>
									<div class="field">
										<label><?php esc_html_e( 'Background', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text"
										       class="vi-wpvs-color vi-wpvs-woo_widget_term_default-bg_color"
										       name="woo_widget_term_default[bg_color]"
										       value="<?php echo esc_attr( $woo_widget_term_default['bg_color'] ?? '' ); ?>">
									</div>
									<div class="field">
										<label><?php esc_html_e( 'Box shadow color', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text"
										       class="vi-wpvs-color vi-wpvs-woo_widget_term_default-box_shadow_color"
										       name="woo_widget_term_default[box_shadow_color]"
										       value="<?php echo esc_attr( $woo_widget_term_default['box_shadow_color'] ?? '' ); ?>">
									</div>
									<div class="field vi-wpvs-field-min-width">
										<label><?php esc_html_e( 'Padding', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text" name="woo_widget_term_default[padding]"
										       class="vi-wpvs-woo_widget_term_default-padding"
										       placeholder="<?php echo esc_attr( 'eg: 3px 5px' ); ?>"
										       value="<?php echo esc_attr( $woo_widget_term_default['padding'] ?? '' ); ?>">
									</div>
								</div>
							</div>
							<div class="title">
								<i class="dropdown icon"></i>
								<?php esc_html_e( 'Hover styling', 'woocommerce-product-variations-swatches' ); ?>
							</div>
							<div class="content">
								<div class="equal width fields">
									<div class="field">
										<label><?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text"
										       class="vi-wpvs-color vi-wpvs-woo_widget_term_hover-color"
										       name="woo_widget_term_hover[color]"
										       value="<?php echo esc_attr( $woo_widget_term_hover['color'] ?? '' ); ?>">
									</div>
									<div class="field">
										<label><?php esc_html_e( 'Background', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text"
										       class="vi-wpvs-color vi-wpvs-woo_widget_term_hover-bg_color"
										       name="woo_widget_term_hover[bg_color]"
										       value="<?php echo esc_attr( $woo_widget_term_hover['bg_color'] ?? '' ); ?>">
									</div>
									<div class="field">
										<label><?php esc_html_e( 'Box shadow color', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text"
										       class="vi-wpvs-color vi-wpvs-woo_widget_term_hover-box_shadow_color"
										       name="woo_widget_term_hover[box_shadow_color]"
										       value="<?php echo esc_attr( $woo_widget_term_hover['box_shadow_color'] ?? '' ); ?>">
									</div>
								</div>
							</div>
							<div class="title">
								<i class="dropdown icon"></i>
								<?php esc_html_e( 'Selected styling', 'woocommerce-product-variations-swatches' ); ?>
							</div>
							<div class="content">
								<div class="equal width fields">
									<div class="field">
										<label><?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text"
										       class="vi-wpvs-color vi-wpvs-woo_widget_term_selected-color"
										       name="woo_widget_term_selected[color]"
										       value="<?php echo esc_attr( $woo_widget_term_selected['color'] ?? '' ); ?>">
									</div>
									<div class="field">
										<label><?php esc_html_e( 'Background', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text"
										       class="vi-wpvs-color vi-wpvs-woo_widget_term_selected-bg_color"
										       name="woo_widget_term_selected[bg_color]"
										       value="<?php echo esc_attr( $woo_widget_term_selected['bg_color'] ?? '' ); ?>">
									</div>
									<div class="field">
										<label><?php esc_html_e( 'Box shadow color', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text"
										       class="vi-wpvs-color vi-wpvs-woo_widget_term_selected-box_shadow_color"
										       name="woo_widget_term_selected[box_shadow_color]"
										       value="<?php echo esc_attr( $woo_widget_term_selected['box_shadow_color'] ?? '' ); ?>">
									</div>
								</div>
							</div>
						</div>
						<div class="vi-ui styled fluid accordion vi-wpvs-accordion-wrap">
							<div class="woo-wpvs-accordion-info">
                                <span>
                                    <h4><span class="vi-wpvs-accordion-name"><?php esc_html_e( 'Product count', 'woocommerce-product-variations-swatches' ); ?></span></h4>
					            </span>
							</div>
							<div class="title active">
								<i class="dropdown icon"></i>
								<?php esc_html_e( 'Default styling', 'woocommerce-product-variations-swatches' ); ?>
							</div>
							<div class="content active">
								<div class="equal width fields">
									<div class="field">
										<label for="vi-wpvs-woo_widget_pd_count_enable-checkbox"><?php esc_html_e( 'Enable', 'woocommerce-product-variations-swatches' ); ?></label>
										<div class="vi-ui toggle checkbox">
											<input type="hidden" name="woo_widget_pd_count_enable" id="vi-wpvs-woo_widget_pd_count_enable"
											       value="<?php echo esc_attr( $woo_widget_pd_count_enable ); ?>">
											<input type="checkbox" id="vi-wpvs-woo_widget_pd_count_enable-checkbox" <?php checked( $woo_widget_pd_count_enable, 1 ); ?> >
										</div>
									</div>
									<div class="field">
										<label><?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text"
										       class="vi-wpvs-color vi-wpvs-woo_widget_pd_count_default-color"
										       name="woo_widget_pd_count_default[color]"
										       value="<?php echo esc_attr( $woo_widget_pd_count_default['color'] ?? '' ); ?>">
									</div>
									<div class="field">
										<label><?php esc_html_e( 'Background', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text"
										       class="vi-wpvs-color vi-wpvs-woo_widget_pd_count_default-bg_color"
										       name="woo_widget_pd_count_default[bg_color]"
										       value="<?php echo esc_attr( $woo_widget_pd_count_default['bg_color'] ?? '' ); ?>">
									</div>
									<div class="field">
										<label><?php esc_html_e( 'Border color', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text"
										       class="vi-wpvs-color vi-wpvs-woo_widget_pd_count_default-border_color"
										       name="woo_widget_pd_count_default[border_color]"
										       value="<?php echo esc_attr( $woo_widget_pd_count_default['border_color'] ?? '' ); ?>">
									</div>
								</div>
								<div class="equal width fields">
									<div class="field"></div>
									<div class="field vi-wpvs-field-min-width">
										<label><?php esc_html_e( 'Padding', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text" name="woo_widget_pd_count_default[padding]"
										       class="vi-wpvs-woo_widget_pd_count_default-padding"
										       placeholder="<?php echo esc_attr( 'eg: 3px 5px' ); ?>"
										       value="<?php echo esc_attr( $woo_widget_pd_count_default['padding'] ?? '' ); ?>">
									</div>
									<div class="field vi-wpvs-field-min-width">
										<label><?php esc_html_e( 'Border width', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text" name="woo_widget_pd_count_default[border_width]"
										       class="vi-wpvs-woo_widget_pd_count_default-border_width"
										       placeholder="<?php echo esc_attr( 'eg: 1px 1px 1px 1px ( top right bottom left)' ); ?>"
										       value="<?php echo esc_attr( $woo_widget_pd_count_default['border_width'] ?? '' ); ?>">
									</div>
									<div class="field">
										<label><?php esc_html_e( 'Border radius', 'woocommerce-product-variations-swatches' ); ?></label>
										<div class="vi-ui right labeled input">
											<input type="number" min="0" class="vi-wpvs-woo_widget_pd_count_default-border_radius"
											       name="woo_widget_pd_count_default[border_radius]"
											       value="<?php echo esc_attr( $woo_widget_pd_count_default['border_radius'] ?? '' ); ?>">
											<div class="vi-ui label vi-wpvs-basic-label"><?php echo esc_html( 'Px' ); ?></div>
										</div>
									</div>
								</div>
							</div>
							<div class="title">
								<i class="dropdown icon"></i>
								<?php esc_html_e( 'Hover styling', 'woocommerce-product-variations-swatches' ); ?>
							</div>
							<div class="content">
								<div class="equal width fields">
									<div class="field">
										<label><?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text"
										       class="vi-wpvs-color vi-wpvs-woo_widget_pd_count_hover-color"
										       name="woo_widget_pd_count_hover[color]"
										       value="<?php echo esc_attr( $woo_widget_pd_count_hover['color'] ?? '' ); ?>">
									</div>
									<div class="field">
										<label><?php esc_html_e( 'Background', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text"
										       class="vi-wpvs-color vi-wpvs-woo_widget_pd_count_hover-bg_color"
										       name="woo_widget_pd_count_hover[bg_color]"
										       value="<?php echo esc_attr( $woo_widget_pd_count_hover['bg_color'] ?? '' ); ?>">
									</div>
									<div class="field">
										<label><?php esc_html_e( 'Border color', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text"
										       class="vi-wpvs-color vi-wpvs-woo_widget_pd_count_hover-border_color"
										       name="woo_widget_pd_count_hover[border_color]"
										       value="<?php echo esc_attr( $woo_widget_pd_count_hover['border_color'] ?? '' ); ?>">
									</div>
									<div class="field">
										<label><?php esc_html_e( 'Border radius', 'woocommerce-product-variations-swatches' ); ?></label>
										<div class="vi-ui right labeled input">
											<input type="number" min="0" class="vi-wpvs-woo_widget_pd_count_hover-border_radius"
											       name="woo_widget_pd_count_hover[border_radius]"
											       value="<?php echo esc_attr( $woo_widget_pd_count_hover['border_radius'] ?? '' ); ?>">
											<div class="vi-ui label vi-wpvs-basic-label"><?php echo esc_html( 'Px' ); ?></div>
										</div>
									</div>
								</div>
							</div>
							<div class="title">
								<i class="dropdown icon"></i>
								<?php esc_html_e( 'Selected styling', 'woocommerce-product-variations-swatches' ); ?>
							</div>
							<div class="content">
								<div class="equal width fields">
									<div class="field">
										<label><?php esc_html_e( 'Color', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text"
										       class="vi-wpvs-color vi-wpvs-woo_widget_pd_count_selected-color"
										       name="woo_widget_pd_count_selected[color]"
										       value="<?php echo esc_attr( $woo_widget_pd_count_selected['color'] ?? '' ); ?>">
									</div>
									<div class="field">
										<label><?php esc_html_e( 'Background', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text"
										       class="vi-wpvs-color vi-wpvs-woo_widget_pd_count_selected-bg_color"
										       name="woo_widget_pd_count_selected[bg_color]"
										       value="<?php echo esc_attr( $woo_widget_pd_count_selected['bg_color'] ?? '' ); ?>">
									</div>
									<div class="field">
										<label><?php esc_html_e( 'Border color', 'woocommerce-product-variations-swatches' ); ?></label>
										<input type="text"
										       class="vi-wpvs-color vi-wpvs-woo_widget_pd_count_selected-border_color"
										       name="woo_widget_pd_count_selected[border_color]"
										       value="<?php echo esc_attr( $woo_widget_pd_count_selected['border_color'] ?? '' ); ?>">
									</div>
									<div class="field">
										<label><?php esc_html_e( 'Border radius', 'woocommerce-product-variations-swatches' ); ?></label>
										<div class="vi-ui right labeled input">
											<input type="number" min="0" class="vi-wpvs-woo_widget_pd_count_selected-border_radius"
											       name="woo_widget_pd_count_selected[border_radius]"
											       value="<?php echo esc_attr( $woo_widget_pd_count_selected['border_radius'] ?? '' ); ?>">
											<div class="vi-ui label vi-wpvs-basic-label"><?php echo esc_html( 'Px' ); ?></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<p class="vi-wpvs-save-wrap">
						<button type="submit" class="vi-wpvs-save vi-ui primary button" name="vi-wpvs-save">
							<?php esc_html_e( 'Save', 'woocommerce-product-variations-swatches' ); ?>
						</button>
					</p>
				</form>
			</div>
		</div>
		<?php
	}
	public function customize_preview_init(){
		wp_enqueue_script( 'vi-wpvs-customize-preview-woo-widget',
            VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'customize-preview-woo-widget.js',
            array( 'jquery' ,'customize-preview'),
            VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION
        );
    }
	public function customize_controls_enqueue_scripts(){
		if ($this->settings->get_params('woo_widget_enable')){
		    wp_enqueue_style( 'vi-wpvs-admin-minicolors', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'minicolors.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'vi-wpvs-customize-woo-widget', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'customize-woo-widget.css', array(  ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'vi-wpvs-admin-minicolors', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'minicolors.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'vi-wpvs-customize-woo-widget', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'customize-woo-widget.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
		}
    }
	public function admin_enqueue_scripts(){
		global $pagenow;
		if ($pagenow ==='widgets.php' && $this->settings->get_params('woo_widget_enable')){
			wp_enqueue_style( 'vi-wpvs-admin-minicolors', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'minicolors.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'vi-wpvs-customize-woo-widget', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'customize-woo-widget.css', array(  ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'vi-wpvs-admin-minicolors', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'minicolors.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'vi-wpvs-customize-woo-widget', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'customize-woo-widget.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
		}
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		if ( $page === 'woocommerce-product-variations-swatches-woo-widget' ) {
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
			wp_enqueue_style( 'semantic-ui-accordion', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'accordion.min.css',array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-button', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'button.min.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-checkbox', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'checkbox.min.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-dropdown', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'dropdown.min.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-form', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'form.min.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-icon', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'icon.min.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-input', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'input.min.css',array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-label', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'label.min.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-menu', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'menu.min.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-message', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'message.min.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-popup', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'popup.min.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-segment', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'segment.min.css',array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'transition', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'transition.min.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'semantic-ui-tab', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'tab.min.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'vi-wpvs-admin', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'admin-settings.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_style( 'vi-wpvs-admin-minicolors', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS . 'minicolors.css', array(), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );

			wp_enqueue_script( 'semantic-ui-accordion', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'accordion.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'semantic-ui-address', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'address.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'semantic-ui-checkbox', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'checkbox.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'semantic-ui-dropdown', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'dropdown.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'semantic-ui-form', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'form.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'transition', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'transition.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'vi-wpvs-admin-woo-widget', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'admin-setting-woo-widget.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
			wp_enqueue_script( 'vi-wpvs-admin-minicolors', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS . 'minicolors.min.js', array( 'jquery' ), VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION );
		}
	}
}