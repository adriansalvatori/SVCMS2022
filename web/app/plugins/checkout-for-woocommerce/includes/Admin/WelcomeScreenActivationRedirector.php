<?php

namespace Objectiv\Plugins\Checkout\Admin;

class WelcomeScreenActivationRedirector {
	public function welcome_screen_do_activation_redirect() {
		// Bail if no activation redirect
		if ( ! get_transient( '_cfw_welcome_screen_activation_redirect' ) ) {
			return;
		}

		// Delete the redirect transient
		delete_transient( '_cfw_welcome_screen_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		// Redirect to bbPress about page
		wp_safe_redirect(
			add_query_arg(
				array(
					'page' => 'cfw-settings',
				),
				admin_url( 'admin.php' )
			)
		);
	}
}
