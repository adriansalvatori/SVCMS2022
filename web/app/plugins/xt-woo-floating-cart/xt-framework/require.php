<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Configuration should be loaded first.
require_once dirname( __FILE__ ) . '/config.php';

require_once XTFW_DIR_INCLUDES . '/functions-wp.php';
require_once XTFW_DIR_INCLUDES . '/functions-wp-filters.php';
require_once XTFW_DIR_INCLUDES . '/functions-formatting.php';
require_once XTFW_DIR_INCLUDES . '/functions-helpers.php';
require_once XTFW_DIR_INCLUDES . '/functions-metabox.php';

require_once XTFW_DIR_INCLUDES . '/class-framework.php';
require_once XTFW_DIR_INCLUDES . '/class-cache.php';
require_once XTFW_DIR_INCLUDES . '/class-transient.php';
require_once XTFW_DIR_INCLUDES . '/class-plugin.php';
require_once XTFW_DIR_INCLUDES . '/class-conflicts-check.php';
require_once XTFW_DIR_INCLUDES . '/class-dependencies-check.php';
require_once XTFW_DIR_INCLUDES . '/class-loader.php';
require_once XTFW_DIR_INCLUDES . '/class-base-hooks.php';
require_once XTFW_DIR_INCLUDES . '/class-i18n.php';
require_once XTFW_DIR_INCLUDES . '/class-ajax.php';
require_once XTFW_DIR_INCLUDES . '/class-wc-ajax.php';
require_once XTFW_DIR_INCLUDES . '/class-woocommerce.php';
require_once XTFW_DIR_MODULES . '/class-modules.php';
require_once XTFW_DIR_MODULES . '/class-module.php';
require_once XTFW_DIR_CUSTOMIZER . '/class-customizer.php';
require_once XTFW_DIR_SETTINGS . '/class-settings.php';
require_once XTFW_DIR_NOTICES . '/class-notices.php';

if ( is_admin() ) {
	require_once XTFW_DIR_INCLUDES . '/class-migration.php';
	require_once XTFW_DIR_INCLUDES . '/class-recommended-plugins.php';
	require_once XTFW_DIR_INCLUDES . '/class-admin-messages.php';
	require_once XTFW_DIR_INCLUDES . '/class-system-status.php';
	require_once XTFW_DIR_ADMIN_TABS . '/class-admin-tabs.php';
	require_once XTFW_DIR_ADMIN_TABS . '/class-framework-tabs.php';
	require_once XTFW_DIR_ADMIN_TABS . '/class-plugin-tabs.php';
    require_once XTFW_DIR_INCLUDES . '/class-review-notice.php';
}