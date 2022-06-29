<?php

namespace Objectiv\Plugins\Checkout\Features;

use Objectiv\Plugins\Checkout\Interfaces\SettingsGetterInterface;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 */
abstract class FeaturesAbstract {
	protected $enabled   = false;
	protected $available = false;
	protected $required_plans_list;

	/**
	 * @var SettingsGetterInterface
	 */
	protected $settings_getter;

	public function __construct( bool $enabled, bool $available, string $required_plans_list, SettingsGetterInterface $settings_getter ) {
		$this->enabled             = $enabled;
		$this->available           = $available;
		$this->required_plans_list = $required_plans_list;
		$this->settings_getter     = $settings_getter;
	}

	public function init() {
		if ( ! $this->is_active() ) {
			return;
		}

		add_action( 'init', array( $this, 'check_if_cfw_is_enabled' ) );
	}

	public function check_if_cfw_is_enabled() {
		if ( ! cfw_is_enabled() ) {
			return;
		}

		$this->run_if_cfw_is_enabled();
	}

	/**
	 * @return bool
	 */
	public function is_active(): bool {
		return $this->available && $this->enabled;
	}

	abstract protected function run_if_cfw_is_enabled();
}
