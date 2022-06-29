<?php
/**
 * Override field methods
 *
 * @package     Xirki
 * @subpackage  Controls
 * @copyright   Copyright (c) 2019, XplodedThemes (@XplodedThemes)
 * @license     https://opensource.org/licenses/MIT
 * @since       2.3.2
 */

/**
 * Field overrides.
 */
class Xirki_Field_Sortable extends Xirki_Field {

	/**
	 * Sets the control type.
	 *
	 * @access protected
	 */
	protected function set_type() {
		$this->type = 'xirki-sortable';
	}

	/**
	 * Sets the $sanitize_callback.
	 *
	 * @access protected
	 */
	protected function set_sanitize_callback() {
		$this->sanitize_callback = array( $this, 'sanitize' );
	}

	/**
	 * Sanitizes sortable values.
	 *
	 * @access public
	 * @param array $value The checkbox value.
	 * @return array
	 */
	public function sanitize( $value = array() ) {
		if ( is_string( $value ) || is_numeric( $value ) ) {
			return array(
				sanitize_text_field( $value ),
			);
		}
		$sanitized_value = array();
		foreach ( $value as $sub_value ) {
			if ( isset( $this->choices[ $sub_value ] ) ) {
				$sanitized_value[] = sanitize_text_field( $sub_value );
			}
		}
		return $sanitized_value;
	}
}
