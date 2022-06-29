<?php

namespace Objectiv\Plugins\Checkout\Admin\Notices;

use Objectiv\Plugins\Checkout\Managers\UpdatesManager;

class InvalidLicenseKeyNotice extends NoticeAbstract {
	public function maybe_show() {
		$key_status  = UpdatesManager::instance()->get_field_value( 'key_status' );
		$license_key = UpdatesManager::instance()->get_field_value( 'license_key' );

		if ( ! empty( $_GET['cfw_welcome'] ) ) {
			return;
		}

		if ( ! empty( $license_key ) && 'valid' === $key_status ) {
			return;
		}
		?>
		<div class='notice notice-error is-dismissible checkout-wc'>
			<h4>Uh oh! There's a problem with your CheckoutWC license.</h4>
			<p>
				<?php if ( 'expired' === $key_status ) : ?>
					<?php cfw_e( 'Your license key appears to have expired. Please <a href="https://www.checkoutwc.com" target="_blank">purchase a new license</a> to restore functionality. If you believe this is in error, <a href="mailto:support@checkoutwc.com?subject=Problem%20With%20License%20Expiration&body=License%20Key%3A%20' . $license_key . '">please contact support</a>.', 'checkout-wc' ); ?>
				<?php else : ?>
					<?php cfw_e( 'Your license key is missing or invalid. Please verify that your license key is valid or <a target="_blank" href="https://www.checkoutwc.com/pricing">purchase a license</a> to restore full functionality.', 'checkout-wc' ); ?>
				<?php endif; ?>
			</p>
		</div>
		<?php
	}
}
