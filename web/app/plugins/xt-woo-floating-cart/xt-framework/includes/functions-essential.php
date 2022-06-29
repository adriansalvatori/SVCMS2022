<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * IMPORTANT:
 *      This file will be loaded based on the order of the plugins load.
 *      If there's a plugin using XT Framework, the plugin's essential
 *      file will always load first.
 *
 * @package     XT_Framework
 * @copyright   Copyright (c) 2019, XplodedThemes, Inc.
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License Version 3
 * @since       1.0.0
 */


#region Core Redirect (copied from BuddyPress) -----------------------------------------

if (!function_exists('xtfw_redirect')) {
    /**
     * Redirects to another page, with a workaround for the IIS Set-Cookie bug.
     *
     * @link  http://support.microsoft.com/kb/q176113/
     * @since 1.5.1
     * @uses  apply_filters() Calls 'wp_redirect' hook on $location and $status.
     *
     * @param string $location The path to redirect to.
     * @param bool $exit If true, exit after redirect (Since 1.2.1.5).
     * @param int $status Status code to use.
     *
     * @return bool False if $location is not set
     */
    function xtfw_redirect($location, $exit = true, $status = 302)
    {
        global $is_IIS;

        $file = '';
        $line = '';
        if (headers_sent($file, $line)) {
            if (XTFW_DEBUG && class_exists('XT_Framework_Admin_Notices')) {
                $notices = new XT_Framework_Admin_Notices();

                $notices->add_error("XT Framework failed to redirect the page because the headers have been already sent from line <b><code>{$line}</code></b> in file <b><code>{$file}</code></b>. If it's unexpected, it usually happens due to invalid space and/or EOL character(s).");
            }

            return false;
        }

        if (defined('DOING_AJAX')) {
            // Don't redirect on AJAX calls.
            return false;
        }

        if (!$location) // allows the wp_redirect filter to cancel a redirect
        {
            return false;
        }

        $location = xtfw_sanitize_redirect($location);

        if ($is_IIS) {
            header("Refresh: 0;url=$location");
        } else {
            if (php_sapi_name() != 'cgi-fcgi') {
                status_header($status);
            } // This causes problems on IIS and some FastCGI setups
            header("Location: $location");
        }

        if ($exit) {
            exit();
        }

        return true;
    }

    if (!function_exists('xtfw_sanitize_redirect')) {
        /**
         * Sanitizes a URL for use in a redirect.
         *
         * @param string $location
         *
         * @return string redirect-sanitized URL
         * @since 2.3
         *
         */
        function xtfw_sanitize_redirect($location)
        {
            $location = preg_replace('|[^a-z0-9-~+_.?#=&;,/:%!]|i', '', $location);
            $location = xtfw_kses_no_null($location);

            // remove %0d and %0a from location
            $strip = array('%0d', '%0a');
            $found = true;
            while ($found) {
                $found = false;
                foreach ((array)$strip as $val) {
                    while (strpos($location, $val) !== false) {
                        $found = true;
                        $location = str_replace($val, '', $location);
                    }
                }
            }

            return $location;
        }
    }

    if (!function_exists('xtfw_kses_no_null')) {
        /**
         * Removes any NULL characters in $string.
         *
         * @param string $string
         *
         * @return string
         * @since 1.0.0
         *
         */
        function xtfw_kses_no_null($string)
        {
            $string = preg_replace('/\0+/', '', $string);
            $string = preg_replace('/(\\\\0)+/', '', $string);

            return $string;
        }
    }
}

#endregion Core Redirect (copied from BuddyPress) -----------------------------------------


if (!function_exists('xtfw_dir_url')) {
    /**
     * Generates an absolute URL to the given path. This function ensures that the URL will be correct whether the dir
     * is inside a plugin's folder.
     *
     * @param string $dir_abs_path Dir's absolute path.
     *
     * @return string Dir's URL.
     * @author XplodedThemes
     * @since  1.0.0
     *
     */
    function xtfw_dir_url($dir_abs_path)
    {
        $wp_content_dir = xtfw_normalize_path(WP_CONTENT_DIR);
        $dir_abs_path = xtfw_normalize_path($dir_abs_path);

        if (0 === strpos($dir_abs_path, $wp_content_dir)) {

            // Handle plugin dirs located in the standard directories.
            $dir_rel_path = str_replace($wp_content_dir, '', $dir_abs_path);
            $dir_url = content_url(xtfw_normalize_path($dir_rel_path));

        } else {
            $wp_plugins_dir = xtfw_normalize_path(WP_PLUGIN_DIR);

            // Try to handle plugin dirs that may be located in a non-standard plugins directory.
            $dir_rel_path = str_replace($wp_plugins_dir, '', $dir_abs_path);
            $dir_url = plugins_url(xtfw_normalize_path($dir_rel_path));

        }

        return $dir_url;
    }
}

if (!function_exists('xtfw_get_plugins')) {
    /**
     * @param bool $delete_cache
     *
     * @return array
     * @author XplodedThemes
     * @since 1.0.0
     *
     */
    function xtfw_get_plugins($delete_cache = false)
    {
        $cached_plugins = wp_cache_get('plugins', 'plugins');
        if (!is_array($cached_plugins)) {
            $cached_plugins = array();
        }

        $plugin_folder = '';
        if (isset($cached_plugins[$plugin_folder])) {
            $plugins = $cached_plugins[$plugin_folder];
        } else {
            if (!function_exists('get_plugins')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            $plugins = get_plugins();

            if ($delete_cache && is_plugin_active('woocommerce/woocommerce.php')) {
                wp_cache_delete('plugins', 'plugins');
            }
        }

        return $plugins;
    }
}

if (!function_exists('xtfw_normalize_path')) {
    if (function_exists('wp_normalize_path')) {
        /**
         * Normalize a filesystem path.
         *
         * Replaces backslashes with forward slashes for Windows systems, and ensures
         * no duplicate slashes exist.
         *
         * @param string $path Path to normalize.
         *
         * @return string Normalized path.
         */
        function xtfw_normalize_path($path)
        {
            return wp_normalize_path($path);
        }
    } else {
        function xtfw_normalize_path($path)
        {
            $path = str_replace('\\', '/', $path);
            $path = preg_replace('|/+|', '/', $path);

            return $path;
        }
    }
}


if (!function_exists('xtfw_get_plugins')) {
    /**
     * @param bool $delete_cache
     *
     * @return array
     * @author XplodedThemes
     * @since 1.0.0
     *
     */
    function xtfw_get_plugins($delete_cache = false)
    {
        $cached_plugins = wp_cache_get('plugins', 'plugins');
        if (!is_array($cached_plugins)) {
            $cached_plugins = array();
        }

        $plugin_folder = '';
        if (isset($cached_plugins[$plugin_folder])) {
            $plugins = $cached_plugins[$plugin_folder];
        } else {
            if (!function_exists('get_plugins')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            $plugins = get_plugins();

            if ($delete_cache && is_plugin_active('woocommerce/woocommerce.php')) {
                wp_cache_delete('plugins', 'plugins');
            }
        }

        return $plugins;
    }
}

#endregion Core Redirect (copied from BuddyPress) -----------------------------------------
/**
 * Leverage backtrace to find caller plugin main file path.
 *
 * @return string
 * @since  1.0.0
 *
 * @author XplodedThemes
 */
function xtfw_find_caller_plugin_file()
{
    /**
     * All the code below will be executed once on activation.
     * If the user changes the main plugin's file name, the file_exists()
     * will catch it.
     */
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $all_plugins = xtfw_get_plugins(true);
    $all_plugins_paths = array();

    // Get active plugin's main files real full names (might be symlinks).
    foreach ($all_plugins as $relative_path => $data) {
        $all_plugins_paths[] = xtfw_normalize_path(realpath(WP_PLUGIN_DIR . '/' . $relative_path));
    }

    $plugin_file = null;
    for ($i = 1, $bt = debug_backtrace(), $len = count($bt); $i < $len; $i++) {
        if (empty($bt[$i]['file'])) {
            continue;
        }

        if (in_array(xtfw_normalize_path($bt[$i]['file']), $all_plugins_paths)) {
            $plugin_file = $bt[$i]['file'];
            break;
        }
    }

    if (is_null($plugin_file)) {
        // Throw an error to the developer in case of some edge case dev environment.
        wp_die(
            'XT FrameWork couldn\'t find the plugin\'s main file. Please contact support@xplodedthemes.com with the current error.',
            'Error',
            array('back_link' => true)
        );
    }

    return $plugin_file;
}

/**
 * Find the plugin main file path based on any given file inside the plugin's folder.
 *
 * @param string $file Absolute path to a file inside a plugin's folder.
 *
 * @return string
 * @author Vova Feldman (@svovaf)
 * @since  1.1.7.1
 *
 */
function xtfw_find_direct_caller_plugin_file($file)
{
    /**
     * All the code below will be executed once on activation.
     * If the user changes the main plugin's file name, the file_exists()
     * will catch it.
     */
    $all_plugins = xtfw_get_plugins(true);

    $file_real_path = xtfw_normalize_path(realpath($file));

    // Get active plugin's main files real full names (might be symlinks).
    foreach ($all_plugins as $relative_path => $data) {
        if (0 === strpos($file_real_path, xtfw_normalize_path(dirname(realpath(WP_PLUGIN_DIR . '/' . $relative_path))))) {
            if ('.' !== dirname(trailingslashit($relative_path))) {
                return $relative_path;
            }
        }
    }

    return null;
}

/**
 * Update SDK newest version reference.
 *
 * @param string $fw_relative_path
 * @param string|bool $plugin_file
 *
 * @author XplodedThemes
 * @since  1.0.0
 *
 * @global            $xtfw_active_plugins
 */
function xtfw_update_fw_newest_version($fw_relative_path, $plugin_file = false)
{

    global $xtfw_active_plugins;

    $newest_fw = $xtfw_active_plugins->plugins[$fw_relative_path];

    if (!is_string($plugin_file)) {
        $plugin_file = plugin_basename(xtfw_find_caller_plugin_file());
    }

    $in_activation = function_exists('is_plugin_active') && (!is_plugin_active($plugin_file));

    $xtfw_active_plugins->newest = (object)array(
        'plugin_path' => $plugin_file,
        'fw_path' => $fw_relative_path,
        'version' => $newest_fw->version,
        'in_activation' => $in_activation,
        'timestamp' => time(),
    );

    // Update DB with latest SDK version and path.
    update_option('xtfw_active_plugins', $xtfw_active_plugins);
}

/**
 * Reorder the plugins load order so the plugin with the newest Freemius SDK is loaded first.
 *
 * @return bool Was plugin order changed. Return false if plugin was loaded first anyways.
 *
 * @since  1.0.0
 *
 * @author XplodedThemes
 * @global $xtfw_active_plugins
 */
function xtfw_newest_fw_plugin_first()
{

    global $xtfw_active_plugins;

    /**
     * @todo Multi-site network activated plugin are always loaded prior to site plugins so if there's a plugin activated in the network mode that has an older version of the SDK of another plugin which is site activated that has new SDK version, the fs-essential-functions.php will be loaded from the older SDK. Same thing about MU plugins (loaded even before network activated plugins).
     *
     * @link https://github.com/Freemius/wordpress-sdk/issues/26
     */

    $newest_fw_plugin_path = $xtfw_active_plugins->newest->plugin_path;

    $active_plugins = get_option('active_plugins', array());
    $updated_active_plugins = array($newest_fw_plugin_path);

    $plugin_found = false;
    $is_first_path = true;

    foreach ($active_plugins as $key => $plugin_path) {
        if ($plugin_path === $newest_fw_plugin_path) {
            if ($is_first_path) {
                // if it's the first plugin already, no need to continue
                return false;
            }

            $plugin_found = true;

            // Skip the plugin (it is already added as the 1st item of $updated_active_plugins).
            continue;
        }

        $updated_active_plugins[] = $plugin_path;

        if ($is_first_path) {
            $is_first_path = false;
        }
    }

    if ($plugin_found) {
        update_option('active_plugins', $updated_active_plugins);

        return true;
    }

    if (is_multisite()) {
        // Plugin is network active.
        $network_active_plugins = get_site_option('active_sitewide_plugins', array());

        if (isset($network_active_plugins[$newest_fw_plugin_path])) {
            reset($network_active_plugins);
            if ($newest_fw_plugin_path === key($network_active_plugins)) {
                // Plugin is already activated first on the network level.
                return false;
            } else {
                $time = $network_active_plugins[$newest_fw_plugin_path];

                // Remove plugin from its current position.
                unset($network_active_plugins[$newest_fw_plugin_path]);

                // Set it to be included first.
                $network_active_plugins = array($newest_fw_plugin_path => $time) + $network_active_plugins;

                update_site_option('active_sitewide_plugins', $network_active_plugins);

                return true;
            }
        }
    }

    return false;
}

/**
 * Go over all Freemius SDKs in the system and find and "remember"
 * the newest SDK which is associated with an active plugin.
 *
 * @author XplodedThemes
 * @since  1.0.0
 *
 * @global $xtfw_active_plugins
 */
function xtfw_fallback_to_newest_active_fw()
{
    global $xtfw_active_plugins;

    /**
     * @var object $newest_fw_data
     */
    $newest_fw_data = null;
    $newest_fw_path = null;

    foreach ($xtfw_active_plugins->plugins as $fw_relative_path => $data) {
        if (is_null($newest_fw_data) || version_compare($data->version, $newest_fw_data->version, '>')
        ) {
            // If plugin inactive or SDK starter file doesn't exist, remove SDK reference.

            $is_module_active = is_plugin_active($data->plugin_path);

            $is_fw_exists = file_exists(xtfw_normalize_path(WP_PLUGIN_DIR . '/' . $fw_relative_path . '/start.php'));

            if (!$is_module_active || !$is_fw_exists) {
                unset($xtfw_active_plugins->plugins[$fw_relative_path]);

                // No need to store the data since it will be stored in xtfw_update_fw_newest_version()
                // or explicitly with update_option().
            } else {
                $newest_fw_data = $data;
                $newest_fw_path = $fw_relative_path;
            }
        }
    }

    if (is_null($newest_fw_data)) {
        // Couldn't find any SDK reference.
        $xtfw_active_plugins = new stdClass();
        update_option('xtfw_active_plugins', $xtfw_active_plugins);
    } else {
        xtfw_update_fw_newest_version($newest_fw_path, $newest_fw_data->plugin_path);
    }
}

/**
 * Queue some JavaScript code to be output in the footer.
 *
 * @param string $code Code.
 */
function xtfw_enqueue_js($code)
{

    global $xtfw_queued_js;

    if (empty($xtfw_queued_js)) {
        $xtfw_queued_js = '';
    }

    $xtfw_queued_js .= "\n" . $code . "\n";
}

/**
 * Output any queued javascript code in the footer.
 */
function xtfw_print_queued_js()
{

    global $xtfw_queued_js;

    if (!empty($xtfw_queued_js)) {

        // Sanitize.
        $xtfw_queued_js = wp_check_invalid_utf8($xtfw_queued_js);
        $xtfw_queued_js = preg_replace('/&#(x)?0*(?(1)27|39);?/i', "'", $xtfw_queued_js);
        $xtfw_queued_js = str_replace("\r", '', $xtfw_queued_js);

        $js = "<!-- XT Framework JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) { $xtfw_queued_js });\n</script>\n";

        /**
         * Queued jsfilter.
         *
         * @param string $js JavaScript code.
         *
         * @since 21.0.0
         */
        echo apply_filters('xtfw_queued_js', $js);
    }
}

add_action('admin_footer', 'xtfw_print_queued_js', 99999);
add_action('wp_footer', 'xtfw_print_queued_js', 99999);
