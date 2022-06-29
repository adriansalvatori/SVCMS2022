<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

class YITHPointsAndRewards {
	public function is_available(): bool {
		return defined( 'YITH_YWPAR_VERSION' );
	}

	public function init() {
		add_action( 'cfw_template_redirect_priority', array( $this, 'maybe_change_template_redirect_priority' ) );
	}

	public function maybe_change_template_redirect_priority( $priority ) {
		if ( $this->is_available() ) {
			$priority = 31;
		}

		return $priority;
	}
}
