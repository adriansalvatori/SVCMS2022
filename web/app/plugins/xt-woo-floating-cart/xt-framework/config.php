<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

#--------------------------------------------------------------------------------
#region Directories
#--------------------------------------------------------------------------------

if ( ! defined( 'XTFW_DEBUG' ) ) {
	define( 'XTFW_DEBUG', false );
}
if ( ! defined( 'XTFW_TEMPLATE_DEBUG_MODE' ) ) {
	define( 'XTFW_TEMPLATE_DEBUG_MODE', false );
}
if ( ! defined( 'XTFW_SCRIPT_SUFFIX' ) ) {
	define( 'XTFW_SCRIPT_SUFFIX', ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '-min' );
}
if ( ! defined( 'XTFW_FILE' ) ) {
	define( 'XTFW_FILE', __FILE__ );
}
if ( ! defined( 'XTFW_DIR' ) ) {
	define( 'XTFW_DIR', dirname( XTFW_FILE ) );
}
if ( ! defined( 'XTFW_DIR_ASSETS' ) ) {
	define( 'XTFW_DIR_ASSETS', XTFW_DIR. '/assets' );
}
if ( ! defined( 'XTFW_DIR_INCLUDES' ) ) {
	define( 'XTFW_DIR_INCLUDES', XTFW_DIR . '/includes' );
}
if ( ! defined( 'XTFW_DIR_CUSTOMIZER' ) ) {
	define( 'XTFW_DIR_CUSTOMIZER', XTFW_DIR_INCLUDES . '/customizer' );
}
if ( ! defined( 'XTFW_DIR_CUSTOMIZER_ASSETS' ) ) {
	define( 'XTFW_DIR_CUSTOMIZER_ASSETS', XTFW_DIR_CUSTOMIZER . '/assets' );
}
if ( ! defined( 'XTFW_DIR_ADMIN_TABS' ) ) {
	define( 'XTFW_DIR_ADMIN_TABS', XTFW_DIR_INCLUDES . '/admin-tabs' );
}
if ( ! defined( 'XTFW_DIR_ADMIN_TABS_ASSETS' ) ) {
	define( 'XTFW_DIR_ADMIN_TABS_ASSETS', XTFW_DIR_ADMIN_TABS . '/assets' );
}
if ( ! defined( 'XTFW_DIR_SETTINGS' ) ) {
	define( 'XTFW_DIR_SETTINGS', XTFW_DIR_INCLUDES . '/settings' );
}
if ( ! defined( 'XTFW_DIR_SETTINGS_ASSETS' ) ) {
	define( 'XTFW_DIR_SETTINGS_ASSETS', XTFW_DIR_SETTINGS . '/assets' );
}
if ( ! defined( 'XTFW_DIR_NOTICES' ) ) {
	define( 'XTFW_DIR_NOTICES', XTFW_DIR_INCLUDES . '/notices' );
}
if ( ! defined( 'XTFW_DIR_NOTICES_ASSETS' ) ) {
	define( 'XTFW_DIR_NOTICES_ASSETS', XTFW_DIR_NOTICES . '/assets' );
}
if ( ! defined( 'XTFW_DIR_LICENSE' ) ) {
	define( 'XTFW_DIR_LICENSE', XTFW_DIR_INCLUDES . '/license' );
}
if ( ! defined( 'XTFW_DIR_LICENSE_ASSETS' ) ) {
	define( 'XTFW_DIR_LICENSE_ASSETS', XTFW_DIR_LICENSE . '/assets' );
}
if ( ! defined( 'XTFW_DIR_FREEMIUS' ) ) {
	define( 'XTFW_DIR_FREEMIUS', XTFW_DIR_INCLUDES . '/freemius' );
}
if ( ! defined( 'XTFW_DIR_MODULES' ) ) {
    define( 'XTFW_DIR_MODULES', XTFW_DIR_INCLUDES . '/modules' );
}
if ( ! defined( 'XTFW_GTM_ID' ) ) {
    define( 'XTFW_GTM_ID', 'GTM-MRT34RM' );
}


#endregion	