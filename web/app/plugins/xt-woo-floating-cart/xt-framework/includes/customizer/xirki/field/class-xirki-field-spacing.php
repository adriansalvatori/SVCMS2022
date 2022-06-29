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
class Xirki_Field_Spacing extends Xirki_Field_Dimensions {

	/**
	 * Set the choices.
	 * Adds a pseudo-element "controls" that helps with the JS API.
	 *
	 * @access protected
	 */
	protected function set_choices() {
		$default_args = array(
			'controls' => array(
				'top'    => ( isset( $this->default['top'] ) ),
				'bottom' => ( isset( $this->default['top'] ) ),
				'left'   => ( isset( $this->default['top'] ) ),
				'right'  => ( isset( $this->default['top'] ) ),
			),
			'labels'   => array(
				'top'    => esc_html__( 'Top', 'xirki' ),
				'bottom' => esc_html__( 'Bottom', 'xirki' ),
				'left'   => esc_html__( 'Left', 'xirki' ),
				'right'  => esc_html__( 'Right', 'xirki' ),
			),
		);

		$this->choices = wp_parse_args( $this->choices, $default_args );
	}
}
