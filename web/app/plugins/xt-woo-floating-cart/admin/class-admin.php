<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    XT_Woo_Floating_Cart
 * @subpackage XT_Woo_Floating_Cart/admin
 * @author     XplodedThemes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class XT_Woo_Floating_Cart_Admin {

	/**
	 * Core class reference.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      XT_Woo_Floating_Cart    core    Core Class
	 */
	private $core;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param XT_Woo_Floating_Cart $core Plugin core class.
	 *
	 * @since    1.0.0
	 */
	public function __construct( &$core ) {

		$this->core = $core;

		// Init modules
		add_filter($this->core->plugin_prefix('modules'), array( $this, 'modules'), 1, 1);

		// Init customizer options
		add_filter( $this->core->plugin_prefix( 'customizer_panels' ), array( $this, 'customizer_panels'), 1, 1 );
		add_filter( $this->core->plugin_prefix( 'customizer_sections' ), array( $this, 'customizer_sections'), 1, 1 );
		add_filter( $this->core->plugin_prefix( 'customizer_fields' ), array( $this, 'customizer_fields'), 1, 2 );
	}

	public function modules($modules) {

		$modules[] = 'add-to-cart';

		return $modules;
	}

	public function customizer_panels( $panels ) {

		$panels[] = array(
			'title'    => $this->core->plugin_menu_name(),
			'icon'     => $this->core->plugin_icon()
		);

		return $panels;
	}

	public function customizer_sections( $sections ) {

		$sections[] = array(
			'id'    => 'general',
			'title' => esc_html__( 'General', 'woo-floating-cart' ),
			'icon'  => 'dashicons-admin-generic'
		);

        $sections[] = array(
            'id'    => 'colors',
            'title' => esc_html__( 'Global Colors', 'woo-floating-cart' ),
            'icon'  => 'dashicons-color-picker'
        );

        $sections[] = array(
            'id'    => 'cart',
            'title' => esc_html__( 'Cart Settings', 'woo-floating-cart' ),
            'icon'  => 'dashicons-list-view'
        );

		$sections[] = array(
			'id'    => 'trigger',
			'title' => esc_html__( 'Trigger Settings', 'woo-floating-cart' ),
			'icon'  => 'dashicons-external'
		);

		$sections[] = array(
			'id'    => 'header',
			'title' => esc_html__( 'Cart Header', 'woo-floating-cart' ),
			'icon'  => 'dashicons-arrow-up-alt2'
		);

		$sections[] = array(
			'id'    => 'product',
			'title' => esc_html__( 'Cart Product', 'woo-floating-cart' ),
			'icon'  => 'dashicons-products'
		);

		$sections[] = array(
			'id'    => 'footer',
			'title' => esc_html__( 'Cart Footer', 'woo-floating-cart' ),
			'icon'  => 'dashicons-arrow-down-alt2'
		);

        $sections[] = array(
            'id'    => 'coupons',
            'title' => esc_html__( 'Cart Coupons', 'woo-floating-cart' ),
            'icon'  => 'dashicons-tag'
        );

        $sections[] = array(
            'id'    => 'totals',
            'title' => esc_html__( 'Cart Totals', 'woo-floating-cart' ),
            'icon'  => 'dashicons-editor-ul'
        );

        $sections[] = array(
            'id'    => 'checkout',
            'title' => esc_html__( 'Checkout Form', 'woo-floating-cart' ),
            'icon'  => 'dashicons-migrate'
        );

        $sections[] = array(
            'id'    => 'sp',
            'title' => esc_html__( 'Suggested Products', 'woo-floating-cart' ),
            'icon'  => 'dashicons-randomize'
        );

        $sections[] = array(
            'id'    => 'menu-item',
            'title' => esc_html__( 'Cart Menu Item', 'woo-floating-cart' ),
            'icon'  => 'dashicons-cart'
        );

        $sections[] = array(
            'id'    => 'shortcode',
            'title' => esc_html__( 'Cart Trigger Shortcode', 'woo-floating-cart' ),
            'icon'  => 'dashicons-shortcode'
        );

        $sections[] = array(
            'id'    => 'notices',
            'title' => esc_html__( 'Cart Notices', 'woo-floating-cart' ),
            'icon'  => 'dashicons-info'
        );

        $sections[] = array(
            'id'    => 'visibility',
            'title' => esc_html__( 'Visibility', 'woo-floating-cart' ),
            'icon'  => 'dashicons-visibility'
        );

        $sections[] = array(
            'id'    => 'typography',
            'title' => esc_html__( 'Typography', 'woo-floating-cart' ),
            'icon'  => 'dashicons-editor-bold'
        );

        $sections[] = array(
            'id'    => 'extras',
            'title' => esc_html__( 'Cart Extras', 'woo-floating-cart' ),
            'icon'  => 'dashicons-plus'
        );

        $sections[] = array(
            'id'    => 'api',
            'title' => esc_html__( 'JS API', 'woo-floating-cart' ),
            'icon'  => 'dashicons-editor-code'
        );

		return $sections;
	}

	public function customizer_fields( $fields, XT_Framework_Customizer $customizer ) {

		require $this->core->plugin_path('admin/customizer/fields', 'general.php');
        require $this->core->plugin_path('admin/customizer/fields', 'colors.php');
        require $this->core->plugin_path('admin/customizer/fields', 'cart.php');
        require $this->core->plugin_path('admin/customizer/fields', 'trigger.php');
        require $this->core->plugin_path('admin/customizer/fields', 'header.php');
		require $this->core->plugin_path('admin/customizer/fields', 'product.php');
		require $this->core->plugin_path('admin/customizer/fields', 'footer.php');
        require $this->core->plugin_path('admin/customizer/fields', 'notices.php');
        require $this->core->plugin_path('admin/customizer/fields', 'visibility.php');
        require $this->core->plugin_path('admin/customizer/fields', 'typography.php');
        require $this->core->plugin_path('admin/customizer/fields', 'coupons.php');
        require $this->core->plugin_path('admin/customizer/fields', 'totals.php');
        require $this->core->plugin_path('admin/customizer/fields', 'checkout.php');
        require $this->core->plugin_path('admin/customizer/fields', 'sp.php');
        require $this->core->plugin_path('admin/customizer/fields', 'menu-item.php');
		require $this->core->plugin_path('admin/customizer/fields', 'shortcode.php');
        require $this->core->plugin_path('admin/customizer/fields', 'extras.php');
		require $this->core->plugin_path('admin/customizer/fields', 'api.php');

		return $fields;
	}

}
