<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class TranslatePress extends CompatibilityAbstract {
	public function is_available(): bool {
		global $trp_output_buffer_started;

		return ! empty( $trp_output_buffer_started );
	}

	public function pre_init() {
		define( 'CFW_ACTION_NO_ERROR_SUPPRESSION_BUFFER', true );
	}
}