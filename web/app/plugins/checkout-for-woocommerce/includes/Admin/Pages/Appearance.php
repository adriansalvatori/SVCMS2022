<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

use Objectiv\Plugins\Checkout\Admin\TabNavigation;
use Objectiv\Plugins\Checkout\Model\Template;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Admin\Pages
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class Appearance extends PageAbstract {
	protected $settings_manager;
	protected $tabbed_navigation;

	public function __construct( SettingsManager $settings_manager ) {
		$this->settings_manager = $settings_manager;

		parent::__construct( cfw__( 'Appearance', 'checkout-wc' ), 'manage_options', 'appearance' );
	}

	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ), 1000 );
		add_action( $this->settings_manager->prefix . '_settings_saved', array( $this, 'maybe_activate_theme' ) );
		add_action( 'cfw_before_admin_page_header', array( $this, 'setup_tabs' ) );

		parent::init();
	}

	public function maybe_activate_theme() {
		$prefix = $this->settings_manager->prefix;

		$new_settings = stripslashes_deep( $_REQUEST[ "{$prefix}_setting" ] );

		if ( empty( $new_settings['active_template'] ) ) {
			return;
		}

		$active_template = new Template( $this->settings_manager->get_setting( 'active_template' ) );
		$active_template->init();
	}

	public function enqueue_assets() {
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_media();

		wp_enqueue_script( 'cfw-webfont-loader', 'https://cdnjs.cloudflare.com/ajax/libs/webfont/1.6.28/webfontloader.js' );
	}

	public function setup_tabs() {
		$this->tabbed_navigation = new TabNavigation( 'Appearance', 'subpage' );

		$this->tabbed_navigation->add_tab( 'Design', add_query_arg( array( 'subpage' => 'design' ), $this->get_url() ) );
		$this->tabbed_navigation->add_tab( 'Template', add_query_arg( array( 'subpage' => 'templates' ), $this->get_url() ) );
	}

	public function output() {
		if ( $this->get_current_tab() === false ) {
			$_GET['subpage'] = 'design';
		}

		$this->tabbed_navigation->display_tabs();

		if ( $this->get_current_tab() === 'templates' ) {
			$this->templates_tab();
		}

		if ( $this->get_current_tab() === 'design' ) {
			$this->design_tab();
		}
	}

	public function templates_tab() {
		$settings        = SettingsManager::instance();
		$templates       = Template::get_all_available();
		$active_template = $settings->get_setting( 'active_template' );
		?>
		<div class="cfw-theme-browser">
			<div class="flex flex-wrap">
				<?php
				foreach ( $templates as $template ) :
					$screenshot = $template->get_template_uri() . '/screenshot.png';

					$active      = ( $active_template === $template->get_slug() );
					$preview_url = wc_get_checkout_url();
					$products    = wc_get_products(
						array(
							'limit'  => 1,
							'status' => 'publish',
							'type'   => array( 'simple' ),
						)
					);

					if ( empty( $products ) ) {
						$products = wc_get_products(
							array(
								'parent_exclude' => 0,
								'limit'          => 1,
								'status'         => 'publish',
								'type'           => array( 'variable' ),
							)
						);
					}

					// Get any simple or variable woocommerce product
					if ( ! empty( $products ) ) {
						$product = $products[0];

						$preview_url = add_query_arg( array( 'add-to-cart' => $product->get_id() ), $preview_url );
					}

					$preview_url = add_query_arg( array( 'cfw-preview' => $template->get_slug() ), $preview_url );
					?>
					<div class="theme max-w-xl mr-8 mb-8 shadow-lg <?php echo $active ? 'active' : ''; ?>">
						<div class="theme-screenshot">
							<img src="<?php echo $screenshot; ?>" class="w-full" />
						</div>
						<div class="flex flex-row justify-between items-center px-4 py-2 <?php echo $active ? 'bg-black text-white' : 'bg-gray-50'; ?> min-h-[50px] border-t-2 border-gray-200">

							<div class="text-base" id="<?php echo $template->get_slug(); ?>-name">
								<strong>
									<?php echo $active ? cfw__( 'Active: ' ) : ''; ?>
								</strong>

								<?php echo $template->get_name(); ?>
								<a class="<?php echo $active ? 'invisible' : ''; ?> block text-sm text-blue-600" target="_blank" href="<?php echo $preview_url; ?>">Preview</a>
							</div>

							<form name="settings" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
								<input type="hidden" name="<?php echo $settings->get_field_name( 'active_template' ); ?>" value="<?php echo $template->get_slug(); ?>" />
								<?php $settings->the_nonce(); ?>
								<?php submit_button( cfw__( 'Activate', 'checkout-wc' ), 'button-secondary', $name = 'submit', $wrap = false ); ?>
							</form>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	public function design_tab() {
		$this->output_form_open();
		?>
		<div class="space-y-6 mt-4">
			<?php
			cfw_admin_page_section(
				cfw__( 'Global Settings', 'checkout-wc' ),
				cfw__( 'These settings apply to all templates.', 'checkout-wc' ),
				$this->get_global_settings()
			);

			cfw_admin_page_section(
				'Template Settings',
				sprintf( cfw__( 'These settings apply to your selected theme. (%s)', 'checkout-wc' ), cfw_get_active_template()->get_name() ),
				$this->get_template_settings()
			);
			?>
		</div>
		<?php
		$this->output_form_close();
	}

	protected function get_global_settings() : string {
		$settings = SettingsManager::instance();
		ob_start();
		?>
		<div class="cfw-admin-field-container cfw-admin-upload-control-parent">
			<legend class="text-base font-medium text-gray-900">
				<?php echo esc_html( cfw__( 'Logo', 'checkout-wc' ) ); ?>
			</legend>
			<p class="text-sm leading-5 text-gray-500">
				<?php echo cfw__( 'Choose the logo you wish to display in the header. If you do not choose a logo we will use your site name.', 'checkout-wc' ); ?>
			</p>
			<div class="cfw-admin-image-preview-wrapper mb-4 mt-4">
				<img class="cfw-admin-image-preview" src='<?php echo wp_get_attachment_url( $settings->get_setting( 'logo_attachment_id' ) ); ?>' width='100' style='max-height: 100px; width: 100px;'>
			</div>
			<input class="cfw-admin-image-picker-button button" type="button" value="<?php cfw_e( 'Upload image' ); ?>" />
			<input type='hidden' name='<?php echo $settings->get_field_name( 'logo_attachment_id' ); ?>' id='logo_attachment_id' value="<?php echo $settings->get_setting( 'logo_attachment_id' ); ?>">

			<a class="delete-custom-img button secondary-button"><?php cfw_e( 'Clear Logo', 'checkout-wc' ); ?></a>
		</div>

		<?php

		$this->output_radio_group_row(
			'label_style',
			cfw__( 'Field Label Style', 'checkout-wc' ),
			cfw__( 'Choose how you want form labels styled.', 'checkout-wc' ),
			'floating',
			array(
				'floating' => cfw__( 'Floating (Recommended)', 'checkout-wc' ),
				'normal'   => cfw__( 'Normal', 'checkout-wc' ),
			),
			array(
				'floating' => cfw__( 'Automatically show and hide labels based on whether the field has a value. (Recommended)', 'checkout-wc' ),
				'normal'   => cfw__( 'Labels appear above each field at all times.', 'checkout-wc' ),
			)
		);

		$this->output_textarea_row(
			'footer_text',
			cfw__( 'Footer Text', 'checkout-wc' ),
			cfw__( 'If left blank, a standard copyright notice will be displayed. Set to a single space to override this behavior.', 'checkout-wc' ),
			array(
				'textarea_rows' => 5,
				'tinymce'       => true,
				'media_buttons' => true,
			)
		);

		return ob_get_clean();
	}

	protected function get_template_settings() {
		$fonts_list           = $this->get_fonts_list();
		$settings             = SettingsManager::instance();
		$current_body_font    = $settings->get_setting( 'body_font' );
		$current_heading_font = $settings->get_setting( 'heading_font' );
		$template_path        = cfw_get_active_template()->get_slug();

		ob_start();
		?>
		<div class="cfw-admin-field-container">
			<h3 class="text-lg leading-6 font-medium text-gray-900">
				<?php cfw_e( 'Typography', 'checkout-wc' ); ?>
			</h3>
			<p class="mt-1 text-sm text-gray-500">
				<?php cfw_e( 'By default, CheckoutWC uses a System Font Stack, which yields the fastest performance. You may choose a Google Font below.', 'checkout-wc' ); ?>
			</p>
			<label for="cfw-body-font-selector" class="block text-sm font-medium text-gray-700 mt-4">
				<?php cfw_e( 'Body Font', 'checkout-wc' ); ?>
			</label>
			<select id="cfw-body-font-selector" class="wc-enhanced-select mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" name="<?php echo $settings->get_field_name( 'body_font' ); ?>">
				<option value="System Font Stack"><?php cfw_e( 'System Font Stack', 'checkout-wc' ); ?></option>
				<?php foreach ( $fonts_list->items as $font ) : ?>
					<option value="<?php echo $font->family; ?>"<?php echo $font->family === $current_body_font ? 'selected="selected"' : ''; ?> >
						<?php echo $font->family; ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="cfw-admin-field-container">
			<label for="cfw-heading-font-selector" class="block text-sm font-medium text-gray-700">
				<?php cfw_e( 'Heading Font', 'checkout-wc' ); ?>
			</label>
			<select id="cfw-heading-font-selector" class="wc-enhanced-select" name="<?php echo $settings->get_field_name( 'heading_font' ); ?>">
				<option value="System Font Stack"><?php cfw_e( 'System Font Stack', 'checkout-wc' ); ?></option>

				<?php foreach ( $fonts_list->items as $font ) : ?>
					<option value="<?php echo $font->family; ?>" <?php echo $font->family === $current_heading_font ? 'selected="selected"' : ''; ?> >
						<?php echo $font->family; ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<?php
		$this->output_textarea_row(
			'custom_css',
			cfw__( 'Custom CSS', 'checkout-wc' ),
			cfw__( 'Add Custom CSS rules to fully control the appearance of the checkout template.', 'checkout-wc' ),
			array(
				'setting_seed' => array( $template_path ),
			)
		);
		?>

		<?php foreach ( $this->get_theme_color_settings() as $color_settings_section ) : ?>
			<?php
			if ( empty( $color_settings_section['settings'] ) ) {
				continue;
			}
			?>
			<div class="cfw-admin-field-container">
				<h3 class="text-lg leading-6 font-medium text-gray-900">
					<?php echo esc_html( $color_settings_section['title'] ); ?>
				</h3>

				<div class="flex flex-wrap">
					<?php foreach ( $color_settings_section['settings'] as $key => $label ) : ?>
						<?php
						$this->output_color_picker_input(
							$key,
							$label,
							cfw_get_active_template()->get_default_setting( $key ),
							array(
								'setting_seed'       => array( $template_path ),
								'additional_classes' => array( 'w-1/3' ),
							)
						);
						?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
		<?php
		return ob_get_clean();
	}

	public function get_current_tab() {
		return empty( $_GET['subpage'] ) ? false : $_GET['subpage'];
	}

	public function get_fonts_list() {
		$cfw_google_fonts_list = get_transient( 'cfw_google_font_list' );

		if ( empty( $cfw_google_fonts_list ) ) {
			$cfw_google_fonts_list = wp_remote_get( 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyAkSLrj88M_Y-rFfjRI2vgIzjIZ0N1fynE&sort=popularity' );
			$cfw_google_fonts_list = json_decode( wp_remote_retrieve_body( $cfw_google_fonts_list ) );

			set_transient( 'cfw_google_font_list', $cfw_google_fonts_list, 30 * DAY_IN_SECONDS );
		}

		return $cfw_google_fonts_list;
	}

	/**
	 * @return array
	 */
	public static function get_theme_color_settings(): array {
		$active_template = cfw_get_active_template();
		$color_settings  = array();

		// Body
		$color_settings['body'] = array(
			'title'    => 'Body',
			'settings' => array(),
		);

		$color_settings['body']['settings']['body_background_color'] = cfw__( 'Body Background Color', 'checkout-wc' );
		$color_settings['body']['settings']['body_text_color']       = cfw__( 'Body Text Color', 'checkout-wc' );
		$color_settings['body']['settings']['link_color']            = cfw__( 'Link Color', 'checkout-wc' );

		// Header
		$color_settings['header'] = array(
			'title'    => 'Header',
			'settings' => array(),
		);

		if ( $active_template->supports( 'header-background' ) ) {
			$color_settings['header']['settings']['header_background_color'] = cfw__( 'Header Background Color', 'checkout-wc' );
		}

		$color_settings['header']['settings']['header_text_color'] = cfw__( 'Header Text Color', 'checkout-wc' );

		// Footer
		$color_settings['footer'] = array(
			'title'    => 'Footer',
			'settings' => array(),
		);

		if ( $active_template->supports( 'footer-background' ) ) {
			$color_settings['footer']['settings']['footer_background_color'] = cfw__( 'Footer Background Color', 'checkout-wc' );
		}

		$color_settings['footer']['settings']['footer_color'] = cfw__( 'Footer Text Color', 'checkout-wc' );

		// Cart Summary
		$color_settings['cart_summary'] = array(
			'title'    => 'Cart Summary',
			'settings' => array(),
		);

		if ( $active_template->supports( 'summary-background' ) ) {
			$color_settings['cart_summary']['settings']['summary_background_color'] = cfw__( 'Summary Background Color', 'checkout-wc' );
			$color_settings['cart_summary']['settings']['summary_text_color']       = cfw__( 'Summary Text Color', 'checkout-wc' );
		}

		$color_settings['cart_summary']['settings']['summary_link_color'] = cfw__( 'Summary Link Color', 'checkout-wc' );

		$color_settings['cart_summary']['settings']['summary_mobile_background_color'] = cfw__( 'Summary Mobile Background Color', 'checkout-wc' );

		$color_settings['cart_summary']['settings']['cart_item_quantity_color']      = cfw__( 'Item Quantity Bubble Background Color', 'checkout-wc' );
		$color_settings['cart_summary']['settings']['cart_item_quantity_text_color'] = cfw__( 'Item Quantity Bubble Text Color', 'checkout-wc' );

		// Breadcrumbs
		$color_settings['breadcrumbs'] = array(
			'title'    => 'Breadcrumbs',
			'settings' => array(),
		);

		if ( $active_template->supports( 'breadcrumb-colors' ) ) {
			$color_settings['breadcrumbs']['settings']['breadcrumb_completed_text_color']   = cfw__( 'Completed Breadcrumb Completed Text Color', 'checkout-wc' );
			$color_settings['breadcrumbs']['settings']['breadcrumb_current_text_color']     = cfw__( 'Current Breadcrumb Text Color', 'checkout-wc' );
			$color_settings['breadcrumbs']['settings']['breadcrumb_next_text_color']        = cfw__( 'Next Breadcrumb Text Color', 'checkout-wc' );
			$color_settings['breadcrumbs']['settings']['breadcrumb_completed_accent_color'] = cfw__( 'Completed Breadcrumb Accent Color', 'checkout-wc' );
			$color_settings['breadcrumbs']['settings']['breadcrumb_current_accent_color']   = cfw__( 'Current Breadcrumb Accent Color', 'checkout-wc' );
			$color_settings['breadcrumbs']['settings']['breadcrumb_next_accent_color']      = cfw__( 'Next Breadcrumb Accent Color', 'checkout-wc' );
		}

		$color_settings['buttons'] = array(
			'title'    => 'Buttons',
			'settings' => array(),
		);

		// Buttons
		$color_settings['buttons']['settings']['button_color']                      = cfw__( 'Primary Button Background Color', 'checkout-wc' );
		$color_settings['buttons']['settings']['button_text_color']                 = cfw__( 'Primary Button Text Color', 'checkout-wc' );
		$color_settings['buttons']['settings']['button_hover_color']                = cfw__( 'Primary Button Background Hover Color', 'checkout-wc' );
		$color_settings['buttons']['settings']['button_text_hover_color']           = cfw__( 'Primary Button Text Hover Color', 'checkout-wc' );
		$color_settings['buttons']['settings']['secondary_button_color']            = cfw__( 'Secondary Button Background Color', 'checkout-wc' );
		$color_settings['buttons']['settings']['secondary_button_text_color']       = cfw__( 'Secondary Button Text Color', 'checkout-wc' );
		$color_settings['buttons']['settings']['secondary_button_hover_color']      = cfw__( 'Secondary Button Background Hover Color', 'checkout-wc' );
		$color_settings['buttons']['settings']['secondary_button_text_hover_color'] = cfw__( 'Secondary Button Text Hover Color', 'checkout-wc' );

		// Theme Specific Colors
		$color_settings['active_theme_colors'] = array(
			'title'    => 'Theme Specific Colors',
			'settings' => apply_filters( 'cfw_active_theme_color_settings', array() ),
		);

		return apply_filters( 'cfw_theme_color_settings', $color_settings );
	}
}
