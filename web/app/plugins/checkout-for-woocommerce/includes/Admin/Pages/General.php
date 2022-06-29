<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

use Objectiv\Plugins\Checkout\Managers\SettingsManager;
use Objectiv\Plugins\Checkout\Managers\UpdatesManager;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Admin\Pages
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class General extends PageAbstract {
	protected $appearance_page;

	public function __construct( Appearance $appearance_page ) {
		$this->appearance_page = $appearance_page;
		parent::__construct( cfw__( 'Start Here', 'checkout-wc' ), 'manage_options' );
	}

	public function init() {
		parent::init();

		add_action( 'admin_bar_menu', array( $this, 'add_parent_node' ), 100 );
		add_action( 'admin_menu', array( $this, 'setup_main_menu_page' ), $this->priority - 5 );
	}

	public function setup_menu() {
		add_submenu_page( self::$parent_slug, $this->title, $this->title, $this->capability, $this->slug, null, $this->priority );
	}

	public function setup_main_menu_page() {
		add_menu_page( 'CheckoutWC', 'CheckoutWC', 'manage_options', self::$parent_slug, array( $this, 'output_with_wrap' ), 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( CFW_PATH . '/assets/admin/images/icon.svg' ) ) );
	}

	public function output() {
		$this->output_form_open();
		?>
		<div class="max-w-3xl pb-8">
			<div>
				<p class="text-5xl font-bold text-gray-900">
					<?php cfw_e( 'Welcome to the new standard for WooCommerce checkouts.', 'checkout-wc' ); ?>
				</p>
				<p class="max-w-xl mt-5 text-2xl text-gray-500">
					<?php cfw_e( 'Higher conversions start here.', 'checkout-wc' ); ?>
				</p>
				<p class="mt-6">
					<a href="https://kb.checkoutwc.com" target="_blank" class="inline-flex items-center px-6 py-3 border border-transparent text-lg shadow font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
						<?php cfw_e( 'Documentation', 'checkout-wc' ); ?>
					</a>
				</p>
			</div>
		</div>
		<div class="hidden sm:block" aria-hidden="true">
			<div class="py-8">
				<div class="border-t border-gray-300"></div>
			</div>
		</div>
		<div class="space-y-8 mt-4">
			<?php
			cfw_admin_page_section(
				cfw__( '1. Enter Your License Key', 'checkout-wc' ),
				cfw__( 'Enter your license key and click <i>Save License Key</i>. Required to enable all features.', 'checkout-wc' ),
				$this->get_licensing_settings()
			);

			cfw_admin_page_section(
				cfw__( '2. Activate Your License', 'checkout-wc' ),
				cfw__( 'Activate your license key for usage on this site.', 'checkout-wc' ),
				$this->get_license_activation_settings()
			);

			cfw_admin_page_section(
				cfw__( '3. Pick a Template', 'checkout-wc' ),
				cfw__( 'Pick from four different designs.', 'checkout-wc' ),
				$this->get_pick_template_content()
			);

			cfw_admin_page_section(
				cfw__( '3. Customize Logo and Colors', 'checkout-wc' ),
				cfw__( 'Review your logo and set your brand colors.', 'checkout-wc' ),
				$this->get_design_content()
			);

			cfw_admin_page_section(
				cfw__( '4. Review Your Checkout Page', 'checkout-wc' ),
				cfw__( 'Test your checkout page and make sure everything is working correctly..', 'checkout-wc' ),
				$this->get_preview_content()
			);

			cfw_admin_page_section(
				cfw__( '5. Start Converting More Sales', 'checkout-wc' ),
				cfw__( 'Control whether CheckoutWC templates are active.', 'checkout-wc' ),
				$this->get_activation_settings()
			);
			?>
		</div>
		<?php
		$this->output_form_close();

		if ( isset( $_GET['cfw_debug_settings'] ) ) {
			$all_settings = SettingsManager::instance()->get_settings_obj();

			echo '<div class="max-w-lg">';
			foreach ( $all_settings as $key => $value ) {
				echo '<h3 class="text-base font-bold mb-4">' . $key . '</h3>';
				echo '<pre class="shadow-sm bg-white p-6 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md mb-6">' . $value . '</pre>';
			}
			echo '</div>';
		}
	}

	/**
	 * @param array $plugin_info
	 */
	public function recommended_plugin_card( array $plugin_info ) {
		?>
		<div class="plugin-card plugin-card-<?php echo $plugin_info['slug']; ?>">
			<div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<a target="_blank" href="<?php echo $plugin_info['url']; ?>">
							<?php echo $plugin_info['name']; ?> <img src="<?php echo $plugin_info['image']; ?>" class="plugin-icon" alt="">
						</a>
					</h3>
				</div>
				<div class="action-links">
					<ul class="plugin-action-buttons">
						<li>
							<a class="button" target="_blank"  href="<?php echo $plugin_info['url']; ?>" role="button"><?php cfw_e( 'More Info' ); ?></a></li>
						</li>
					</ul>
				</div>
				<div class="desc column-description">
					<p><?php echo $plugin_info['description']; ?></p>
					<p class="authors"> <cite><?php echo sprintf( cfw__( 'By %s' ), $plugin_info['author'] ); ?></cite></p>
				</div>
			</div>
		</div>
		<?php
	}

	public function get_activation_settings() {
		ob_start();

		$this->output_toggle_checkbox(
			'enable',
			cfw__( 'Activate CheckoutWC Templates', 'checkout-wc' ),
			cfw__( 'Requires a valid and active license key. CheckoutWC Templates are always activated for admin users, even without a valid license.', 'checkout-wc' )
		);

		return ob_get_clean();
	}

	public function get_licensing_settings() {
		ob_start();

		UpdatesManager::instance()->admin_page_fields();
		?>
		<div>
			<input type="submit" name="submit" id="submit" class="button" value="<?php cfw_e( 'Save License Key', 'checkout-wc' ); ?>">
		</div>
		<?php
		return ob_get_clean();
	}

	public function get_license_activation_settings() {
		ob_start();

		UpdatesManager::instance()->admin_page_activation_status_button();

		return ob_get_clean();
	}

	public function get_pick_template_content() {
		ob_start();
		?>
		<div class="flex flex-row items-center">
			<a href="<?php echo add_query_arg( array( 'subpage' => 'templates' ), $this->appearance_page->get_url() ); ?>" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
				<?php cfw_e( 'Choose a Template', 'checkout-wc' ); ?>
			</a>
			<svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-label="<?php cfw_e( 'Opens in new tab' ); ?>">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
			</svg>
		</div>
		<?php
		return ob_get_clean();
	}

	public function get_design_content() {
		ob_start();
		?>
		<div class="flex flex-row items-center">
			<a href="<?php echo $this->appearance_page->get_url(); ?>" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
				<?php cfw_e( 'Customize Logo and Colors', 'checkout-wc' ); ?>
			</a>
			<svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-label="<?php cfw_e( 'Opens in new tab' ); ?>">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
			</svg>
		</div>
		<?php
		return ob_get_clean();
	}

	public function get_preview_content() {
		$url = wc_get_checkout_url();

		$products = wc_get_products(
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

			$url = add_query_arg( array( 'add-to-cart' => $product->get_id() ), $url );
		}

		ob_start();
		?>
		<div class="flex flex-row items-center">
			<a href="<?php echo $url; ?>" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
				<?php cfw_e( 'Preview Your Checkout Page', 'checkout-wc' ); ?>
			</a>
			<svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-label="<?php cfw_e( 'Opens in new tab' ); ?>">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
			</svg>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * @param \WP_Admin_Bar $admin_bar
	 */
	public function add_parent_node( \WP_Admin_Bar $admin_bar ) {
		if ( ! $this->can_show_admin_bar_button() ) {
			return;
		}

		if ( cfw_is_checkout() ) {
			// Remove irrelevant buttons
			$admin_bar->remove_node( 'new-content' );
			$admin_bar->remove_node( 'updates' );
			$admin_bar->remove_node( 'edit' );
			$admin_bar->remove_node( 'comments' );
		}

		$url = $this->get_url();

		$admin_bar->add_node(
			array(
				'id'     => self::$parent_slug,
				'title'  => '<span class="ab-icon dashicons dashicons-cart"></span>' . cfw__( 'CheckoutWC', 'checkout-wc' ),
				'href'   => $url,
				'parent' => false,
			)
		);
	}

	/**
	 * @param \WP_Admin_Bar $admin_bar
	 */
	public function add_admin_bar_menu_node( \WP_Admin_Bar $admin_bar ) {
		if ( ! apply_filters( 'cfw_do_admin_bar', true ) ) {
			return;
		}

		$admin_bar->add_node(
			array(
				'id'     => $this->slug . '-general',
				'title'  => $this->title,
				'href'   => $this->get_url(),
				'parent' => self::$parent_slug,
			)
		);
	}
}
