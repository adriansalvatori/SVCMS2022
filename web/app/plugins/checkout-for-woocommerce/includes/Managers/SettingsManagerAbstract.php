<?php

namespace Objectiv\Plugins\Checkout\Managers;

use Objectiv\Plugins\Checkout\SingletonAbstract;

abstract class SettingsManagerAbstract extends SingletonAbstract {
	var $settings = array();
	var $prefix;
	var $delimiter;
	var $network_only = false;

	public function __construct() {}

	public function init() {
		// Set a default prefix
		if ( function_exists( 'get_called_class' ) && empty( $this->prefix ) ) {
			$this->prefix = get_called_class();
		}

		// Set a default delimiter for separated values
		if ( empty( $this->delimiter ) ) {
			$this->delimiter = ';';
		}

		$this->settings = $this->get_settings_obj();

		add_action( 'admin_init', array( $this, 'save_settings' ), 0 );
	}

	public function reload() {
		$this->settings = $this->get_settings_obj();
	}

	/**
	 * Add a new setting
	 *
	 * @author Clifton H. Griffin II
	 * @since 0.1.0
	 *
	 * @param string $setting The name of the new option
	 * @param mixed $value The value of the new option
	 * @return boolean True if successful, false otherwise
	 **/
	public function add_setting( string $setting, $value ): bool {
		if ( ! isset( $this->settings[ $setting ] ) ) {
			return $this->update_setting( $setting, $value );
		} else {
			return false;
		}
	}

	/**
	 * Updates or adds a setting
	 *
	 * @param string $setting The name of the option
	 * @param string|array $value The new value of the option
	 * @param bool $save_to_db
	 *
	 * @return boolean True if successful, false if not
	 * @author Clifton H. Griffin II
	 * @since 0.1.0
	 *
	 */
	public function update_setting( string $setting, $value, bool $save_to_db = true ): bool {
		if ( empty( $setting ) ) {
			return false;
		}

		$old_value                  = isset( $this->settings[ $setting ] ) ?? null;
		$this->settings[ $setting ] = $value;

		do_action( "{$this->prefix}_update_setting", $setting, $old_value, $value );
		do_action( "{$this->prefix}_update_setting_{$setting}", $old_value, $value );

		if ( $save_to_db ) {
			return $this->set_settings_obj( $this->settings );
		}

		return true;
	}

	/**
	 * Deletes a setting
	 *
	 * @author Clifton H. Griffin II
	 * @since 0.1
	 *
	 * @param string $setting The name of the option
	 * @return boolean True if successful, false if not
	 **/
	public function delete_setting( string $setting ): bool {
		if ( ! isset( $this->settings[ $setting ] ) ) {
			return false;
		}

		unset( $this->settings[ $setting ] );

		return $this->set_settings_obj( $this->settings );
	}

	/**
	 * Retrieves a setting value
	 *
	 * @param string $setting The name of the option
	 * @return mixed The value of the setting
	 * @author Clifton H. Griffin II
	 * @since 0.1.0
	 *
	 */
	public function get_setting( string $setting ) {
		if ( ! isset( $this->settings[ $setting ] ) ) {
			return false;
		}

		$value = $this->settings[ $setting ];

		return apply_filters( $this->prefix . '_get_setting', $value, $setting );
	}

	/**
	 * Generates HTML field name for a particular setting
	 *
	 * @param string $setting The name of the setting
	 * @return string The name of the field
	 * @author Clifton H. Griffin II
	 * @since 0.1.0
	 *
	 */
	public function get_field_name( string $setting ): string {
		$type = 'string';

		return "{$this->prefix}_setting[$setting][$type]";
	}

	/**
	 * Prints nonce for admin form
	 *
	 * @author Clifton H. Griffin II
	 * @since 0.1.0
	 *
	 * @return void
	 **/
	public function the_nonce() {
		wp_nonce_field( "save_{$this->prefix}_settings", "{$this->prefix}_save" );
	}

	/**
	 * Saves settings
	 *
	 * @author Clifton H. Griffin II
	 * @since 0.1.0
	 *
	 * @return void
	 **/
	public function save_settings() {
		if ( isset( $_REQUEST[ "{$this->prefix}_setting" ] ) && check_admin_referer( "save_{$this->prefix}_settings", "{$this->prefix}_save" ) ) {
			// Only do this if button name is 'submit'
			// This allows for more flexibility with
			// having other buttons on a form that should
			// not actually save but should do other stuff
			if ( isset( $_REQUEST['submit'] ) ) {

				$new_settings = $_REQUEST[ "{$this->prefix}_setting" ];

				// Strip Magic Slashes
				$new_settings = stripslashes_deep( $new_settings );

				foreach ( $new_settings as $setting_name => $setting_value ) {
					foreach ( $setting_value as $type => $value ) {
						if ( 'array' === $type ) {
							if ( ! is_array( $value ) && ! empty( $value ) ) {
								$value = (array) explode( $this->delimiter, $value );
							}

							$this->update_setting( $setting_name, $value, false );
						} else {
							$this->update_setting( $setting_name, $value, false );
						}
					}
				}

				// Actually write the changes to the db
				$this->set_settings_obj( $this->settings );
			}

			// Always run this action as long as we had a valid nonce
			do_action( "{$this->prefix}_settings_saved", $new_settings ?? array() );
		}
	}

	public function get_settings_obj() {
		if ( $this->network_only ) {
			return get_site_option( "{$this->prefix}_settings", false );
		}

		return get_option( "{$this->prefix}_settings", false );
	}

	/**
	 * Sets settings object
	 *
	 * @param array $newobj The new settings object
	 * @return boolean True if successful, false otherwise
	 **@author Clifton H. Griffin II
	 * @since 0.1.0
	 *
	 */
	public function set_settings_obj( array $newobj ): bool {
		if ( $this->network_only ) {
			return update_site_option( "{$this->prefix}_settings", $newobj );
		}

		return update_option( "{$this->prefix}_settings", $newobj );
	}
}
