<?php

namespace Objectiv\Plugins\Checkout;
class TrustBadgeImageSizeAdder {
	/**
	 * Add a new image size for our cart views
	 */
	public function add_trust_badge_image_size() {
		/**
		 * Filter cart thumbnail width
		 *
		 * @since 3.0.0
		 *
		 * @param int $thumb_width The width of thumbnails in cart
		 */
		$cfw_cart_thumb_width = apply_filters( 'cfw_trust_badge_thumb_width', 110 );

		/**
		 * Filter cart thumbnail height
		 *
		 * 0 indicates auto height
		 *
		 * @since 3.0.0
		 *
		 * @param int $thumb_width The height of thumbnails in cart
		 */
		$cfw_cart_thumb_height = apply_filters( 'cfw_trust_badge_thumb_height', 0 );

		/**
		 * Filter whether to crop cart thumbnails
		 *
		 * @since 3.0.0
		 *
		 * @param bool $crop True allows cropping
		 */
		$cfw_crop_cart_thumbs = apply_filters( 'cfw_crop_trust_badge_thumbs', false );

		add_image_size( 'cfw_trust_badge_thumb', $cfw_cart_thumb_width, $cfw_cart_thumb_height, $cfw_crop_cart_thumbs );
	}
}
