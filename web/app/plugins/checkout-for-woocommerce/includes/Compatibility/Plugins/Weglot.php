<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use Weglot\Client\Api\LanguageEntry;

class Weglot extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'WEGLOT_NAME' );
	}

	public function run_immediately() {
		// Weglot uses output buffering that runs afoul of our error prevention strategies
		wc_maybe_define_constant( 'CFW_ACTION_NO_ERROR_SUPPRESSION_BUFFER', true );

		add_filter( 'cfw_parsley_locale', array( $this, 'override_parsley_locale' ) );
	}

	public function override_parsley_locale( $locale ) {
		if ( ! function_exists( 'weglot_get_service' ) ) {
			return $locale;
		}

		/** @var LanguageEntry $weglot_locale */
		$weglot_locale = weglot_get_service( 'Request_Url_Service_Weglot' )->get_current_language();

		if ( ! $weglot_locale ) {
			return $locale;
		}

		if ( $weglot_locale instanceof LanguageEntry ) {
			$locale = $weglot_locale->getInternalCode();
		} elseif ( is_string( $weglot_locale ) ) {
			$locale = $weglot_locale;
		}

		return $locale;
	}
}
