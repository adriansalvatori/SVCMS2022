<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class TMOrganik extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\Insight_Functions' );
	}

	public function run() {
		add_action( 'wp_head', array( $this, 'shim_headroom' ) );
	}

	public function shim_headroom() {
		?>
		<script type="text/javascript">
			jQuery(document).ready( function() {
				jQuery.fn.headroom = function () {};
			} );
		</script>
		<?php
	}
}
