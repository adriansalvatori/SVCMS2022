<?php

namespace Objectiv\Plugins\Checkout\Admin\Notices;

use Objectiv\Plugins\Checkout\Managers\SettingsManager;
use Objectiv\Plugins\Checkout\Managers\UpdatesManager;

class TemplateDisabledNotice extends NoticeAbstract {
	public function maybe_show() {
		$enabled     = SettingsManager::instance()->get_setting( 'enable' ) === 'yes';
		$key_status  = UpdatesManager::instance()->get_field_value( 'key_status' );
		$license_key = UpdatesManager::instance()->get_field_value( 'license_key' );

		if ( $enabled || empty( $license_key ) || 'valid' !== $key_status ) {
			return;
		}
		?>
		<div class='notice notice-warning checkout-wc'>
			<h4>
				<?php cfw_e( 'CheckoutWC Templates Deactivated', 'checkout-wc' ); ?>
			</h4>

			<p>
				<?php echo sprintf( cfw__( 'Your license is valid and activated for this site but CheckoutWC is disabled for normal customers. To fix this, go to %s > %s and toggle "%s".', 'checkout-wc' ), cfw__( 'Settings', 'checkout-wc' ), cfw__( 'Start Here', 'checkout-wc' ), cfw__( 'Activate CheckoutWC Templates', 'checkout-wc' ) ); ?>
			</p>
		</div>
		<?php
	}
}
