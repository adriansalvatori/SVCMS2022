<?php
/**
 * Override field methods
 *
 * @package    Xirki
 * @subpackage Controls
 * @copyright  Copyright (c) 2019, XplodedThemes (@XplodedThemes)
 * @license     https://opensource.org/licenses/MIT
 * @since      3.0.36
 */

/**
 * Field overrides.
 */
class Xirki_Field_Dropdown_Pages extends Xirki_Field_Select {

	/**
	 * Sets the default value.
	 *
	 * @access protected
	 * @since 3.0.0
	 */
	protected function set_choices() {
		$all_pages = get_pages();
		foreach ( $all_pages as $page ) {
			$this->choices[ $page->ID ] = $page->post_title;
		}
	}
}
