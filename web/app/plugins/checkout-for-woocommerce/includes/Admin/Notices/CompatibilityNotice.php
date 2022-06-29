<?php

namespace Objectiv\Plugins\Checkout\Admin\Notices;

class CompatibilityNotice extends NoticeAbstract {
	public function maybe_show() {
		$active_plugins = get_option( 'active_plugins' );
		$search_plugins = array(
			'wc-fields-factory/wcff.php' => 'WC Fields Factory',
		);

		$incompatible_plugins = array();

		foreach ( $search_plugins as $file => $plugin_name ) {
			if ( in_array( $file, $active_plugins, true ) ) {
				$deactivate_url         = wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . urlencode( $file ) . '&amp;plugin_status=active', 'deactivate-plugin_' . $file );
				$incompatible_plugins[] = $plugin_name . ' - <a href="' . $deactivate_url . '">Deactivate</a>';
			}
		}

		if ( ! empty( $incompatible_plugins ) ) : ?>
			<div class="notice notice-error is-dismissible checkout-wc">
				<h4>CheckoutWC: Plugin Conflict Detected</h4>
				<p>
					<?php cfw_e( 'The following plugin(s) has known conflicts with CheckoutWC and may cause errors:', 'checkout-wc' ); ?>
					<ol>
						<?php foreach ( $incompatible_plugins as $incompatible_plugin ) : ?>
							<li>
								<?php echo $incompatible_plugin; ?>
							</li>
						<?php endforeach; ?>
					</ol>

					<?php echo cfw__( 'Please deactivate to avoid problems with your checkout page.', 'checkout-wc' ) . ''; ?>
				</p>
			</div>
			<?php
		endif;
	}
}
