<?php
/**
 * Provides standard object for accessing user-defined plugin settings
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout\Managers
 * @author Brandon Tassone <brandontassone@gmail.com>
 */


namespace Objectiv\Plugins\Checkout\Managers;

use Objectiv\Plugins\Checkout\Interfaces\SettingsGetterInterface;

class SettingsManager extends SettingsManagerAbstract implements SettingsGetterInterface {

	var $prefix = '_cfw_';

	/**
	 * @param string $setting_name
	 * @param array $keys
	 * @return string
	 */
	private function add_suffix( string $setting_name, array $keys = array() ): string {
		if ( empty( $keys ) ) {
			return $setting_name;
		}

		asort( $keys );

		return $setting_name . '_' . join( '', $keys );
	}

	/**
	 * @param string $setting
	 * @param mixed $value
	 * @param array $keys
	 * @return bool
	 */
	public function add_setting( string $setting, $value, array $keys = array() ): bool {
		return parent::add_setting( $this->add_suffix( $setting, $keys ), $value );
	}

	/**
	 * @param string $setting
	 * @param array|string $value
	 * @param bool $save_to_db
	 * @param array $keys
	 * @return bool
	 */
	public function update_setting( string $setting, $value, bool $save_to_db = true, array $keys = array() ): bool {
		return parent::update_setting( $this->add_suffix( $setting, $keys ), $value, $save_to_db );
	}

	/**
	 * @param string $setting
	 * @param array $keys
	 * @return bool
	 */
	public function delete_setting( string $setting, array $keys = array() ): bool {
		return parent::delete_setting( $this->add_suffix( $setting, $keys ) );
	}

	/**
	 * @param string $setting
	 * @param array $keys
	 * @return false|mixed
	 */
	public function get_setting( string $setting, array $keys = array() ) {
		return parent::get_setting( $this->add_suffix( $setting, $keys ) );
	}

	/**
	 * @param string $setting
	 * @param array $keys
	 * @return string
	 */
	public function get_field_name( string $setting, array $keys = array() ): string {
		return parent::get_field_name( $this->add_suffix( $setting, $keys ) );
	}

	/**
	 * @param string $setting
	 * @param int $required_activations
	 * @deprecated
	 * @return bool
	 */
	public function is_premium_feature_enabled( string $setting, int $required_activations = 5 ): bool {
		$enough_activations = UpdatesManager::instance()->get_license_activation_limit() >= $required_activations;

		$value = $this->get_setting( $setting );

		return $enough_activations && ( 'yes' === $value || 'enabled' === $value );
	}
}
