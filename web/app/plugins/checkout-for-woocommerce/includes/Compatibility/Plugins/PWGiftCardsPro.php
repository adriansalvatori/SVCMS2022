<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class PWGiftCardsPro extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'PWGC_GIFT_CARD_NOTIFICATIONS_META_KEY' );
	}

	public function run() {
		add_action( 'cfw_wp_head', array( $this, 'gift_card_remove_fix' ), 51 );
	}

	public function gift_card_remove_fix() {
		?>
		<script>
			jQuery( document ).ready( function() {
				jQuery( document.body ).on('click', '.pwgc-remove-card', function(e) {
					var cardNumber = jQuery(this).attr('data-card-number');

					jQuery.post(pwgc.ajaxurl, {'action': 'pw-gift-cards-remove', 'card_number': cardNumber, 'security': pwgc.nonces.remove_card}, function( result ) {
						location.reload();
					}).fail(function(xhr, textStatus, errorThrown) {
						if (errorThrown) {
							alert(errorThrown);
						} else {
							alert('Unknown Error');
						}
						location.reload();
					});

					e.preventDefault();
					return false;
				});
			} );
		</script>
		<?php
	}
}
