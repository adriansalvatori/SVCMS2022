<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Admin\Pages
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class OrderBumps extends PageAbstract {
	protected $post_type_slug;
	protected $nonce_field  = '_cfw_ob_nonce';
	protected $nonce_action = 'cfw_save_ob_mb';
	protected $formatted_required_plans_list;
	protected $is_available;

	public function __construct( string $post_type_slug, string $formatted_required_plans_list, bool $is_available ) {
		parent::__construct( cfw__( 'Order Bumps', 'checkout-wc' ), 'manage_options', null );

		$this->post_type_slug                = $post_type_slug;
		$this->slug                          = 'edit.php?post_type=' . $this->post_type_slug;
		$this->formatted_required_plans_list = $formatted_required_plans_list;
		$this->is_available                  = $is_available;
	}

	public function init() {
		parent::init();

		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_metaboxes' ) );
		add_action( 'all_admin_notices', array( $this, 'output_with_wrap' ) );
		add_action( 'all_admin_notices', array( $this, 'maybe_show_license_upgrade_splash' ) );

		/**
		 * Highlights Order Bumps submenu item when
		 * on the New Order Bumps admin page
		 */
		add_filter( 'submenu_file', array( $this, 'maybe_highlight_order_bumps_submenu_item' ) );
	}

	public function get_url(): string {
		$url = admin_url( $this->slug );

		return esc_url( $url );
	}

	public function setup_menu() {
		global $submenu;

		$stash_menu_item = null;

		if ( empty( $submenu[ self::$parent_slug ] ) ) {
			return;
		}

		foreach ( (array) $submenu[ self::$parent_slug ] as $i => $item ) {
			if ( $this->slug === $item[2] ) {
				$stash_menu_item = $submenu[ self::$parent_slug ][ $i ];
				unset( $submenu[ self::$parent_slug ][ $i ] );
			}
		}

		if ( empty( $stash_menu_item ) ) {
			return;
		}

		$submenu[ self::$parent_slug ][ $this->priority ] = $stash_menu_item;
	}

	public function register_meta_boxes() {
		add_meta_box( 'cfw_order_bump_products_mb', cfw__( 'Display Conditions', 'checkout-wc' ), array( $this, 'render_products_meta_box' ), $this->post_type_slug );
		add_meta_box( 'cfw_order_bump_offer_mb', cfw__( 'Offer', 'checkout-wc' ), array( $this, 'render_offer_meta_box' ), $this->post_type_slug );
	}

	/**
	 * @param \WP_Post $post
	 */
	public function render_products_meta_box( \WP_Post $post ) {
		$cfw_ob_display_for_options = array(
			'all_products'        => cfw__( 'All Products', 'checkout-wc' ),
			'specific_products'   => cfw__( 'Specific Products', 'checkout-wc' ),
			'specific_categories' => cfw__( 'Specific Categories', 'checkout-wc' ),
		);

		$cfw_ob_display_for_value = get_post_meta( $post->ID, 'cfw_ob_display_for', true );
		$cfw_ob_any_product_value = get_post_meta( $post->ID, 'cfw_ob_any_product', true );

		wp_nonce_field( $this->nonce_action, $this->nonce_field );
		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top">
						<label for="cfw_ob_display_for">
							<?php cfw_e( 'Display Offer For', 'checkout-wc' ); ?>
						</label>
					</th>
					<td>
						<select id="cfw_ob_display_for" name="cfw_ob_display_for">
							<?php foreach ( $cfw_ob_display_for_options as $option_value => $option_label ) : ?>
								<option value="<?php echo $option_value; ?>" <?php echo $option_value === $cfw_ob_display_for_value ? 'selected="selected"' : ''; ?>>
									<?php echo $option_label; ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label for="cfw_ob_products">
							<?php cfw_e( 'Products', 'checkout-wc' ); ?>
						</label>
					</th>
					<td>
						<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="cfw_ob_products" name="cfw_ob_products[]" data-placeholder="<?php cfw_esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations">
							<?php
							$product_ids = get_post_meta( $post->ID, 'cfw_ob_products', true );

							foreach ( $product_ids as $product_id ) {
								$product = wc_get_product( $product_id );
								if ( is_object( $product ) ) {
									echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ) . '</option>';
								}
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label for="cfw_ob_products">
							<?php cfw_e( 'Categories', 'checkout-wc' ); ?>
						</label>
					</th>
					<td>
						<select class="wc-category-search" multiple="multiple" style="width: 50%;" id="cfw_ob_categories" name="cfw_ob_categories[]" data-placeholder="<?php cfw_esc_attr_e( 'Search for a category&hellip;', 'woocommerce' ); ?>" data-allow_clear="true">
							<?php
							$category_slugs = get_post_meta( $post->ID, 'cfw_ob_categories', true );

							foreach ( $category_slugs as $category_slug ) {
								$category = get_term_by( 'slug', $category_slug, 'product_cat' );

								if ( $category ) {
									echo '<option value="' . esc_attr( $category_slug ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $category->name ) ) . '</option>';
								}
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label for="cfw_ob_any_product">
							<?php cfw_e( 'Condition', 'checkout-wc' ); ?>
						</label>
					</th>
					<td>
						<input type="hidden" name="cfw_ob_any_product" value="yes" />
						<input type="checkbox" class="cfw-checkbox" name="cfw_ob_any_product" id="cfw_ob_any_product" value="no" <?php echo 'no' === $cfw_ob_any_product_value ? 'checked' : ''; ?> />

						<label class="cfw-checkbox-label" for="cfw_ob_any_product">
							<?php cfw_e( 'Apply if all matching products are in the cart.', 'checkout-wc' ); ?>
						</label>

						<p>
							<span class="description">
								<?php cfw_e( 'If checked, all products above must be in the cart. If unchecked order bump will show if any of the above products are in the cart.', 'checkout-wc' ); ?>
							</span>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label for="cfw_ob_display_location">
							<?php cfw_e( 'Display Location', 'checkout-wc' ); ?>
						</label>
					</th>
					<td>
						<p>
							<?php
							$cfw_ob_display_location_value = get_post_meta( $post->ID, 'cfw_ob_display_location', true );
							$default_value                 = 'below_cart_items';

							$display_location_options = array(
								'below_cart_items' => 'Below Cart Items',
								'above_terms_and_conditions' => 'Above Terms and Conditions',
							);
							foreach ( $display_location_options as $option_value => $option_label ) :
								?>
								<label>
									<input type="radio" name="cfw_ob_display_location" value="<?php echo $option_value; ?>" <?php echo $option_value === $cfw_ob_display_location_value || ( empty( $cfw_ob_display_location_value ) && $option_value === $default_value ) ? 'checked' : ''; ?> /> <?php echo $option_label; ?><br />
								</label>
							<?php endforeach; ?>
						</p>

						<p class="description">
							<?php cfw_e( 'Where to display order bumps. Order bumps will always display above terms and conditions on mobile.', 'checkout-wc' ); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * @param \WP_Post $post
	 */
	public function render_offer_meta_box( \WP_Post $post ) {
		$cfw_ob_discount_type_options = array(
			'percent' => 'Percent Off',
			'amount'  => 'Amount Off',
		);

		$cfw_ob_discount_type_default = 'percent';

		$cfw_ob_discount_type_value           = get_post_meta( $post->ID, 'cfw_ob_discount_type', true );
		$cfw_ob_offer_product                 = get_post_meta( $post->ID, 'cfw_ob_offer_product', true );
		$cfw_ob_offer_discount                = get_post_meta( $post->ID, 'cfw_ob_offer_discount', true );
		$cfw_ob_offer_language                = get_post_meta( $post->ID, 'cfw_ob_offer_language', true );
		$cfw_ob_offer_description             = get_post_meta( $post->ID, 'cfw_ob_offer_description', true );
		$cfw_ob_upsell_value                  = get_post_meta( $post->ID, 'cfw_ob_upsell', true );
		$cfw_ob_offer_quantity                = get_post_meta( $post->ID, 'cfw_ob_offer_quantity', true );
		$cfw_ob_item_removal_behavior_value   = get_post_meta( $post->ID, 'cfw_ob_item_removal_behavior', true );
		$cfw_ob_enable_quantity_updates_value = get_post_meta( $post->ID, 'cfw_ob_enable_quantity_updates', true );

		if ( empty( $cfw_ob_offer_quantity ) ) {
			$cfw_ob_offer_quantity = 1;
		}

		if ( empty( $cfw_ob_offer_language ) ) {
			$cfw_ob_offer_language = 'Yes! Please add this offer to my order';
		}

		if ( empty( $cfw_ob_offer_description ) ) {
			$cfw_ob_offer_description = 'Limited time offer! Get an EXCLUSIVE discount right now! Click the checkbox above to add this product to your order now.';
		}
		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top">
						<label for="cfw_ob_offer_product">
							<?php cfw_e( 'Product', 'checkout-wc' ); ?>
						</label>
					</th>
					<td>
						<select class="wc-product-search" style="width: 50%;" id="cfw_ob_offer_product" name="cfw_ob_offer_product" data-placeholder="<?php cfw_esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations">
							<?php
							$product_ids = array( $cfw_ob_offer_product );

							foreach ( $product_ids as $product_id ) {
								$product = wc_get_product( $product_id );
								if ( is_object( $product ) ) {
									echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ) . '</option>';
								}
							}
							?>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row" valign="top">
						<label for="cfw_ob_upsell">
							<?php cfw_e( 'Upsell', 'checkout-wc' ); ?>
						</label>
					</th>
					<td>
						<input type="hidden" name="cfw_ob_upsell" value="no" />
						<input type="checkbox" class="cfw-checkbox" name="cfw_ob_upsell" id="cfw_ob_upsell" value="yes" <?php echo 'yes' === $cfw_ob_upsell_value ? 'checked' : ''; ?> />

						<label class="cfw-checkbox-label" for="cfw_ob_upsell">
							<?php cfw_e( 'Replace cart product with offer product when this order bump is taken.', 'checkout-wc' ); ?>
						</label>

						<p>
							<span class="description">
								<?php cfw_e( 'Requirements: <i>Display Offer For</i> must be set to <i>Specific Products</i>. Only one product should be defined in <i>Products</i> list.', 'checkout-wc' ); ?>
							</span>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row" valign="top">
						<label for="cfw_ob_offer_quantity">
							<?php cfw_e( 'Quantity', 'checkout-wc' ); ?>
						</label>
					</th>
					<td>
						<input type="text" value="<?php echo esc_attr( $cfw_ob_offer_quantity ); ?>" name="cfw_ob_offer_quantity" id="cfw_ob_offer_quantity" />

						<p class="description">
							<?php cfw_e( 'The quantity to add to the cart when offer is accepted.', 'checkout-wc' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row" valign="top">
						<label for="cfw_ob_enable_quantity_updates">
							<?php cfw_e( 'Quantity Updates', 'checkout-wc' ); ?>
						</label>
					</th>
					<td>
						<input type="hidden" name="cfw_ob_enable_quantity_updates" value="no" />
						<input type="checkbox" class="cfw-checkbox" name="cfw_ob_enable_quantity_updates" id="cfw_ob_enable_quantity_updates" value="yes" <?php echo 'yes' === $cfw_ob_enable_quantity_updates_value ? 'checked' : ''; ?> />

						<label class="cfw-checkbox-label" for="cfw_ob_enable_quantity_updates">
							<?php cfw_e( 'Allow customer to change the quantity of this order bump in the cart.', 'checkout-wc' ); ?>
						</label>
					</td>
				</tr>

				<tr>
					<th scope="row" valign="top">
						<label for="cfw_ob_discount_type">
							<?php cfw_e( 'Discount Type', 'checkout-wc' ); ?>
						</label>
					</th>
					<td>
						<p>
							<?php foreach ( $cfw_ob_discount_type_options as $option_value => $option_label ) : ?>
								<label>
									<input type="radio" name="cfw_ob_discount_type" value="<?php echo $option_value; ?>" <?php echo $option_value === $cfw_ob_discount_type_value || ( empty( $cfw_ob_discount_type_value ) && $option_value === $cfw_ob_discount_type_default ) ? 'checked' : ''; ?> /> <?php echo $option_label; ?><br />
								</label>
							<?php endforeach; ?>
						</p>

						<p class="description">
							<?php cfw_e( 'Amount Off: Remove fixed amount from the product price.', 'checkout-wc' ); ?>
						</p>

						<p class="description">
							<?php cfw_e( 'Percent Off: Discount product by specified percentage.', 'checkout-wc' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row" valign="top">
						<label for="cfw_ob_offer_discount">
							<?php cfw_e( 'Discount', 'checkout-wc' ); ?>
						</label>
					</th>
					<td>
						<input type="text" value="<?php echo esc_attr( $cfw_ob_offer_discount ); ?>" name="cfw_ob_offer_discount" />
					</td>
				</tr>

				<tr>
					<th scope="row" valign="top">
						<label for="cfw_ob_offer_language">
							<?php cfw_e( 'Offer Language', 'checkout-wc' ); ?>
						</label>
					</th>
					<td>
						<input size="60" type="text" value="<?php echo esc_attr( $cfw_ob_offer_language ); ?>" name="cfw_ob_offer_language" />

						<p class="description">
							<?php cfw_e( 'Example: Yes! Please add this offer to my order', 'checkout-wc' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row" valign="top">
						<label for="cfw_ob_offer_description">
							<?php cfw_e( 'Offer Description', 'checkout-wc' ); ?>
						</label>
					</th>
					<td>
						<textarea cols="60" rows="6" type="text" name="cfw_ob_offer_description"><?php echo esc_attr( $cfw_ob_offer_description ); ?></textarea>

						<p class="description">
							Example: Limited time offer! Get an EXCLUSIVE discount right now! Click the checkbox above to add this product to your order now.
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row" valign="top">
						<label for="cfw_ob_item_removal_behavior">
							<?php cfw_e( 'Item Removal Behavior', 'checkout-wc' ); ?>
						</label>
					</th>
					<td>
						<p>
							<?php
							$default_value = 'keep';

							$display_location_options = array(
								'keep'   => 'Leave In Cart With Normal Price',
								'delete' => 'Remove Order Bump From Cart',
							);
							foreach ( $display_location_options as $option_value => $option_label ) :
								?>
								<label>
									<input type="radio" name="cfw_ob_item_removal_behavior" value="<?php echo $option_value; ?>" <?php echo $option_value === $cfw_ob_item_removal_behavior_value || ( empty( $cfw_ob_item_removal_behavior_value ) && $option_value === $default_value ) ? 'checked' : ''; ?> /> <?php echo $option_label; ?><br />
								</label>
							<?php endforeach; ?>
						</p>

						<p class="description">
							<?php cfw_e( 'What happens when the display for product is removed from the cart. Default: Order Bump remains in the cart but bump specific discounts are removed.', 'checkout-wc' ); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * @param int $post_id
	 */
	public function save_metaboxes( int $post_id ) {
		$nonce_name = $_POST[ $this->nonce_field ] ?? '';

		if ( ! wp_verify_nonce( $nonce_name, $this->nonce_action ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Display Conditions
		update_post_meta( $post_id, 'cfw_ob_display_for', $_POST['cfw_ob_display_for'] );
		update_post_meta( $post_id, 'cfw_ob_products', $_POST['cfw_ob_products'] );
		update_post_meta( $post_id, 'cfw_ob_categories', $_POST['cfw_ob_categories'] );
		update_post_meta( $post_id, 'cfw_ob_any_product', $_POST['cfw_ob_any_product'] );
		update_post_meta( $post_id, 'cfw_ob_display_location', $_POST['cfw_ob_display_location'] );
		update_post_meta( $post_id, 'cfw_ob_item_removal_behavior', $_POST['cfw_ob_item_removal_behavior'] );

		// Offer Fields
		update_post_meta( $post_id, 'cfw_ob_discount_type', $_POST['cfw_ob_discount_type'] );
		update_post_meta( $post_id, 'cfw_ob_offer_product', $_POST['cfw_ob_offer_product'] );
		update_post_meta( $post_id, 'cfw_ob_offer_discount', $_POST['cfw_ob_offer_discount'] );
		update_post_meta( $post_id, 'cfw_ob_offer_language', $_POST['cfw_ob_offer_language'] );
		update_post_meta( $post_id, 'cfw_ob_offer_description', $_POST['cfw_ob_offer_description'] );
		update_post_meta( $post_id, 'cfw_ob_upsell', $_POST['cfw_ob_upsell'] );
		update_post_meta( $post_id, 'cfw_ob_offer_quantity', $_POST['cfw_ob_offer_quantity'] );
		update_post_meta( $post_id, 'cfw_ob_enable_quantity_updates', $_POST['cfw_ob_enable_quantity_updates'] );
	}

	public function is_current_page(): bool {
		global $post;

		if ( isset( $_GET['post_type'] ) && $this->post_type_slug === $_GET['post_type'] ) {
			return true;
		}

		if ( $post && $this->post_type_slug === $post->post_type ) {
			return true;
		}

		return false;
	}

	/**
	 * The admin page wrap
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function output_with_wrap() {
		if ( ! $this->is_current_page() ) {
			return;
		}
		?>
		<div class="cfw-tw">
			<div id="cfw_admin_page_header" class="absolute left-0 right-0 top-0 divide-y shadow z-50">
				<?php do_action( 'cfw_before_admin_page_header', $this ); ?>
				<div class="min-h-[64px] bg-white flex items-center pl-8">
					<span>
						<?php echo file_get_contents( CFW_PATH . '/assets/admin/images/icon.svg' ); ?>
					</span>
					<nav class="flex" aria-label="Breadcrumb">
						<ol role="list" class="flex items-center space-x-2">
							<li class="m-0">
								<div class="flex items-center">
									<span class="ml-2 text-sm font-medium text-gray-800">
										<?php cfw_e( 'CheckoutWC', 'checkout-wc' ); ?>
									</span>
								</div>
							</li>
							<li class="m-0">
								<div class="flex items-center">
									<!-- Heroicon name: solid/chevron-right -->
									<svg class="flex-shrink-0 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
										<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
									</svg>
									<span class="ml-2 text-sm font-medium text-gray-500" aria-current="page">
										<?php echo $this->title; ?>
									</span>
								</div>
							</li>
						</ol>
					</nav>
				</div>
				<?php do_action( 'cfw_after_admin_page_header', $this ); ?>
			</div>
		</div>
		<?php
	}

	public function maybe_show_license_upgrade_splash() {
		if ( $this->is_current_page() && ! $this->is_available ) {
			echo $this->get_old_style_upgrade_required_notice( $this->formatted_required_plans_list );
		}
	}

	/**
	 * @param mixed $submenu_file
	 * @return mixed
	 */
	public function maybe_highlight_order_bumps_submenu_item( $submenu_file ) {
		$post_type = $this->post_type_slug;

		if ( stripos( $_SERVER['REQUEST_URI'], "post-new.php?post_type=$post_type" ) !== false ) {
			return $this->get_slug();
		}

		return $submenu_file;
	}

	public function output() {}
}
