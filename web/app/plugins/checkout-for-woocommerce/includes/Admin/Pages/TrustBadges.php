<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Admin\Pages
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class TrustBadges extends PageAbstract {
	public function __construct() {
		parent::__construct( cfw__( 'Trust Badges', 'checkout-wc' ), 'manage_options', 'trust-badges' );
	}
	public function output() {
		$this->output_form_open();
		?>
		<div class="space-y-6">
			<?php
			/**
			 * Fires at the bottom of the trust badges admin page
			 *
			 * @since 7.1.3
			 *
			 * @param TrustBadges $trust_badges_admin_page The trust badges admin page
			 */
			do_action( 'cfw_trust_badges_after_admin_page_settings', $this );
			?>
		</div>
		<?php
		$this->output_form_close();
	}
}
