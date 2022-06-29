<?php

namespace Objectiv\Plugins\Checkout\Model\Bumps;

class AllProductsBump extends BumpAbstract {
	public function is_displayable(): bool {
		if ( ! $this->can_offer_product_be_added_to_the_cart() ) {
			return false;
		}

		if ( $this->quantity_of_product_in_cart( $this->offer_product ) ) {
			return false;
		}

		return true;
	}

	public function is_cart_bump_valid(): bool {
		// If the bump is valid for all products, make sure we have at least one other product in the cart
		return WC()->cart->get_cart_contents_count() > $this->quantity_of_product_in_cart( $this->offer_product );
	}
}
