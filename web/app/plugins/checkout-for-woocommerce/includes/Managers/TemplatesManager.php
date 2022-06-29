<?php

namespace Objectiv\Plugins\Checkout\Managers;

use Objectiv\Plugins\Checkout\Template;
use Objectiv\Plugins\Checkout\SingletonAbstract;

/**
 * The templates manager loads the active template
 * as well as provides information on all available templates
 *
 * @deprecated 5.0.0
 * @link checkoutwc.com
 * @since 2.31.0
 * @package Objectiv\Plugins\Checkout\Managers
 * @author Clifton Griffin <clif@checkoutwc.com>
 */

class TemplatesManager extends SingletonAbstract {
	/**
	 * @deprecated 5.0.0
	 * use CFW_PATH_THEME_TEMPLATE constant instead
	 *
	 * @return string
	 */
	public function get_user_template_path(): string {
		return cfw_get_user_template_path();
	}

	/**
	 * @deprecated 5.0.0
	 * @see cfw_get_plugin_template_path
	 * @return string
	 */
	public function get_plugin_template_path(): string {
		return cfw_get_plugin_template_path();
	}

	/**
	 * @deprecated 5.0.0
	 * @see Template::get_all_available()
	 * @return Template[]
	 */
	public function get_available_templates(): array {
		return Template::get_all_available();
	}

	/**
	 * @deprecated 5.0.0
	 * @return Template
	 */
	public function get_active_template(): Template {
		return cfw_get_active_template();
	}
}
