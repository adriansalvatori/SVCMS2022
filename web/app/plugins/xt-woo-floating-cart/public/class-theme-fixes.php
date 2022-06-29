<?php
/**
 * The woocommerce-facing functionality of the plugin.
 *
 * @package    XT_Woo_Floating_Cart
 * @subpackage XT_Woo_Floating_Cart/Theme_Fixes
 * @author     XplodedThemes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class XT_Woo_Floating_Cart_Theme_Fixes {

	/**
	 * Core class reference.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      XT_Woo_Floating_Cart $core
	 */
	private $core;

	public $theme_name;

	public $theme_fixes = array(
		//'theme-name' => array('css', 'js', 'php'),
		'avada' => array('css', 'php'),
        'flatsome' => array('css')
	);

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param XT_Woo_Floating_Cart $core Plugin core class
	 *
	 * @since    1.0.0
	 */
	public function __construct( &$core ) {

		$this->core = $core;

		$this->theme_name = isset($_GET['customize_changeset_uuid']) && !empty($_GET['theme']) ? sanitize_text_field($_GET['theme']) : get_template();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_theme_fixes_assets' ) );
        add_action( 'init', array( $this, 'init_theme_fixes' ) );

	}


	/**
	 * Enqueue Theme Fixes Assets.
	 * @access  public
	 * @return void
	 * @since   1.0.0
	 */
	public function enqueue_theme_fixes_assets() {

		if ( ! empty( $this->theme_fixes[ $this->theme_name ] ) ) {

			foreach ( $this->theme_fixes[ $this->theme_name ] as $type ) {

				if ( $type == 'css' ) {

					wp_register_style( $this->core->plugin_slug( $this->theme_name ), $this->core->plugin_url( 'public' ) . 'assets/css/' . $this->theme_name . '.css', array(), $this->core->plugin_version() );
					wp_enqueue_style( $this->core->plugin_slug( $this->theme_name ) );

				} else if ( $type == 'js' ) {

					wp_register_script( $this->core->plugin_slug( $this->theme_name ), $this->core->plugin_url( 'public' ) . 'assets/js/' . $this->theme_name . '.js', array(), $this->core->plugin_version(), true );
					wp_enqueue_script( $this->core->plugin_slug( $this->theme_name ) );
				}

			}

		}

	} // End enqueue_theme_fixes_assets ()

	/**
	 * Init PHP Theme Fixes
	 * @access  public
	 * @return void
	 * @since   1.0.0
	 */
	public function init_theme_fixes() {

		if ( ! empty( $this->theme_fixes[ $this->theme_name ] ) ) {

			foreach ( $this->theme_fixes[ $this->theme_name ] as $type ) {

				if ( $type == 'php' && method_exists($this, "init_".$this->theme_name."_fixes") ) {

					$this->{"init_".$this->theme_name."_fixes"}();

				}

			}

		}

	} // End init_theme_fixes ()

	public function init_avada_fixes() {

		global $avada_woocommerce;

		if(class_exists('Avada_Woocommerce') && !empty($avada_woocommerce) && !is_checkout() && !is_cart()) {

			remove_action( 'woocommerce_checkout_after_order_review', [ $avada_woocommerce, 'checkout_after_order_review' ], 20 );
		}

	}
}

