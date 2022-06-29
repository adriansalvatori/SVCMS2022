<?php

namespace WCBEditor\Includes\Admin;

use WCBEditor\Includes\Coupons\Coupons;
use WCBEditor\Includes\Orders\Orders;
use WCBEditor\Includes\Products\Products;

defined( 'ABSPATH' ) || exit;

class Admin {

	protected static $instance = null;

	public function __construct() {
		add_action( 'admin_init', [ $this, 'save_settings' ] );
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );

	}

	public static function instance() {
		return self::$instance == null ? self::$instance = new self : self::$instance;
	}

	public function admin_menu() {
		add_menu_page(
			esc_html__( 'WooCommerce Bulk Editor', 'bulky-woocommerce-bulk-edit-products' ),
			esc_html__( 'Bulky', 'bulky-woocommerce-bulk-edit-products' ),
			WCBE_CONST['capability'],
			'vi_wbe_edit_products',
			'',
			'dashicons-media-spreadsheet',
			40
		);

		add_submenu_page( 'vi_wbe_edit_products',
			esc_html__( 'Edit Products', 'bulky-woocommerce-bulk-edit-products' ),
			esc_html__( 'Edit Products', 'bulky-woocommerce-bulk-edit-products' ),
			WCBE_CONST['capability'],
			'vi_wbe_edit_products',
			[ $this, 'product_type_init' ]
		);

		add_submenu_page( 'vi_wbe_edit_products',
			esc_html__( 'Edit Orders', 'bulky-woocommerce-bulk-edit-products' ),
			esc_html__( 'Edit Orders', 'bulky-woocommerce-bulk-edit-products' ),
			WCBE_CONST['capability'],
			'vi_wbe_edit_orders',
			[ $this, 'order_type_init' ]
		);

		add_submenu_page( 'vi_wbe_edit_products',
			esc_html__( 'Edit Coupons', 'bulky-woocommerce-bulk-edit-products' ),
			esc_html__( 'Edit Coupons', 'bulky-woocommerce-bulk-edit-products' ),
			WCBE_CONST['capability'],
			'vi_wbe_edit_coupons',
			[ $this, 'coupon_type_init' ]
		);

		add_submenu_page( 'vi_wbe_edit_products',
			esc_html__( 'Settings', 'bulky-woocommerce-bulk-edit-products' ),
			esc_html__( 'Settings', 'bulky-woocommerce-bulk-edit-products' ),
			'manage_options',
			'vi_wbe_settings',
			[ $this, 'setting_page' ]
		);

	}

	public function product_type_init() {
		Products::instance()->editor();
	}

	public function order_type_init() {
		Orders::instance()->editor();
	}

	public function coupon_type_init() {
		Coupons::instance()->editor();
	}

	public function setting_page() {
		$vi_wbe_enable_hook                            = get_option( 'vi_wbe_enable_hook' );
		$woocommerce_admin_process_product_object      = ! empty( $vi_wbe_enable_hook['woocommerce_admin_process_product_object'] ) ? 'checked' : '';
		$save_post                                     = ! empty( $vi_wbe_enable_hook['save_post'] ) ? 'checked' : '';
		$save_post_product                             = ! empty( $vi_wbe_enable_hook['save_post_product'] ) ? 'checked' : '';
		$edit_post                                     = ! empty( $vi_wbe_enable_hook['edit_post'] ) ? 'checked' : '';
		$woocommerce_process_product_meta_product_type = ! empty( $vi_wbe_enable_hook['woocommerce_process_product_meta_product_type'] ) ? 'checked' : '';
		$woocommerce_update_product                    = ! empty( $vi_wbe_enable_hook['woocommerce_update_product'] ) ? 'checked' : '';
		$woocommerce_update_product_variation          = ! empty( $vi_wbe_enable_hook['woocommerce_update_product_variation'] ) ? 'checked' : '';
		?>
        <div class="vi-wbe-settings-page">

            <h2><?php esc_html_e( 'Bulky - Bulk Edit Products for WooCommerce Premium Settings', 'bulky-woocommerce-bulk-edit-products' ); ?></h2>

            <form class="vi-ui form" method="post">
				<?php wp_nonce_field( 'vi_wbe_nonce' ); ?>

                <div class="field">
                    <div class="vi-ui segment">
						<?php do_action( 'vi_wbe_admin_field_auto_update_key' ); ?>
                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e( 'Hooks in save product processing', 'bulky-woocommerce-bulk-edit-products' ); ?></th>
                                <td>
                                    <div class="field">
                                        <div class="vi-ui toggle checkbox">
                                            <input type="checkbox" name="vi_wbe_enable_hook[woocommerce_admin_process_product_object]"
                                                   value="1" <?php echo esc_attr( $woocommerce_admin_process_product_object ) ?>>
                                            <label>woocommerce_admin_process_product_object</label>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <div class="vi-ui toggle checkbox">
                                            <input type="checkbox" name="vi_wbe_enable_hook[save_post]"
                                                   value="1" <?php echo esc_attr( $save_post ) ?>>
                                            <label>save_post</label>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <div class="vi-ui toggle checkbox">
                                            <input type="checkbox" name="vi_wbe_enable_hook[save_post_product]"
                                                   value="1" <?php echo esc_attr( $save_post_product ) ?>>
                                            <label>save_post_product</label>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <div class="vi-ui toggle checkbox">
                                            <input type="checkbox" name="vi_wbe_enable_hook[edit_post]"
                                                   value="1" <?php echo esc_attr( $edit_post ) ?>>
                                            <label>edit_post</label>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <div class="vi-ui toggle checkbox">
                                            <input type="checkbox" name="vi_wbe_enable_hook[woocommerce_process_product_meta_product_type]"
                                                   value="1" <?php echo esc_attr( $woocommerce_process_product_meta_product_type ) ?>>
                                            <label>woocommerce_process_product_meta_{product_type}</label>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <div class="vi-ui toggle checkbox">
                                            <input type="checkbox" name="vi_wbe_enable_hook[woocommerce_update_product]"
                                                   value="1" <?php echo esc_attr( $woocommerce_update_product ) ?>>
                                            <label>woocommerce_update_product</label>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <div class="vi-ui toggle checkbox">
                                            <input type="checkbox" name="vi_wbe_enable_hook[woocommerce_update_product_variation]"
                                                   value="1" <?php echo esc_attr( $woocommerce_update_product_variation ) ?>>
                                            <label>woocommerce_update_product_variation</label>
                                        </div>
                                    </div>
                                    <p class="description">
										<?php
										esc_html_e( 'Enable hook that 3rd plugins need in save product processing', 'bulky-woocommerce-bulk-edit-products' );
										?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="field">
                    <button type="submit" class="vi-ui button primary labeled icon vi-wbe-save-settings" name="vi_wbe_save_settings" value="save_settings">
                        <i class="send icon"> </i>
						<?php esc_html_e( 'Save', 'bulky-woocommerce-bulk-edit-products' ); ?>
                    </button>

                    <button type="submit" class="vi-ui button labeled icon vi-wbe-save-settings" name="vi_wbe_save_settings" value="save_n_check_key">
                        <i class="send icon"> </i>
						<?php esc_html_e( 'Save & Check key', 'bulky-woocommerce-bulk-edit-products' ); ?>
                    </button>
                </div>
            </form>
        </div>

		<?php

		do_action( 'villatheme_support_' . WCBE_CONST['slug'] );
	}

	public function save_settings() {
		if ( isset( $_POST['vi_wbe_save_settings'], $_POST['_wpnonce'] )
		     && in_array( $_POST['vi_wbe_save_settings'], [ 'save_settings', 'save_n_check_key' ] )
		     && wp_verify_nonce( $_POST['_wpnonce'], 'vi_wbe_nonce' )
		     && current_user_can( 'manage_options' )
		) {
			$auto_update_key = ! empty( $_POST['vi_wbe_auto_update_key'] ) ? sanitize_text_field( $_POST['vi_wbe_auto_update_key'] ) : '';
			update_option( 'vi_wbe_auto_update_key', $auto_update_key );

			$hooks = ! empty( $_POST['vi_wbe_enable_hook'] ) ? wc_clean( $_POST['vi_wbe_enable_hook'] ) : [];
			update_option( 'vi_wbe_enable_hook', $hooks );

			if ( $_POST['vi_wbe_save_settings'] === 'save_n_check_key' ) {
				delete_site_transient( 'update_plugins' );
				delete_transient( 'villatheme_item_81532' );
				delete_option( 'bulky-woocommerce-bulk-edit-products_messages' );
				do_action( 'villatheme_save_and_check_key_bulky-woocommerce-bulk-edit-products', $auto_update_key );
			}
		}
	}
}