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
 * This is nothing more than an alias for the Xirki_Field_Select class.
 * In older versions of Xirki there was a separate 'select2' field.
 * This exists here just for compatibility purposes.
 */
class Xirki_Field_Select2_Multiple extends Xirki_Field_Select {

	/**
	 * Sets the $multiple
	 *
	 * @access protected
	 */
	protected function set_multiple() {
		$this->multiple = 999;
	}
}
