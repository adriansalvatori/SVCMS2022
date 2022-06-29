<?php

namespace Objectiv\Plugins\Checkout\Model\Bumps;

use Objectiv\Plugins\Checkout\Interfaces\BumpInterface;
use Objectiv\Plugins\Checkout\Factories\BumpFactory;

abstract class BumpAbstract implements BumpInterface {
	protected $id;
	protected $title;
	protected $display_for;
	protected $products;
	protected $categories;
	protected $any_product = false;
	protected $location;
	protected $discount_type;
	protected $offer_product;
	protected $offer_discount;
	protected $offer_language;
	protected $offer_description;
	protected $upsell = false;
	protected $offer_quantity;
	protected $can_quantity_be_updated = false;

	public function __construct() {}

	/**
	 * @param $post
	 *
	 * @return bool
	 */
	public function load( $post ): bool {
		$this->id                      = $post->ID;
		$this->title                   = $post->post_title;
		$this->display_for             = get_post_meta( $post->ID, 'cfw_ob_display_for', true );
		$this->products                = get_post_meta( $post->ID, 'cfw_ob_products', true );
		$this->categories              = get_post_meta( $post->ID, 'cfw_ob_categories', true );
		$this->any_product             = get_post_meta( $post->ID, 'cfw_ob_any_product', true ) === 'yes';
		$this->location                = get_post_meta( $post->ID, 'cfw_ob_display_location', true );
		$this->discount_type           = get_post_meta( $post->ID, 'cfw_ob_discount_type', true );
		$this->offer_product           = get_post_meta( $post->ID, 'cfw_ob_offer_product', true );
		$this->offer_discount          = get_post_meta( $post->ID, 'cfw_ob_offer_discount', true );
		$this->offer_language          = get_post_meta( $post->ID, 'cfw_ob_offer_language', true );
		$this->offer_description       = get_post_meta( $post->ID, 'cfw_ob_offer_description', true );
		$this->upsell                  = get_post_meta( $post->ID, 'cfw_ob_upsell', true ) === 'yes';
		$this->offer_quantity          = get_post_meta( $post->ID, 'cfw_ob_offer_quantity', true );
		$this->can_quantity_be_updated = get_post_meta( $post->ID, 'cfw_ob_enable_quantity_updates', true ) === 'yes';

		if ( empty( $this->offer_quantity ) ) {
			$this->offer_quantity = 1;
		}

		return true;
	}

	/**
	 * @return string
	 */
	public function get_title(): string {
		return $this->title;
	}

	/**
	 * @return bool
	 */
	abstract public function is_displayable(): bool;

	/**
	 * @return bool
	 */
	abstract public function is_cart_bump_valid(): bool;

	/**
	 * @return bool
	 */
	public function is_valid_upsell(): bool {
		return $this->upsell && 'specific_products' === $this->display_for && count( $this->products ) === 1;
	}

	public function can_offer_product_be_added_to_the_cart(): bool {
		$product = $this->get_offer_product();

		return $product && $product->is_purchasable() && ( $product->is_in_stock() || $product->backorders_allowed() );
	}

	public function get_cart_item_subtotal( $subtotal, $cart_item ): string {
		if ( ! $this->is_cart_bump_valid() ) {
			return $subtotal;
		}
		// This was added for TM Extra Product Options
		// Which aggressively tries to override the display of the cart item price
		// We are operating on the assumption that there are other, equally evil plugins out there
		// But we don't know for sure - so we should circle back to this
		// TODO: Should we specifically solve this in a compatibility class or leave this as protective code for n+1 situations?
		return isset( $cart_item['cfw_order_bump_price'] ) ? wc_price( $cart_item['cfw_order_bump_price'] * $cart_item['quantity'] ) : $subtotal;
	}

	public function update_cart_item_price( \WC_Cart $cart, $cart_item_key ) {
		if ( ! $this->is_cart_bump_valid() ) {
			return;
		}

		$bump_price = $cart->cart_contents[ $cart_item_key ]['cfw_order_bump_price'] ?? false;

		if ( false === $bump_price ) {
			return;
		}

		$product = $cart->cart_contents[ $cart_item_key ]['data'] ?? false;

		if ( ! ( $product instanceof \WC_Product ) ) {
			return;
		}

		$cart->cart_contents[ $cart_item_key ]['cfw_order_bump_regular_price'] = $product->get_price();
		$product->set_price( $bump_price );
	}

	public function add_bump_meta_to_order_item( $item, $values ) {
		if ( ! $this->is_cart_bump_valid() ) {
			return;
		}

		$item->update_meta_data( '_cfw_order_bump_id', $values['_cfw_order_bump_id'] );
	}

	public function get_cfw_cart_item_discount( string $price_html ): string {
		if ( ! $this->is_cart_bump_valid() ) {
			return $price_html;
		}

		return $this->get_offer_product_price();
	}

	/**
	 * @throws \Exception
	 */
	public function add_to_cart( \WC_Cart $cart ) {
		$product          = $this->get_offer_product();
		$discounted_price = $this->get_offer_product_sale_price();
		$variation_id     = $product->is_type( 'variable' ) ? $product->get_id() : null;
		$product_id       = $product->is_type( 'variable' ) ? $product->get_parent_id() : $product->get_id();
		$variation_data   = null;
		$metadata         = array(
			'cfw_order_bump_price' => strval( $discounted_price ),
			'_cfw_order_bump_id'   => $this->id,
		);

		if ( $product->is_type( 'variation' ) ) {
			$variation_data = array();

			foreach ( $product->get_variation_attributes() as $taxonomy => $term_names ) {
				$taxonomy                                = str_replace( 'attribute_', '', $taxonomy );
				$attribute_label_name                    = str_replace( 'attribute_', '', wc_attribute_label( $taxonomy ) );
				$variation_data[ $attribute_label_name ] = $term_names;
			}
		}

		$quantity = $this->get_offer_quantity();

		if ( $this->is_valid_upsell() ) {
			$search_product = array_values( $this->get_products() )[0];
			$quantity       = $this->quantity_of_product_in_cart( $search_product );

			$this->remove_product_from_cart( $search_product );
		}

		do_action( 'cfw_before_order_bump_add_to_cart', $this );

		if ( has_action( 'cfw_order_bump_add_to_cart_product_type_' . $product->get_type() ) ) {
			do_action( 'cfw_order_bump_add_to_cart_product_type_' . $product->get_type(), $product_id, $quantity, $variation_id, $variation_data, $metadata, $product );

			return true;
		}

		return $cart->add_to_cart( $product_id, $quantity, $variation_id, $variation_data, $metadata );
	}

	/**
	 * @param int $needle_product_id
	 * @return bool
	 */
	protected function remove_product_from_cart( int $needle_product_id ): bool {
		$needle_product = wc_get_product( $needle_product_id );

		if ( ! $needle_product ) {
			return false;
		}

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$cart_item_variation_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0;
			$cart_item_parent_id    = $cart_item_variation_id ? wp_get_post_parent_id( $cart_item_variation_id ) : 0;
			$possible_ids           = array( $cart_item_parent_id, $cart_item_variation_id, $cart_item['product_id'] );
			$in_cart                = in_array( $needle_product_id, $possible_ids, true );

			if ( $in_cart ) {
				WC()->cart->remove_cart_item( $cart_item_key );

				return true;
			}
		}

		return false;
	}

	/**
	 * @param int $needle_product_id
	 * @return int
	 */
	public function quantity_of_product_in_cart( int $needle_product_id ): int {
		$needle_product = wc_get_product( $needle_product_id );

		if ( ! $needle_product ) {
			return 0;
		}

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( $this->cart_item_is_product( $cart_item, $needle_product_id ) ) {
				return $cart_item['quantity'];
			}
		}

		return 0;
	}

	/**
	 * @param int $needle_product_id
	 * @return int
	 */
	public function quantity_of_normal_product_in_cart( int $needle_product_id ): int {
		$needle_product = wc_get_product( $needle_product_id );

		if ( ! $needle_product ) {
			return 0;
		}

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( isset( $cart_item['_cfw_order_bump_id'] ) ) {
				continue;
			}

			if ( $this->cart_item_is_product( $cart_item, $needle_product_id ) ) {
				return $cart_item['quantity'];
			}
		}

		return 0;
	}

	protected function cart_item_is_product( array $cart_item, int $product_id ): bool {
		if ( $cart_item['product_id'] === $product_id ) {
			return true;
		}

		if ( empty( $cart_item['variation_id'] ) ) {
			return false;
		}

		if ( $cart_item['variation_id'] === $product_id ) {
			return true;
		}

		return wp_get_post_parent_id( $cart_item['variation_id'] ) === $product_id;
	}

	public function record_purchased() {
		$this->increment_displayed_on_purchases_count();
		$this->increment_purchased_count();
		$this->update_conversion_rate();

		$offer_product = $this->get_offer_product();

		if ( ! $offer_product ) {
			return;
		}

		if ( $this->is_valid_upsell() ) {
			$base_product = wc_get_product( $this->get_products()[0] );

			if ( ! $base_product ) {
				return;
			}

			$new_revenue = $base_product->get_price() - $this->get_offer_product_sale_price();
		} else {
			$new_revenue = $this->get_offer_product_sale_price();
		}

		$this->add_captured_revenue( $new_revenue );
	}

	public function display( string $location ) {
		$display_bump          = $this->is_displayable() && ( 'all' === $location || $this->get_display_location() === $location );
		$filtered_display_bump = apply_filters( 'cfw_display_bump', $display_bump, $this, $location );

		if ( ! $filtered_display_bump ) {
			return;
		}

		$link_wrap     = '<a target="_blank" href="%s">%s</a>';
		$offer_product = $this->get_offer_product();
		$thumb         = $offer_product->get_image( 'cfw_cart_thumb' );
		$wrapped_thumb = $offer_product->is_visible() ? sprintf( $link_wrap, $offer_product->get_permalink(), $thumb ) : $thumb;

		?>
		<div class="cfw-order-bump cfw-module">
			<input type="hidden" name="cfw_displayed_order_bump[]" value="<?php echo $this->get_id(); ?>" />

			<div class="cfw-order-bump-header">
				<label class="woocommerce-form__label-for-checkbox">
					<input type="checkbox" class="cfw_order_bump_check" name="cfw_order_bump[]" value="<?php echo $this->get_id(); ?>" />
					<span>
						<?php echo do_shortcode( $this->get_offer_language() ); ?>
					</span>
				</label>
			</div>
			<div class="cfw-order-bump-body">
				<div class="row">
					<div class="col-2">
						<?php echo $wrapped_thumb; ?>
					</div>
					<div class="col-10">
						<?php echo do_shortcode( $this->get_offer_description() ); ?>

						<div class="cfw-order-bump-total">
							<?php echo $this->get_offer_product_price(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * @return int
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * @return mixed
	 */
	public function get_offer_language() {
		return $this->offer_language;
	}

	/**
	 * @return mixed
	 */
	public function get_offer_description() {
		return $this->offer_description;
	}

	/**
	 * @return mixed
	 */
	public function get_offer_quantity() {
		return $this->offer_quantity;
	}

	/**
	 * @return false|\WC_Product|null
	 */
	protected function get_offer_product() {
		return wc_get_product( $this->offer_product );
	}

	/**
	 * @return string
	 */
	protected function get_offer_product_price(): string {
		$product              = $this->get_offer_product();
		$price                = wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price( 'view' ) ) );
		$sale_price           = wc_get_price_to_display( $product, array( 'price' => $this->get_offer_product_sale_price() ) );
		$sale_price_formatted = wc_price( $sale_price );

		if ( $price > $sale_price ) {
			return wc_format_sale_price( $price, $sale_price );
		}

		return $sale_price_formatted;
	}

	/**
	 * @return float|int
	 */
	protected function get_offer_product_sale_price() {
		$product = $this->get_offer_product();

		$discount_type = $this->discount_type;
		$discount      = $this->offer_discount;

		if ( wc_prices_include_tax() ) {
			$price = wc_get_price_including_tax( $product );
		} else {
			$price = wc_get_price_excluding_tax( $product );
		}

		$discount_value = 'percent' === $discount_type ? ( $price / 100 ) * $discount : $discount;

		return $price - $discount_value;
	}

	protected function get_display_location(): string {
		return $this->location ?? 'below_cart_items';
	}

	/**
	 * Get Displayed On Purchases Count
	 *
	 * The number of times this bump was displayed and a purchase was subsequently made.
	 *
	 * @return integer
	 */
	private function get_displayed_on_purchases_count(): int {
		return intval( get_post_meta( $this->id, 'times_bump_displayed_on_purchases', true ) );
	}

	/**
	 * Get Purchase Count
	 *
	 * The number of times this bump was added to the cart and purchased.
	 *
	 * @return integer
	 */
	public function get_purchase_count(): int {
		return intval( get_post_meta( $this->id, 'times_bump_purchased', true ) );
	}

	public function record_displayed() {
		$this->increment_displayed_on_purchases_count();
		$this->update_conversion_rate();
	}

	public function increment_displayed_on_purchases_count() {
		update_post_meta( $this->id, 'times_bump_displayed_on_purchases', $this->get_displayed_on_purchases_count() + 1 );
	}

	public function increment_purchased_count() {
		update_post_meta( $this->id, 'times_bump_purchased', $this->get_purchase_count() + 1 );
	}

	public function add_captured_revenue( float $new_revenue ) {
		$captured_revenue = max( (float) get_post_meta( $this->id, 'captured_revenue', true ), 0.0 );

		update_post_meta( $this->id, 'captured_revenue', $captured_revenue + $new_revenue );
	}

	public function update_conversion_rate() {
		$purchase_count  = $this->get_purchase_count();
		$displayed_count = $this->get_displayed_on_purchases_count();
		$not_calculable  = min( $purchase_count, $displayed_count ) < 1;

		$value = $not_calculable ? 0 : round( $purchase_count / $displayed_count * 100, 2 );

		update_post_meta( $this->id, 'conversion_rate', $value );
	}

	public function get_conversion_rate(): string {
		$value = get_post_meta( $this->id, 'conversion_rate', true );

		return '' === $value ? '--' : floatval( $value ) . '%';
	}

	public function get_item_removal_behavior(): string {
		$value = get_post_meta( $this->id, 'cfw_ob_item_removal_behavior', true );

		return empty( $value ) ? 'keep' : $value;
	}

	public function get_estimated_revenue(): float {
		$offer_product = $this->get_offer_product();

		if ( ! $offer_product ) {
			return 0.0;
		}

		if ( $this->is_valid_upsell() ) {
			$base_product = wc_get_product( $this->get_products()[0] );

			if ( ! $base_product ) {
				return 0.0;
			}

			return ( $base_product->get_price() - $this->get_offer_product_sale_price() ) * $this->get_purchase_count();
		}

		return $this->get_purchase_count() * $this->get_offer_product_sale_price();
	}

	public function get_captured_revenue(): float {
		return floatval( get_post_meta( $this->id, 'captured_revenue', true ) );
	}

	/**
	 * @return array
	 */
	public function get_products(): array {
		return (array) $this->products;
	}

	static public function get_post_type(): string {
		return 'cfw_order_bumps';
	}

	static public function init( $parent_menu_slug ) {
		$post_type = self::get_post_type();

		add_action(
			'init',
			function() use ( $post_type, $parent_menu_slug ) {
				register_post_type(
					$post_type,
					array(
						'labels'             => array(
							'name'               => cfw__( 'Order Bumps', 'checkout-wc' ),
							'singular_name'      => cfw__( 'Order Bump', 'checkout-wc' ),
							'add_new'            => cfw__( 'Add New', 'checkout-wc' ),
							'add_new_item'       => cfw__( 'Add New Order Bump', 'checkout-wc' ),
							'edit_item'          => cfw__( 'Edit Order Bump', 'checkout-wc' ),
							'new_item'           => cfw__( 'New Order Bump', 'checkout-wc' ),
							'view_item'          => cfw__( 'View Order Bump', 'checkout-wc' ),
							'search_items'       => cfw__( 'Find Order Bump', 'checkout-wc' ),
							'not_found'          => cfw__( 'No order bumps were found.', 'checkout-wc' ),
							'not_found_in_trash' => cfw__( 'Not found in trash', 'checkout-wc' ),
							'menu_name'          => cfw__( 'Order Bumps', 'checkout-wc' ),
						),
						'public'             => false,
						'publicly_queryable' => true,
						'show_ui'            => true,
						'show_in_menu'       => $parent_menu_slug,
						'query_var'          => false,
						'rewrite'            => false,
						'has_archive'        => false,
						'hierarchical'       => false,
						'supports'           => array( 'title' ),
						'capabilities'       => array(
							'edit_post'          => 'manage_options',
							'read_post'          => 'manage_options',
							'delete_post'        => 'manage_options',
							'edit_posts'         => 'manage_options',
							'edit_others_posts'  => 'manage_options',
							'delete_posts'       => 'manage_options',
							'publish_posts'      => 'manage_options',
							'read_private_posts' => 'manage_options',
						),
					)
				);
			}
		);

		add_filter(
			"manage_{$post_type}_posts_columns",
			function( $columns ) {
				$date = array_pop( $columns );

				$columns['conversion_rate'] = 'Conversion Rate' . wc_help_tip( 'Conversion Rate tracks how often a bump is added to an actual completed purchase. If 20 orders are placed and a bump was displayed on 10 of those orders and the bump was purchased 5 times, the conversion rate is 50%.' );
				$columns['revenue']         = 'Revenue' . wc_help_tip( 'The additional revenue that an Order Bump has captured. When configured as an upsell, it calculates the relative value between the offer product and the product being replaced. Revenues incurred before version 6.1.4 are estimated.' );
				$columns['date']            = $date;

				return $columns;
			}
		);

		add_action(
			"manage_{$post_type}_posts_custom_column",
			function( $column, $post_id ) {
				if ( 'conversion_rate' === $column ) {
					echo BumpFactory::get( $post_id )->get_conversion_rate();
				}

				if ( 'revenue' === $column ) {
					$captured_revenue = BumpFactory::get( $post_id )->get_captured_revenue();

					echo 0.0 === $captured_revenue ? '--' : wc_price( $captured_revenue );
				}
			},
			10,
			2
		);
	}

	/**
	 * @return bool
	 */
	public function is_in_cart(): bool {
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( isset( $cart_item['_cfw_order_bump_id'] ) && $cart_item['_cfw_order_bump_id'] === $this->id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function can_quantity_be_updated(): bool {
		return $this->can_quantity_be_updated;
	}
}
