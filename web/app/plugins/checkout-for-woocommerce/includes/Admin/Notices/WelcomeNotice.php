<?php

namespace Objectiv\Plugins\Checkout\Admin\Notices;

class WelcomeNotice extends NoticeAbstract {
	public function __construct() {}

	public function maybe_show() {
		if ( empty( $_GET['cfw_welcome'] ) ) {
			return;
		}
		?>
		<div id="cfw_welcome_notice" style="display:block !important" class="notice notice-info">
			<p>
				<h2>
					<?php cfw_e( 'Welcome', 'checkout-wc' ); ?>
				</h2>
				<?php cfw_e( 'Thank you for installing CheckoutWC! To get started, enter your <strong>License Key</strong> below, save, and click <strong>Activate Site</strong>.', 'checkout-wc' ); ?>
			</p>
		</div>
		<?php
	}
}
