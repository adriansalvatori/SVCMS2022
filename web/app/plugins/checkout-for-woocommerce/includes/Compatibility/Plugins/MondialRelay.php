<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class MondialRelay extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'run_MRWP' );
	}

	public function run() {
		add_filter( 'cfw_body_classes', array( $this, 'add_body_class' ) );
	}

	/**
	 * @param array $classes The body classes
	 *
	 * @return array
	 */
	public function add_body_class( $classes ) {
		$classes[] = 'checkoutwc-mondial-relay';

		return $classes;
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'MondialRelay',
			'params' => array(),
		);

		return $compatibility;
	}
}
