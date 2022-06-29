<?php
/**
 * Get User IP
 *
 * Returns the IP address of the current visitor
 *
 * @since 1.0.8.2
 * @return string $ip User's IP address
 */
function ph_status_get_ip() {
	$ip = '127.0.0.1';

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return apply_filters( 'edd_get_ip', $ip );
}

/**
 * Get user host
 *
 * Returns the webhost this site is using if possible
 *
 * @since 2.0
 * @return mixed string $host if detected, false otherwise
 */
function sss_get_host() {
	$host = false;

	if( defined( 'WPE_APIKEY' ) ) {
		$host = 'WP Engine';
	} elseif( defined( 'PAGELYBIN' ) ) {
		$host = 'Pagely';
	} elseif( DB_HOST == 'localhost:/tmp/mysql5.sock' ) {
		$host = 'ICDSoft';
	} elseif( DB_HOST == 'mysqlv5' ) {
		$host = 'NetworkSolutions';
	} elseif( strpos( DB_HOST, 'ipagemysql.com' ) !== false ) {
		$host = 'iPage';
	} elseif( strpos( DB_HOST, 'ipowermysql.com' ) !== false ) {
		$host = 'IPower';
	} elseif( strpos( DB_HOST, '.gridserver.com' ) !== false ) {
		$host = 'MediaTemple Grid';
	} elseif( strpos( DB_HOST, '.pair.com' ) !== false ) {
		$host = 'pair Networks';
	} elseif( strpos( DB_HOST, '.stabletransit.com' ) !== false ) {
		$host = 'Rackspace Cloud';
	} elseif( strpos( DB_HOST, '.sysfix.eu' ) !== false ) {
		$host = 'SysFix.eu Power Hosting';
	} elseif( strpos( $_SERVER['SERVER_NAME'], 'Flywheel' ) !== false ) {
		$host = 'Flywheel';
	} else {
		// Adding a general fallback for data gathering
		$host = 'DBH: ' . DB_HOST . ', SRV: ' . $_SERVER['SERVER_NAME'];
	}

	return $host;
} ?>

ProjectHuddle Info
==================
Version:                  <?php echo esc_html( PH_VERSION ) . "\n"; ?>
PUT Requests:             <?php echo sanitize_text_field( $put ) . "\n"; ?>
PATCH Requests:           <?php echo sanitize_text_field( $patch ) . "\n"; ?>
DELETE Requests:          <?php echo sanitize_text_field( $delete ) . "\n"; ?>
Headers:                  <?php echo sanitize_text_field( $headers ). "\n"; ?>
Script Shielding:         <?php echo get_option( 'ph_script_shielding' ) ? "On\n" : "Off\n"; ?>
Role Silo-ing:            <?php echo get_option( 'ph_un_silo', 'off' ) === 'on' ? "Off\n" : "On\n"; ?>
Session Type: 			  <?php echo defined( 'PH_SESSION_TYPE' ) ? ucfirst( PH_SESSION_TYPE ) : 'Unknown'; echo "\n"; ?>
Cache Directory:          <?php echo function_exists( 'ph_dir_is_empty' ) && ph_dir_is_empty( WP_CONTENT_DIR . '/cache' ) ? 'Empty' : 'Cache Files Detected'; echo "\n"; ?>
Object Caching: 		  <?php echo defined( 'ENABLE_CACHE' ) && true === ENABLE_CACHE ? 'Enabled' : 'Disabled';  echo "\n"; ?>
advanced-cache.php: 	  <?php echo $cache_file_is_file ? 'Yes' : 'No';  echo "\n"; ?>

WordPress Hooks
===============

Redirects
---------
Canonical Redirects : <?php echo has_filter( 'template_redirect', 'redirect_canonical' ) ? 'Good' : 'Disabled: Please enable canonical redirects for ProjectHuddle to work properly.'; ?>


Template Include
----------------
<?php echo $this->hooks_reference('template_include'); ?>

WordPress Environment
=====================
Home URL:                   <?php echo home_url() . "\n"; ?>
Site URL:                   <?php echo site_url() . "\n"; ?>
WP Version:                 <?php echo get_bloginfo( 'version' ) . "\n"; ?>
WP_DEBUG:                   <?php echo defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>
WP Language:                <?php echo ( defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' ) . "\n"; ?>
Multisite:                  <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>
WP Memory Limit:            <?php echo ( $this->let_to_num( WP_MEMORY_LIMIT )/( 1024 ) )."MB"; ?><?php echo "\n"; ?>
WP Memory Limit Status:     <?php if ( $this->let_to_num( ( WP_MEMORY_LIMIT ) )/( 1024 ) > 63) { echo 'OK'. "\n"; } else {echo 'Not OK - Recommended Memory Limit is 64MB'."\n";} ?>
WP Timezone:                <?php echo get_option('timezone_string') . ', GMT: ' . get_option('gmt_offset') . "\n"; ?>
Permalink Structure:        <?php echo get_option( 'permalink_structure' ) . "\n"; ?>
Object Caching: 		    <?php echo defined( 'ENABLE_CACHE' ) && true === ENABLE_CACHE ? 'Enabled' : 'Disabled';  echo "\n"; ?>
Registered Post Statuses:   <?php echo "\n" . implode( ", ", get_post_stati() ) . "\n\n"; ?>
<?php
global $wp_roles;
if ( ! isset( $wp_roles ) ) {
	$wp_roles = new WP_Roles();
}
$roles = $wp_roles->get_names();
?>
Registered Roles:         <?php echo "\n" . implode( ', ', $roles ) . "\n\n"; ?>
Show On Front:            <?php echo get_option( 'show_on_front' ) . "\n" ?>
<?php if( get_option( 'show_on_front' ) == 'page' ) {
	$front_page_id = get_option( 'page_on_front' );
	$blog_page_id = get_option( 'page_for_posts' ); ?>
Page On Front:            <?php ( $front_page_id != 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' ) . "\n"; ?>
Page For Posts:           <?php ( $blog_page_id != 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) . "\n"; ?>
<?php } ?>


Theme Information
=================
<?php $active_theme = wp_get_theme(); ?>
Theme Name:               <?php echo $active_theme->Name . "\n"; ?>
Theme Version:            <?php echo $active_theme->Version . "\n"; ?>
Theme Author:             <?php echo $active_theme->get('Author') . "\n"; ?>
Theme Author URI:         <?php echo $active_theme->get('AuthorURI') . "\n"; ?>
Is Child Theme:           <?php echo is_child_theme() ? 'Yes' . "\n" : 'No' . "\n"; if( is_child_theme() ) { $parent_theme = wp_get_theme( $active_theme->Template ); ?>
Parent Theme:             <?php echo $parent_theme->Name ?>
Parent Theme Version:     <?php echo $parent_theme->Version . "\n"; ?>
Parent Theme URI:         <?php echo $parent_theme->get('ThemeURI') . "\n"; ?>
Parent Theme Author URI:  <?php echo $parent_theme->{'Author URI'} . "\n"; ?>
<?php } ?>

Plugins Information
===================
<?php
$muplugins = wp_get_mu_plugins();
if ( $muplugins && is_array( $muplugins ) && count( $muplugins) > 0 ) {
	echo "\n" . '-- Must-Use Plugins' . "\n\n";

	foreach( $muplugins as $plugin => $plugin_data ) {
		echo $plugin['Name'] . ': ' . $plugin['Version'] . ' ' .$plugin['Author'] .' ' .$plugin['PluginURI'] ."\n";
	}
}

// WordPress active plugins
echo "\n" . '-- WordPress Active Plugins' . "\n\n";
if ( ! function_exists( 'get_plugins' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
$plugins = get_plugins();
$active_plugins = get_option( 'active_plugins', array() );

foreach( $plugins as $plugin_path => $plugin ) {
	if( !in_array( $plugin_path, $active_plugins ) )
		continue;

	echo $plugin['Name'] . ': ' . $plugin['Version'] . ' ' .$plugin['Author'] .' ' .$plugin['PluginURI'] ."\n";
}


// WordPress inactive plugins
echo "\n" . '-- WordPress Inactive Plugins' . "\n\n";

foreach( $plugins as $plugin_path => $plugin ) {
	if( in_array( $plugin_path, $active_plugins ) )
		continue;

	echo $plugin['Name'] . ': ' . $plugin['Version'] . ' ' .$plugin['Author'] .' ' .$plugin['PluginURI'] ."\n";
}

if( is_multisite() ) {
	// WordPress Multisite active plugins
	echo "\n" . '-- Network Active Plugins' . "\n\n";

	$plugins = wp_get_active_network_plugins();
	$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

	foreach( $plugins as $plugin_path ) {
		$plugin_base = plugin_basename( $plugin_path );

		if( !array_key_exists( $plugin_base, $active_plugins ) )
			continue;

		$plugin  = get_plugin_data( $plugin_path );
		echo $plugin['Name'] . ': ' . $plugin['Version'] . ' ' .$plugin['Author'] .' ' .$plugin['PluginURI'] ."\n";
	}
}
?>

Server Environment
==================
Server Info:              <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>
Host:                     <?php echo sss_get_host() . "\n"; ?>
Default Timezone:         <?php echo date_default_timezone_get() . "\n"; ?>
<?php
global $wpdb;
$mysql_ver = 'Unknown';
if ( $wpdb->use_mysqli ) {
	$mysql_ver = @mysqli_get_server_info( $wpdb->dbh );
}
?>
MySQL Version:            <?php echo $mysql_ver . "\n"; ?>

-- PHP Configuration

PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
PHP Post Max Size:        <?php echo ini_get( 'post_max_size' ) . "\n"; ?>
PHP Time Limit:           <?php echo ini_get( 'max_execution_time' ) . "\n"; ?>
PHP Max Input Vars:       <?php echo ini_get( 'max_input_vars' ) . "\n"; ?>
PHP Safe Mode:            <?php echo ini_get( 'safe_mode' ) ? "Yes" : "No\n"; ?>
PHP Memory Limit:         <?php echo ini_get( 'memory_limit' ) . "\n"; ?>
PHP Upload Max Size:      <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Upload Max Filesize:  <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Arg Separator:        <?php echo ini_get( 'arg_separator.output' ) . "\n"; ?>
PHP Allow URL File Open:  <?php echo ini_get( 'allow_url_fopen' ) ? "Yes". "\n" : "No" . "\n"; ?>

-- PHP Extentions

DISPLAY ERRORS:           <?php echo ( ini_get( 'display_errors' ) ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A'; ?><?php echo "\n"; ?>
cURL:                     <?php echo ( function_exists( 'curl_init' ) ) ? 'Your server supports cURL.' : 'Your server does not support cURL.'; ?><?php echo "\n"; ?>
XMLRPC:                   <?php echo ( extension_loaded( 'xmlrpc' ) ) ? 'Your server has XMLRPC installed.' : 'Your server does not have XMLRPC installed.'; ?><?php echo "\n"; ?>
Mod Security:             <?php echo ( extension_loaded( 'mod_security' ) ) ? 'Your server has mod_security installed.' : 'Your server does not have mod_security installed.'; ?><?php echo "\n"; ?>

-- Session Configuration

Session:                  <?php echo isset( $_SESSION ) ? 'Enabled' : 'Disabled'; ?><?php echo "\n"; ?>
Session Name:             <?php echo esc_html( ini_get( 'session.name' ) ); ?><?php echo "\n"; ?>
Cookie Path:              <?php echo esc_html( ini_get( 'session.cookie_path' ) ); ?><?php echo "\n"; ?>
Save Path:                <?php echo esc_html( ini_get( 'session.save_path' ) ); ?><?php echo "\n"; ?>
Use Cookies:              <?php echo ini_get( 'session.use_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>
Use Only Cookies:         <?php echo ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>

-- Client Details:

Client IP Address:        <?php echo ph_status_get_ip() . "\n"; ?>

### End System Status ###
