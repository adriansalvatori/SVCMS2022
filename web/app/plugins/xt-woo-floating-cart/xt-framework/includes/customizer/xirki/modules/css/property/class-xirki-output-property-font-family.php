<?php
/**
 * Handles CSS output for font-family.
 *
 * @package     Xirki
 * @subpackage  Controls
 * @copyright   Copyright (c) 2019, XplodedThemes (@XplodedThemes)
 * @license     https://opensource.org/licenses/MIT
 * @since       2.2.0
 */

/**
 * Output overrides.
 */
class Xirki_Output_Property_Font_Family extends Xirki_Output_Property {

	/**
	 * Modifies the value.
	 *
	 * @access protected
	 */
	protected function process_value() {
		$google_fonts_array = Xirki_Fonts::get_google_fonts();

		$family = $this->value;
		if ( is_array( $this->value ) && isset( $this->value[0] ) && isset( $this->value[1] ) ) {
			$family = $this->value[0];
		}

		// Make sure the value is a string.
		// If not, then early exit.
		if ( ! is_string( $family ) ) {
			return;
		}

		// Hack for standard fonts.
		$family = str_replace( '&quot;', '"', $family );

		// Add double quotes if needed.
		if ( false !== strpos( $family, ' ' ) && false === strpos( $family, '"' ) ) {
			$this->value = '"' . $family . '"';
		}
		$this->value = html_entity_decode( $family, ENT_QUOTES );
	}
}
