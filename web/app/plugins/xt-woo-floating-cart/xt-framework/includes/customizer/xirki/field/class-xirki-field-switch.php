<?php
/**
 * Override field methods
 *
 * @package     Xirki
 * @subpackage  Controls
 * @copyright   Copyright (c) 2019, XplodedThemes (@XplodedThemes)
 * @license     https://opensource.org/licenses/MIT
 * @since       2.2.7
 */

/**
 * Field overrides.
 */
class Xirki_Field_Switch extends Xirki_Field_Checkbox {

	/**
	 * Sets the control type.
	 *
	 * @access protected
	 */
	protected function set_type() {
		$this->type = 'xirki-switch';
	}

	/**
	 * Sets the control choices.
	 *
	 * @access protected
	 */
	protected function set_choices() {
		if ( ! is_array( $this->choices ) ) {
			$this->choices = array();
		}

		$this->choices = wp_parse_args(
			$this->choices,
			array(
				'on'    => esc_html__( 'On', 'xirki' ),
				'off'   => esc_html__( 'Off', 'xirki' ),
				'round' => false,
			)
		);
	}
}
