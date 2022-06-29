<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class NextGenGallery extends CompatibilityAbstract {
	public function is_available(): bool {
		return true; // we may as well, it's just one filter
	}

	public function pre_init() {
		add_filter(
			'run_ngg_resource_manager',
			function( $valid_request ) {
				if ( ! empty( $_GET['wc-ajax'] ) ) {
					return false;
				}

				return $valid_request;
			}
		);
	}
}
